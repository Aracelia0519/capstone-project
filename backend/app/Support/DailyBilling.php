<?php

namespace App\Support;

use Carbon\Carbon;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\DailyWorkLog;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServiceJobCompletion;
use App\Models\ServiceProvider\ServicePaymentTransaction;
use Illuminate\Support\Facades\Schema;

/**
 * Daily Billing snapshot for services whose price_type is "Daily".
 *
 * Rule (per spec):
 *  - The client pays the agreed deal price (daily rate) for every day the
 *    Service Provider actually WORKS, counting from the day the official deal
 *    was agreed (status "ongoing").
 *  - If the Provider marks a day as NOT worked (daily_work_logs: worked=false),
 *    that day is exempt and the Client is NOT charged for it.
 *  - Every completed ServicePaymentTransaction represents exactly one paid
 *    day (days are covered chronologically across the billable days).
 *  - Unpaid billable days accumulate as an outstanding balance.
 *  - No new days accrue once the service is done (completion submitted) and
 *    none accrue after the Client approves the completion.
 */
class DailyBilling
{
    public static function isDaily($serviceOffering): bool
    {
        return $serviceOffering !== null && $serviceOffering->price_type === 'Daily';
    }

    /**
     * The date the daily counter starts: stored anchor, or (for deals created
     * before the column existed) the deal's last update time.
     */
    public static function billingStart(OfficialDeal $deal): ?Carbon
    {
        if (Schema::hasColumn('official_deals', 'daily_billing_started_at')
            && !empty($deal->daily_billing_started_at)) {
            return Carbon::parse($deal->daily_billing_started_at);
        }

        return $deal->updated_at ? Carbon::parse($deal->updated_at) : null;
    }

    /**
     * Build the daily-billing snapshot for a service request.
     *
     * @return array|null
     */
    public static function snapshot(ClientServiceRequest $req, ?OfficialDeal $deal = null, ?OfficialPaymentTerm $term = null, string $baseUrl = null)
    {
        if (!$deal || !self::isDaily($req->serviceOffering)) {
            return null;
        }

        $dailyRate = (float) $deal->price;
        $start = self::billingStart($deal);
        if (!$start) {
            return null;
        }
        $start = $start->copy()->startOfDay();

        // Map "today" to the last billable day. Days stop accruing the moment
        // the provider submits the completion proof, and the approved
        // completion date is the absolute final day.
        $cap = Carbon::now()->startOfDay();
        if (in_array($req->status, ['completion_review', 'completed'])) {
            if ($req->status === 'completed' && $req->updated_at) {
                $cap = Carbon::parse($req->updated_at)->startOfDay();
            } else {
                $completion = ServiceJobCompletion::where('client_service_request_id', $req->id)
                    ->latest()
                    ->first();
                if ($completion && $completion->created_at) {
                    $cap = Carbon::parse($completion->created_at)->startOfDay();
                }
            }
        }

        // All calendar days in the billing window, plus the "not worked" marks.
        $calendarDates = [];
        $exemptDates = [];
        if (in_array($req->status, ['ongoing', 'completion_review', 'completed']) && !$cap->lt($start)) {
            $cursor = $start->copy();
            while ($cursor->lte($cap)) {
                $calendarDates[] = $cursor->toDateString();
                $cursor->addDay();
            }

            $workLogs = DailyWorkLog::where('client_service_request_id', $req->id)
                ->whereBetween('work_date', [$start->toDateString(), $cap->toDateString()])
                ->get();

            foreach ($workLogs as $wl) {
                if (!$wl->worked) {
                    $exemptDates[$wl->work_date] = true;
                }
            }
        }

        $billableDates = array_values(array_filter($calendarDates, function ($d) use ($exemptDates) {
            return !isset($exemptDates[$d]);
        }));

        $daysElapsed = count($calendarDates);
        $daysExempt = count($exemptDates);
        $daysBillable = count($billableDates);

        $termIds = [];
        if ($term) {
            $q = OfficialPaymentTerm::where('official_deal_id', $deal->id);
            if (Schema::hasColumn('official_payment_terms', 'is_materials_term')) {
                $q->where('is_materials_term', false);
            }
            $termIds = $q->pluck('id')->all();
        }

        $transactions = [];
        if (!empty($termIds)) {
            $transactions = ServicePaymentTransaction::whereIn('payment_term_id', $termIds)
                ->where('status', 'completed')
                ->orderBy('created_at')
                ->get();
        }

        $totalPaid = collect($transactions)->sum(function ($t) {
            return (float) $t->amount;
        });
        $daysPaid = count($transactions); // each completed transaction = one paid day

        $pendingProof = null;
        if ($term && $term->status === 'awaiting_proof_approval') {
            $pendingProof = $term->proof_of_payment
                ? ($baseUrl ? $baseUrl . '/storage/' . ltrim(preg_replace('/^\/?storage\//', '', $term->proof_of_payment), '/') : $term->proof_of_payment)
                : null;
        }

        $totalDue = $daysBillable * $dailyRate;
        $outstanding = max(0, $totalDue - $totalPaid);
        $daysOutstanding = max(0, $daysBillable - $daysPaid);
        $nextDueDate = ($daysPaid < $daysBillable) ? $billableDates[$daysPaid] : null;

        $todayStr = Carbon::now()->startOfDay()->toDateString();
        $todayExempt = isset($exemptDates[$todayStr]);
        $todayPaid = !$todayExempt && $daysBillable > 0 && $daysPaid >= $daysBillable;

        $log = [];
        foreach ($transactions as $i => $t) {
            $coversDate = $i < $daysBillable ? $billableDates[$i] : ($daysBillable > 0 ? $billableDates[$daysBillable - 1] : $start->toDateString());
            $log[] = [
                'covers_date' => $coversDate,
                'paid_date' => $t->created_at ? Carbon::parse($t->created_at)->toDateString() : null,
                'amount' => (float) $t->amount,
                'status' => $t->status,
                'reference_number' => $t->reference_number,
            ];
        }

        return [
            'daily_rate' => $dailyRate,
            'billing_started_at' => $start->toDateString(),
            'days_elapsed' => (int) $daysElapsed,
            'days_billable' => (int) $daysBillable,
            'days_exempt' => (int) $daysExempt,
            'days_paid' => (int) $daysPaid,
            'days_outstanding' => (int) $daysOutstanding,
            'today_paid' => (bool) $todayPaid,
            'today_exempt' => (bool) $todayExempt,
            'next_due_date' => $nextDueDate,
            'total_due' => round($totalDue, 2),
            'total_paid' => round($totalPaid, 2),
            'outstanding' => round($outstanding, 2),
            'job_status' => $req->status,
            'pending_proof_url' => $pendingProof,
            'payment_log' => $log,
            'work_log' => collect($workLogs ?? [])->map(function ($wl) {
                return [
                    'date' => $wl->work_date,
                    'worked' => (bool) $wl->worked,
                ];
            })->values()->all(),
        ];
    }
}