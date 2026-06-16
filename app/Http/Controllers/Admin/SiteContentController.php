<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Services\SiteConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteContentController extends Controller
{
    /**
     * Show the CMS dashboard with all sections.
     */
    public function index(): Response
    {
        $sections = SiteContent::allAsConfig();
        $configDefaults = config('site', []);

        return Inertia::render('Admin/Cms/Index', [
            'sections' => $sections,
            'configDefaults' => $configDefaults,
        ]);
    }

    /**
     * Get a specific section's data.
     */
    public function show(string $section): JsonResponse
    {
        $record = SiteContent::where('section', $section)->first();

        if (!$record) {
            return response()->json(['error' => 'Section not found'], 404);
        }

        return response()->json($record);
    }

    /**
     * Store or update a section's data.
     */
    public function update(Request $request, string $section)
    {
        $request->validate([
            'value' => 'required',
        ]);

        SiteContent::saveSection($section, $request->value);
        SiteConfig::clearCache();

        return redirect()->back()->with('success', "Section {$section} berhasil disimpan.");
    }

    /**
     * Initialize all sections from the config file (seed).
     */
    public function seed()
    {
        $config = config('site', []);

        foreach ($config as $section => $value) {
            if (is_array($value)) {
                SiteContent::saveSection($section, $value);
            }
        }

        SiteConfig::clearCache();

        return redirect()->back()->with('success', 'Semua section berhasil di-seed dari config default.');
    }

    /**
     * Reset a section back to config default.
     */
    public function reset(string $section)
    {
        $configValue = config("site.{$section}");

        if (!$configValue || !is_array($configValue)) {
            return redirect()->back()->with('error', 'Section not found in config');
        }

        SiteContent::saveSection($section, $configValue);
        SiteConfig::clearCache();

        return redirect()->back()->with('success', "Section {$section} berhasil di-reset ke default.");
    }
}
