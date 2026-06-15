<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\SiteConfig;

class ShareSiteData extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'siteConfig' => [
                'pricing' => SiteConfig::pricing(),
                'demoAccounts' => SiteConfig::demoAccounts(),
                'stats' => SiteConfig::stats(),
                'contact' => SiteConfig::contact(),
            ],
        ]);
    }
}
