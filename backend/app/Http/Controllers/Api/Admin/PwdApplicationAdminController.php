<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SystemNotification;
use App\Events\Notification\NotificationEvent;
use App\Events\PwdApplicationStatusUpdated;

class PwdApplicationAdminController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $applications = DB::table('pwd_applications')
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        foreach ($applications->items() as $app) {
            $app->photo_front_url = $app->photo_front_path ? asset('storage/' . $app->photo_front_path) : null;
            $app->photo_back_url = $app->photo_back_path ? asset('storage/' . $app->photo_back_path) : null;
        }

        return response()->json([
            'status' => 'success',
            'data' => $applications->items(),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'from' => $applications->firstItem(),
                'to' => $applications->lastItem()
            ]
        ]);
    }

    public function verify($id)
    {
        $app = DB::table('pwd_applications')->where('id', $id)->first();
        if (!$app) {
            return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
        }

        DB::table('pwd_applications')
            ->where('id', $id)
            ->update([
                'status' => 'verified',
                'rejection_reason' => null,
                'updated_at' => now()
            ]);

        // 1. Insert into system_notifications table
        $notification = SystemNotification::create([
            'type' => 'Success',
            'title' => 'PWD Application Verified',
            'message' => 'Your PWD ID application has been successfully verified. You are now eligible for special discounts on future transactions.',
            'receiver_id' => $app->user_id,
            'receiver_role' => 'client',
            'sender_role' => 'admin',
            'is_read' => 0,
        ]);

        // 2. Broadcast via existing NotificationEvent
        event(new NotificationEvent($notification));

        // 3. Broadcast real-time status update event for PWD component view
        event(new PwdApplicationStatusUpdated($app->user_id, 'verified'));

        return response()->json([
            'status' => 'success',
            'message' => 'PWD Application has been successfully verified.'
        ]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $app = DB::table('pwd_applications')->where('id', $id)->first();
        if (!$app) {
            return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
        }

        DB::table('pwd_applications')
            ->where('id', $id)
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'updated_at' => now()
            ]);

        // 1. Insert into system_notifications table
        $notification = SystemNotification::create([
            'type' => 'Warning',
            'title' => 'PWD Application Rejected',
            'message' => 'Your recent PWD ID application was rejected. Reason: ' . $request->reason . '. Please review and resubmit your details.',
            'receiver_id' => $app->user_id,
            'receiver_role' => 'client',
            'sender_role' => 'admin',
            'is_read' => 0,
        ]);

        // 2. Broadcast via existing NotificationEvent
        event(new NotificationEvent($notification));

        // 3. Broadcast real-time status update event for PWD component view
        event(new PwdApplicationStatusUpdated($app->user_id, 'rejected', $request->reason));

        return response()->json([
            'status' => 'success',
            'message' => 'PWD Application has been rejected.'
        ]);
    }
}