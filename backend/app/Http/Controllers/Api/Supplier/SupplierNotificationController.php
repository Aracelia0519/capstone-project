<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;

class SupplierNotificationController extends Controller
{
    /**
     * Fetch notifications for the currently authenticated supplier/user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notifications = SystemNotification::where('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50) // Limit to recent 50
            ->get()
            ->map(function ($notification) {
                // Ensure we provide a ready-to-use full URL for the frontend
                if ($notification->attachment) {
                    $cleanPath = str_replace('storage/', '', $notification->attachment);
                    $notification->attachment_url = asset('storage/' . $cleanPath);
                } else {
                    $notification->attachment_url = null;
                }
                return $notification;
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $notifications->where('is_read', false)->count()
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = SystemNotification::where('id', $id)
            ->where('receiver_id', $user->id)
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications for this user as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        SystemNotification::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true]);
    }
}