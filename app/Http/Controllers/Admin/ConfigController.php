<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConfigController extends Controller
{
    public function index(): Response
    {
        $saved = SiteContent::where('section', 'config')->value('value');
        if (!is_array($saved)) {
            $saved = config('site.config', []);
            SiteContent::saveSection('config', $saved);
            SiteConfig::clearCache();
        }

        return Inertia::render('Admin/Config', [
            'config' => $saved,
            'defaults' => config('site.config', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $defaults = config('site.config', []);
        $values = [];

        foreach ($defaults as $key => $default) {
            $request->validate([$key => 'nullable']);
            $values[$key] = $request->has($key) ? $request->input($key) : $default;
        }

        SiteContent::saveSection('config', $values);
        SiteConfig::clearCache();

        return redirect()->back()->with('success', 'Config berhasil disimpan.');
    }
}
