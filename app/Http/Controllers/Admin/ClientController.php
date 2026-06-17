<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function dashboard(): Response
    {
        $totalClients = User::where('role', 'client')->count();

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->count();

        $expiredSubscriptions = Subscription::where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere(function ($q2) {
                    $q2->where('status', 'active')->where('ends_at', '<', now());
                });
        })->count();

        $totalRevenue = Payment::where('status', 'paid')->sum('amount');

        $totalRevenueFormatted = 'Rp' . number_format($totalRevenue, 0, ',', '.');

        $recentPayments = Payment::with('subscription.user')
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get();

        $planDistribution = Subscription::select('plan', DB::raw('count(*) as total'))
            ->groupBy('plan')
            ->pluck('total', 'plan')
            ->toArray();

        $monthlyRevenue = Payment::where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->select(DB::raw("strftime('%Y-%m', paid_at) as month"), DB::raw('sum(amount) as total'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_clients' => $totalClients,
                'active_subscriptions' => $activeSubscriptions,
                'expired_subscriptions' => $expiredSubscriptions,
                'total_revenue' => $totalRevenueFormatted,
                'total_revenue_raw' => $totalRevenue,
            ],
            'recentPayments' => $recentPayments,
            'planDistribution' => $planDistribution,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }

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
