# Client Subscription Dashboard — Design Spec

## 1. Ringkasan

Dashboard khusus untuk **koperasi pelanggan** (client) melihat status subscription mereka. Client login, lihat detail paket, status aktif/expired, sisa hari, riwayat pembayaran, dan download invoice.

## 2. Arsitektur

- **Satu aplikasi** — domain sama (e-koperasi.com), prefix `/client/*`
- Pendekatan: tambah ke aplikasi Inertia + Laravel yang sudah ada (Opsi A)
- Layout dashboard terpisah dari admin CMS: `ClientLayout.vue`
- Auth: login terpisah dari admin (halaman `/client/login` sendiri)

## 3. Data Model

### Tabel `subscriptions`

| Kolom | Type | Keterangan |
|---|---|---|
| id | bigIncrements | |
| user_id | foreignId → users.id | Client pemilik |
| plan | string | starter, premium, enterprise |
| status | string | active, expired, cancelled, trialing |
| started_at | datetime | |
| trial_ends_at | datetime nullable | Masa percobaan |
| ends_at | datetime nullable | Tanggal berakhir |
| renewed_at | datetime nullable | Terakhir diperpanjang |
| created_at | timestamp | |
| updated_at | timestamp | |

### Tabel `payments`

| Kolom | Type | Keterangan |
|---|---|---|
| id | bigIncrements | |
| subscription_id | foreignId → subscriptions.id | |
| amount | decimal(12,0) | Dalam Rupiah (integer, no fraction) |
| status | string | paid, pending, failed |
| payment_method | string | manual_transfer |
| paid_at | datetime nullable | |
| notes | text nullable | Catatan admin |
| receipt_number | string nullable | Nomor invoice |
| created_at | timestamp | |
| updated_at | timestamp | |

### Relasi

```
User (role: client) → hasOne → Subscription → hasMany → Payment
```

## 4. Route Structure

### Client Routes (prefix `/client`, middleware auth+role:client)

| Method | URI | Controller | Keterangan |
|---|---|---|---|
| GET | /client/login | AuthController@showClientLogin | Halaman login client |
| POST | /client/login | AuthController@clientLogin | Proses login client |
| POST | /client/logout | AuthController@logout | Logout |
| GET | /client/dashboard | ClientDashboardController@index | Beranda |
| GET | /client/subscription | ClientSubscriptionController@show | Detail langganan |
| GET | /client/payments | ClientPaymentController@index | Riwayat pembayaran |
| GET | /client/payments/{id} | ClientPaymentController@show | Detail invoice |

### Admin Routes (prefix `/admin`, middleware auth+role:admin)

| Method | URI | Controller | Keterangan |
|---|---|---|---|
| GET | /admin/clients | AdminClientController@index | Daftar client |
| GET | /admin/clients/{id} | AdminClientController@show | Detail client + subscription |
| PUT | /admin/clients/{id}/subscription | AdminSubscriptionController@update | Edit subscription |
| POST | /admin/clients/{id}/payments | AdminPaymentController@store | Input pembayaran manual |
| GET | /admin/clients/{id}/payments | AdminPaymentController@index | Riwayat pembayaran client |

## 5. Halaman & Komponen Vue

### ClientLayout.vue

- Sidebar kiri dengan navigasi: Beranda, Langganan, Pembayaran, Akun
- Header atas dengan nama koperasi
- Mode terang/gelap mengikuti existing
- Tombol logout
- Simpler dibanding AdminLayout — lebih bersih, font lebih besar

### Halaman Dashboard (Client/Dashboard.vue)

- Kartu **Status Langganan** — icon bulat hijau/merah, teks "Aktif" / "Expired", progress bar sisa hari
- Kartu **Paket** — nama paket (Starter/Premium/Enterprise), harga, tombol "Lihat Detail"
- Kartu **Ringkasan Pembayaran** — 3-5 baris terakhir, link ke halaman pembayaran

### Halaman Langganan (Client/Subscription.vue)

- Detail paket: nama, harga per bulan, daftar fitur dari config
- Informasi tanggal mulai, berakhir, status
- Badge status dengan warna

### Halaman Pembayaran (Client/Payments.vue)

- Tabel: No, Tanggal, Invoice, Jumlah, Status
- Pagination
- Klik invoice → detail

### Halaman Detail Invoice (Client/PaymentDetail.vue)

- Format invoice: nomor invoice, tanggal, untuk periode, jumlah, status
- Tombol Download PDF (nanti)

### Halaman Login Client (Auth/ClientLogin.vue)

- Form email + password
- Tampilan branding e-Koperasi (logo + tagline)
- Link ke halaman utama website
- **Terpisah dari login admin** — admin login tetap di `/login`

## 6. Perubahan Existing Files

### Backend

- **User model**: tambah `phone` (nullable)
- **Role migration**: status sudah `default: 'editor'`, untuk client di-set manual via admin nanti
- **CheckRole middleware**: sudah support role checking — tinggal tambah `'client'`
- **HandleInertiaRequests**: share `auth.user.subscription` ke client pages
- **AuthController**: tambah `showClientLogin`, `clientLogin`

### Frontend

- **AdminLayout.vue** — tidak diubah (client punya layout sendiri)
- **CmsPreview.vue** — tidak perlu diubah

## 7. Admin Panel untuk Kelola Client

Dibutuhkan halaman admin sederhana untuk:
- Daftar client (users dengan role=client)
- Buat/ubah subscription client
- Input pembayaran manual & generate nomor invoice

Ini penting karena pembayaran masih manual — admin yang input.

## 8. Alur Kerja

### Client Signup
1. Admin daftarkan koperasi sebagai user role=client
2. Admin buat subscription untuk client tersebut
3. Client login via `/client/login`
4. Client lihat status subscription + riwayat pembayaran

### Pembayaran Manual
1. Client transfer ke rekening e-Koperasi
2. Admin input pembayaran di panel admin
3. Status payment = paid, subscription ends_at diperpanjang
4. Client lihat riwayat pembayaran di dashboard

### Expired
- Ends_at lewat → status auto expired
- Dashboard client menampilkan status merah
- Admin perpanjang via input pembayaran baru

## 9. Scope Saat Ini (v1)

✅ Data model migrations
✅ Model Subscription + Payment + relasi
✅ Client login
✅ Client dashboard (status, ringkasan)
✅ Subscription detail page
✅ Payment history page
✅ Invoice detail page
✅ Admin panel: daftar client
✅ Admin panel: input subscription
✅ Admin panel: input pembayaran manual
✅ ClientLayout.vue
❌ Download PDF invoice (v2)
❌ Email notification (v2)
❌ Auto-expire scheduler (v2 — bisa pakai command artisan manual dulu)
❌ Payment gateway integration (v2)

## 10. Teknis

- Framework: Laravel + Inertia + Vue 3 + TypeScript
- Database: MySQL via migration
- CSS: Tailwind (mengikuti existing)
- Layout: ClientLayout.vue dengan sidebar navigasi
- State: Props dari server via Inertia
