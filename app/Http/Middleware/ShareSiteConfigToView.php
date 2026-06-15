<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SiteConfig;
use Symfony\Component\HttpFoundation\Response;

class ShareSiteConfigToView
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = [
            'brand'        => SiteConfig::get('brand'),
            'nav'          => SiteConfig::get('nav'),
            'footer'       => SiteConfig::get('footer'),
            'hero'         => SiteConfig::get('hero'),
            'trustBar'     => SiteConfig::get('trust_bar'),
            'stats'        => SiteConfig::get('stats'),
            'personas'     => SiteConfig::get('personas'),
            'products'     => SiteConfig::get('products'),
            'features'     => SiteConfig::get('features'),
            'howItWorks'   => SiteConfig::get('how_it_works'),
            'pricing'      => SiteConfig::get('pricing'),
            'testimonials' => SiteConfig::get('testimonials'),
            'faqs'         => SiteConfig::get('faqs'),
            'cta'          => SiteConfig::get('cta'),
            'demo'         => SiteConfig::get('demo'),
            'contact'      => SiteConfig::get('contact'),
            'legal'        => SiteConfig::get('legal'),
            'about'        => SiteConfig::get('about'),
            'seo'          => SiteConfig::get('seo'),
        ];

        view()->share('siteConfig', $config);

        return $next($request);
    }
}
