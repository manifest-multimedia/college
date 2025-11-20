<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index()
    {
        return view('notifications.index', [
            'notifications' => Auth::user()->notifications()->paginate(10),
        ]);
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function getNotifications()
    {
        try {
            $user = Auth::user();
            $notifications = $user->notifications()->latest()->take(15)->get();

            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        try {
            $notification = DatabaseNotification::findOrFail($id);

            // Check if the notification belongs to the authenticated user
            if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== get_class(Auth::user())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $notification->markAsRead();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'error' => $e->getMessage(),
                'notification_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead()
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['error' => 'Failed to mark notifications as read'], 500);
        }
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        try {
            $notification = DatabaseNotification::findOrFail($id);

            // Check if the notification belongs to the authenticated user
            if ($notification->notifiable_id !== Auth::id() || $notification->notifiable_type !== get_class(Auth::user())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $notification->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting notification', [
                'error' => $e->getMessage(),
                'notification_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }
}
