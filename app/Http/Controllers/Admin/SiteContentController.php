<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Services\SiteConfig;
use Illuminate\Http\JsonResponse;
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
    public function update(Request $request, string $section): JsonResponse
    {
        $request->validate([
            'value' => 'required',
        ]);

        $siteContent = SiteContent::saveSection($section, $request->value);
        SiteConfig::clearCache();

        return response()->json([
            'success' => true,
            'data' => $siteContent,
        ]);
    }

    /**
     * Initialize all sections from the config file (seed).
     */
    public function seed(): JsonResponse
    {
        $config = config('site', []);

        foreach ($config as $section => $value) {
            if (is_array($value)) {
                SiteContent::saveSection($section, $value);
            }
        }

        SiteConfig::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'All sections seeded from config.',
        ]);
    }

    /**
     * Reset a section back to config default.
     */
    public function reset(string $section): JsonResponse
    {
        $configValue = config("site.{$section}");

        if (!$configValue || !is_array($configValue)) {
            return response()->json(['error' => 'Section not found in config'], 404);
        }

        $siteContent = SiteContent::saveSection($section, $configValue);
        SiteConfig::clearCache();

        return response()->json([
            'success' => true,
            'data' => $siteContent,
        ]);
    }
}
