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
        $subscription = $user->subscription;

        $planFeatures = [];
        if ($subscription) {
            $tiers = SiteConfig::get('pricing.tiers', []);
            $planFeatures = $tiers[$subscription->plan]['features'] ?? [];
            // Flatten the features (they come as [{feature: "..."}] from repeater)
            $planFeatures = array_map(function ($f) {
                return is_array($f) ? ($f['feature'] ?? $f) : $f;
            }, $planFeatures);
        }

        return Inertia::render('Client/Subscription', [
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan,
                'status' => $subscription->status,
                'is_active' => $subscription->isActive(),
                'started_at' => $subscription->started_at?->format('d M Y'),
                'ends_at' => $subscription->ends_at?->format('d M Y'),
                'renewed_at' => $subscription->renewed_at?->format('d M Y'),
                'days_remaining' => $subscription->daysRemaining(),
                'usage_percent' => $subscription->usagePercent(),
            ] : null,
            'planFeatures' => $planFeatures,
        ]);
    }
}
