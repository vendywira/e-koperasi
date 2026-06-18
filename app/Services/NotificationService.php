<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Send a notification to a user.
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?Model $reference = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'reference_id' => $reference?->getKey(),
            'reference_type' => $reference ? get_class($reference) : null,
            'is_read' => false,
        ]);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToMany(
        iterable $users,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?Model $reference = null
    ): Collection {
        $notifications = new Collection();

        foreach ($users as $user) {
            $notifications->push(
                $this->send($user, $type, $title, $body, $link, $reference)
            );
        }

        return $notifications;
    }

    /**
     * Send to all admin and it-ops users.
     */
    public function sendToStaff(
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?Model $reference = null
    ): Collection {
        $staff = User::whereIn('role', ['admin', 'it-ops'])->get();
        return $this->sendToMany($staff, $type, $title, $body, $link, $reference);
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnreadNotifications(User $user, int $limit = 20): Collection
    {
        return Notification::forUser($user)
            ->unread()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::forUser($user)
            ->unread()
            ->count();
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::forUser($user)
            ->unread()
            ->update(['is_read' => true]);
    }

    /**
     * Delete read notifications older than specified days.
     */
    public function deleteOldRead(int $days = 30): void
    {
        Notification::where('is_read', true)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
