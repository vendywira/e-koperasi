<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? 'editor',
                    'subscription' => $request->user()->role === 'client' && $request->user()->subscription
                        ? [
                            'id' => $request->user()->subscription->id,
                            'plan' => $request->user()->subscription->plan,
                            'status' => $request->user()->subscription->status,
                            'is_active' => $request->user()->subscription->isActive(),
                            'days_remaining' => $request->user()->subscription->daysRemaining(),
                            'usage_percent' => $request->user()->subscription->usagePercent(),
                            'ends_at' => $request->user()->subscription->ends_at?->format('d M Y'),
                        ]
                        : null,
                ] : null,
            ],
        ]);
    }
}
