<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\SiteConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function show(): Response
    {
        $user = auth()->user();

        // Ambil semua KSU subscription milik user (include tenant info)
        $subscriptions = $user->ksuSubscriptions()->with('tenant')->get()->map(function ($sub) {
            $tiers = SiteConfig::get('pricing.tiers', []);
            $features = $tiers[$sub->plan]['features'] ?? [];
            $features = array_map(fn($f) => is_array($f) ? ($f['feature'] ?? $f) : $f, $features);

            return [
                'id' => $sub->id,
                'tenant_name' => $sub->tenant?->name ?? '-',
                'tenant_domain' => $sub->tenant?->domain ?? '-',
                'tenant_status' => $sub->tenant?->status ?? '-',
                'plan' => $sub->plan,
                'max_resorts' => $sub->max_resorts,
                'price_per_resort' => $sub->price_per_resort,
                'status' => $sub->status,
                'is_active' => $sub->isActive(),
                'started_at' => $sub->started_at?->format('d M Y'),
                'ends_at' => $sub->ends_at?->format('d M Y'),
                'days_remaining' => $sub->daysRemaining(),
                'usage_percent' => $sub->usagePercent(),
                'features' => $features,
            ];
        });

        return Inertia::render('Client/Subscription', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
