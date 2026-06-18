<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        $recentPayments = $subscription
            ? $subscription->payments()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
            : [];

        // Ticket statistics
        $ticketStats = [
            'total' => Ticket::where('user_id', $user->id)->count(),
            'pending' => Ticket::where('user_id', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Ticket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'solved' => Ticket::where('user_id', $user->id)->where('status', 'solved')->count(),
            'close' => Ticket::where('user_id', $user->id)->where('status', 'close')->count(),
        ];

        return Inertia::render('Client/Dashboard', [
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan,
                'status' => $subscription->status,
                'is_active' => $subscription->isActive(),
                'started_at' => $subscription->started_at?->format('d M Y'),
                'ends_at' => $subscription->ends_at?->format('d M Y'),
                'days_remaining' => $subscription->daysRemaining(),
                'usage_percent' => $subscription->usagePercent(),
            ] : null,
            'recentPayments' => $recentPayments,
            'ticketStats' => $ticketStats,
        ]);
    }
}
