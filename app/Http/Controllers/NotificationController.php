<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Get unread notifications for the authenticated user.
     */
    public function getUnread(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->notificationService->getUnreadNotifications($user);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json(['message' => 'ok']);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['message' => 'ok']);
    }

    /**
     * Show all notifications page for admin.
     */
    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();
        $notifications = \App\Models\Notification::forUser($user)
            ->latest()
            ->paginate(20);

        $unreadCount = $this->notificationService->getUnreadCount($user);

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
