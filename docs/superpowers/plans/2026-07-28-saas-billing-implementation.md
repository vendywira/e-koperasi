# e-Koperasi SaaS Subscription & Billing Implementation Plan

**For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement plan task-by-task.

**Goal:** Transform e-Koperasi from manual billing to automated SaaS subscription management with Duitku payment gateway, coupon engine, proration, and redesigned UI.

**Architecture:** All new code lives inside existing Laravel monolith. BillingService orchestrates invoice generation, proration, discount. DuitkuService handles payment gateway. Eloquent models use UUID traits. Vue 3 Inertia frontend mobile-first with Tailwind CSS 4 + Reka UI.

**Tech Stack:** Laravel 12, Inertia.js Vue 3, Tailwind CSS 4, Reka UI, Lucide Icons, Duitku API, barryvdh/laravel-dompdf

---

## File Structure Map

```
app/
├── Console/Commands/
│   ├── BillingPreBill.php              [CREATE]
│   ├── BillingDunning.php              [CREATE]
│   └── BillingCancelExpired.php        [CREATE]
├── Http/Controllers/
│   ├── Client/
│   │   ├── CouponController.php        [CREATE]
│   │   ├── InvoiceController.php       [MODIFY]
│   │   ├── PaymentController.php       [MODIFY]
│   │   └── SubscriptionController.php  [MODIFY]
│   ├── Admin/
│   │   ├── PlanController.php          [CREATE]
│   │   ├── CouponController.php        [CREATE]
│   │   ├── PaymentChannelController.php [CREATE]
│   │   ├── PaymentTransactionController.php [CREATE]
│   │   └── BillingController.php       [CREATE]
│   ├── Webhook/
│   │   └── DuitkuController.php        [CREATE]
│   └── HomeController.php              [MODIFY]
├── Models/
│   ├── Plan.php, PlanFeature.php, Coupon.php [CREATE]
│   ├── InvoiceItem.php, SubscriptionLineItem.php [CREATE]
│   └── PaymentChannel.php, PaymentTransaction.php [CREATE]
├── Services/
│   ├── BillingService.php              [CREATE]
│   ├── CouponService.php               [CREATE]
│   └── DuitkuService.php               [CREATE]
├── routes/{web.php,cms.php}           [MODIFY]
resources/
├── js/Components/{InvoiceCard,BillingSummaryCard,PaymentChannelSelector,CouponInput,PlanSelector,PriceDisplay}.vue [CREATE]
├── js/Pages/Admin/{Plans,Coupons,Billing,PaymentChannels,PaymentTransactions}/*.vue [CREATE]
├── js/Pages/Client/{Invoices,Payments,Subscription,BillingSettings}.vue [MODIFY]
└── views/pdf/invoice.blade.php         [CREATE]
```

---

### Task 1: Migrations & Eloquent Models (2 days)

Create 9 migrations + 7 Eloquent models. UUID PKs with HasUuids trait. All migrations backwards-compatible — ALTER TABLE on existing subscriptions/invoices, new tables never touch existing schema.

```bash
# Migrations (in order):
2026_XX_XX_000001_create_plans_table.php
2026_XX_XX_000002_create_plan_features_table.php
2026_XX_XX_000003_create_coupons_table.php
2026_XX_XX_000004_create_subscription_line_items_table.php
2026_XX_XX_000005_create_invoice_items_table.php
2026_XX_XX_000006_create_payment_channels_table.php
2026_XX_XX_000007_create_payment_transactions_table.php
2026_XX_XX_000008_add_billing_fields_to_subscriptions.php
2026_XX_XX_000009_add_billing_fields_to_invoices.php
```

**Plans:** id(UUID PK), name, description, max_resorts, price_per_month, trial_days(default 30), sort_order, is_active, timestamps  
**PlanFeatures:** id(UUID PK), plan_id(FK), feature_text, sort_order, timestamps  
**Coupons:** id(UUID PK), code(unique), discount_type(enum), discount_value, max_uses(nullable), used_count(default 0), valid_from, valid_until, plan_ids(json nullable), is_active, timestamps  
**SubscriptionLineItems:** id(UUID PK), subscription_id(FK), type(upgrade/downgrade/renewal/adjustment), previous_plan_id, new_plan_id, previous_price, new_price, prorated_amount, discount_amount, total_amount, start_date, end_date, timestamps  
**InvoiceItems:** id(UUID PK), invoice_id(FK), description, quantity, unit_price, discount_amount, total_amount, type(subscription/proration/discount), timestamps  
**PaymentChannels:** id(UUID PK), code(unique), name, icon_url, type(va/qris/ewallet/retail), is_active, sort_order, timestamps  
**PaymentTransactions:** id(UUID PK), invoice_id(FK), duitku_ref, amount, channel_code, channel_name, status(pending/success/failed/expired), paid_at, expiry, raw_response(json), timestamps  

**ALTER subscriptions:** ADD plan_id(UUID FK), billing_cycle(enum), grace_period_days(default 7), cancelled_at  
**ALTER invoices:** ADD invoice_number(unique), subtotal, discount_amount, coupon_id(FK), due_date, payment_channel, payment_transaction_id(FK)

Models: Plan (hasMany features), PlanFeature (belongsTo plan), Coupon (isValidForPlan()), InvoiceItem, SubscriptionLineItem, PaymentChannel (scope active), PaymentTransaction (belongsTo invoice).

```bash
git commit -m "feat: add billing tables (plans, coupons, invoice_items, payment_channels, payment_transactions) and Eloquent models"
```

### Task 2: BillingService & CouponService (3 days)

**BillingService methods:**
- `generateInvoice(Subscription, ?Coupon, bool isManual)` — calculates subtotal, cycle discount (monthly=0%, quarterly=5%, semiannual=10%, yearly=20%), applies coupon discount, generates invoice number (INV-YYYYMM-XXXX with monthly sequence), creates Invoice + InvoiceItems, increments coupon usage. Prevents duplicate invoices for same period.
- `calculateProration(Subscription, newMaxResorts, newPricePerResort)` — calculates prorata: (remaining_days/total_days) * (new_price - old_price). Returns array with type.
- `upgrade(Subscription, newMaxResorts, newPricePerResort, ?Coupon)` — calls calculateProration, if positive creates prorata invoice, updates subscription immediately.
- `downgradeCredit(Subscription, proration)` — updates subscription immediately, records credit to next invoice via SubscriptionLineItem.
- `confirmPayment(Invoice)` — marks invoice paid, extends subscription ends_at (max(current_ends_at, now+1day) + cycle_months), reactivates tenant if suspended.
- `extendSubscription(Subscription, days)` — admin manual extend. new_ends_at = max(ends_at, now+1day) + days.

**CouponService methods:**
- `validateCoupon(code, planId)` — validates active, not expired, not over max_uses, applies to plan. Throws ValidationException with user-friendly message.
- `calculateDiscount(Coupon, subtotal)` — returns discount amount, capped at subtotal.

```bash
git commit -m "feat: add BillingService (invoice generation, proration, payment confirmation, extend) and CouponService"
```

### Task 3: DuitkuService & Webhook (2 days)

**DuitkuService:**
- `createInvoice(Invoice, paymentMethod, customerName, customerEmail, customerPhone)` — POST to Duitku createInvoice API. Creates PaymentTransaction record. Returns paymentUrl, vaNumber, reference.
- `checkStatus(merchantOrderId)` — GET transaction status from Duitku.
- `verifyCallback(array data)` — validates md5 signature with merchant key.
- `syncPaymentChannels()` — GET paymentMethod list, upserts payment_channels table.

**DuitkuController (webhook):**
- POST /webhook/duitku — no auth, receives Duitku callback
- Verifies signature, updates PaymentTransaction status
- On success (statusCode=00): calls BillingService::confirmPayment(), sends notification

**Config:**
- config/services.php: duitku merchant_code, api_key, callback_url, return_url, expiry_period, sandbox
- .env.example: DUITKU_ vars

```bash
git commit -m "feat: add DuitkuService, webhook handler, and config"
```

### Task 4: Admin CRUD Controllers (2 days)

**PlanController:** index (with features, ordered), create/store, edit/update (manage features inline), destroy  
**CouponController:** index (paginated), create/store, edit/update, destroy  
**BillingController:** index (invoice list + stats: MRR, total_revenue, pending_count, paid_count), transactionLog (payment_transactions paginated)  
**PaymentChannelController:** index, sync (from Duitku API), toggle active, reorder  
**PaymentTransactionController:** index (filter by status), checkStatus (call Duitku API)

**TenantController — add retryProvision method:** POST /admin/tenants/{id}/retry-provision. Calls existing provision API to ksu-app with stored tenant data. On success: tenant→active, subscription→active.

**Routes (cms.php):** plan CRUD, coupon CRUD, billing index+transactions, payment-channel sync+toggle+reorder, payment-transaction index+check-status, tenant retry-provision

```bash
git commit -m "feat: admin CRUD controllers — plans, coupons, billing, payment channels, transactions, retry provision"
```

### Task 5: Client Controllers & Routes (2 days)

**CouponController (Client):** POST /client/coupon/validate — AJAX validation, returns {valid, discount_type, discount_value} or 422.

**InvoiceController — download PDF:** uses barryvdh/laravel-dompdf, renders resources/views/pdf/invoice.blade.php. Includes invoice items table, subtotal/discount/total, payment status.

**PaymentController — payViaDuitku:** POST /client/payment/duitku — validates invoice ownership + pending status, calls DuitkuService::createInvoice, redirects to payment URL.

**SubscriptionController — upgrade:** POST /client/subscription/upgrade — validates subscription ownership, validates coupon if provided, calls BillingService::upgrade() or downgradeCredit(), redirects with message.

**Blade PDF view:** Clean bordered table layout. Company logo from SiteConfig. Invoice number, dates, line items, subtotal, discount, grand total. Footer with company name.

**Routes (web.php):** webhook Duitku (public POST), client coupon validate, client duitku pay, client invoice download, client subscription upgrade. Admin invoice download in cms.php.

```bash
git commit -m "feat: client payment flow (Duitku, coupon, invoice PDF), upgrade/downgrade, webhook handler"
```

### Task 6: Home Page — Dynamic Pricing (0.5 day)

**HomeController:** loads Plan::with('features')->active()->get() → passes to Home page as `plans` prop.

**PricingTable.vue:** accepts `plans` prop. When available, renders from DB data instead of CMS config. Shows price/month, feature list, cycle discount badges (hemat 5%, 10%, 20%). "Pilih" button links to /register?plan={id}. Falls back to CMS tiers if no DB plans.

```bash
git commit -m "feat: dynamic home page pricing from plans table"
```

### Task 7: Cron Commands (1 day)

**BillingPreBill:** daily 06:00. Scans active subscriptions expiring in ≤7 days. Calls BillingService::generateInvoice. Sends in-app notification on new invoice.

**BillingDunning:** daily 08:00. Scans pending invoices. Sends reminders at H-7, H-3, D+0, D+1, D+7.

**BillingCancelExpired:** daily 04:00. Cancels pending invoices past due_date+7 days. Sends cancellation notification.

**Console routes (routes/console.php):** register all 3 commands + existing tenant:auto-suspend.

```bash
git commit -m "feat: billing cron commands — pre-bill, dunning, cancel expired"
```

### Task 8-9: Admin + Client UI (4 days)

**Admin Vue pages:** Plans Index (card grid with edit/delete), Plans Form (dynamic feature list), Coupons Index (table with usage stats), Coupons Form, Billing Index (stats row + invoice table), Billing TransactionLog, PaymentChannels Index (enable/disable list), PaymentTransactions Index (filterable table). All with dark mode, mobile card layout, skeleton states.

**Client Vue redesign:** Invoices (InvoiceCard component with built-in payment: Duitku channel selector + manual upload + status badge), Subscription (upgrade/downgrade inline form), BillingSettings (new), Dashboard (add BillingSummaryCard). All mobile-first: tables become stacked cards below md breakpoint, touch targets 44px, font 16px min.

**Shared components:** InvoiceCard, BillingSummaryCard, PaymentChannelSelector, CouponInput, PlanSelector, PriceDisplay.

**AdminLayout sidebar:** Add Billing, Plans, Coupons nav items with role gating.

```bash
git commit -m "feat: client UI redesign — InvoiceCard, upgrade/downgrade, payment channel selector, coupon input"
git commit -m "feat: admin UI pages — plans, coupons, billing, payment channels, transaction log"
git commit -m "feat: admin sidebar — add billing, plans, coupons nav items"
```

---

## Spec Coverage

| Requirement | Task |
|---|---|
| Data model (UUID PKs) | Task 1 |
| Billing engine (generate, prorasi, confirm, extend) | Task 2 |
| Coupon engine (validation, discount) | Task 2 |
| Duitku integration (createInvoice, webhook, sync channels) | Task 3 |
| Admin Plans CRUD | Task 4 |
| Admin Coupons CRUD | Task 4 |
| Admin Billing dashboard + stats | Task 4 |
| Admin Payment Channel management | Task 4 |
| Admin Payment Transaction log | Task 4 |
| Admin retry provision | Task 4 |
| Client coupon validation (AJAX) | Task 5 |
| Client invoice PDF download | Task 5 |
| Client Duitku payment initiation | Task 5 |
| Client upgrade/downgrade subscription | Task 5 |
| Home page dynamic pricing | Task 6 |
| Cron: pre-bill, dunning, cancel-expired | Task 7 |
| Admin UI (Plans, Coupons, Billing, PaymentChannels, Transactions) | Task 8 |
| Client UI (InvoiceCard, Subscription upgrade, PaymentChannelSelector) | Task 9 |
| Admin sidebar nav items | Task 9 |
