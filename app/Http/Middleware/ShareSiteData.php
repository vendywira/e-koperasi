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
                'brand'        => SiteConfig::get('brand'),
                'nav'          => SiteConfig::get('nav'),
                'footer'       => SiteConfig::get('footer'),
                'hero'         => SiteConfig::get('hero'),
                'trustBar'     => SiteConfig::get('trust_bar'),
                'stats'        => SiteConfig::get('stats'),
                'personas'     => SiteConfig::get('personas'),
                'features'     => SiteConfig::get('features'),
                'howItWorks'   => SiteConfig::get('how_it_works'),
                'pricing'      => SiteConfig::get('pricing'),
                'testimonials' => SiteConfig::get('testimonials'),
                'faqs'         => SiteConfig::get('faqs'),
                'cta'          => SiteConfig::get('cta'),
                'demo'         => SiteConfig::get('demo'),
                'contact'      => SiteConfig::get('contact'),
                'legal'        => SiteConfig::get('legal'),
                'seo'          => SiteConfig::get('seo'),
                'about'        => SiteConfig::get('about'),
            ],
        ]);
    }
}
