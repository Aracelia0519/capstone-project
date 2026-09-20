<?php

namespace App\Support;

use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\MaterialExpenseRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServicePaymentTransaction;

/**
 * Shared helper that attaches the materials-reimbursement breakdown to a
 * service request that is being serialized for the Client or the Service
 * Provider dashboard.
 *
 * Attaches:
 *  - $req->materials         : all material expense batches (with items,
 *                              per-batch total and proof photo URL)
 *  - $req->materials_term    : the payment term that bills the approved
 *                              materials total (amount / total_paid / balance)
 *  - $req->materials_summary : approved total, billing target, paid, balance
 */
class MaterialExpenses
{
    public static function attach(ClientServiceRequest $req, ?OfficialDeal $deal, string $baseUrl)
    {
        $materials = MaterialExpenseRequest::with('items')
            ->where('client_service_request_id', $req->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedTotal = 0.0;
        foreach ($materials as $m) {
            $m->items_total = (float) $m->items->sum('total_price');
            $m->proof_photo_url = $m->proof_photo_path
                ? $baseUrl . '/storage/' . ltrim(preg_replace('/^\/?storage\//', '', $m->proof_photo_path), '/')
                : null;
            if ($m->status === 'approved') {
                $approvedTotal += $m->items_total;
            }
        }
        $req->materials = $materials;

        $target = 0.0;
        $paid = 0.0;
        $materialsTerm = null;
        if ($deal) {
            $mTerms = OfficialPaymentTerm::where('official_deal_id', $deal->id)
                ->where('is_materials_term', true)
                ->orderBy('id', 'desc')
                ->get();

            foreach ($mTerms as $mt) {
                $mtPaid = ServicePaymentTransaction::where('payment_term_id', $mt->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                $mt->total_paid = (float) $mtPaid;
                $mt->amount = (float) $mt->amount;
                $mt->balance = max(0, (float) $mt->amount - (float) $mtPaid);
                $mt->proof_of_payment_url = $mt->proof_of_payment
                    ? $baseUrl . '/storage/' . ltrim(preg_replace('/^\/?storage\//', '', $mt->proof_of_payment), '/')
                    : null;
                $target += (float) $mt->amount;
                $paid += (float) $mtPaid;
                if (!$materialsTerm) {
                    $materialsTerm = $mt;
                }
            }
        }
        $req->materials_term = $materialsTerm;
        $req->materials_summary = [
            'total_approved' => round($approvedTotal, 2),
            'total_target' => round($target, 2),
            'total_paid' => round($paid, 2),
            'balance' => round(max(0, $target - $paid), 2),
        ];
    }
}