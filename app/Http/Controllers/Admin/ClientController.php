<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            ->select(DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Ticket statistics
        $ticketStats = [
            'total' => Ticket::count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'solved' => Ticket::where('status', 'solved')->count(),
            'close' => Ticket::where('status', 'close')->count(),
        ];

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
            'ticketStats' => $ticketStats,
        ]);
    }

    public function index(Request $request): Response
    {
        $query = User::where('role', 'client')
            ->with(['ksuSubscriptions.tenant']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Add KSU subscription count per client
        $clients->getCollection()->transform(function ($client) {
            $client->ksu_count = $client->ksuSubscriptions->count();
            $client->ksu_active_count = $client->ksuSubscriptions->filter(
                fn($s) => $s->status === 'active'
            )->count();
            return $client;
        });

        return Inertia::render('Admin/Client/Index', [
            'clients' => $clients,
            'filters' => ['search' => $search],
        ]);
    }

    public function show(string $id): Response
    {
        $client = User::where('role', 'client')
            ->with(['subscriptions.payments' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->findOrFail($id);

        $planLabels = [
            'starter' => ['name' => 'Starter', 'price' => 'Rp499.000/bln'],
            'premium' => ['name' => 'Premium', 'price' => 'Rp1.500.000/bln'],
            'enterprise' => ['name' => 'Enterprise', 'price' => 'Custom'],
        ];

        // Separate client subscription vs KSU tenant subscriptions
        $clientSubscription = $client->subscriptions()
            ->where(function ($q) { $q->whereNull('type')->orWhere('type', 'client'); })
            ->with('payments')
            ->first();

        $ksuSubscriptions = $client->subscriptions()
            ->where('type', 'ksu')
            ->with('tenant')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'tenant_id' => $sub->tenant_id,
                    'tenant_name' => $sub->tenant?->name,
                    'tenant_domain' => $sub->tenant?->domain,
                    'tenant_status' => $sub->tenant?->status,
                    'plan' => $sub->plan,
                    'max_resorts' => $sub->max_resorts,
                    'price_per_resort' => $sub->price_per_resort,
                    'status' => $sub->status,
                    'started_at' => $sub->started_at?->format('d M Y'),
                    'ends_at' => $sub->ends_at?->format('d M Y'),
                    'days_remaining' => $sub->daysRemaining(),
                ];
            });

        return Inertia::render('Admin/Client/Show', [
            'client' => $client,
            'clientSubscription' => $clientSubscription ? [
                'id' => $clientSubscription->id,
                'plan' => $clientSubscription->plan,
                'status' => $clientSubscription->status,
                'started_at' => $clientSubscription->started_at?->format('d M Y'),
                'ends_at' => $clientSubscription->ends_at?->format('d M Y'),
                'is_active' => $clientSubscription->isActive(),
            ] : null,
            'ksuSubscriptions' => $ksuSubscriptions,
            'planLabels' => $planLabels,
        ]);
    }

    public function resetPassword(Request $request, string $id): RedirectResponse
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $client->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password client berhasil direset.');
    }

    public function updateSubscription(Request $request, string $id): RedirectResponse
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

        // Notify staff and the client
        $notifService = app(NotificationService::class);
        $planName = ucfirst($validated['plan']);
        $statusText = $validated['status'] === 'active' ? 'diaktifkan' : $validated['status'];

        $notifService->sendToStaff(
            'subscription',
            "Langganan Baru: {$planName}",
            "{$client->name} berlangganan paket {$planName} — status: {$statusText}",
            "/admin/clients/{$client->id}",
            $subscription
        );

        $notifService->send(
            $client,
            'subscription',
            "Langganan {$planName} {$statusText}",
            "Paket {$planName} Anda telah {$statusText}. Silakan cek detail langganan.",
            '/client/subscription',
            $subscription
        );

        return redirect()->back()->with('success', 'Subscription berhasil diperbarui.');
    }
}
