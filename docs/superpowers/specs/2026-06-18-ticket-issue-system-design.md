# Ticket Issue System — Design Spec

**Date:** 2026-06-18
**Project:** e-koperasi.com

## 1. Overview

Sistem ticket issue untuk KSU yang memungkinkan client me-raise ticket, dan IT-Ops/Admin menangani, membalas, serta menyelesaikan ticket. Terintegrasi langsung ke e-koperasi (Laravel + Inertia + Vue.js).

## 2. Database Design

### 2.1 New Tables

#### `tickets`

| Column | Type | Notes |
|---|---|---|
| id | uuid (PK) | Laravel UUID |
| ticket_number | string(20) | Auto-format: `TKT-XXXX` (sequential) |
| user_id | uuid (FK → users) | Client yang raise ticket |
| subject | string(255) | |
| description | text | |
| status | enum | `pending`, `acknowledge`, `in_progress`, `solved`, `close` |
| priority | enum | `low`, `medium`, `high` |
| assigned_to | uuid (FK → users, nullable) | IT-Ops/Admin yg handle |
| timestamps | | + soft_deletes |

#### `attachments` (Polymorphic)

| Column | Type | Notes |
|---|---|---|
| id | uuid (PK) | |
| attachable_id | uuid | FK ke ticket atau ticket_reply |
| attachable_type | string | `ticket` atau `ticket_reply` |
| file_path | string | Path di storage |
| file_name | string | Nama asli file |
| file_size | integer | Bytes |
| mime_type | string | |
| created_at | timestamp | |

#### `ticket_replies`

| Column | Type | Notes |
|---|---|---|
| id | uuid (PK) | |
| ticket_id | uuid (FK → tickets) | |
| user_id | uuid (FK → users) | Siapa yang reply |
| message | text | |
| timestamps | | |

### 2.2 Enums

- `ticket_status`: `pending`, `acknowledge`, `in_progress`, `solved`, `close`
- `ticket_priority`: `low`, `medium`, `high`

### 2.3 User Role

Tambah `it-ops` ke enum `role` di User model. Admin dan IT-Ops sama-sama bisa akses panel ticket.

## 3. Status Flow

```
pending ──→ acknowledge ──→ in_progress ──→ solved ──→ close
                  │                               │
                  └── Auto saat admin/IT-Ops       │
                      klik "Acknowledge"           │
                                                   │
              ┌────────────────────────────────────┘
              │
              ▼
          solved → Close oleh admin/IT-Ops ATAU client
          close  → Terminal state (read-only, no more replies)
```

- Client bisa close ticket yang sudah `solved`
- Reply hanya bisa jika status belum `close`
- Setiap status change tercatat via Laravel timestamps

## 4. Architecture

### 4.1 Stack

- **Backend:** Laravel (di e-koperasi yang sudah ada)
- **Frontend:** Vue.js + Inertia.js
- **Auth:** Reuse auth & role middleware existing + tambah role `it-ops`
- **Storage:** Laravel Storage (public disk → `storage/app/public/tickets/`)
- **Mail:** Laravel Mail + Queue (database driver)
- **Attachment:** Polymorphic relationship (morphMany)

### 4.2 Routes

**Client routes (role: client):**

| Method | URI | Controller/Action |
|---|---|---|
| GET | `/tickets` | `App\Http\Controllers\TicketController@index` |
| GET | `/tickets/create` | `App\Http\Controllers\TicketController@create` |
| POST | `/tickets` | `App\Http\Controllers\TicketController@store` |
| GET | `/tickets/{id}` | `App\Http\Controllers\TicketController@show` |
| POST | `/tickets/{id}/reply` | `App\Http\Controllers\TicketController@reply` |
| PUT | `/tickets/{id}/close` | `App\Http\Controllers\TicketController@closeByClient` |
| POST | `/tickets/{id}/attachments` | `App\Http\Controllers\TicketController@uploadAttachment` |

**Admin/IT-Ops routes (role: admin, it-ops):**

| Method | URI | Controller/Action |
|---|---|---|
| GET | `/admin/tickets` | `App\Http\Controllers\Admin\TicketController@index` |
| GET | `/admin/tickets/{id}` | `App\Http\Controllers\Admin\TicketController@show` |
| PUT | `/admin/tickets/{id}/status` | `App\Http\Controllers\Admin\TicketController@updateStatus` |
| POST | `/admin/tickets/{id}/reply` | `App\Http\Controllers\Admin\TicketController@reply` |
| PUT | `/admin/tickets/{id}/assign` | `App\Http\Controllers\Admin\TicketController@assign` |
| DELETE | `/admin/tickets/{id}/attachment/{attId}` | `App\Http\Controllers\Admin\TicketController@deleteAttachment` |

**API routes (for dashboard stats):**

| Method | URI | Action |
|---|---|---|
| GET | `/api/tickets/stats` | Statistik per status untuk dashboard |

### 4.3 File Upload Rules

- Max file size: 10 MB
- Allowed types: `pdf`, `jpg`, `jpeg`, `png`, `mp4`, `mov`, `avi`
- Storage path: `storage/app/public/tickets/{ticket_id}/`
- Multiple file upload per request

## 5. Notifikasi Email

### 5.1 Triggers

| Event | Recipient | Template |
|---|---|---|
| Ticket baru dibuat | Client (konfirmasi) + IT-Ops/Admin | `ticket-created.blade.php` |
| Reply dari IT-Ops/Admin | Client | `ticket-reply.blade.php` |
| Status berubah | Client | `ticket-status.blade.php` |

### 5.2 Queue

- Semua email dikirim via Laravel Queue (database driver)
- Butuh jobs table migration (`php artisan queue:table`)
- Mailables: `TicketCreated`, `TicketReplied`, `TicketStatusChanged`

### 5.3 Mail Config

Tinggal isi `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@e-koperasi.com"
MAIL_FROM_NAME="e-Koperasi Support"
```

## 6. Halaman Frontend (Vue.js)

### 6.1 Client Pages (Layout: ClientLayout)

| Page | File | Description |
|---|---|---|
| Ticket List | `resources/js/Pages/Client/Tickets/Index.vue` | Daftar ticket client — filter, search, pagination |
| Create Ticket | `resources/js/Pages/Client/Tickets/Create.vue` | Form: subject, description, priority, file upload |
| Ticket Detail | `resources/js/Pages/Client/Tickets/Show.vue` | Detail ticket + thread replies + balas + close |

### 6.2 Admin/IT-Ops Pages (Layout: AdminLayout)

| Page | File | Description |
|---|---|---|
| Ticket List | `resources/js/Pages/Admin/Tickets/Index.vue` | All tickets — filter by status, priority, assigned |
| Ticket Detail | `resources/js/Pages/Admin/Tickets/Show.vue` | Detail + reply + update status + assign |

### 6.3 Dashboard Stats

- **Client dashboard:** Ringkasan ticket client (total, pending, solved, close)
- **Admin dashboard:** Ringkasan semua ticket — chart/total per status

## 7. Models & Relationships

```
User
  - hasMany(Ticket, 'user_id')         # tickets yg di-raise
  - hasMany(Ticket, 'assigned_to')     # tickets yg di-assign
  - hasMany(TicketReply, 'user_id')

Ticket
  - belongsTo(User, 'user_id')
  - belongsTo(User, 'assigned_to')
  - hasMany(TicketReply)
  - morphMany(Attachment, 'attachable')

TicketReply
  - belongsTo(Ticket)
  - belongsTo(User)
  - morphMany(Attachment, 'attachable')

Attachment (polymorphic)
  - morphTo('attachable')
```

## 8. Error Handling & Edge Cases

- **Tidak bisa reply ke ticket `close`** — Show error toast "Ticket sudah ditutup"
- **File upload gagal karena size** — Frontend validation + server validation
- **Queue gagal kirim email** — Job log otomatis retry (Laravel queue default: retry 3x)
- **Ticket number format** — Auto-generate via DB sequence agar unik & sequential
- **Role check** — Middleware `role:admin,it-ops` untuk admin routes
- **Soft deletes** — Ticket tidak pernah hard-deleted

## 9. Reply-via-Email (v2 — Inbound Email)

Fitur ini memungkinkan client **membalas ticket langsung dari email** tanpa perlu login ke dashboard. Reply masuk diproses dan ditambahkan ke thread ticket.

### 9.1 Cara Kerja

```
Client terima email notifikasi
       │
       ▼
Client reply email (dari email client)
       │
       ▼
Inbound mail handler menerima email masuk
       │
       ├── Parse: parse inbound email → extract reply + attachment
       │
       ├── Authenticate: cocokkan Reply-To / Reference ID dengan ticket
       │
       └── Store: simpan reply sebagai TicketReply + attachments → kirim notifikasi ke IT-Ops
```

### 9.2 Identifikasi Ticket

Setiap email notifikasi harus menyertakan **unique identifier** yang bisa diparsing dari reply:

**Opsi A — Custom Reply-To Address (Rekomendasi)**
```
Reply-To: ticket+TKT-0001-abc123@e-koperasi.com
```
- Bagian `TKT-0001` = ticket number
- Bagian `abc123` = hash rahasia untuk validasi (tidak bisa brute-force)
- Mail server konfigurasi catch-all atau pipe ke script Laravel

**Opsi B — Subject Tracking**
```
Subject: [Ticket TKT-0001] Ada masalah dengan sistem pembayaran
REPLY: TKT-0001|<hash>
```
- Reply subject di-scan untuk regex ticket number
- Kurang reliable karena subject bisa diedit

**Opsi C — Email Headers**
- Kirim custom email header `X-Ticket-ID` atau `X-Ticket-Reference`
- Forward/reply tetap membawa headers
- Parse headers setelah inbound capture

### 9.3 Arsitektur Inbound

```
[Client reply] → [Mailgun/SendGrid Inbound Parse]
                    → Webhook POST ke /api/inbound/ticket-reply
                    → Validate HMAC signature
                    → Parse reply content (strip quoted text)
                    → Optional: handle attachments
                    → Store reply & notify IT-Ops
```

atau self-hosted:

```
[Client reply] → [Server Mail Server]
                    → pipe ke artisan command
                    → Parse raw email
                    → Process reply
```

**Recommended tools:**
- **Mailgun Inbound Parse** — paling mudah, webhook JSON, auto-parse multipart
- **SendGrid Inbound Parse** — sama dengan Mailgun
- **Laravel Mailbox** (package) — handle inbound email native Laravel
- **Self-hosted:** `imap` PHP extension + cron job fetch INBOX

### 9.4 Stripping Quoted Text

Ketika client reply, email biasanya mengandung quoted text:
```
On Thu, 18 Jun 2026 at 14:30, Support wrote:
> Terima kasih sudah melaporkan...
```

Perlu library untuk extract reply saja:
- **Package:** `willdurand/email-reply-parser` (PHP)
- Atau regex threshold detection

### 9.5 Security Considerations

- **Rate limit** per IP / per ticket per jam
- **HMAC signature** untuk webhook inbound
- **Hash rahasia** di Reply-To address (bukan base64 encode)
- **Attachment scanning** — scan virus sebelum simpan
- **Only verified sender** — cocokkan email sender dengan email user di database
- **Logging** — semua inbound email di-log untuk audit

### 9.6 Database Changes (v2)

Tidak perlu perubahan tabel utama — cukup tambah kolom nullable di `ticket_replies`:
```sql
ALTER TABLE ticket_replies ADD COLUMN is_inbound boolean DEFAULT false;
```
Atau pakai created_by field (system = inbound reply).

### 9.7 Implementation Steps (v2)

1. Setup inbound email service (Mailgun/SendGrid atau fetch IMAP)
2. Install/buat email parser untuk strip quoted text
3. Buat console command: `tickets:process-inbound`
4. Buat Webhook controller: `POST /api/inbound/ticket-reply`
5. Handle attachments via inbound
6. Integrasi queue untuk processing
7. Testing: kirim reply dari berbagai email client (Gmail, Outlook, Apple Mail)

## 10. Future Considerations (yang perlu riset lebih lanjut)

- Knowledge base / FAQ untuk self-service
- SLA auto-escalation (ticket terlalu lama di pending/acknowledge)
- Prioritas otomatis berdasarkan keyword
- Template reply cepat (canned responses) untuk IT-Ops
