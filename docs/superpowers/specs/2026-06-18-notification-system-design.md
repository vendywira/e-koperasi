# Notification System — e-Koperasi

**Date:** 2026-06-18
**Status:** Approved
**Stack:** Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS

---

## 1. Overview

Internal notification system for e-Koperasi. Users receive in-app notifications (bell icon + badge + dropdown) when events occur. Clicking a notification navigates to the relevant page.

**Target:** cPanel deployment — no WebSocket. Uses polling via database.

## 2. Data Model

### `notifications` table

All primary keys use UUID v4 (consistent with existing `User` and `Ticket` models). `HasUuids` trait on model; `$incrementing = false`.

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid, PK | UUID v4 |
| `user_id` | uuid, FK→users | Notification recipient |
| `type` | string(50) | `ticket`, `ticket_reply`, `demo`, `subscription`, `payment` |
| `title` | string(255) | Short title |
| `body` | text | Description |
| `link` | string(255), nullable | Target URL when clicked |
| `reference_id` | uuid, nullable | UUID of related entity (ticket, subscription, etc.) |
| `reference_type` | string(100), nullable | Related model class (polymorphic) |
| `is_read` | boolean, default false | Read status |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Indexes: `user_id`, `is_read`, `created_at`.

### `demo_requests` table

UUID primary key, consistent with the rest of the codebase.

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid, PK | UUID v4 |
| `name` | string(255) | Requester name |
| `role` | string(255) | Job role |
| `cooperative_name` | string(255) | Cooperative name |
| `member_count` | string(50) | Number of members |
| `whatsapp` | string(20) | WhatsApp number |
| `message` | text, nullable | Additional message |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

## 3. Architecture

```
[Event]
  ↓
[NotificationService::send()]
  ↓
[INSERT INTO notifications]
  ↓
[Frontend polls GET /api/notifications every 20s]
  ↓
[update badge count + dropdown list]
```

### NotificationService

Class `App\Services\NotificationService`:

- `send(User $user, string $type, string $title, string $body, ?string $link, ?Model $reference): Notification`
- `getUnreadNotifications(User $user, int $limit = 20): Collection`
- `getUnreadCount(User $user): int`
- `markAsRead(Notification $notification): void`
- `markAllAsRead(User $user): int`
- `deleteOldRead(int $days = 30): void` — scheduled cleanup

## 4. API Endpoints

All endpoints require authentication. Routes defined in `routes/web.php` with `/api` prefix (compatible with cPanel — no separate api.php needed).

| Method | URL | Controller Method | Description |
|--------|-----|-------------------|-------------|
| GET | `/api/notifications/unread` | `NotificationController@getUnread` | Returns `{data: [...], unread_count: N}` |
| POST | `/api/notifications/{id}/read` | `NotificationController@markAsRead` | Mark single as read |
| POST | `/api/notifications/read-all` | `NotificationController@markAllAsRead` | Mark all as read |

## 5. Event → Notification Mapping

| Event | Trigger | Payload | Recipients | Link |
|-------|---------|---------|------------|------|
| New ticket | `TicketController@store` | `{user: client, ticket}` | Admin & IT-Ops users | `/admin/tickets/{id}` |
| Ticket replied | `Admin\TicketController@reply` | `{ticket}` | Ticket owner (client) | `/tickets/{id}` |
| Demo request | `DemoController@store` | `{demo_request}` | Admin users | `/admin/clients` |
| New subscription | `Admin\ClientController@updateSubscription` | `{user: client, subscription}` | Admin & the client | Admin: `/admin/clients/{id}` / Client: `/client/subscription` |
| Payment confirmed | `Admin\ClientPaymentController@store` | `{user: client, payment}` | The client | `/client/payments/{id}` |

## 6. Frontend Components

### `useNotifications` Composable (`resources/js/Composables/useNotifications.ts`)

Reactive state:
- `unreadCount: Ref<number>`
- `notifications: Ref<Notification[]>`
- `showDropdown: Ref<boolean>`
- `loading: Ref<boolean>`
- `error: Ref<string | null>`

Methods:
- `fetchNotifications()` — GET unread list
- `markAsRead(notif)` — POST single read
- `markAllAsRead()` — POST read-all
- `startPolling(intervalMs = 20000)` — setInterval
- `stopPolling()` — clearInterval
- `toggleDropdown()` — toggle showDropdown
- `closeDropdown()` — close dropdown

### `NotificationBell.vue`

Self-contained component with:
- Bell icon with badge counter (unread count)
- Dropdown notification list on click
- States: loading (skeleton), empty ("Belum ada notifikasi"), error (retry message), unread > 99 ("99+")
- Click notification → markAsRead + Inertia visit to `link`
- "Tandai telah dibaca" link in header
- "Lihat semua notifikasi" link at bottom
- Click outside dropdown → close
- Auto-polling on mount, cleanup on unmount

### Pages

- `Admin/Notifications/Index.vue` — full notification list for admin (accessible via "Lihat semua notifikasi")
- `Client/Notifications/Index.vue` — full notification list for client (optional, can use same component)

## 7. Layout Integration

### AdminLayout.vue
Add `NotificationBell` before user avatar in both mobile header and desktop header areas.

### ClientLayout.vue
Add `NotificationBell` before logout button in desktop header; add to mobile header area.

## 8. Scheduled Cleanup

`app/Console/Kernel.php` (or `routes/console.php`):

```
// Delete read notifications older than 30 days
Schedule::call(fn => NotificationService::deleteOldRead(30))->daily();
```

## 9. Files to Create

```
database/migrations/xxxx_create_notifications_table.php
database/migrations/xxxx_create_demo_requests_table.php
app/Models/DemoRequest.php
app/Models/Notification.php
app/Services/NotificationService.php
app/Http/Controllers/NotificationController.php
app/Http/Controllers/Api/NotificationController.php              (if separating API)
resources/js/Composables/useNotifications.ts
resources/js/Components/NotificationBell.vue
resources/js/Pages/Admin/Notifications/Index.vue
```

## 10. Files to Modify

```
app/Http/Controllers/TicketController.php              → trigger on store
app/Http/Controllers/Admin/TicketController.php         → trigger on reply
app/Http/Controllers/DemoController.php                 → save to DB + trigger
app/Http/Controllers/Admin/ClientController.php         → trigger on subscription update
app/Http/Controllers/Admin/ClientPaymentController.php  → trigger on payment store
resources/js/Layouts/AdminLayout.vue                    → add NotificationBell
resources/js/Layouts/ClientLayout.vue                   → add NotificationBell
routes/api.php                                          → add notification routes
```

## 11. Design Decisions

- **Polling over WebSocket**: cPanel environment doesn't support persistent WebSocket servers. Polling every 20s is sufficient for these notification types.
- **Separate `demo_requests` table**: Demo requests currently only send email. Persisting to DB enables notifications and future admin review.
- **Per-user notifications**: Inserted per-recipient (not group-based) for simple read tracking and future flexibility.
- **Auto-cleanup**: Old read notifications purged after 30 days to prevent table bloat.
