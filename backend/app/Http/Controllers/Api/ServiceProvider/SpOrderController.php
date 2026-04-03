<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider\SpOrder;
use App\Models\ServiceProvider\SpOrderItem;
use App\Models\ServiceProvider\SpReturnRequest;
use App\Models\ServiceProvider\SpReturnMessage;
use App\Events\SpReturnMessageSent; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([]);
        }

        $orders = SpOrder::with(['items.product', 'items.distributor'])
            ->where('service_provider_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $orderIds = $orders->pluck('id');
        
        // FIXED: Querying the existing 'product_reviews' table using 'sp_order_id'
        $reviews = DB::table('product_reviews')
            ->where('service_provider_id', $user->id)
            ->whereIn('sp_order_id', $orderIds)
            ->get();

        $orders->each(function ($order) use ($reviews) {
            $order->items->each(function ($item) use ($reviews, $order) {
                // FIXED: Matching against sp_order_id
                $review = $reviews->where('sp_order_id', $order->id)->where('product_id', $item->product_id)->first();
                $item->is_reviewed = $review ? true : false;
                $item->review_rating = $review ? $review->rating : null;
                $item->review_comment = $review ? $review->comment : null;

                // Added active return check so frontend can disable inventory add
                $item->has_active_return = SpReturnRequest::where('order_item_id', $item->id)
                    ->whereNotIn('status', ['rejected', 'cancelled'])->exists();

                if ($item->product && $item->product->image_url) {
                    if (!filter_var($item->product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($item->product->image_url, 'data:')) {
                        $item->product->image_url = asset('storage/' . ltrim($item->product->image_url, '/'));
                    }
                }
            });
        });

        return response()->json($orders);
    }

    public function getPickUpDetails(Request $request, $id)
    {
        $user = $request->user();
        $order = SpOrder::where('id', $id)->where('service_provider_id', $user->id)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $delivery = DB::table('sp_order_deliveries')->where('order_id', $id)->first();
        $firstItem = DB::table('sp_order_items')->where('sp_order_id', $id)->first();
        $distributorId = $firstItem ? $firstItem->distributor_id : null;
        
        $address = null;
        if ($distributorId) {
            $req = DB::table('distributor_requirements')->where('user_id', $distributorId)->first();
            if ($req) {
                $address = DB::table('distributor_addresses')->where('distributor_requirements_id', $req->id)->first();
            }
        }

        return response()->json([
            'success' => true,
            'preparation_proof' => $delivery && $delivery->preparation_proof_path ? asset($delivery->preparation_proof_path) : null,
            'distributor_lat' => $address ? $address->latitude : null,
            'distributor_lng' => $address ? $address->longitude : null,
            'distributor_address' => $address ? ($address->block_address . ', ' . $address->barangay . ', ' . $address->city . ', ' . $address->province) : null
        ]);
    }

    public function submitPickUp(Request $request, $id)
    {
        $request->validate([
            'proof_of_pickup' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = $request->user();
        $order = SpOrder::where('id', $id)->where('service_provider_id', $user->id)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $pickupPath = $request->file('proof_of_pickup')->store('sp_deliveries_proof', 'public');
        $paymentPath = $request->file('proof_of_payment')->store('sp_payment_proofs', 'public');

        DB::beginTransaction();
        try {
            DB::table('sp_order_deliveries')
                ->where('order_id', $id)
                ->update([
                    'status' => 'completed',
                    'delivery_proof_path' => 'storage/' . $pickupPath,
                    'payment_received_proof_path' => 'storage/' . $paymentPath,
                    'updated_at' => now()
                ]);

            $order->update(['status' => 'delivered']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pick-up confirmed successfully!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to confirm pick-up: ' . $e->getMessage()], 500);
        }
    }

    public function submitReview(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:sp_orders,id',
            'product_id' => 'required|exists:distributor_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $user = $request->user();

        // FIXED: Using 'sp_order_id' instead of 'order_id'
        DB::table('product_reviews')->updateOrInsert(
            [
                'service_provider_id' => $user->id,
                'sp_order_id' => $request->order_id, // Map the payload to sp_order_id
                'product_id' => $request->product_id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'client_id' => null, 
                'order_id' => null, // Explicitly null the client's order_id column
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json(['success' => true, 'message' => 'Review submitted successfully!']);
    }

    public function submitReturnRequest(Request $request) 
    {
        $request->validate([
            'order_item_id' => 'required|exists:sp_order_items,id',
            'reason' => 'required|string',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $user = $request->user();
        $orderItem = SpOrderItem::findOrFail($request->order_item_id);

        $path = $request->file('proof_image')->store('sp_returns', 'public');

        $returnReq = SpReturnRequest::create([
            'order_item_id' => $orderItem->id,
            'sp_id' => $user->id,
            'distributor_id' => $orderItem->distributor_id,
            'reason' => $request->reason,
            'proof_image_path' => 'storage/' . $path,
            'status' => 'pending'
        ]);

        $message = SpReturnMessage::create([
            'return_request_id' => $returnReq->id,
            'sender_id' => $user->id,
            'receiver_id' => $orderItem->distributor_id,
            'message' => "Return Request initialized. Reason: " . $request->reason,
            'type' => 'text'
        ]);

        $imageMsg = SpReturnMessage::create([
            'return_request_id' => $returnReq->id,
            'sender_id' => $user->id,
            'receiver_id' => $orderItem->distributor_id,
            'type' => 'image',
            'file_path' => 'storage/' . $path
        ]);

        broadcast(new SpReturnMessageSent($message))->toOthers();
        broadcast(new SpReturnMessageSent($imageMsg))->toOthers();

        return response()->json(['success' => true, 'request' => $returnReq]);
    }

    public function getReturnChat($orderItemId) 
    {
        $user = Auth::guard('sanctum')->user();
        $req = SpReturnRequest::where('order_item_id', $orderItemId)->where('sp_id', $user->id)->first();
        
        if(!$req) {
            return response()->json(['success' => true, 'request' => null]);
        }

        SpReturnMessage::where('return_request_id', $req->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = SpReturnMessage::where('return_request_id', $req->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')->get();

        return response()->json(['success' => true, 'request' => $req, 'messages' => $messages]);
    }

    public function sendReturnMessage(Request $request, $id) 
    {
        $request->validate([
            'message' => 'nullable|string', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $user = $request->user();
        $returnReq = SpReturnRequest::findOrFail($id);
        $msg = null;

        if($request->hasFile('image')) {
            $path = $request->file('image')->store('sp_returns_chat', 'public');
            $msg = SpReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $user->id,
                'receiver_id' => $returnReq->distributor_id,
                'type' => 'image',
                'file_path' => 'storage/' . $path
            ]);
            broadcast(new SpReturnMessageSent($msg))->toOthers();
        }

        if($request->message) {
            $msg2 = SpReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $user->id,
                'receiver_id' => $returnReq->distributor_id,
                'type' => 'text',
                'message' => $request->message
            ]);
            broadcast(new SpReturnMessageSent($msg2))->toOthers();
            $msg = $msg2; 
        }

        return response()->json(['success' => true, 'data' => $msg]);
    }

    public function submitReturnTracking(Request $request, $id) 
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'tracking_proof' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $user = $request->user();
        $returnReq = SpReturnRequest::findOrFail($id);

        $path = $request->file('tracking_proof')->store('sp_returns_tracking', 'public');

        $returnReq->update([
            'status' => 'shipped',
            'tracking_number' => $request->tracking_number,
            'tracking_proof_path' => 'storage/' . $path
        ]);

        $msg = SpReturnMessage::create([
            'return_request_id' => $returnReq->id,
            'sender_id' => $user->id,
            'receiver_id' => $returnReq->distributor_id,
            'type' => 'system',
            'message' => "Service Provider has shipped the item. Tracking Number: {$request->tracking_number}",
            'file_path' => 'storage/' . $path
        ]);

        broadcast(new SpReturnMessageSent($msg))->toOthers();

        return response()->json(['success' => true, 'request' => $returnReq, 'message' => $msg]);
    }
}