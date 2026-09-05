<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SystemNotification;
use App\Events\Notification\NotificationEvent;

class ECBannedUserController extends Controller
{
    public function index()
    {
        // Fetch pending/reviewed petitions from Clients
        $clientPetitions = DB::table('client_restriction_petitions')
            ->join('users', 'client_restriction_petitions.client_id', '=', 'users.id')
            ->select(
                'client_restriction_petitions.id as petition_id',
                'client_restriction_petitions.client_id as user_id',
                'client_restriction_petitions.reason',
                'client_restriction_petitions.attachment_path',
                'client_restriction_petitions.status',
                'client_restriction_petitions.created_at',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.role'
            )
            ->whereIn('client_restriction_petitions.status', ['pending', 'reviewed'])
            ->get();

        // Fetch pending/reviewed petitions from Service Providers
        $spPetitions = DB::table('service_provider_restriction_petitions')
            ->join('users', 'service_provider_restriction_petitions.service_provider_id', '=', 'users.id')
            ->select(
                'service_provider_restriction_petitions.id as petition_id',
                'service_provider_restriction_petitions.service_provider_id as user_id',
                'service_provider_restriction_petitions.reason',
                'service_provider_restriction_petitions.attachment_path',
                'service_provider_restriction_petitions.status',
                'service_provider_restriction_petitions.created_at',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.role'
            )
            ->whereIn('service_provider_restriction_petitions.status', ['pending', 'reviewed'])
            ->get();

        $allPetitions = $clientPetitions->merge($spPetitions);

        // Group petitions by user so a user only appears once in the table
        $groupedUsers = $allPetitions->groupBy(function($item) {
            return $item->role . '_' . $item->user_id;
        })->map(function($userPetitions) {
            $first = $userPetitions->first();
            
            // Get the total failed deliveries for this user
            $failed_deliveries = DB::table('failed_deliveries')
                ->where('user_id', $first->user_id)
                ->count();
            
            return [
                'user_id' => $first->user_id,
                'first_name' => $first->first_name,
                'last_name' => $first->last_name,
                'email' => $first->email,
                'role' => $first->role,
                'failed_deliveries' => $failed_deliveries,
                'status' => $first->status,
                'latest_petition_date' => $userPetitions->max('created_at'),
                'total_petitions' => $userPetitions->count(),
                'petitions' => $userPetitions->map(function($p) {
                    return [
                        'petition_id' => $p->petition_id,
                        'reason' => $p->reason,
                        'attachment_path' => $p->attachment_path,
                        'status' => $p->status,
                        'created_at' => $p->created_at,
                    ];
                })->sortByDesc('created_at')->values()->toArray()
            ];
        })->values()->sortByDesc('latest_petition_date')->values();

        return response()->json([
            'success' => true,
            'data' => $groupedUsers
        ]);
    }

    public function revoke(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'role' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            // Delete all failed deliveries for the user to lift the restriction completely
            DB::table('failed_deliveries')->where('user_id', $request->user_id)->delete();

            // Delete all petitions for this user
            if ($request->role === 'client') {
                DB::table('client_restriction_petitions')->where('client_id', $request->user_id)->delete();
            } else {
                DB::table('service_provider_restriction_petitions')->where('service_provider_id', $request->user_id)->delete();
            }

            // Send Real-Time System Notification
            $notification = SystemNotification::create([
                'type' => 'Success',
                'title' => 'Shop Restrictions Lifted',
                'message' => 'Your petition has been reviewed and accepted. Your E-Commerce shop purchasing privileges have been fully restored.',
                'receiver_id' => $request->user_id,
                'sender_role' => 'admin',
                'receiver_role' => $request->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            event(new NotificationEvent($notification));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Restriction revoked successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deny(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'role' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            // Delete all pending petitions for this user since it was denied
            if ($request->role === 'client') {
                DB::table('client_restriction_petitions')
                    ->where('client_id', $request->user_id)
                    ->delete();
            } else {
                DB::table('service_provider_restriction_petitions')
                    ->where('service_provider_id', $request->user_id)
                    ->delete();
            }

            // Send Real-Time System Notification
            $notification = SystemNotification::create([
                'type' => 'Warning',
                'title' => 'Petition Denied',
                'message' => 'Your petition to lift the shop restriction has been denied after administrative review. The restriction remains in place.',
                'receiver_id' => $request->user_id,
                'sender_role' => 'admin',
                'receiver_role' => $request->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            event(new NotificationEvent($notification));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Petition denied successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}