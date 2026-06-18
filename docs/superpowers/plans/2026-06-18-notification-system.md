# Notification System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add in-app notification system with bell icon, badge counter, dropdown list, and click-to-navigate for admin and client portals.

**Architecture:** Database-backed notifications with polling. `NotificationService` creates notifications on key events (ticket, demo, subscription, payment). Frontend polls every 20s via `useNotifications` composable. `NotificationBell.vue` component dropped into `AdminLayout` and `ClientLayout`.

**Tech Stack:** Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS + UUID PKs

---

### Task 1: Create migrations (notifications + demo_requests tables)

**Files:**
- Create: `database/migrations/2026_06_18_000005_create_notifications_table.php`
- Create: `database/migrations/2026_06_18_000006_create_demo_requests_table.php`

- [ ] **Step 1: Create notifications migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title', 255);
            $table->text('body');
            $table->string('link', 255)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

- [ ] **Step 2: Create demo_requests migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('role', 255);
            $table->string('cooperative_name', 255);
            $table->string('member_count', 50);
            $table->string('whatsapp', 20);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_requests');
    }
};
```

- [ ] **Step 3: Run migrations**

Run: `php artisan migrate`
Expected: 2 new tables created (notifications, demo_requests)

### Task 2: Create Models (Notification, DemoRequest)

**Files:**
- Create: `app/Models/Notification.php`
- Create: `app/Models/DemoRequest.php`

- [ ] **Step 1: Create Notification model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'link',
        'reference_id',
        'reference_type',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
```

- [ ] **Step 2: Create DemoRequest model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'role',
        'cooperative_name',
        'member_count',
        'whatsapp',
        'message',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
```

### Task 3: Create NotificationService

**Files:**
- Create: `app/Services/NotificationService.php`

- [ ] **Step 1: Create the service class**

```php
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
     * Send notification to multiple users (all admin, all it-ops, etc.).
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
```

### Task 4: Create NotificationController (API)

**Files:**
- Create: `app/Http/Controllers/NotificationController.php`

- [ ] **Step 1: Create the controller**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
```

- [ ] **Step 2: Register routes in routes/web.php**

Add before `require __DIR__ . '/cms.php';`:

```php
// Notification API routes
Route::middleware(['auth'])->prefix('api/notifications')->name('api.notifications.')->group(function () {
    Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('unread');
    Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
});
```

### Task 5: Create useNotifications composable

**Files:**
- Create: `resources/js/Composables/useNotifications.ts`

- [ ] **Step 1: Create the composable**

```typescript
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export interface Notification {
    id: string;
    user_id: string;
    type: 'ticket' | 'ticket_reply' | 'demo' | 'subscription' | 'payment';
    title: string;
    body: string;
    link: string | null;
    reference_id: string | null;
    reference_type: string | null;
    is_read: boolean;
    created_at: string;
    updated_at: string;
}

export function useNotifications(pollingIntervalMs = 20000) {
    const unreadCount = ref(0);
    const notifications = ref<Notification[]>([]);
    const showDropdown = ref(false);
    const loading = ref(false);
    const error = ref<string | null>(null);

    let pollingTimer: ReturnType<typeof setInterval> | null = null;

    async function fetchNotifications() {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get('/api/notifications/unread');
            notifications.value = response.data.data;
            unreadCount.value = response.data.unread_count;
        } catch (e: any) {
            error.value = 'Gagal memuat notifikasi';
            console.error('Fetch notifications error:', e);
        } finally {
            loading.value = false;
        }
    }

    async function markAsRead(notif: Notification) {
        try {
            await axios.post(`/api/notifications/${notif.id}/read`);
            notifications.value = notifications.value.filter(n => n.id !== notif.id);
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (e) {
            console.error('Mark as read error:', e);
        }
    }

    async function markAllAsRead() {
        try {
            await axios.post('/api/notifications/read-all');
            notifications.value = [];
            unreadCount.value = 0;
        } catch (e) {
            console.error('Mark all as read error:', e);
        }
    }

    function startPolling() {
        stopPolling();
        fetchNotifications();
        pollingTimer = setInterval(fetchNotifications, pollingIntervalMs);
    }

    function stopPolling() {
        if (pollingTimer !== null) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
    }

    function toggleDropdown() {
        showDropdown.value = !showDropdown.value;
        if (showDropdown.value) {
            fetchNotifications();
        }
    }

    function closeDropdown() {
        showDropdown.value = false;
    }

    onMounted(() => {
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        unreadCount,
        notifications,
        showDropdown,
        loading,
        error,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        startPolling,
        stopPolling,
        toggleDropdown,
        closeDropdown,
    };
}
```

### Task 6: Create NotificationBell.vue component

**Files:**
- Create: `resources/js/Components/NotificationBell.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNotifications, type Notification } from '@/Composables/useNotifications';

const {
    unreadCount,
    notifications,
    showDropdown,
    loading,
    error,
    markAsRead,
    markAllAsRead,
    toggleDropdown,
    closeDropdown,
    fetchNotifications,
} = useNotifications();

function handleClickOutside(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (!target.closest('[data-notification-bell]')) {
        closeDropdown();
    }
}

function handleNotificationClick(notif: Notification) {
    if (!notif.is_read) {
        markAsRead(notif);
    }
    closeDropdown();
    if (notif.link) {
        router.visit(notif.link);
    }
}

function getIcon(type: string): string {
    switch (type) {
        case 'ticket':
        case 'ticket_reply':
            return 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155';
        case 'demo':
            return 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155';
        case 'subscription':
            return 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605';
        case 'payment':
            return 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z';
        default:
            return 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0';
    }
}

function getIconColor(type: string): string {
    switch (type) {
        case 'ticket':
        case 'ticket_reply':
            return 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30';
        case 'demo':
            return 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30';
        case 'subscription':
            return 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30';
        case 'payment':
            return 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30';
        default:
            return 'text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800';
    }
}

function timeAgo(dateStr: string): string {
    const now = new Date();
    const date = new Date(dateStr);
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'baru saja';
    if (diffMins < 60) return `${diffMins} menit yang lalu`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} jam yang lalu`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays} hari yang lalu`;
    return date.toLocaleDateString('id-ID');
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div data-notification-bell class="relative">
        <!-- Bell Button -->
        <button
            @click.stop="toggleDropdown"
            class="relative p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            aria-label="Notifikasi"
        >
            <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-2"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-xl overflow-hidden z-50"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-white">Notifikasi</h4>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-[10px] font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                    >
                        Tandai telah dibaca
                    </button>
                </div>

                <!-- Loading state -->
                <div v-if="loading && notifications.length === 0" class="px-4 py-6 text-center">
                    <div class="flex justify-center gap-1">
                        <span class="w-2 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                        <span class="w-2 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                        <span class="w-2 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                    </div>
                </div>

                <!-- Error state -->
                <div v-else-if="error && notifications.length === 0" class="px-4 py-6 text-center">
                    <p class="text-xs text-red-500 dark:text-red-400">{{ error }}</p>
                    <button
                        @click="fetchNotifications"
                        class="mt-2 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        Coba lagi
                    </button>
                </div>

                <!-- Empty state -->
                <div v-else-if="notifications.length === 0 && !loading" class="px-4 py-8 text-center">
                    <svg class="w-8 h-8 mx-auto text-neutral-300 dark:text-neutral-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="text-xs text-neutral-400 dark:text-neutral-500">Belum ada notifikasi</p>
                </div>

                <!-- Notification list -->
                <div v-else class="max-h-80 overflow-y-auto divide-y divide-neutral-100 dark:divide-neutral-800">
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        @click="handleNotificationClick(notif)"
                        class="px-4 py-3 cursor-pointer transition-colors"
                        :class="!notif.is_read
                            ? 'bg-blue-50 dark:bg-blue-900/10 hover:bg-blue-100 dark:hover:bg-blue-900/20'
                            : 'hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                :class="getIconColor(notif.type)"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(notif.type)" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-neutral-900 dark:text-white">{{ notif.title }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ notif.body }}</p>
                                <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-1">{{ timeAgo(notif.created_at) }}</p>
                            </div>
                            <div v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 border-t border-neutral-100 dark:border-neutral-800 text-center">
                    <a
                        href="/admin/notifications"
                        class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                    >
                        Lihat semua notifikasi →
                    </a>
                </div>
            </div>
        </Transition>
    </div>
</template>
```

### Task 7: Create notification list page (Admin)

**Files:**
- Create: `resources/js/Pages/Admin/Notifications/Index.vue`

- [ ] **Step 1: Create the page**

```vue
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {Link, usePage} from '@inertiajs/vue3';
import {ref, computed} from 'vue';
import axios from 'axios';
import type {Notification} from '@/Composables/useNotifications';

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    notifications: PaginatedData<Notification>;
    unreadCount: number;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);

function getIconColor(type: string): string {
    switch (type) {
        case 'ticket':
        case 'ticket_reply':
            return 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30';
        case 'demo':
            return 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30';
        case 'subscription':
            return 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30';
        case 'payment':
            return 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30';
        default:
            return 'text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800';
    }
}

function getIcon(type: string): string {
    switch (type) {
        case 'ticket':
        case 'ticket_reply':
            return 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155';
        case 'demo':
            return 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155';
        case 'subscription':
            return 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605';
        case 'payment':
            return 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z';
        default:
            return 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0';
    }
}

function timeAgo(dateStr: string): string {
    const now = new Date();
    const date = new Date(dateStr);
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'baru saja';
    if (diffMins < 60) return `${diffMins} menit yang lalu`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} jam yang lalu`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays} hari yang lalu`;
    return date.toLocaleDateString('id-ID');
}

const typeLabels: Record<string, string> = {
    ticket: 'Tiket',
    ticket_reply: 'Balasan Tiket',
    demo: 'Demo Request',
    subscription: 'Langganan',
    payment: 'Pembayaran',
};
</script>

<template>
    <AdminLayout title="Notifikasi">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Semua Notifikasi</h2>
                    <p v-if="unreadCount > 0" class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ unreadCount }} belum dibaca
                    </p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="notifications.data.length === 0" class="text-center py-16">
                <svg class="w-12 h-12 mx-auto text-neutral-300 dark:text-neutral-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Belum ada notifikasi</p>
            </div>

            <!-- Notification list -->
            <div v-else class="space-y-1">
                <div
                    v-for="notif in notifications.data"
                    :key="notif.id"
                    :href="notif.link || '#'"
                    class="block"
                >
                    <Link
                        :href="notif.link || '#'"
                        class="flex items-start gap-4 px-4 py-3.5 rounded-xl transition-colors"
                        :class="!notif.is_read
                            ? 'bg-blue-50 dark:bg-blue-900/10 hover:bg-blue-100 dark:hover:bg-blue-900/20'
                            : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                    >
                        <div
                            class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                            :class="getIconColor(notif.type)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(notif.type)" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                                    {{ notif.title }}
                                </p>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400">
                                    {{ typeLabels[notif.type] || notif.type }}
                                </span>
                            </div>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ notif.body }}</p>
                            <p class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-1">
                                {{ timeAgo(notif.created_at) }}
                            </p>
                        </div>
                        <div v-if="!notif.is_read" class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0 mt-2"></div>
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="flex items-center justify-center gap-2 mt-6">
                <Link
                    v-if="notifications.current_page > 1"
                    :href="`/admin/notifications?page=${notifications.current_page - 1}`"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800"
                >
                    Sebelumnya
                </Link>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    Halaman {{ notifications.current_page }} dari {{ notifications.last_page }}
                </span>
                <Link
                    v-if="notifications.current_page < notifications.last_page"
                    :href="`/admin/notifications?page=${notifications.current_page + 1}`"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800"
                >
                    Selanjutnya
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
```

### Task 8: Integrate NotificationBell into AdminLayout.vue

**Files:**
- Modify: `resources/js/Layouts/AdminLayout.vue`

- [ ] **Step 1: Import NotificationBell at top of `<script setup>`**

After the existing imports, add:

```typescript
import NotificationBell from '@/Components/NotificationBell.vue';
```

- [ ] **Step 2: Add NotificationBell in the desktop header (before user avatar)**

Find the desktop sidebar's user section (the one with `class="px-3 py-3 border-t border-neutral-200 dark:border-neutral-800"` around line 433), and add `NotificationBell` before the user avatar div. Replace the user section with:

```vue
            <!-- User -->
            <div class="px-3 py-3 border-t border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-3 px-3 py-2">
                    <NotificationBell />
                    <div
                        class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 text-sm font-semibold">
                        {{ user?.name?.charAt(0) || 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                            {{ user?.name || 'Admin' }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{
                                user?.email || ''
                            }}</p>
                    </div>
                    <button
                        @click="logout"
                        class="p-1.5 rounded-lg text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        title="Logout"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                    </button>
                </div>
            </div>
```

- [ ] **Step 3: Also add NotificationBell in the mobile sidebar's user section**

Find the mobile sidebar user section (around line 255-269) and add `<NotificationBell />` before the user avatar there too.

### Task 9: Integrate NotificationBell into ClientLayout.vue

**Files:**
- Modify: `resources/js/Layouts/ClientLayout.vue`

- [ ] **Step 1: Import NotificationBell at top of `<script setup>`**

After existing imports, add:

```typescript
import NotificationBell from '@/Components/NotificationBell.vue';
```

- [ ] **Step 2: Add NotificationBell to desktop header (before logout button)**

Find the desktop header section (line 103-119) and add `NotificationBell` before the user email:

```vue
            <!-- Desktop header -->
            <header class="hidden lg:block bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold text-neutral-900 dark:text-white">{{ title || 'Dashboard' }}</h1>
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <span class="text-xs text-neutral-400 dark:text-neutral-500">{{ user?.email }}</span>
                        <button
                            @click="logout"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-neutral-500 dark:text-neutral-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Logout
                        </button>
                    </div>
                </div>
            </header>
```

- [ ] **Step 3: Add NotificationBell to mobile header**

Find the mobile header (around line 78-101) and add `NotificationBell` before the logout button:

```vue
            <!-- Mobile header -->
            <header class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30 lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <h1 class="text-base font-bold text-neutral-900 dark:text-white truncate">{{ title || 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <button
                            @click="logout"
                            class="p-1.5 rounded-lg text-neutral-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Logout"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>
```

### Task 10: Add notification trigger — TicketController@store

**Files:**
- Modify: `app/Http/Controllers/TicketController.php`

- [ ] **Step 1: Add imports and inject NotificationService**

Add to imports at top:

```php
use App\Services\NotificationService;
```

- [ ] **Step 2: Add notify logic after ticket creation but before email sending**

After `$ticket->user_id` check and after the attachment loop (after line 70), add:

```php
        // Notify admin & it-ops about new ticket
        $notifService = app(NotificationService::class);
        $notifService->sendToStaff(
            'ticket',
            "Tiket Baru {$ticket->ticket_number}",
            "{$ticket->user->name} mengirim tiket: \"{$ticket->subject}\"",
            "/admin/tickets/{$ticket->id}",
            $ticket
        );
```

### Task 11: Add notification trigger — Admin TicketController@reply

**Files:**
- Modify: `app/Http/Controllers/Admin/TicketController.php`

- [ ] **Step 1: Add import**

```php
use App\Services\NotificationService;
```

- [ ] **Step 2: Add notify logic after reply creation (after line 115)**

```php
        // Notify client that their ticket was replied
        $notifService = app(NotificationService::class);
        $notifService->send(
            $ticket->user,
            'ticket_reply',
            "Tiket {$ticket->ticket_number} Direspon",
            "Admin telah membalas tiket Anda: \"{$ticket->subject}\"",
            "/tickets/{$ticket->id}",
            $ticket
        );
```

### Task 12: Add notification trigger — DemoController@store

**Files:**
- Modify: `app/Http/Controllers/DemoController.php`

- [ ] **Step 1: Add imports**

```php
use App\Models\DemoRequest;
use App\Services\NotificationService;
```

- [ ] **Step 2: Save demo request to DB and send notification after email success (after line 34, before Log::info)**

```php
            // Save to database
            $demoRequest = DemoRequest::create($validated);

            // Notify admin users
            $notifService = app(NotificationService::class);
            $notifService->sendToStaff(
                'demo',
                'Request Demo Baru',
                "{$validated['name']} ({$validated['cooperative_name']}) ingin demo — WA: {$validated['whatsapp']}",
                '/admin/clients',
                $demoRequest
            );
```

### Task 13: Add notification trigger — Admin ClientController@updateSubscription

**Files:**
- Modify: `app/Http/Controllers/Admin/ClientController.php`

- [ ] **Step 1: Add import**

```php
use App\Services\NotificationService;
```

- [ ] **Step 2: Add notify logic after subscription is created/updated (after line 158)**

```php
        // Notify admin (themselves) and the client
        $notifService = app(NotificationService::class);
        $planName = ucfirst($validated['plan']);
        $statusText = $validated['status'] === 'active' ? 'diaktifkan' : $validated['status'];

        // Notify staff
        $notifService->sendToStaff(
            'subscription',
            "Langganan Baru: {$planName}",
            "{$client->name} berlangganan paket {$planName} — status: {$statusText}",
            "/admin/clients/{$client->id}",
            $subscription
        );

        // Notify the client
        $notifService->send(
            $client,
            'subscription',
            "Langganan {$planName} {$statusText}",
            "Paket {$planName} Anda telah {$statusText}. Silakan cek detail langganan.",
            '/client/subscription',
            $subscription
        );
```

### Task 14: Add notification trigger — Admin ClientPaymentController@store

**Files:**
- Modify: `app/Http/Controllers/Admin/ClientPaymentController.php`

- [ ] **Step 1: Add import**

```php
use App\Services\NotificationService;
```

- [ ] **Step 2: Add notify logic after payment creation (inside `if ($validated['status'] === 'paid')` block, after subscription is updated)**

After line 58 (inside the `if` block):

```php
            // Notify client about confirmed payment
            $notifService = app(NotificationService::class);
            $notifService->send(
                $subscription->user,
                'payment',
                "Pembayaran Dikonfirmasi",
                "Pembayaran {$payment->receipt_number} sebesar {$payment->amountFormatted()} telah dikonfirmasi.",
                "/client/payments/{$payment->id}",
                $payment
            );
```

### Task 15: Add "Lihat semua notifikasi" admin route + controller

**Files:**
- Modify: `routes/cms.php`
- Modify: `app/Http/Controllers/NotificationController.php` (add index method)

- [ ] **Step 1: Add `index` method to NotificationController**

```php
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

        return \Inertia\Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
```

- [ ] **Step 2: Add route to cms.php**

Add inside the `Route::middleware(['auth'])->prefix('admin')->name('admin.')` group (before it closes), after the ticket management section:

```php
    // Notifications page
    Route::middleware('role:admin,it-ops')->get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
```

### Task 16: Add scheduled cleanup

**Files:**
- Modify: `routes/console.php`

- [ ] **Step 1: Add schedule command**

```php
use App\Services\NotificationService;

// Clean up read notifications older than 30 days
Schedule::call(function () {
    app(NotificationService::class)->deleteOldRead(30);
})->daily();
```

### Task 17: Run tests and verify

**Files:**
- Test: `php artisan migrate`
- Test: `php artisan route:list`
- Test: `npm run build`

- [ ] **Step 1: Verify migrations work**

Run: `php artisan migrate`
Expected: 2 new tables created successfully (notifications, demo_requests), no errors.

- [ ] **Step 2: Verify routes are registered**

Run: `php artisan route:list | grep notification`
Expected: Routes for unread, read, read-all, and the admin notifications index page.

- [ ] **Step 3: Verify frontend compiles**

Run: `npm run build`
Expected: No TypeScript or Vue compilation errors.
