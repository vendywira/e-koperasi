<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\CouponService;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function show(): Response
    {
        $user = auth()->user();

        $allPlans = Plan::with('features')->active()->get();

        $subscriptions = $user->ksuSubscriptions()->with('tenant')->get()->map(fn($sub) => [
            'id' => $sub->id,
            'tenant_id' => $sub->tenant_id,
            'tenant_name' => $sub->tenant?->name ?? '-',
            'tenant_domain' => $sub->tenant?->domain ?? '-',
            'tenant_status' => $sub->tenant?->status ?? '-',
            'plan' => $sub->plan,
            'plan_id' => $sub->plan_id,
            'max_resorts' => $sub->max_resorts,
            'price_per_resort' => $sub->price_per_resort,
            'status' => $sub->status,
            'is_active' => $sub->isActive(),
            'is_trialing' => $sub->status === 'trialing',
            'trial_ends_at' => $sub->trial_ends_at?->format('d M Y'),
            'is_grace' => $sub->isGrace(),
            'grace_days_remaining' => $sub->graceDaysRemaining(),
            'grace_ends_at' => $sub->isGrace() ? $sub->graceEndsAt()->format('d M Y') : null,
            'started_at' => $sub->started_at?->format('d M Y'),
            'ends_at' => $sub->ends_at?->format('d M Y'),
            'billing_cycle' => $sub->billing_cycle ?? 'monthly',
            'days_remaining' => $sub->daysRemaining(),
            'usage_percent' => $sub->usagePercent(),
            'next_bill_date' => $sub->ends_at?->copy()->subDays(7)->format('d M Y'),
            'available_plans' => $this->availablePlansForTenant($sub->tenant_id, $allPlans),
            'pending_invoice' => (function () use ($sub) {
                if (!$sub->tenant_id) return null;
                $inv = \App\Models\Invoice::where('tenant_id', $sub->tenant_id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
                return $inv ? [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'total_amount' => (int) $inv->total_amount,
                ] : null;
            })(),
        ]);

        // Tenants owned by this user that have no subscription yet (order flow)
        $subscribedTenantIds = $user->ksuSubscriptions()->pluck('tenant_id')->filter()->toArray();
        $orderTenants = Tenant::where('requested_by', $user->id)
            ->whereNotIn('id', $subscribedTenantIds)
            ->get()
            ->map(fn($t) => [
                'tenant_id' => $t->id,
                'tenant_name' => $t->name,
                'tenant_domain' => $t->domain,
                'used_trial' => $this->tenantUsedTrial($t->id),
                'available_plans' => $this->availablePlansForTenant($t->id, $allPlans),
            ]);

        return Inertia::render('Client/Subscription', [
            'subscriptions' => $subscriptions,
            'plans' => $allPlans,
            'billingCycles' => \App\Models\BillingCycle::orderBy('months')->get(),
            'orderTenants' => $orderTenants,
        ]);
    }

    public function plans(): Response
    {
        $user = auth()->user();
        $allPlans = Plan::with('features')->active()->get();

        $subscribedTenantIds = $user->ksuSubscriptions()->pluck('tenant_id')->filter()->toArray();

        // Tenants without subscription → order flow
        $orderTenants = Tenant::where('requested_by', $user->id)
            ->whereNotIn('id', $subscribedTenantIds)
            ->get()
            ->map(fn($t) => [
                'tenant_id' => $t->id,
                'tenant_name' => $t->name,
                'tenant_domain' => $t->domain,
                'used_trial' => $this->tenantUsedTrial($t->id),
                'available_plans' => $this->availablePlansForTenant($t->id, $allPlans),
            ]);

        // Tenants with subscription → upgrade flow
        $upgradeTenants = $user->ksuSubscriptions()->with('tenant')->get()->map(fn($sub) => [
            'tenant_id' => $sub->tenant_id,
            'tenant_name' => $sub->tenant?->name ?? '-',
            'subscription_id' => $sub->id,
            'current_plan' => $sub->plan,
            'plan_id' => $sub->plan_id,
            'max_resorts' => $sub->max_resorts,
            'price_per_resort' => $sub->price_per_resort,
            'billing_cycle' => $sub->billing_cycle ?? 'monthly',
            'available_plans' => $this->availablePlansForTenant($sub->tenant_id, $allPlans),
        ]);

        $hasAnyTenant = Tenant::where('requested_by', $user->id)->exists();

        return Inertia::render('Client/Plans', [
            'plans' => $allPlans,
            'billingCycles' => \App\Models\BillingCycle::orderBy('months')->get(),
            'orderTenants' => $orderTenants,
            'upgradeTenants' => $upgradeTenants,
            'hasAnyTenant' => $hasAnyTenant,
        ]);
    }

    /**
     * Whether a tenant has ever used the Trial plan (past or present subscription).
     */
    private function tenantUsedTrial(?string $tenantId): bool
    {
        if (!$tenantId) return false;

        return Subscription::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('plan', 'like', '%trial%')
                    ->orWhere('status', 'trialing')
                    ->orWhereNotNull('trial_ends_at');
            })
            ->exists();
    }

    /**
     * Active plans, minus Trial if the tenant already used it.
     */
    private function availablePlansForTenant(?string $tenantId, $allPlans)
    {
        $usedTrial = $this->tenantUsedTrial($tenantId);
        // Trial hidden if tenant ever used it OR already has any non-trial subscription
        $hasPaidSubscription = $tenantId && Subscription::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('plan', 'not like', '%trial%')
                    ->orWhereNull('trial_ends_at');
            })
            ->exists();

        return $allPlans
            ->filter(fn($p) => !($p->type === 'trial' && ($usedTrial || $hasPaidSubscription)))
            ->values();
    }

    public function order(Request $request, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'tenant_id' => 'required|exists:tenants,id',
            'resort_qty' => 'required|integer|min:1',
            'billing_cycle' => 'required|in:monthly,quarterly,semiannual,yearly',
        ]);

        $tenant = Tenant::where('id', $validated['tenant_id'])
            ->where('requested_by', auth()->id())
            ->firstOrFail();

        if ($tenant->subscription) {
            return redirect()->route('client.subscription')
                ->with('error', 'Tenant sudah punya langganan. Gunakan Ganti Paket.');
        }

        $plan = Plan::findOrFail($validated['plan_id']);

        // Tenant already used trial → Trial plan not allowed
        if ($plan->type === 'trial' && $this->tenantUsedTrial($tenant->id)) {
            return redirect()->back()
                ->with('error', 'Tenant ini sudah pernah menggunakan paket Trial.');
        }

        // One-time plan → flat price, resort diabaikan (unlimited)
        $cfg = $plan->pricing_config ?? [];
        $isOneTime = ($cfg['has_cycle'] ?? true) === false;
        $pricePerResort = $isOneTime
            ? (float) ($cfg['price'] ?? 0)
            : ($cfg['price_per_resort'] ?? ($plan->price_per_month / max(1, $plan->max_resorts)));
        $maxResorts = $isOneTime ? 1 : (int) $validated['resort_qty'];

        $subscription = Subscription::create([
            'user_id' => auth()->id(),
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'plan' => $plan->name,
            'billing_cycle' => $isOneTime ? 'monthly' : $validated['billing_cycle'],
            'max_resorts' => $maxResorts,
            'price_per_resort' => $pricePerResort,
            'status' => $plan->type === 'trial' ? 'trialing' : 'pending',
            'started_at' => now(),
            'ends_at' => $plan->type === 'trial' ? now()->addDays($plan->trial_days) : null,
            'trial_ends_at' => $plan->type === 'trial' ? now()->addDays($plan->trial_days) : null,
        ]);

        // Trial → langsung aktif gratis, gak bikin invoice payable
        if ($plan->type === 'trial') {
            $tenant->update(['status' => 'active']);
            return redirect()->route('client.subscription')
                ->with('success', 'Trial dimulai! Aktif selama ' . $plan->trial_days . ' hari.');
        }

        $billing->generateInvoice($subscription, null, true);

        return redirect()->route('client.invoices')
            ->with('success', 'Tagihan dibuat. Selesaikan pembayaran untuk aktivasi.');
    }

    public function regenerateInvoice(Request $request, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Hanya sub pending/trialing yang butuh invoice
        if (!in_array($subscription->status, ['pending', 'trialing'])) {
            return redirect()->route('client.subscription')
                ->with('error', 'Langganan sudah aktif, tidak perlu tagihan baru.');
        }

        // Cek apakah sudah ada invoice pending
        $existingPending = $subscription->tenant_id
            ? \App\Models\Invoice::where('tenant_id', $subscription->tenant_id)
                ->where('status', 'pending')
                ->exists()
            : false;
        if ($existingPending) {
            return redirect()->route('client.subscription')
                ->with('info', 'Masih ada tagihan pending. Selesaikan pembayaran yang ada.');
        }

        $invoice = $billing->generateInvoice($subscription, null, true);

        if (!$invoice) {
            return redirect()->route('client.subscription')
                ->with('error', 'Gagal membuat tagihan. Hubungi admin.');
        }

        return redirect()->route('client.invoices')
            ->with('success', 'Tagihan baru dibuat. Selesaikan pembayaran untuk aktivasi.');
    }

    public function upgrade(Request $request, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'plan_id' => 'required|exists:plans,id',
            'max_resorts' => 'nullable|integer|min:1',
            'billing_cycle' => 'nullable|in:monthly,quarterly,semiannual,yearly',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $plan = Plan::findOrFail($validated['plan_id']);
        $cfg = $plan->pricing_config ?? [];
        $isOneTime = ($cfg['has_cycle'] ?? true) === false;
        $newPrice = $isOneTime
            ? (float) ($cfg['price'] ?? 0)
            : (float) ($cfg['price_per_resort'] ?? ($plan->price_per_month / max(1, $plan->max_resorts)));

        // Resort default: pakai dari invoice terakhir (paid/pending) kalau ada, fallback 1.
        // One-time → resort diabaikan.
        $lastResort = \App\Models\Invoice::where('tenant_id', $subscription->tenant_id)
            ->whereNotNull('resort_count')
            ->latest()
            ->value('resort_count');
        $newMax = $isOneTime ? 1 : (int) ($validated['max_resorts'] ?? $lastResort ?: 1);

        $newCycle = $isOneTime ? 'monthly' : ($validated['billing_cycle'] ?? $subscription->billing_cycle ?? 'monthly');

        // Update invoice in-place (paket + cycle). Subscription berubah setelah bayar.
        $billing->applyPlanChange($subscription, $newMax, $newPrice, $newCycle, $plan->id);

        return redirect()->route('client.invoices')
            ->with('success', 'Perubahan paket menunggu pembayaran. Bayar invoice untuk mengaktifkan paket baru.');
    }

    public function changeCycle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'billing_cycle' => 'required|in:monthly,quarterly,semiannual,yearly',
        ]);

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $subscription->update(['billing_cycle' => $validated['billing_cycle']]);

        return redirect()->route('client.subscription')
            ->with('success', 'Siklus tagihan diubah ke ' . $validated['billing_cycle'] . '. Berlaku untuk tagihan berikutnya.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'trialing'])
            ->firstOrFail();

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('client.subscription')
            ->with('success', "Langganan dibatalkan. Tenant tetap aktif sampai {$subscription->ends_at?->format('d M Y')}.");
    }

    public function resume(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('user_id', auth()->id())
            ->where('status', 'cancelled')
            ->firstOrFail();

        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            return redirect()->back()->with('error', 'Periode sudah berakhir. Silakan hubungi admin untuk perpanjangan.');
        }

        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);

        return redirect()->route('client.subscription')
            ->with('success', 'Langganan diaktifkan kembali. Tagihan akan berjalan normal.');
    }
}