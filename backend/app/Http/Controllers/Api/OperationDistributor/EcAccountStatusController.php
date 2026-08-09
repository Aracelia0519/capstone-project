<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountTermination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EcAccountStatusController extends Controller
{
    /**
     * Checks the real-time termination status of the currently authenticated operational distributor
     * and their parent distributor.
     */
    public function getStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get the parent distributor ID
        $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
        $parentId = $opDist ? $opDist->parent_distributor_id : null;

        // Check if either the user themselves OR their parent distributor is terminated
        $idsToCheck = array_filter([$user->id, $parentId]);

        $termination = AccountTermination::whereIn('account_id', $idsToCheck)
            ->orderBy('id', 'desc')
            ->first();

        // If a termination record exists and is marked 'terminated', restrict access
        $isTerminated = $termination && $termination->status === 'terminated';

        return response()->json([
            'success' => true,
            'is_terminated' => $isTerminated,
            'termination_details' => $termination,
            'parent_id' => $parentId
        ]);
    }
}