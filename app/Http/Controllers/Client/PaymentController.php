<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $payments = $user->subscription
            ? $user->subscription->payments()
                ->orderBy('created_at', 'desc')
                ->paginate(15)
            : collect([]);

        return Inertia::render('Client/Payments', [
            'payments' => $payments,
        ]);
    }

    public function show(int $id): Response
    {
        $user = auth()->user();
        $payment = Payment::where('id', $id)
            ->whereHas('subscription', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        return Inertia::render('Client/PaymentDetail', [
            'payment' => $payment,
            'subscription' => $payment->subscription,
        ]);
    }
}
