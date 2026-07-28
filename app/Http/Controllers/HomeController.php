<?php

namespace App\Http\Controllers;

use App\Models\BillingCycle;
use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::with('features')->active()->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'type' => $p->type,
            'max_resorts' => $p->max_resorts,
            'price_per_month' => (int) $p->price_per_month,
            'trial_days' => $p->trial_days,
            'pricing_config' => $p->pricing_config,
            'features' => $p->features->pluck('feature_text'),
        ]);

        $billingCycles = BillingCycle::active()->get(['slug', 'name', 'months', 'discount_percent']);

        return Inertia::render('Home', [
            'plans' => $plans,
            'billingCycles' => $billingCycles,
        ]);
    }
}