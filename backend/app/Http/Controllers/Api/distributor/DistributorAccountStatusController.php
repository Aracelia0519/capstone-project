<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountTermination;
use Illuminate\Support\Facades\Auth;

class DistributorAccountStatusController extends Controller
{
    /**
     * Checks the real-time termination status of the currently authenticated distributor.
     */
    public function getStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Check if there is any active termination record for this user
        $termination = AccountTermination::where('account_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        // If the latest record is 'terminated', they are restricted
        $isTerminated = $termination && $termination->status === 'terminated';

        return response()->json([
            'success' => true,
            'is_terminated' => $isTerminated,
            'termination_details' => $termination
        ]);
    }
}