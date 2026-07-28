<?php

namespace App\Services;

use App\Models\BillingCycle;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\SubscriptionLineItem;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function cycleDiscountPercent(string $cycle): int
    {
        return BillingCycle::where('slug', $cycle)->value('discount_percent') ?? 0;
    }

    public function generateInvoice(Subscription $subscription, ?Coupon $coupon = null, bool $isManual = false): ?Invoice
    {
        $existing = Invoice::where('tenant_id', $subscription->tenant_id)
            ->whereIn('status', ['pending', 'paid'])
            ->where('created_at', '>=', now()->subDays(45))
            ->exists();
        if ($existing && !$isManual) return null;

        $tenant = Tenant::find($subscription->tenant_id);
        if (!$tenant) return null;

        $cycle = BillingCycle::where('slug', $subscription->billing_cycle)->first();
        $cycleMonths = $cycle?->months ?? 1;

        $plan = $subscription->plan_id ? Plan::find($subscription->plan_id) : null;
        $config = $plan?->pricing_config ?? [];
        $qty = $subscription->max_resorts ?? 1;
        
        if (($plan?->type ?? 'business') === 'enterprise') {
            $price = $config['price'] ?? 0;
            $subtotal = $price;
            $cycleMonths = 0;
        } elseif (($plan?->type ?? 'business') === 'trial') {
            $price = 0;
            $subtotal = 0;
            $cycleMonths = 0;
        } else {
            $price = $config['price_per_resort'] ?? ($subscription->price_per_resort ?? 0);
            $subtotal = $qty * $price * $cycleMonths;
        }
        $cycleDiscountPct = $this->cycleDiscountPercent($subscription->billing_cycle);
        $cycleDiscount = $subtotal * $cycleDiscountPct / 100;
        $couponDiscount = 0;

        if ($coupon) {
            $afterCycle = $subtotal - $cycleDiscount;
            $couponDiscount = $coupon->discount_type === 'percentage'
                ? $afterCycle * $coupon->discount_value / 100
                : $coupon->discount_value;
            $couponDiscount = min($couponDiscount, $afterCycle);
        }

        $total = max(0, $subtotal - $cycleDiscount - $couponDiscount);

        $month = now()->format('Ym');
        $lastSeq = Invoice::where('invoice_number', 'like', "INV-{$month}-%")
            ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
            ->value('invoice_number');
        $seq = $lastSeq ? (int) substr($lastSeq, -4) + 1 : 1;
        $invoiceNumber = sprintf('INV-%s-%04d', $month, $seq);

        $invoice = DB::transaction(function () use (
            $subscription, $tenant, $invoiceNumber, $subtotal,
            $cycleDiscount, $couponDiscount, $total, $cycleMonths, $coupon, $qty, $price
        ) {
            $inv = Invoice::create([
                'tenant_id' => $tenant?->id,
                'user_id' => $subscription->user_id,
                'name' => $tenant?->name ?? '',
                'domain' => $tenant?->domain ?? '',
                'invoice_number' => $invoiceNumber,
                'resort_count' => $qty,
                'price_per_resort' => $price,
                'months' => $cycleMonths,
                'subtotal' => $subtotal,
                'discount_amount' => $cycleDiscount + $couponDiscount,
                'coupon_id' => $coupon?->id,
                'total_amount' => $total,
                'status' => 'pending',
                'due_date' => now()->addDays(14),
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv->id,
                'description' => "Langganan {$tenant?->name} — {$qty} resort × {$cycleMonths} bulan",
                'quantity' => 1,
                'unit_price' => $subtotal,
                'total_amount' => $subtotal,
                'type' => 'subscription',
            ]);

            if ($cycleDiscount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'description' => "Diskon siklus {$subscription->billing_cycle} ({$cycleDiscountPct}%)",
                    'quantity' => 1,
                    'unit_price' => -$cycleDiscount,
                    'discount_amount' => $cycleDiscount,
                    'total_amount' => -$cycleDiscount,
                    'type' => 'discount',
                ]);
            }

            if ($couponDiscount > 0 && $coupon) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'description' => "Kupon: {$coupon->code}",
                    'quantity' => 1,
                    'unit_price' => -$couponDiscount,
                    'discount_amount' => $couponDiscount,
                    'total_amount' => -$couponDiscount,
                    'type' => 'discount',
                ]);
                $coupon->increment('used_count');
            }

            return $inv;
        });

        return $invoice;
    }

    public function calculateProration(Subscription $subscription, int $newMaxResorts, float $newPricePerResort): array
    {
        $endsAt = $subscription->ends_at ?? now()->addMonth();
        $totalDays = max(1, now()->diffInDays($endsAt) ?: 30);
        $remainingDays = max(1, now()->diffInDays($endsAt, false) ?: 1);

        $oldMonthly = ($subscription->max_resorts ?? 1) * ($subscription->price_per_resort ?? 0);
        $newMonthly = $newMaxResorts * $newPricePerResort;
        $diffMonthly = $newMonthly - $oldMonthly;

        $proratedAmount = ($remainingDays / $totalDays) * $diffMonthly;

        return [
            'old_monthly' => $oldMonthly,
            'new_monthly' => $newMonthly,
            'diff_monthly' => $diffMonthly,
            'prorated_amount' => round($proratedAmount, 2),
            'remaining_days' => $remainingDays,
            'total_days' => $totalDays,
            'type' => $diffMonthly >= 0 ? 'upgrade' : 'downgrade',
        ];
    }

    public function upgrade(Subscription $subscription, int $newMaxResorts, float $newPricePerResort, ?Coupon $coupon = null): ?Invoice
    {
        $proration = $this->calculateProration($subscription, $newMaxResorts, $newPricePerResort);

        if ($proration['type'] === 'downgrade' || $proration['prorated_amount'] <= 0) {
            $proration['new_price_per_resort'] = $newPricePerResort;
            $this->downgradeCredit($subscription, $proration);
            return null;
        }

        $tenant = Tenant::find($subscription->tenant_id);
        $month = now()->format('Ym');
        $lastSeq = Invoice::where('invoice_number', 'like', "INV-{$month}-%")
            ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
            ->value('invoice_number');
        $seq = $lastSeq ? (int) substr($lastSeq, -4) + 1 : 1;
        $invoiceNumber = sprintf('INV-%s-%04d', $month, $seq);

        $total = $proration['prorated_amount'];
        $discount = 0;
        if ($coupon) {
            $discount = $coupon->discount_type === 'percentage'
                ? $total * $coupon->discount_value / 100
                : $coupon->discount_value;
            $total = max(0, $total - $discount);
            $coupon->increment('used_count');
        }

        $invoice = DB::transaction(function () use (
            $subscription, $tenant, $invoiceNumber, $proration, $total, $discount, $coupon,
            $newMaxResorts, $newPricePerResort
        ) {
            $inv = Invoice::create([
                'tenant_id' => $tenant?->id,
                'user_id' => $subscription->user_id,
                'name' => $tenant?->name ?? '',
                'domain' => $tenant?->domain ?? '',
                'invoice_number' => $invoiceNumber,
                'resort_count' => $newMaxResorts,
                'price_per_resort' => $newPricePerResort,
                'months' => 0,
                'subtotal' => $proration['prorated_amount'],
                'discount_amount' => $discount,
                'coupon_id' => $coupon?->id,
                'total_amount' => $total,
                'status' => 'pending',
                'due_date' => now()->addDays(14),
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv->id,
                'description' => "Upgrade: {$subscription->max_resorts}→{$newMaxResorts} resort (prorata {$proration['remaining_days']}/{$proration['total_days']} hari)",
                'quantity' => 1,
                'unit_price' => $proration['prorated_amount'],
                'total_amount' => $proration['prorated_amount'],
                'type' => 'proration',
            ]);

            return $inv;
        });

        SubscriptionLineItem::create([
            'subscription_id' => $subscription->id,
            'type' => 'upgrade',
            'previous_price' => $proration['old_monthly'],
            'new_price' => $proration['new_monthly'],
            'prorated_amount' => $proration['prorated_amount'],
            'total_amount' => $total,
        ]);

        $subscription->update([
            'max_resorts' => $newMaxResorts,
            'price_per_resort' => $newPricePerResort,
        ]);

        return $invoice;
    }

    public function downgradeCredit(Subscription $subscription, array $proration): void
    {
        $subscription->update([
            'max_resorts' => (int) (($proration['new_monthly'] ?? 0) / max(1, ($proration['new_price_per_resort'] ?? 1))),
            'price_per_resort' => $proration['new_price_per_resort'] ?? 0,
        ]);

        SubscriptionLineItem::create([
            'subscription_id' => $subscription->id,
            'type' => 'downgrade',
            'previous_price' => $proration['old_monthly'] ?? 0,
            'new_price' => $proration['new_monthly'] ?? 0,
            'prorated_amount' => abs($proration['prorated_amount'] ?? 0),
            'total_amount' => abs($proration['prorated_amount'] ?? 0),
        ]);
    }

    public function confirmPayment(Invoice $invoice): void
    {
        $tenant = Tenant::find($invoice->tenant_id);
        $subscription = $tenant?->subscription;

        DB::transaction(function () use ($invoice, $tenant, $subscription) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($subscription) {
                $cyc = BillingCycle::where('slug', $subscription->billing_cycle)->first();
                $cycleMonths = $cyc?->months ?? 1;
                $base = $subscription->ends_at && $subscription->ends_at->isFuture()
                    ? $subscription->ends_at->copy()->addDay()
                    : now()->addDay();
                $subscription->update([
                    'status' => 'active',
                    'started_at' => $subscription->started_at ?? now(),
                    'ends_at' => $base->copy()->addMonths($cycleMonths),
                    'renewed_at' => now(),
                ]);
            }

            if ($tenant && $tenant->status === 'suspended') {
                $tenant->update(['status' => 'active']);
            }
        });
    }

    public function extendSubscription(Subscription $subscription, int $days): void
    {
        $base = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at->copy()->addDay()
            : now()->addDay();
        $newEnd = $base->addDays($days);

        $subscription->update([
            'ends_at' => $newEnd,
            'status' => 'active',
        ]);

        Tenant::where('id', $subscription->tenant_id)->update(['status' => 'active']);
    }
}