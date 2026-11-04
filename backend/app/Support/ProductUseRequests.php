<?php

namespace App\Support;

use App\Events\Notification\NotificationEvent;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\MaterialExpenseItem;
use App\Models\ServiceProvider\MaterialExpenseRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServiceProductUseRequest;
use App\Models\ServiceProvider\SpInventory;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\DB;

/**
 * Helpers for the client-driven "use a product from the Service Provider's
 * inventory" flow. A final approved markup is written into the existing
 * materials reimbursement tables so it is billed through the same payment
 * term as the rest of the deal.
 */
class ProductUseRequests
{
    public static function attach(ClientServiceRequest $req, string $baseUrl): void
    {
        $requests = ServiceProductUseRequest::with('product')
            ->where('client_service_request_id', $req->id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($requests as $request) {
            if ($request->product && $request->product->image_url) {
                $image = $request->product->image_url;
                if (!filter_var($image, FILTER_VALIDATE_URL) && !str_starts_with($image, 'data:')) {
                    $image = $baseUrl . '/storage/' . ltrim(preg_replace('/^\/?storage\//', '', $image), '/');
                }
                $request->product->image_url = $image;
            }

            $base = (float) $request->base_unit_price;
            $qty = (int) $request->quantity;
            $offer = $request->offer_percentage !== null ? (float) $request->offer_percentage : null;
            $offerUnit = $offer !== null ? round($base * (1 + ($offer / 100)), 2) : $base;

            $request->effective_unit_price = $offerUnit;
            $request->effective_total = round($offerUnit * $qty, 2);

            if ($request->approved_percentage !== null) {
                $approvedUnit = round($base * (1 + ((float) $request->approved_percentage / 100)), 2);
                $request->approved_unit_price = $approvedUnit;
                $request->approved_total = round($approvedUnit * $qty, 2);
            }
        }

        $req->product_use_requests = $requests;
    }

    /**
     * Finalizes an accepted markup: records the approved materials batch and
     * adds its total to the existing materials payment term.
     */
    public static function finalize(int $useRequestId, ?float $percentage = null): ?ServiceProductUseRequest
    {
        return DB::transaction(function () use ($useRequestId, $percentage) {
            $useRequest = ServiceProductUseRequest::lockForUpdate()->find($useRequestId);
            if (!$useRequest) return null;
            if ($useRequest->status === 'approved') return $useRequest;

            $job = ClientServiceRequest::findOrFail($useRequest->client_service_request_id);
            if ($percentage === null) {
                $percentage = $useRequest->approved_percentage !== null
                    ? (float) $useRequest->approved_percentage
                    : (float) $useRequest->offer_percentage;
            }

            $base = (float) $useRequest->base_unit_price;
            $qty = (int) $useRequest->quantity;
            $unitPrice = round($base * (1 + ($percentage / 100)), 2);
            $totalPrice = round($unitPrice * $qty, 2);

            $batch = MaterialExpenseRequest::create([
                'client_service_request_id' => $job->id,
                'provider_id' => $job->provider_id,
                'proof_photo_path' => null,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            MaterialExpenseItem::create([
                'material_expense_request_id' => $batch->id,
                'item_name' => $useRequest->product_name . ' (Service Provider inventory, ' . rtrim(rtrim(number_format($percentage, 2, '.', ''), '0'), '.') . '% markup)',
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            self::addMaterialsPaymentTerm($job, $totalPrice);

            $useRequest->status = 'approved';
            $useRequest->approved_percentage = $percentage;
            $useRequest->offer_percentage = $percentage;
            $useRequest->offer_by = null;
            $useRequest->approved_at = now();
            $useRequest->material_expense_request_id = $batch->id;
            $useRequest->save();

            return $useRequest;
        });
    }

    /**
     * Deducts the requested quantity from the provider's inventory when the
     * provider approves the request. The inventory row is removed completely
     * when its remaining stock reaches zero.
     *
     * Must be called inside a database transaction so the row lock is kept
     * until the approval status is saved.
     */
    public static function deductStock(ServiceProductUseRequest $useRequest): bool
    {
        if (!$useRequest->sp_inventory_id) return false;

        $inventory = SpInventory::whereKey($useRequest->sp_inventory_id)
            ->lockForUpdate()
            ->first();

        if (!$inventory || (int) $inventory->quantity < (int) $useRequest->quantity) {
            return false;
        }

        $inventory->quantity = (int) $inventory->quantity - (int) $useRequest->quantity;
        if ($inventory->quantity <= 0) {
            $inventory->delete();
        } else {
            $inventory->save();
        }

        return true;
    }

    public static function notifyRejected(ServiceProductUseRequest $useRequest, ClientServiceRequest $job, string $reason): void
    {
        $notification = SystemNotification::create([
            'type' => 'Warning',
            'title' => 'Inventory Request Rejected',
            'message' => 'Your request to use "' . $useRequest->product_name . '" was rejected by the service provider. Reason: ' . $reason,
            'sender_id' => $job->provider_id,
            'receiver_id' => $job->client_id,
            'sender_role' => 'service_provider',
            'receiver_role' => 'client',
        ]);

        event(new NotificationEvent($notification));
    }

    /**
     * Same payment term used by the existing materials reimbursement flow.
     */
    private static function addMaterialsPaymentTerm(ClientServiceRequest $req, float $delta): void
    {
        $deal = OfficialDeal::where('client_service_request_id', $req->id)->latest()->first();
        if (!$deal || $delta <= 0) return;

        $term = OfficialPaymentTerm::where('official_deal_id', $deal->id)
            ->where('is_materials_term', true)
            ->where('status', '!=', 'paid')
            ->latest('id')->first();

        if ($term) {
            $term->amount = round(((float) $term->amount) + $delta, 2);
            $term->status = 'agreed';
            $term->save();
            return;
        }

        $mainTerm = OfficialPaymentTerm::where('official_deal_id', $deal->id)
            ->where('is_materials_term', false)
            ->latest('id')->first();

        OfficialPaymentTerm::create([
            'official_deal_id' => $deal->id,
            'provider_id' => $deal->provider_id,
            'client_id' => $deal->client_id,
            'payment_method' => $mainTerm?->payment_method ?? 'on_hand',
            'payment_term' => 'Materials Reimbursement',
            'status' => 'agreed',
            'is_materials_term' => true,
            'amount' => round($delta, 2),
        ]);
    }
}
