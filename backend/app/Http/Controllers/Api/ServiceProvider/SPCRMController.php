<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider\ServiceReview;
use Illuminate\Support\Facades\Auth;

class SPCRMController extends Controller
{
    public function getReviews()
    {
        $providerId = Auth::id();

        $reviews = ServiceReview::with(['client', 'serviceRequest.serviceOffering'])
            ->where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0;
        
        $ratingCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'stats' => [
                    'total' => $totalReviews,
                    'average' => $averageRating,
                    'distribution' => $ratingCounts
                ]
            ]
        ]);
    }

    public function replyToReview(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:1000'
        ]);

        $review = ServiceReview::where('provider_id', Auth::id())->findOrFail($id);
        $review->reply = $request->reply;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Reply submitted successfully.',
            'data' => $review
        ]);
    }

    public function toggleVisibility($id)
    {
        $review = ServiceReview::where('provider_id', Auth::id())->findOrFail($id);
        $review->is_hidden = !$review->is_hidden;
        $review->save();

        $status = $review->is_hidden ? 'hidden from' : 'visible to';

        return response()->json([
            'success' => true,
            'message' => "Client comment is now {$status} the public.",
            'is_hidden' => $review->is_hidden
        ]);
    }
}