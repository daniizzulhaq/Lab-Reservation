<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of user notifications.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        // Filter by type if specified
        if ($request->filled('type')) {
            $query->where('data->type', $request->type);
        }

        // Filter by read status
        if ($request->filled('read_status')) {
            if ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get counts for badges
        $unreadCount = auth()->user()->unreadNotifications->count();
        $totalCount = auth()->user()->notifications->count();

        return view('user.notifications.index', compact('notifications', 'unreadCount', 'totalCount'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($id);
            
            if (!$notification->read_at) {
                $notification->markAsRead();
            }

            return redirect()->back()->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menandai notifikasi sebagai dibaca.');
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();
            
            return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menandai semua notifikasi sebagai dibaca.');
        }
    }

    /**
     * Delete a specific notification.
     */
    public function destroy($id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->delete();

            return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus notifikasi.');
        }
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead()
    {
        try {
            auth()->user()->readNotifications()->delete();
            
            return redirect()->back()->with('success', 'Semua notifikasi yang sudah dibaca berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus notifikasi.');
        }
    }

    /**
     * Get unread notifications count (API endpoint).
     */
    public function getUnreadCount()
    {
        try {
            $count = auth()->user()->unreadNotifications->count();
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'has_unread' => $count > 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'has_unread' => false,
                'message' => 'Gagal mengambil jumlah notifikasi'
            ], 500);
        }
    }

    /**
     * Get latest notifications (API endpoint).
     */
    public function getLatestNotifications(Request $request)
    {
        try {
            $limit = $request->input('limit', 5);
            
            $notifications = auth()->user()
                ->notifications()
                ->take($limit)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->data['title'] ?? 'Notifikasi',
                        'message' => $notification->data['message'] ?? '',
                        'type' => $notification->data['type'] ?? 'general',
                        'icon' => $notification->data['icon'] ?? 'fas fa-bell',
                        'color' => $notification->data['color'] ?? 'primary',
                        'is_read' => !is_null($notification->read_at),
                        'created_at' => $notification->created_at->diffForHumans(),
                        'created_at_formatted' => $notification->created_at->format('d M Y, H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => auth()->user()->unreadNotifications->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi',
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * View notification details and mark as read.
     */
    public function show($id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($id);
            
            // Mark as read if not already read
            if (!$notification->read_at) {
                $notification->markAsRead();
            }

            return view('user.notifications.show', compact('notification'));
        } catch (\Exception $e) {
            return redirect()->route('user.notifications.index')->with('error', 'Notifikasi tidak ditemukan.');
        }
    }
}