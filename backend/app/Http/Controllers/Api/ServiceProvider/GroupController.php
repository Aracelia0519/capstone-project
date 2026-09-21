<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\ServiceProvider\ProviderGroup;
use App\Models\ServiceProvider\ProviderGroupMember;
use App\Models\ServiceProvider\ProviderGroupServiceApproval;
use App\Models\ServiceProvider\ProviderGroupShareRequest;
use App\Models\ServiceProvider\ProviderGroupShareApproval;
use App\Models\ServiceProvider\ServiceOffering;
use App\Models\ServiceProvider\OfficialDeal;
use App\Support\ProviderGroups;

class GroupController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // Groups overview / management
    // ────────────────────────────────────────────────────────────

    public function index()
    {
        $me = Auth::id();

        $myGroups = ProviderGroupMember::with(['group.leader'])
            ->where('member_id', $me)
            ->where('status', 'accepted')
            ->whereHas('group', fn($q) => $q->where('status', 'active'))
            ->get()
            ->map(function ($m) use ($me) {
                $group = $m->group;
                if (!$group) return null;
                $data = ProviderGroups::groupPayload($group->id);
                $data['my_role'] = $m->role;
                $data['my_id'] = (int) $me;
                return $data;
            })->filter()->values();

        $receivedInvites = ProviderGroupMember::with(['group.leader', 'member'])
            ->where('member_id', $me)
            ->where('status', 'pending')
            ->whereHas('group', fn($q) => $q->where('status', 'active'))
            ->get()
            ->map(function ($m) {
                $group = $m->group;
                if (!$group) return null;
                return [
                    'id' => $m->id,
                    'group_id' => $group->id,
                    'group_name' => $group->group_name,
                    'description' => $group->description,
                    'leader_id' => $group->leader_id,
                    'leader_name' => ProviderGroups::memberName((int) $group->leader_id),
                    'invited_at' => $m->created_at?->toIso8601String(),
                ];
            })->filter()->values();

        $sentInvites = ProviderGroupMember::with(['group', 'member'])
            ->whereHas('group', fn($q) => $q->where('leader_id', $me)->where('status', 'active'))
            ->where('status', 'pending')
            ->get()
            ->map(function ($m) {
                $group = $m->group;
                if (!$group) return null;
                $data = ProviderGroups::groupPayload($group->id);
                $data['invite_id'] = $m->id;
                $data['invitee_id'] = (int) $m->member_id;
                $data['invitee_name'] = ProviderGroups::memberName((int) $m->member_id);
                $data['invited_at'] = $m->created_at?->toIso8601String();
                return $data;
            })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'my_groups' => $myGroups,
                'received_invites' => $receivedInvites,
                'sent_invites' => $sentInvites,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
        ]);

        $me = Auth::id();

        // A provider can only lead one active group at a time.
        $alreadyLeads = ProviderGroup::where('leader_id', $me)->where('status', 'active')->exists();
        if ($alreadyLeads) {
            return response()->json(['success' => false, 'message' => 'You already lead an active group. You must disband your current group first.'], 409);
        }

        $group = DB::transaction(function () use ($validated, $me) {
            $g = ProviderGroup::create([
                'group_name' => $validated['group_name'],
                'description' => $validated['description'] ?? null,
                'leader_id' => $me,
                'status' => 'active',
            ]);

            ProviderGroupMember::create([
                'group_id' => $g->id,
                'member_id' => $me,
                'role' => 'leader',
                'status' => 'accepted',
                'joined_at' => now(),
            ]);

            return $g;
        });

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully.',
            'data' => ProviderGroups::groupPayload($group->id),
        ], 201);
    }

    public function invite(Request $request, $group)
    {
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();
        $me = Auth::id();

        if (!ProviderGroups::isLeader($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'Only the group leader can send invitations.'], 403);
        }

        $validated = $request->validate(['email' => 'required|email']);

        $invitee = User::where('email', $validated['email'])->first();
        if (!$invitee || $invitee->role !== 'service_provider') {
            return response()->json(['success' => false, 'message' => 'No verified service provider account was found with that email.'], 422);
        }
        if (!ProviderGroups::isVerifiedProvider((int) $invitee->id)) {
            return response()->json(['success' => false, 'message' => 'That service provider has not completed professional verification yet.'], 422);
        }
        if ((int) $invitee->id === (int) $me) {
            return response()->json(['success' => false, 'message' => 'You cannot invite yourself.'], 422);
        }

        $existing = ProviderGroupMember::where('group_id', $group->id)->where('member_id', $invitee->id)->first();
        if ($existing) {
            $status = $existing->status;
            if ($status === 'accepted') {
                return response()->json(['success' => false, 'message' => 'That provider is already a member of your group.'], 409);
            }
            if ($status === 'pending') {
                return response()->json(['success' => false, 'message' => 'An invitation is already pending for that provider.'], 409);
            }
            // Re-invite after decline / left / removed.
            $existing->update(['status' => 'pending', 'role' => 'member']);
            return response()->json(['success' => true, 'message' => 'Invitation sent to ' . ProviderGroups::memberName((int) $invitee->id) . '.', 'data' => ['invitee_name' => ProviderGroups::memberName((int) $invitee->id)]]);
        }

        ProviderGroupMember::create([
            'group_id' => $group->id,
            'member_id' => $invitee->id,
            'role' => 'member',
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent to ' . ProviderGroups::memberName((int) $invitee->id) . '.',
            'data' => ['invitee_name' => ProviderGroups::memberName((int) $invitee->id)],
        ], 201);
    }

    public function respondInvite(Request $request, $group, $memberId)
    {
        $me = Auth::id();
        $invite = ProviderGroupMember::where('id', $memberId)
            ->where('group_id', $group)
            ->where('member_id', $me)
            ->where('status', 'pending')
            ->first();

        if (!$invite) {
            return response()->json(['success' => false, 'message' => 'Invitation not found.'], 404);
        }

        $validated = $request->validate(['action' => 'required|in:accept,decline']);

        if ($validated['action'] === 'accept') {
            $invite->update(['status' => 'accepted', 'joined_at' => now()]);
            return response()->json(['success' => true, 'message' => 'You are now a member of the group.']);
        }

        $invite->update(['status' => 'declined']);
        return response()->json(['success' => true, 'message' => 'Invitation declined.']);
    }

    public function removeMember($group, $memberId)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();

        if (!ProviderGroups::isLeader($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'Only the group leader can remove members.'], 403);
        }
        if ((int) $memberId === (int) $group->leader_id) {
            return response()->json(['success' => false, 'message' => 'The group leader cannot be removed.'], 422);
        }

        $member = ProviderGroupMember::where('group_id', $group->id)->where('member_id', $memberId)->first();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Member not found.'], 404);
        }

        $member->update(['status' => 'removed', 'joined_at' => null]);

        return response()->json(['success' => true, 'message' => 'Member removed from the group.']);
    }

    public function leaveGroup($group)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();

        if (ProviderGroups::isLeader($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'The group leader cannot leave. Disband the group or transfer leadership instead.'], 422);
        }

        $member = ProviderGroupMember::where('group_id', $group->id)->where('member_id', $me)->where('status', 'accepted')->first();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'You are not an active member of this group.'], 404);
        }

        $member->update(['status' => 'left', 'joined_at' => null]);

        return response()->json(['success' => true, 'message' => 'You left the group.']);
    }

    // ────────────────────────────────────────────────────────────
    // Group services — create, approve, reject, publish
    // ────────────────────────────────────────────────────────────

    public function services($group)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();

        if (!ProviderGroups::isMember($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group.'], 403);
        }

        $services = ServiceOffering::where('group_id', $group->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) use ($group, $me) {
                $s->image_paths_formatted = $this->formatPaths($s->image_paths, request());
                $s->approval_state = ProviderGroups::serviceApprovalState((int) $s->id, (int) $group->id, (int) $me);
                $s->created_by_name = ProviderGroups::memberName((int) $s->provider_id);
                return $s;
            });

        return response()->json(['success' => true, 'data' => $services]);
    }

    public function storeService(Request $request, $group)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();

        if (!ProviderGroups::isMember($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'price_type' => 'required|string',
            'duration' => 'required|string|max:255',
            'description' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('service_offerings', 'public');
            }
        }

        $service = ServiceOffering::create([
            'provider_id' => $me,
            'group_id' => $group->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'duration' => $validated['duration'],
            'description' => $validated['description'],
            'image_paths' => $imagePaths,
            'is_active' => false,
            'is_published' => false,
        ]);

        // Every OTHER accepted member must approve before this is published.
        ProviderGroups::createServiceApprovals((int) $service->id, (int) $group->id, (int) $me);

        $service->approval_state = ProviderGroups::serviceApprovalState((int) $service->id, (int) $group->id, (int) $me);
        $service->image_paths_formatted = $this->formatPaths($service->image_paths, $request);

        return response()->json([
            'success' => true,
            'message' => 'Service submitted for group approval. It will be published once every member approves it.',
            'data' => $service,
        ], 201);
    }

    public function updateService(Request $request, $group, $serviceId)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();
        $service = ServiceOffering::where('id', $serviceId)->where('group_id', $group->id)->firstOrFail();

        if (!ProviderGroups::isMember($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group.'], 403);
        }
        if ((int) $service->provider_id !== (int) $me) {
            return response()->json(['success' => false, 'message' => 'Only the member who created the draft can edit it.'], 403);
        }
        if ($service->is_published) {
            return response()->json(['success' => false, 'message' => 'This service is already published and cannot be edited here.'], 409);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'price_type' => 'required|string',
            'duration' => 'required|string|max:255',
            'description' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('service_offerings', 'public');
            }
        }

        $service->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'duration' => $validated['duration'],
            'description' => $validated['description'],
            'image_paths' => !empty($imagePaths) ? $imagePaths : $service->image_paths,
            'is_active' => false,
            'is_published' => false,
        ]);

        // Reset the approval round after an edit.
        ProviderGroupServiceApproval::where('service_offering_id', $service->id)
            ->where('group_id', $group->id)
            ->update(['status' => 'pending', 'rejection_reason' => null, 'decided_at' => null]);

        $service->approval_state = ProviderGroups::serviceApprovalState((int) $service->id, (int) $group->id, (int) $me);
        $service->image_paths_formatted = $this->formatPaths($service->image_paths, $request);

        return response()->json([
            'success' => true,
            'message' => 'Service draft updated. Members must approve it again before publishing.',
            'data' => $service,
        ]);
    }

    public function approveService($group, $serviceId)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();
        $service = ServiceOffering::where('id', $serviceId)->where('group_id', $group->id)->firstOrFail();

        if (!ProviderGroups::isMember($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group.'], 403);
        }
        if ((int) $service->provider_id === (int) $me) {
            return response()->json(['success' => false, 'message' => 'You cannot approve your own submitted service.'], 422);
        }

        $approval = ProviderGroupServiceApproval::where('service_offering_id', $service->id)
            ->where('member_id', $me)
            ->first();

        if (!$approval || $approval->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'No pending approval found for you on this service.'], 422);
        }

        $approval->update(['status' => 'approved', 'decided_at' => now()]);

        $published = ProviderGroups::publishIfAllApproved((int) $service->id);

        return response()->json([
            'success' => true,
            'message' => $published
                ? 'Service approved by all members and published to the e-commerce services. '
                : 'Approval recorded. Waiting for the other members.',
            'data' => [
                'approval_state' => ProviderGroups::serviceApprovalState((int) $service->id, (int) $group->id, (int) $me),
                'published' => $published,
            ],
        ]);
    }

    public function rejectService(Request $request, $group, $serviceId)
    {
        $me = Auth::id();
        $group = ProviderGroup::where('id', $group)->where('status', 'active')->firstOrFail();
        $service = ServiceOffering::where('id', $serviceId)->where('group_id', $group->id)->firstOrFail();

        if (!ProviderGroups::isMember($me, (int) $group->id)) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this group.'], 403);
        }
        if ((int) $service->provider_id === (int) $me) {
            return response()->json(['success' => false, 'message' => 'You cannot reject your own submitted service.'], 422);
        }

        $validated = $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $approval = ProviderGroupServiceApproval::where('service_offering_id', $service->id)
            ->where('member_id', $me)
            ->first();

        if (!$approval || $approval->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'No pending approval found for you on this service.'], 422);
        }

        $approval->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'decided_at' => now(),
        ]);

        $service->update(['is_active' => false, 'is_published' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Service rejected. The draft will not be published.',
            'data' => ['approval_state' => ProviderGroups::serviceApprovalState((int) $service->id, (int) $group->id, (int) $me)],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Revenue split (non-daily group jobs)
    // ────────────────────────────────────────────────────────────

    public function splitState($dealId)
    {
        $me = Auth::id();
        $deal = OfficialDeal::findOrFail($dealId);

        if (!$deal->group_id || !ProviderGroups::isMember($me, (int) $deal->group_id)) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $state = ProviderGroups::splitState((int) $deal->id, (int) $me);
        $state['lock'] = ProviderGroups::acquireLockCheckOnly('official_deal', (int) $deal->id, 'split', $me);

        return response()->json(['success' => true, 'data' => $state]);
    }

    public function proposeShare(Request $request, $dealId)
    {
        $me = Auth::id();
        $deal = OfficialDeal::findOrFail($dealId);

        if (!$deal->group_id || !ProviderGroups::isMember($me, (int) $deal->group_id)) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }
        if (!ProviderGroups::isSplitRequired((int) $deal->id)) {
            return response()->json(['success' => false, 'message' => 'Revenue splits only apply to non-daily group jobs.'], 422);
        }

        // One-at-a-time: another member may already be handling the split.
        $lock = ProviderGroups::activeLock('official_deal', (int) $deal->id, 'split');
        if ($lock && (int) $lock->member_id !== (int) $me) {
            return response()->json([
                'success' => false,
                'message' => ProviderGroups::memberName((int) $lock->member_id) . ' is currently handling the revenue split. Please wait.',
                'locker' => $lock->member_id,
            ], 409);
        }

        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0.01|max:100',
        ]);

        // STRICT: the split can only ever lock at exactly 100%. Block any
        // proposal that would push the already-approved total past 100%.
        $approvedTotal = (float) ProviderGroupShareRequest::where('official_deal_id', $deal->id)
            ->where('status', 'approved')
            ->sum('percentage');
        if ($approvedTotal + (float) $validated['percentage'] > 100.001) {
            return response()->json([
                'success' => false,
                'message' => 'Approved shares already total ' . round($approvedTotal, 2) . '%. Proposing ' . $validated['percentage'] . '% would exceed 100% — the team split must total exactly 100%. Please propose a lower percentage.',
            ], 422);
        }

        $existing = ProviderGroupShareRequest::where('official_deal_id', $deal->id)
            ->where('member_id', $me)
            ->first();

        if ($existing && $existing->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Your approved percentage (' . $existing->percentage . '%) cannot be changed.'], 409);
        }

        if ($existing) {
            // Re-propose (pending / rejected) → reset approvals.
            ProviderGroupShareApproval::where('share_request_id', $existing->id)->delete();
            $existing->update([
                'percentage' => $validated['percentage'],
                'status' => 'pending',
                'rejection_reason' => null,
            ]);
            $proposal = $existing;
        } else {
            $proposal = ProviderGroupShareRequest::create([
                'group_id' => $deal->group_id,
                'official_deal_id' => $deal->id,
                'member_id' => $me,
                'percentage' => $validated['percentage'],
                'status' => 'pending',
            ]);
        }

        // The lock is TRANSIENT: it only serializes the propose/re-propose
        // action so two members cannot write a proposal at the same instant.
        // It is released immediately so the other members can approve it.
        ProviderGroups::releaseLock((int) $me, 'official_deal', (int) $deal->id, 'split');

        return response()->json([
            'success' => true,
            'message' => 'Your proposed share of ' . $validated['percentage'] . '% was submitted. The other members must approve it.',
            'data' => ProviderGroups::splitState((int) $deal->id, (int) $me),
        ], 201);
    }

    public function approveShare($proposalId)
    {
        $me = Auth::id();
        $proposal = ProviderGroupShareRequest::with('deal')->findOrFail($proposalId);

        $deal = $proposal->deal;
        if (!$deal->group_id || !ProviderGroups::isMember($me, (int) $deal->group_id)) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }
        if ((int) $proposal->member_id === (int) $me) {
            return response()->json(['success' => false, 'message' => 'You cannot approve your own proposal.'], 422);
        }
        if ($proposal->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This proposal is no longer pending.'], 422);
        }

        $lock = ProviderGroups::activeLock('official_deal', (int) $deal->id, 'split');
        if ($lock && (int) $lock->member_id !== (int) $me) {
            return response()->json([
                'success' => false,
                'message' => ProviderGroups::memberName((int) $lock->member_id) . ' is currently handling the revenue split. Please wait.',
            ], 409);
        }

        ProviderGroupShareApproval::updateOrCreate(
            ['share_request_id' => $proposal->id, 'member_id' => $me],
            ['status' => 'approved', 'decided_at' => now()]
        );

        // Fully approved once every OTHER member approves.
        $otherMembers = array_diff(ProviderGroups::acceptedMemberIds((int) $deal->group_id), [(int) $proposal->member_id]);
        $approvedCount = ProviderGroupShareApproval::where('share_request_id', $proposal->id)
            ->where('status', 'approved')
            ->count();
        $fullyApproved = count($otherMembers) > 0 && $approvedCount >= count($otherMembers);

        if ($fullyApproved) {
            $proposal->update(['status' => 'approved']);

            // STRICT: if the approved proposals now total more than 100%,
            // REVOKE every proposal and approval so the team must re-agree
            // on a combination that totals exactly 100%.
            $approvedTotal = (float) ProviderGroupShareRequest::where('official_deal_id', $deal->id)
                ->where('status', 'approved')
                ->sum('percentage');

            if ($approvedTotal > 100.001) {
                ProviderGroups::revokeSplit((int) $deal->id);
                ProviderGroups::releaseLock((int) $me, 'official_deal', (int) $deal->id, 'split');

                return response()->json([
                    'success' => false,
                    'message' => 'The approved shares total ' . round($approvedTotal, 2) . '%, which exceeds 100%. ALL proposed revenue shares have been revoked — members must agree on percentages that total exactly 100%.',
                    'data' => ProviderGroups::splitState((int) $deal->id, (int) $me),
                ], 409);
            }

            ProviderGroups::checkAndLockSplit((int) $deal->id);
            ProviderGroups::releaseLock((int) $me, 'official_deal', (int) $deal->id, 'split');
        }

        return response()->json([
            'success' => true,
            'message' => $fullyApproved ? 'Proposal approved by all members.' : 'Approval recorded.',
            'data' => ProviderGroups::splitState((int) $deal->id, (int) $me),
        ]);
    }

    public function rejectShare(Request $request, $proposalId)
    {
        $me = Auth::id();
        $validated = $request->validate(['reason' => 'required|string|max:1000']);

        $proposal = ProviderGroupShareRequest::with('deal')->findOrFail($proposalId);
        $deal = $proposal->deal;
        if (!$deal->group_id || !ProviderGroups::isMember($me, (int) $deal->group_id)) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }
        if ((int) $proposal->member_id === (int) $me) {
            return response()->json(['success' => false, 'message' => 'You cannot reject your own proposal.'], 422);
        }
        if ($proposal->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This proposal is no longer pending.'], 422);
        }

        $lock = ProviderGroups::activeLock('official_deal', (int) $deal->id, 'split');
        if ($lock && (int) $lock->member_id !== (int) $me) {
            return response()->json([
                'success' => false,
                'message' => ProviderGroups::memberName((int) $lock->member_id) . ' is currently handling the revenue split. Please wait.',
            ], 409);
        }

        ProviderGroupShareApproval::updateOrCreate(
            ['share_request_id' => $proposal->id, 'member_id' => $me],
            ['status' => 'rejected', 'reason' => $validated['reason'], 'decided_at' => now()]
        );

        $proposal->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proposal rejected. The member can submit a revised percentage.',
            'data' => ProviderGroups::splitState((int) $deal->id, (int) $me),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Generic one-at-a-time lock endpoints (used by the frontend
    // before performing any group-job action).
    // ────────────────────────────────────────────────────────────

    public function acquireLockEndpoint(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
            'action' => 'required|string',
            'group_id' => 'nullable|integer',
        ]);

        $result = ProviderGroups::acquireLock(
            (int) Auth::id(),
            $validated['entity_type'],
            (int) $validated['entity_id'],
            $validated['action'],
            isset($validated['group_id']) ? (int) $validated['group_id'] : null
        );

        if (!$result['acquired']) {
            return response()->json([
                'success' => false,
                'message' => $result['locker']['member_name'] . ' is currently handling this action. Please wait a moment.',
                'locker' => $result['locker'],
            ], 409);
        }

        return response()->json(['success' => true, 'lock' => $result['lock']]);
    }

    public function releaseLockEndpoint(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
            'action' => 'required|string',
        ]);

        ProviderGroups::releaseLock(
            (int) Auth::id(),
            $validated['entity_type'],
            (int) $validated['entity_id'],
            $validated['action']
        );

        return response()->json(['success' => true, 'message' => 'Lock released.']);
    }

    // ────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────

    private function formatPaths($paths, $request)
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        if (empty($paths)) return [];

        return array_map(function ($path) use ($baseUrl) {
            if (str_starts_with($path, 'http')) {
                $parsed = parse_url($path);
                $path = $parsed['path'] ?? $path;
            }
            $clean = preg_replace('/^\/?storage\//', '', $path);
            return $baseUrl . '/storage/' . ltrim($clean, '/');
        }, $paths);
    }
}