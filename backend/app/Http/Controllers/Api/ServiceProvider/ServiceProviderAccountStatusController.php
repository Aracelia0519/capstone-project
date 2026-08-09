<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceProviderAccountStatusController extends Controller
{
    /**
     * Fetch the current account status and check if there is an active termination record.
     */
    public function getStatus(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Check if there is an active termination record for this user
            $termination = DB::table('account_terminations')
                ->where('account_id', $user->id)
                ->where('status', 'terminated')
                ->first();

            return response()->json([
                'success' => true,
                'is_terminated' => $termination ? true : false,
                'termination_details' => $termination
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch account status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}