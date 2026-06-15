<?php

namespace App\Http\Controllers;

use App\Mail\DemoRequestMail;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DemoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Demo', [
            'demoAccounts' => SiteConfig::demoAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse|SymfonyResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'cooperative_name' => 'required|string|max:255',
            'member_count' => 'required|string|max:50',
            'whatsapp' => 'required|string|max:20',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            $adminEmail = SiteConfig::get('contact.email');
            Mail::to($adminEmail)->send(new DemoRequestMail($validated));

            Log::info('Demo request submitted', $validated);

            return back()->with('success', 'Terima kasih! Tim kami akan menghubungi Anda dalam 1x24 jam via WhatsApp.');
        } catch (\Throwable $e) {
            Log::error('Demo request failed', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return back()->withErrors(['submission' => 'Maaf, terjadi kesalahan. Silakan coba lagi atau hubungi kami langsung.']);
        }
    }
}
