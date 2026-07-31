<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
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

        $subscriptions = $user->ksuSubscriptions()->with('tenant')->get()->map(fn($sub) => [
            'id' => $sub->id,
            'tenant_name' => $sub->tenant?->name ?? '-',
            'tenant_domain' => $sub->tenant?->domain ?? '-',
            'tenant_status' => $sub->tenant?->status ?? '-',
            'plan' => $sub->plan,
            'max_resorts' => $sub->max_resorts,
            'price_per_resort' => $sub->price_per_resort,
            'status' => $sub->status,
            'is_active' => $sub->isActive(),
            'is_grace' => $sub->isGrace(),
            'grace_days_remaining' => $sub->graceDaysRemaining(),
            'grace_ends_at' => $sub->isGrace() ? $sub->graceEndsAt()->format('d M Y') : null,
            'started_at' => $sub->started_at?->format('d M Y'),
            'ends_at' => $sub->ends_at?->format('d M Y'),
            'billing_cycle' => $sub->billing_cycle ?? 'monthly',
            'days_remaining' => $sub->daysRemaining(),
            'usage_percent' => $sub->usagePercent(),
            'next_bill_date' => $sub->ends_at?->copy()->subDays(7)->format('d M Y'),
        ]);

        $plans = \App\Models\Plan::with('features')->active()->get();

        return Inertia::render('Client/Subscription', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
        ]);
    }

    public function upgrade(Request $request, BillingService $billing, CouponService $couponService): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'max_resorts' => 'required|integer|min:1',
            'price_per_resort' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $coupon = null;
        if ($couponCode = $validated['coupon_code'] ?? null) {
            $coupon = $couponService->validateCoupon($couponCode, $subscription->plan_id ?? '');
        }

        $proration = $billing->calculateProration(
            $subscription,
            (int) $validated['max_resorts'],
            (float) $validated['price_per_resort']
        );

        if ($proration['type'] === 'upgrade' && $proration['prorated_amount'] > 0) {
            $billing->upgrade($subscription, (int) $validated['max_resorts'], (float) $validated['price_per_resort'], $coupon);
            return redirect()->route('client.invoices')
                ->with('success', 'Paket di-upgrade. Silakan bayar invoice prorata.');
        }

        $proration['new_price_per_resort'] = (float) $validated['price_per_resort'];
        $billing->downgradeCredit($subscription, $proration);

        return redirect()->route('client.subscription')
            ->with('success', 'Paket diubah. Selisih akan dikreditkan ke invoice bulan depan.');
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