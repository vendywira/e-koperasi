# e-Koperasi — SaaS Subscription Management & Billing Design

**Date:** 2026-07-28
**Status:** Approved
**Project:** e-Koperasi (Laravel 12 + Inertia.js Vue 3 + Tailwind CSS 4)

## 1. Context & Scope

e-Koperasi is a multi-tenant SaaS B2B platform for KSU (Koperasi Simpan Pinjam). Tenants are provisioned as isolated Laravel instances (ksu-app) via API. Current flow is manual: admin creates tenant, generates invoice, client uploads proof, admin confirms. This spec automates the full billing lifecycle while preserving all existing provisioning/support/management flows.

### Existing (kept untouched)
- Provisioning API POST `/api/tenants/{domain}/provision` to ksu-app
- Auto-suspend cron `tenant:auto-suspend` (grace period 7 days)
- Ticket support (client + admin, existing routes & UI)
- Admin manual tenant CRUD, extend, toggle-suspend
- Admin manual invoice generation & payment confirmation (manual transfer)
- Dark mode support
- Role system: admin / it-ops / editor / client

### New
- Plan & pricing model (plans, features, tiered)
- Auto-recurring billing engine with pre-bill cron
- Proration engine (upgrade immediate bill, downgrade credits next cycle)
- Coupon/discount engine (percentage/fixed, usage caps, per-plan)
- Duitku payment gateway integration (VA, QRIS, e-wallet, retail)
- Dunning emails & notifications (H-7, H-3, D+1, D+7)
- Client portal UI redesign (mobile-first)
- Admin billing dashboard (MRR, revenue, transaction logs)

## 2. Data Model Changes

### New Tables (migrations, no existing table modifications)

```sql
CREATE TABLE plans (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT NULL,
    max_resorts INT UNSIGNED NOT NULL DEFAULT 1,
    price_per_month DECIMAL(12,2) NOT NULL,
    trial_days  INT UNSIGNED NOT NULL DEFAULT 30,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);

CREATE TABLE plan_features (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id      BIGINT UNSIGNED NOT NULL,
    feature_text VARCHAR(255) NOT NULL,
    sort_order   INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
);

CREATE TABLE coupons (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    discount_type   ENUM('percentage', 'fixed') NOT NULL,
    discount_value  DECIMAL(12,2) NOT NULL,
    max_uses        INT UNSIGNED NULL,
    used_count      INT UNSIGNED NOT NULL DEFAULT 0,
    valid_from      TIMESTAMP NULL,
    valid_until     TIMESTAMP NULL,
    plan_ids        JSON NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);

CREATE TABLE subscription_line_items (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id   BIGINT UNSIGNED NOT NULL,
    type              ENUM('upgrade','downgrade','renewal','adjustment') NOT NULL,
    previous_plan_id  BIGINT UNSIGNED NULL,
    new_plan_id       BIGINT UNSIGNED NULL,
    previous_price    DECIMAL(12,2) NULL,
    new_price         DECIMAL(12,2) NULL,
    prorated_amount   DECIMAL(12,2) NULL,
    discount_amount   DECIMAL(12,2) NULL DEFAULT 0,
    total_amount      DECIMAL(12,2) NOT NULL,
    start_date        DATE NULL,
    end_date          DATE NULL,
    created_at        TIMESTAMP NULL,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

CREATE TABLE invoice_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id      CHAR(36) NOT NULL,
    description     VARCHAR(255) NOT NULL,
    quantity        INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price      DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount    DECIMAL(12,2) NOT NULL,
    type            ENUM('subscription','proration','discount') NOT NULL,
    created_at      TIMESTAMP NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

CREATE TABLE payment_channels (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50) NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    icon_url    VARCHAR(255) NULL,
    type        ENUM('va','qris','ewallet','retail') NOT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0
);

CREATE TABLE payment_transactions (
    id              CHAR(36) PRIMARY KEY,
    invoice_id      CHAR(36) NOT NULL,
    duitku_ref      VARCHAR(100) NULL,
    amount          DECIMAL(12,2) NOT NULL,
    channel_code    VARCHAR(50) NULL,
    channel_name    VARCHAR(100) NULL,
    status          ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
    paid_at         TIMESTAMP NULL,
    expiry          TIMESTAMP NULL,
    raw_response    JSON NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
```

### Existing Table Additions (new migration)

```sql
-- Add to subscriptions table
ALTER TABLE subscriptions
    ADD COLUMN plan_id              BIGINT UNSIGNED NULL AFTER tenant_id,
    ADD COLUMN billing_cycle        ENUM('monthly','quarterly','semiannual','yearly') NOT NULL DEFAULT 'monthly' AFTER price_per_resort,
    ADD COLUMN grace_period_days    INT UNSIGNED NOT NULL DEFAULT 7 AFTER billing_cycle,
    ADD COLUMN cancelled_at         TIMESTAMP NULL AFTER renewed_at,
    ADD INDEX idx_plan_id (plan_id);

-- Add to invoices table
ALTER TABLE invoices
    ADD COLUMN invoice_number       VARCHAR(30) NULL AFTER id,
    ADD COLUMN subtotal             DECIMAL(12,2) NULL AFTER total_amount,
    ADD COLUMN discount_amount      DECIMAL(12,2) NULL DEFAULT 0 AFTER subtotal,
    ADD COLUMN coupon_id            BIGINT UNSIGNED NULL AFTER discount_amount,
    ADD COLUMN due_date             TIMESTAMP NULL AFTER status,
    ADD COLUMN payment_channel      VARCHAR(50) NULL AFTER due_date,
    ADD COLUMN payment_transaction_id CHAR(36) NULL AFTER payment_channel,
    ADD UNIQUE INDEX idx_invoice_number (invoice_number);
```

## 3. Billing Lifecycle

### 3a. Trial Flow
1. Client registers → 30-day trial activated, 1 resort
2. Tenant provisioned immediately via existing ksu-app API
3. Tenant status: `trialing`, Subscription status: `trialing`
4. No invoice generated during trial

### 3b. Pre-Bill (Cron: `billing:pre-bill`, daily)
1. Scan subscriptions WHERE status='active' AND ends_at <= now+7days
2. Skip if pending/paid invoice already exists for overlapping period
3. Generate invoice with proper number (INV-{YYYYMM}-{XXXX}) — XXXX resets monthly, stored as sequence per month

> **ponytail:** Sequence uses a `monthly_sequences` table or DB query `SELECT COALESCE(MAX(CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED)), 0) + 1 FROM invoices WHERE invoice_number LIKE 'INV-YYYYMM-%'`. Replace with DB sequence when on PostgreSQL.
4. Apply cycle discount (quarterly=5%, semiannual=10%, yearly=20%)
5. Set due_date = now+14days
6. Create invoice_items for each line
7. Notify client (email + in-app)

### 3c. Dunning (Cron: `billing:dunning`, daily)
- H-7: "Invoice #{inv} akan jatuh tempo dalam 7 hari"
- H-3: "Invoice #{inv} akan jatuh tempo dalam 3 hari"
- D+1: "Invoice #{inv} sudah lewat jatuh tempo"
- D+7: "Tenant akan dinonaktifkan dalam 7 hari jika belum bayar"

### 3d. Cancel Expired Invoices (Cron: `billing:cancel-expired`, daily)
- Set status='cancelled' for invoices past due_date+7
- Triggers auto-suspend via existing `tenant:auto-suspend` cron

### 3e. Payment Confirmation (Duitku callback)
1. Validates signature (merchant key)
2. Updates PaymentTransaction → success
3. Updates Invoice → paid, paid_at=now
4. Extends subscription: ends_at = max(ends_at, now) + cycle_period
5. Reactivates tenant if suspended
6. Sends notification

## 4. Proration Engine

### Upgrade (mid-cycle)
- Calculate: (remaining_days / total_days) × (new_price - old_price)
- Generate new invoice for prorated amount
- Update subscription line item: type='upgrade'
- Update max_resorts immediately

### Downgrade (mid-cycle)
- Update max_resorts immediately
- Calculate credit: (remaining_days / total_days) × (old_price - new_price)
- Record subscription_line_item: type='downgrade' with negative total_amount
- Credit applied to next invoice (next month automatically lower)

## 5. Coupon Engine

### Validation Rules
- Code exists and is_active=true
- Not expired (valid_from/valid_until)
- Not over max_uses
- If plan_ids specified, current plan must match
- Only one coupon per invoice
- Cannot stack with cycle discount (cycle discount auto-applied, coupon optional)

### Application
- Client enters code → AJAX validation → show discount preview
- On invoice generation: apply coupon, create invoice_item type='discount'
- increment coupon.used_count

## 6. Duitku Integration

### Configuration
```
DUITKU_MERCHANT_CODE=xxxx
DUITKU_API_KEY=xxxx
DUITKU_CALLBACK_URL=https://e-koperasi.com/webhook/duitku
DUITKU_RETURN_URL=https://e-koperasi.com/payment/{ref}/finish
DUITKU_EXPIRY_PERIOD=1440  # minutes (24h for VA)
```

### Payment Channel Sync
- Duitku provides list of available payment channels per merchant
- Sync on deploy / via admin button
- Store in `payment_channels` table
- Client sees: VA (BCA, Mandiri, BNI, BRI, Permata), QRIS, e-wallet (GoPay, OVO, Dana, ShopeePay), retail (Indomaret, Alfamart)

### Webhook Handler
```
POST /webhook/duitku
  Body: { merchantCode, amount, merchantOrderId, reference, paymentCode, 
          paymentMethod, callbackUrl, signature, statusCode }
  Status codes: 00=Success, 01=Pending, 02=Failed, 03=Expired
  03 → payment_transaction.status='failed' 
  00 → trigger payment_confirmation flow (section 3e)
```

## 7. UI/UX Design

### Design System
| Token | Value |
|-------|-------|
| Primary | `#059669` (emerald-600) + palette 50-950 |
| Font body | Inter (system fallback) |
| Font mono | JetBrains Mono (pricing, numbers) |
| Radius card | `rounded-xl` (0.75rem) |
| Radius button | `rounded-lg` (0.5rem) |
| Shadow base | `shadow-sm` |
| Shadow modal | `shadow-xl` |

### Mobile-First Approach
- Breakpoints: `sm:640px` `md:768px` `lg:1024px` `xl:1280px`
- Tables → stacked cards on mobile (< md)
- Filters → bottom sheet on mobile, inline on desktop
- Navigation: existing sidebar (desktop) + hamburger (mobile) — kept
- Touch targets: minimum 44×44px

### Client Portal Pages

| Page | Key Changes |
|------|-------------|
| Dashboard | + billing summary card (next due, amount), trial countdown bar |
| Subscription | redesigned: pricing cards, plan selector, upgrade/downgrade slider |
| Invoices | redesigned: InvoiceCard component, payment channel selector, countdown timer for VA, Duitku quick pay or upload proof |
| Payments | timeline view, filter by status, payment detail with breakdown |
| Coupon input | at checkout, real-time validation, discount badge preview |
| Billing settings | new: default payment method, transaction history, billing address |

### Admin Panel Pages

| Page | Key Changes |
|------|-------------|
| Dashboard | MRR, active tenants chart, pending invoices, expiring-soon alert |
| Billing | new: revenue dashboard, invoice list with filters, transaction log, failed payments |
| Plans | new: CRUD plans + features, drag reorder, toggle active |
| Coupons | new: CRUD coupons, usage stats, validity calendar |
| Tenant create | + plan selector, billing cycle, coupon, prorata preview |
| Tenant detail | + subscription timeline, payment history inline, next bill date |

### New Shared Components
- `BillingSummaryCard` — due amount, next bill, payment method
- `PlanSelector` — radio group with pricing cards
- `PaymentChannelSelector` — grid of payment methods
- `InvoiceCard` — card template with status badge + actions
- `CouponInput` — input + apply with validation feedback
- `UsageProgressBar` — subscription progress
- `BillingTimeline` — vertical timeline
- `PriceDisplay` — formatted price with optional discount strikethrough

## 8. File Structure Additions

```
app/
├── Console/Commands/
│   ├── BillingPreBill.php          [NEW]
│   ├── BillingDunning.php          [NEW]
│   └── BillingCancelExpired.php    [NEW]
├── Http/Controllers/
│   ├── Client/
│   │   ├── CouponController.php    [NEW]
│   │   ├── InvoiceController.php   [REDESIGN — payment channel]
│   │   ├── PaymentController.php   [REDESIGN — Duitku flow]
│   │   └── SubscriptionController.php [REDESIGN — upgrade/downgrade]
│   ├── Admin/
│   │   ├── PlanController.php      [NEW]
│   │   ├── CouponController.php    [NEW]
│   │   └── BillingController.php   [NEW]
│   └── Webhook/
│       └── DuitkuController.php    [NEW]
├── Models/
│   ├── Plan.php                    [NEW]
│   ├── PlanFeature.php             [NEW]
│   ├── Coupon.php                  [NEW]
│   ├── InvoiceItem.php             [NEW]
│   ├── SubscriptionLineItem.php    [NEW]
│   ├── PaymentChannel.php          [NEW]
│   └── PaymentTransaction.php      [NEW]
└── Services/
    ├── BillingService.php          [NEW]
    ├── CouponService.php           [NEW]
    └── DuitkuService.php           [NEW]
resources/js/Pages/
├── Client/
│   ├── BillingSettings.vue         [NEW]
│   ├── Invoices.vue                [REDESIGN]
│   ├── Payments.vue                [REDESIGN]
│   └── Subscription.vue            [REDESIGN]
├── Admin/
│   ├── Billing/
│   │   ├── Index.vue               [NEW]
│   │   └── TransactionLog.vue      [NEW]
│   ├── Plans/
│   │   ├── Index.vue               [NEW]
│   │   └── Form.vue                [NEW]
│   └── Coupons/
│       ├── Index.vue               [NEW]
│       └── Form.vue                [NEW]
routes/
├── web.php                         [ADD webhook & billing routes]
└── cms.php                         [ADD admin plan/coupon/billing routes]
```

## 9. Cron Schedule

```
// Existing
tenant:auto-suspend --grace-days=7  → daily

// New
billing:pre-bill                    → daily (06:00)
billing:dunning                     → daily (08:00)
billing:cancel-expired              → daily (04:00)
```

## 10. Estimated Effort

| Module | Days | Dependencies |
|--------|------|-------------|
| Migrations + models | 2 | — |
| BillingService (pre-bill, proration) | 3 | Models |
| CouponService + CouponController | 2 | Models |
| DuitkuService + Webhook | 3 | BillingService |
| Admin UI: Plans, Coupons, Billing | 3 | Controllers |
| Client UI redesign: Invoice, Payment, Subscription | 4 | BillingService, Duitku flow |
| Cron commands + testing | 2 | BillingService |
| Integration testing + Duitku sandbox | 2 | All |
| **Total** | **~21 days** | |

## 11. What to Skip (YAGNI)

- No separate billing microservice (stays in e-Koperasi monolith)
- No usage-based metering (per-resort is flat limit, not metered)
- No bank reconciliation automation (manual confirm stays for transfer)
- No tax/invoice PDF generation (defer to phase 2)
- No multi-currency (IDR only)
- No partial payments (full or nothing)