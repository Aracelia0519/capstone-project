<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EcommerceProductController extends Controller
{
    /**
     * Fetch all deployed products, hiding any that belong to terminated distributors.
     */
    public function getProducts(Request $request)
    {
        try {
            // 1. Fetch strictly terminated distributors
            $terminatedIds = DB::table('account_terminations')
                ->where('status', 'terminated')
                ->whereIn('role', ['distributor', 'operational_distributor'])
                ->pluck('account_id')
                ->toArray();

            // 2. Safely query the base products without complex address joins
            $query = DB::table('distributor_products as dp')
                ->join('users as u', 'dp.distributor_id', '=', 'u.id')
                ->leftJoin('distributor_inventories as di', 'dp.id', '=', 'di.product_id')
                ->where('dp.is_active', 1)
                ->where('di.ecommerce_status', 'deployed');
            
            // Apply restriction block if records exist
            if (!empty($terminatedIds)) {
                $query->whereNotIn('dp.distributor_id', $terminatedIds);
            }

            $products = $query->select(
                'dp.id', 'dp.distributor_id', 'dp.name', 'dp.category', 'dp.type', 
                'dp.size', 'dp.color_code as color', 'dp.price', 'dp.image_url',
                'u.full_name as distributor_name',
                'di.quantity as stock'
            )->get();

            // 3. Independent safe mapping for Distributor Addresses to prevent LeftJoin NULL crashes
            $distributorIds = $products->pluck('distributor_id')->unique();
            $addresses = [];
            
            foreach ($distributorIds as $dId) {
                $req = DB::table('distributor_requirements')->where('user_id', $dId)->first();
                if ($req) {
                    $addr = DB::table('distributor_addresses')->where('distributor_requirements_id', $req->id)->first();
                    if ($addr) {
                        $addresses[$dId] = [
                            'lat' => $addr->latitude,
                            'lng' => $addr->longitude
                        ];
                    }
                }
            }

            // 4. Attach Promotions & Reviews Data
            $productIds = $products->pluck('id')->toArray();
            
            $promotions = DB::table('crm_promotions')
                ->whereIn('product_id', $productIds)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->get()
                ->keyBy('product_id');

            $reviews = DB::table('product_reviews')
                ->whereIn('product_id', $productIds)
                ->where('status', 'published')
                ->select('product_id', DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(id) as review_count'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // 5. Format Output for Vue (Injecting Coordinates cleanly)
            $formatted = $products->map(function($p) use ($promotions, $reviews, $addresses) {
                $promo = $promotions->get($p->id);
                $review = $reviews->get($p->id);
                
                $originalPrice = $p->price;
                $currentPrice = $p->price;

                if ($promo) {
                    if ($promo->type === 'percentage_discount') {
                        $currentPrice = $originalPrice - ($originalPrice * ($promo->discount_value / 100));
                    } elseif (in_array($promo->type, ['fixed_discount', 'fixed_amount'])) {
                        $currentPrice = max(0, $originalPrice - $promo->discount_value);
                    }
                }

                // Default coordinates to 0 to bypass validation crashes securely
                $lat = $addresses[$p->distributor_id]['lat'] ?? 0;
                $lng = $addresses[$p->distributor_id]['lng'] ?? 0;

                return [
                    'id' => $p->id,
                    'distributor_id' => $p->distributor_id,
                    'name' => $p->name,
                    'brand' => $p->distributor_name, 
                    'distributor_name' => $p->distributor_name,
                    'category' => $p->category,
                    'type' => $p->type,
                    'finish' => 'Standard',
                    'size' => $p->size,
                    'color' => $p->color,
                    'price' => (float)$currentPrice,
                    'original_price' => (float)$originalPrice,
                    'stock' => (int)$p->stock,
                    'image_url' => $p->image_url,
                    'distributor_lat' => (float)$lat,
                    'distributor_lng' => (float)$lng,
                    'rating' => $review ? round($review->avg_rating, 1) : 0,
                    'review_count' => $review ? $review->review_count : 0,
                    'promotion' => $promo ? [
                        'type' => $promo->type,
                        'discount_value' => $promo->discount_value
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching E-Commerce Products: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch products'], 500);
        }
    }

    /**
     * Fetch a single product detail, instantly blocking access if distributor is terminated.
     */
    public function getProduct($id)
    {
        try {
            $terminatedIds = DB::table('account_terminations')
                ->where('status', 'terminated')
                ->whereIn('role', ['distributor', 'operational_distributor'])
                ->pluck('account_id')
                ->toArray();

            $query = DB::table('distributor_products as dp')
                ->join('users as u', 'dp.distributor_id', '=', 'u.id')
                ->leftJoin('distributor_inventories as di', 'dp.id', '=', 'di.product_id')
                ->leftJoin('distributor_payment_settings as dps', 'dp.distributor_id', '=', 'dps.distributor_id')
                ->where('dp.id', $id)
                ->where('dp.is_active', 1)
                ->select(
                    'dp.*', 
                    'u.full_name as distributor_name',
                    'di.quantity as stock',
                    'dps.is_gcash_enabled',
                    'dps.is_pickup_enabled'
                );

            if (!empty($terminatedIds)) {
                $query->whereNotIn('dp.distributor_id', $terminatedIds);
            }

            $p = $query->first();

            // Deny if unavailable
            if (!$p) {
                return response()->json(['success' => false, 'message' => 'Product is currently unavailable or has been removed.'], 404);
            }

            // Independent explicit safe fetch for address mapping
            $req = DB::table('distributor_requirements')->where('user_id', $p->distributor_id)->first();
            $addr = $req ? DB::table('distributor_addresses')->where('distributor_requirements_id', $req->id)->first() : null;
            
            $lat = $addr ? $addr->latitude : 0;
            $lng = $addr ? $addr->longitude : 0;

            // Variants Query
            $variantsQuery = DB::table('distributor_products as dp')
                ->leftJoin('distributor_inventories as di', 'dp.id', '=', 'di.product_id')
                ->where('dp.distributor_id', $p->distributor_id)
                ->where('dp.name', $p->name)
                ->where('dp.category', $p->category)
                ->where('dp.is_active', 1)
                ->select('dp.id', 'dp.name', 'dp.size', 'dp.color_code as color', 'dp.price', 'di.quantity as stock', 'dp.image_url');
            
            if (!empty($terminatedIds)) {
                $variantsQuery->whereNotIn('dp.distributor_id', $terminatedIds);
            }

            $variants = $variantsQuery->get()->map(function($v) use ($lat, $lng) {
                $v->distributor_lat = (float)$lat;
                $v->distributor_lng = (float)$lng;
                return $v;
            });

            $reviews = DB::table('product_reviews as pr')
                ->leftJoin('users as u', 'pr.client_id', '=', 'u.id')
                ->where('pr.product_id', $id)
                ->where('pr.status', 'published')
                ->select(
                    'pr.id', 'pr.rating', 'pr.comment', 'pr.response', 'pr.response_date', 
                    DB::raw('DATE_FORMAT(pr.created_at, "%b %d, %Y") as date'),
                    DB::raw('COALESCE(u.full_name, "Anonymous") as client'),
                    DB::raw('SUBSTRING(COALESCE(u.first_name, "A"), 1, 1) as clientInitials'),
                    DB::raw('"Client" as reviewerType')
                )
                ->orderBy('pr.created_at', 'desc')
                ->get();

            $ratingAvg = $reviews->avg('rating');

            $data = [
                'id' => $p->id,
                'distributor_id' => $p->distributor_id,
                'name' => $p->name,
                'brand' => $p->distributor_name,
                'distributor_name' => $p->distributor_name,
                'type' => $p->type,
                'category' => $p->category,
                'price' => (float)$p->price,
                'original_price' => (float)$p->price,
                'stock' => (int)$p->stock,
                'image_url' => $p->image_url,
                'description' => $p->description,
                'distributor_lat' => (float)$lat,
                'distributor_lng' => (float)$lng,
                'distributor_gcash_enabled' => (bool)$p->is_gcash_enabled,
                'distributor_pickup_enabled' => (bool)$p->is_pickup_enabled,
                'rating' => $ratingAvg ? round($ratingAvg, 1) : 0,
                'review_count' => $reviews->count(),
                'variants' => $variants,
                'reviews' => $reviews
            ];

            return response()->json(['success' => true, 'data' => $data]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching product details: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch product details'], 500);
        }
    }
}