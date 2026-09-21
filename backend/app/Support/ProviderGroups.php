<?php

namespace App\Support;

use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ProviderGroup;
use App\Models\ServiceProvider\ServicePaymentTransaction;
use App\Models\ServiceProvider\ProviderGroupActionLock;
use App\Models\ServiceProvider\ProviderGroupDealAllocation;
use App\Models\ServiceProvider\ProviderGroupMember;
use App\Models\ServiceProvider\ProviderGroupServiceApproval;
use App\Models\ServiceProvider\ProviderGroupShareApproval;
use App\Models\ServiceProvider\ProviderGroupShareRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Shared logic for service-provider GROUPS (teams).
 *
 * A group is a set of service providers who:
 *  - publish group services that every member must approve first,
 *  - act together on client requests (one action at a time via locks),
 *  - and, for NON-daily jobs, agree a revenue split where every member
 *    proposes the percentage they keep and the others must approve it.
 */
class ProviderGroups
{
    /** How long a one-at-a-time action lock lives before it auto-releases. */
    public const LOCK_TTL_MINUTES = 10;

    // ────────────────────────────────────────────────────────────
    // Membership helpers
    // ────────────────────────────────────────────────────────────

    public static function isMember(int $userId, int $groupId): bool
    {
        return ProviderGroupMember::where('group_id', $groupId)
            ->where('member_id', $userId)
            ->where('status', 'accepted')
            ->exists();
    }

    public static function isLeader(int $userId, int $groupId): bool
    {
        return ProviderGroupMember::where('group_id', $groupId)
            ->where('member_id', $userId)
            ->where('role', 'leader')
            ->where('status', 'accepted')
            ->exists();
    }

    /** Group ids the user is an ACCEPTED member of. */
    public static function acceptedGroupIds(int $userId): array
    {
        return ProviderGroupMember::where('member_id', $userId)
            ->where('status', 'accepted')
            ->pluck('group_id')
            ->all();
    }

    /** Accepted member user-ids of a group (leader included). */
    public static function acceptedMemberIds(int $groupId): array
    {
        return ProviderGroupMember::where('group_id', $groupId)
            ->where('status', 'accepted')
            ->pluck('member_id')
            ->all();
    }

    public static function memberName(int $userId): string
    {
        $u = User::find($userId);
        if (!$u) return 'N/A';
        return trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?? 'Provider');
    }

    public static function groupPayload(int $groupId): ?array
    {
        $group = ProviderGroup::with('leader')->find($groupId);
        if (!$group) return null;

        $members = ProviderGroupMember::with('member')
            ->where('group_id', $groupId)
            ->where('status', 'accepted')
            ->get()
            ->map(fn($m) => [
                'id' => $m->member_id,
                'name' => self::memberName($m->member_id),
                'role' => $m->role,
            ])->values();

        return [
            'id' => $group->id,
            'group_name' => $group->group_name,
            'description' => $group->description,
            'leader_id' => $group->leader_id,
            'leader_name' => self::memberName($group->leader_id),
            'member_count' => $members->count(),
            'members' => $members,
        ];
    }

    // ────────────────────────────────────────────────────────────
    // ONE-AT-A-TIME ACTION LOCKS
    // ────────────────────────────────────────────────────────────

    public static function activeLock(string $entityType, int $entityId, string $action): ?ProviderGroupActionLock
    {
        // Garbage-collect expired locks for this entity+action each read.
        ProviderGroupActionLock::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('action', $action)
            ->where('locked_at', '<', now()->subMinutes(self::LOCK_TTL_MINUTES))
            ->delete();

        return ProviderGroupActionLock::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('action', $action)
            ->first();
    }

    /**
     * @return array{acquired: bool, lock?: array, locker?: array}
     */
    public static function acquireLock(int $userId, string $entityType, int $entityId, string $action, ?int $groupId = null): array
    {
        $existing = self::activeLock($entityType, $entityId, $action);

        if ($existing && (int) $existing->member_id !== $userId) {
            return [
                'acquired' => false,
                'locker' => [
                    'member_id' => $existing->member_id,
                    'member_name' => self::memberName((int) $existing->member_id),
                    'locked_at' => $existing->locked_at?->toIso8601String(),
                    'expires_at' => $existing->locked_at?->copy()->addMinutes(self::LOCK_TTL_MINUTES)->toIso8601String(),
                ],
            ];
        }

        $lock = ProviderGroupActionLock::updateOrCreate(
            ['entity_type' => $entityType, 'entity_id' => $entityId, 'action' => $action],
            ['group_id' => $groupId, 'member_id' => $userId, 'locked_at' => now()]
        );

        return [
            'acquired' => true,
            'lock' => [
                'id' => $lock->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'member_id' => $userId,
                'member_name' => self::memberName($userId),
                'locked_at' => $lock->locked_at?->toIso8601String(),
                'expires_at' => $lock->locked_at?->copy()->addMinutes(self::LOCK_TTL_MINUTES)->toIso8601String(),
            ],
        ];
    }

    public static function releaseLock(int $userId, string $entityType, int $entityId, string $action): void
    {
        ProviderGroupActionLock::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('action', $action)
            ->where('member_id', $userId)
            ->delete();
    }

    /**
     * Read-only lock display (does not acquire). Returns a serializable
     * lock payload or null when nobody holds an active lock.
     */
    public static function acquireLockCheckOnly(string $entityType, int $entityId, string $action, int $viewerId): ?array
    {
        $existing = self::activeLock($entityType, $entityId, $action);
        if (!$existing) return null;

        return [
            'id' => $existing->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'member_id' => (int) $existing->member_id,
            'member_name' => self::memberName((int) $existing->member_id),
            'locked_at' => $existing->locked_at?->toIso8601String(),
            'expires_at' => $existing->locked_at?->copy()->addMinutes(self::LOCK_TTL_MINUTES)->toIso8601String(),
            'is_mine' => (int) $existing->member_id === $viewerId,
        ];
    }

    /**
     * All active locks attached to a service request (request + deal +
     * latest payment term entities) enriched with member names + is_mine.
     */
    public static function locksForRequest(int $requestId, int $viewerId): array
    {
        $deal = OfficialDeal::where('client_service_request_id', $requestId)->latest()->first();
        $term = $deal
            ? \App\Models\ServiceProvider\OfficialPaymentTerm::where('official_deal_id', $deal->id)->where('is_materials_term', false)->latest()->first()
            : null;

        $entities = [['client_service_request', $requestId]];
        if ($deal) $entities[] = ['official_deal', $deal->id];
        if ($term) $entities[] = ['official_payment_term', $term->id];

        $locks = collect();
        foreach ($entities as [$type, $id]) {
            ProviderGroupActionLock::where('entity_type', $type)
                ->where('entity_id', $id)
                ->where('locked_at', '<', now()->subMinutes(self::LOCK_TTL_MINUTES))
                ->delete();

            foreach (ProviderGroupActionLock::where('entity_type', $type)->where('entity_id', $id)->get() as $l) {
                $locks->push([
                    'id' => $l->id,
                    'entity_type' => $type,
                    'entity_id' => $l->entity_id,
                    'action' => $l->action,
                    'member_id' => $l->member_id,
                    'member_name' => self::memberName((int) $l->member_id),
                    'locked_at' => $l->locked_at?->toIso8601String(),
                    'expires_at' => $l->locked_at?->copy()->addMinutes(self::LOCK_TTL_MINUTES)->toIso8601String(),
                    'is_mine' => (int) $l->member_id === $viewerId,
                ]);
            }
        }
        return $locks->values()->all();
    }

    // ────────────────────────────────────────────────────────────
    // GROUP SERVICE CREATION APPROVAL WORKFLOW
    // ────────────────────────────────────────────────────────────

    public static function createServiceApprovals(int $serviceId, int $groupId, int $creatorId): void
    {
        foreach (array_diff(self::acceptedMemberIds($groupId), [$creatorId]) as $memberId) {
            ProviderGroupServiceApproval::firstOrCreate(
                ['service_offering_id' => $serviceId, 'member_id' => $memberId],
                ['group_id' => $groupId, 'status' => 'pending']
            );
        }
    }

    public static function serviceApprovalState(int $serviceId, int $groupId, int $viewerId): array
    {
        $rows = ProviderGroupServiceApproval::with('member')
            ->where('service_offering_id', $serviceId)
            ->where('group_id', $groupId)
            ->get();

        $approvals = $rows->map(fn($a) => [
            'member_id' => (int) $a->member_id,
            'member_name' => self::memberName((int) $a->member_id),
            'status' => $a->status,
            'rejection_reason' => $a->rejection_reason,
            'decided_at' => $a->decided_at?->toIso8601String(),
        ])->values();

        $total = $rows->count();
        $approved = $rows->where('status', 'approved')->count();
        $rejected = $rows->where('status', 'rejected')->count();

        $status = 'pending';
        if ($total > 0 && $approved === $total) $status = 'approved';
        if ($rejected > 0) $status = 'rejected';

        $my = $rows->firstWhere('member_id', $viewerId);

        return [
            'approvals' => $approvals,
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'status' => $status,
            'my_approval' => $my ? [
                'status' => $my->status,
                'rejection_reason' => $my->rejection_reason,
            ] : null,
        ];
    }

    /** Publish the service once every member has approved it. Returns true when published. */
    public static function publishIfAllApproved(int $serviceId): bool
    {
        $service = \App\Models\ServiceProvider\ServiceOffering::find($serviceId);
        if (!$service || !$service->group_id) return false;

        $rows = ProviderGroupServiceApproval::where('service_offering_id', $serviceId)->get();
        if ($rows->count() === 0) return false;
        if ($rows->where('status', 'approved')->count() !== $rows->count()) return false;

        $service->update(['is_published' => true, 'is_active' => true]);
        return true;
    }

    // ────────────────────────────────────────────────────────────
    // REVENUE SPLIT (non-daily group jobs)
    // ────────────────────────────────────────────────────────────

    public static function isSplitRequired(int $dealId): bool
    {
        $deal = OfficialDeal::find($dealId);
        if (!$deal || !$deal->group_id) return false;

        $offering = $deal->serviceOffering;
        // Daily jobs pay every member the same daily rate — no split needed.
        return $offering && $offering->price_type !== 'Daily';
    }

    /**
     * Remove every proposal, approval and allocation for a deal's split,
     * resetting it to the untouched state. Used when proposals exceed 100%.
     */
    public static function revokeSplit(int $dealId): void
    {
        DB::transaction(function () use ($dealId) {
            $ids = ProviderGroupShareRequest::where('official_deal_id', $dealId)->pluck('id');
            ProviderGroupShareApproval::whereIn('share_request_id', $ids)->delete();
            ProviderGroupShareRequest::where('official_deal_id', $dealId)->delete();
            ProviderGroupDealAllocation::where('official_deal_id', $dealId)->delete();
        });
    }

    /** @return array{status: string, proposals: array, allocations: array, total_proposed: float, my_proposal: ?array} */
    public static function splitState(int $dealId, int $viewerId): array
    {
        $deal = OfficialDeal::with('serviceOffering')->find($dealId);
        $groupId = $deal?->group_id;

        $proposalsRaw = ProviderGroupShareRequest::with('approvals.member')
            ->where('official_deal_id', $dealId)
            ->orderBy('id', 'asc')
            ->get();

        $proposals = $proposalsRaw->map(function ($p) {
            $approvals = $p->approvals->map(fn($a) => [
                'member_id' => (int) $a->member_id,
                'member_name' => self::memberName((int) $a->member_id),
                'status' => $a->status,
                'reason' => $a->reason,
                'decided_at' => $a->decided_at?->toIso8601String(),
            ])->values();

            return [
                'id' => $p->id,
                'member_id' => (int) $p->member_id,
                'member_name' => self::memberName((int) $p->member_id),
                'percentage' => (float) $p->percentage,
                'status' => $p->status,
                'rejection_reason' => $p->rejection_reason,
                'approvals' => $approvals,
            ];
        })->values();

        $allocations = ProviderGroupDealAllocation::where('official_deal_id', $dealId)->get()
            ->map(fn($a) => [
                'member_id' => (int) $a->member_id,
                'member_name' => self::memberName((int) $a->member_id),
                'percentage' => (float) $a->percentage,
            ])->sortBy('member_id')->values();

        $approvedProposals = $proposalsRaw->where('status', 'approved');
        $totalProposed = (float) $approvedProposals->sum('percentage');

        $my = $proposalsRaw->where('member_id', $viewerId)->first();

        // ── STRICT 100% RULE ──────────────────────────────────────
        // If the approved proposals already total more than 100% and no
        // allocation has been locked, the team's split is invalid.
        // Auto-revoke so they start from a clean slate.
        if ($allocations->count() === 0 && $totalProposed > 100.001) {
            self::revokeSplit($dealId);
            return self::splitState($dealId, $viewerId);
        }

        return [
            'deal_id' => $dealId,
            'group_id' => $groupId,
            'status' => $allocations->count() ? 'locked' : ($proposalsRaw->count() === 0 ? 'none' : 'in_progress'),
            'proposals' => $proposals,
            'allocations' => $allocations,
            'total_proposed' => round($totalProposed, 2),
            'my_proposal' => $my ? [
                'id' => $my->id,
                'percentage' => (float) $my->percentage,
                'status' => $my->status,
                'rejection_reason' => $my->rejection_reason,
            ] : null,
        ];
    }

    /**
     * When a proposal is fully approved, check if every accepted member has
     * an approved proposal and whether the percentages total 100%. If so the
     * split is LOCKED for the deal (allocations created).
     */
    public static function checkAndLockSplit(int $dealId): array
    {
        $deal = OfficialDeal::find($dealId);
        if (!$deal || !$deal->group_id) return ['locked' => false, 'reason' => 'no group'];

        $memberIds = self::acceptedMemberIds((int) $deal->group_id);
        $approved = ProviderGroupShareRequest::where('official_deal_id', $dealId)
            ->where('status', 'approved')
            ->get();

        // Every member must have an approved proposal.
        $approvedMemberIds = $approved->pluck('member_id')->map(fn($id) => (int) $id)->all();
        $missing = array_values(array_diff($memberIds, $approvedMemberIds));
        if (count($missing) > 0) {
            return ['locked' => false, 'reason' => 'pending_members', 'missing' => $missing];
        }

        $total = (float) $approved->sum('percentage');
        if (abs($total - 100.0) > 0.009) {
            return ['locked' => false, 'reason' => 'total_not_100', 'total' => $total];
        }

        // Lock it in.
        DB::transaction(function () use ($deal, $approved) {
            ProviderGroupDealAllocation::where('official_deal_id', $deal->id)->delete();
            foreach ($approved as $p) {
                ProviderGroupDealAllocation::create([
                    'official_deal_id' => $deal->id,
                    'group_id' => $deal->group_id,
                    'member_id' => $p->member_id,
                    'percentage' => $p->percentage,
                ]);
            }
        });

        return ['locked' => true, 'total' => $total];
    }

    /**
     * Attach everything the dashboards need for a group job directly onto
     * the serialized request object.
     */
    public static function attachToJob(&$req, string $baseUrl, int $viewerId): void
    {
        $req->is_group = false;
        $req->viewer_id = $viewerId;
        $req->group = null;
        $req->group_locks = [];
        $req->split = null;
        $req->group_can_act = false;

        if (!$req->group_id) return;

        $groupId = (int) $req->group_id;
        $req->is_group = true;
        $req->group = self::groupPayload($groupId);
        $req->group_can_act = self::isMember($viewerId, $groupId);
        $req->group_locks = self::locksForRequest((int) $req->id, $viewerId);

        $deal = $req->official_deal;
        if ($deal && (int) $deal->group_id === $groupId && self::isSplitRequired((int) $deal->id)) {
            $req->split = self::splitState((int) $deal->id, $viewerId);
        }

        $req->revenue_summary = self::revenueSummary((int) $req->id, $viewerId);
    }

    /**
     * Post-job revenue breakdown for a GROUP job: how much the team earned
     * in total and what each member's share is.
     *
     * - Daily jobs: the deal daily-rate × billable days, split equally among
     *   all accepted members.
     * - Non-daily jobs: the locked allocations apply; while the split is
     *   still being negotiated the approved proposals are shown instead.
     */
    public static function revenueSummary(int $requestId, int $viewerId): ?array
    {
        $req = ClientServiceRequest::find($requestId);
        if (!$req || !$req->group_id) return null;

        $deal = OfficialDeal::with('serviceOffering')
            ->where('client_service_request_id', $requestId)
            ->latest()
            ->first();
        if (!$deal) return null;

        $groupId = (int) $req->group_id;
        $memberIds = self::acceptedMemberIds($groupId);
        $isDaily = $deal->serviceOffering && $deal->serviceOffering->price_type === 'Daily';

        $mainTerm = OfficialPaymentTerm::where('official_deal_id', $deal->id)
            ->where('is_materials_term', false)
            ->latest()
            ->first();

        // Team figures (what the client actually owes / paid for the service).
        if ($isDaily && $mainTerm) {
            $snapshot = DailyBilling::snapshot($req, $deal, $mainTerm);
            $teamEarned = (float) ($snapshot['total_due'] ?? $deal->price);
            $paidTotal  = (float) ($snapshot['total_paid'] ?? 0);
        } else {
            $teamEarned = (float) $deal->price;
            $serviceTermIds = OfficialPaymentTerm::where('official_deal_id', $deal->id)
                ->where('is_materials_term', false)
                ->pluck('id');
            $paidTotal = (float) ServicePaymentTransaction::whereIn('payment_term_id', $serviceTermIds)
                ->where('status', 'completed')
                ->sum('amount');
        }

        $materialsTermIds = OfficialPaymentTerm::where('official_deal_id', $deal->id)
            ->where('is_materials_term', true)
            ->pluck('id');
        $materialsPaid = (float) ServicePaymentTransaction::whereIn('payment_term_id', $materialsTermIds)
            ->where('status', 'completed')
            ->sum('amount');

        $allocations = ProviderGroupDealAllocation::where('official_deal_id', $deal->id)->get();
        $splitLocked = $allocations->count() > 0;
        $equalSharePct = count($memberIds) > 0 ? round(100 / count($memberIds), 4) : 0;

        $members = [];
        foreach ($memberIds as $mid) {
            if ($splitLocked) {
                $alloc = $allocations->firstWhere('member_id', $mid);
                $pct = $alloc ? (float) $alloc->percentage : 0.0;
            } elseif ($isDaily) {
                $pct = $equalSharePct;
            } else {
                $approved = ProviderGroupShareRequest::where('official_deal_id', $deal->id)
                    ->where('member_id', $mid)
                    ->where('status', 'approved')
                    ->first();
                $pct = $approved ? (float) $approved->percentage : 0.0;
            }
            $members[] = [
                'member_id' => (int) $mid,
                'member_name' => self::memberName((int) $mid),
                'percentage' => round($pct, 2),
                'share_amount' => round($teamEarned * $pct / 100, 2),
            ];
        }

        // Safety net: never surface a broken breakdown from un-locked
        // approved proposals that total more than 100%.
        $approvedTotal = (float) collect($members)->sum('percentage');
        if (!$splitLocked && !$isDaily && $approvedTotal > 100.001) {
            $approvedTotal = 0.0;
            foreach ($members as &$m) {
                $m['percentage'] = 0.0;
                $m['share_amount'] = 0.0;
            }
            unset($m);
        }

        $your = collect($members)->firstWhere('member_id', $viewerId);

        return [
            'is_group' => true,
            'group_id' => $groupId,
            'group_name' => $req->group?->group_name ?? 'Service Team',
            'deal_id' => (int) $deal->id,
            'is_daily' => $isDaily,
            'split_locked' => $splitLocked,
            'team_earned' => round($teamEarned, 2),
            'paid_total' => round($paidTotal, 2),
            'outstanding' => round(max(0, $teamEarned - $paidTotal), 2),
            'materials_paid' => round($materialsPaid, 2),
            'approved_total' => round($approvedTotal, 2),
            'members' => array_values($members),
            'you' => (int) $viewerId,
            'your_share_amount' => round((float) ($your['share_amount'] ?? 0), 2),
        ];
    }

    /** True when the provider passed verified requirements (approved). */
    public static function isVerifiedProvider(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user || $user->role !== 'service_provider') return false;

        return DB::table('service_provider_requirements')
            ->where('user_id', $userId)
            ->where('status', 'verified')
            ->exists();
    }
}