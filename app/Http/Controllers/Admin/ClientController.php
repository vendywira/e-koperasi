<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $clients = User::where('role', 'client')
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Client/Index', [
            'clients' => $clients,
        ]);
    }

    public function show(int $id): Response
    {
        $client = User::where('role', 'client')
            ->with(['subscription.payments' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->findOrFail($id);

        $planLabels = [
            'starter' => ['name' => 'Starter', 'price' => 'Rp499.000/bln'],
            'premium' => ['name' => 'Premium', 'price' => 'Rp1.500.000/bln'],
            'enterprise' => ['name' => 'Enterprise', 'price' => 'Custom'],
        ];

        return Inertia::render('Admin/Client/Show', [
            'client' => $client,
            'planLabels' => $planLabels,
        ]);
    }

    public function updateSubscription(Request $request, int $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'plan' => 'required|in:starter,premium,enterprise',
            'status' => 'required|in:active,expired,cancelled,trialing',
            'started_at' => 'required|date',
            'ends_at' => 'nullable|date|after:started_at',
            'trial_ends_at' => 'nullable|date',
        ]);

        $subscription = $client->subscription;

        if ($subscription) {
            $subscription->update($validated);
        } else {
            $subscription = Subscription::create(array_merge($validated, ['user_id' => $client->id]));
        }

        return redirect()->back()->with('success', 'Subscription berhasil diperbarui.');
    }
}
