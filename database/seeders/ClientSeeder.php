<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $clients = [
            [
                'name' => 'KSU Tabanan Jaya',
                'email' => 'ksu.tabanan@e-koperasi.com',
                'phone' => '081234567890',
            ],
            [
                'name' => 'Koperasi Mekar Sari',
                'email' => 'mekar.sari@e-koperasi.com',
                'phone' => '081234567891',
            ],
            [
                'name' => 'KSU Dharma Bakti',
                'email' => 'dharma.bakti@e-koperasi.com',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Koperasi Sejahtera Bersama',
                'email' => 'sejahtera@e-koperasi.com',
                'phone' => '081234567893',
            ],
            [
                'name' => 'KSU Sinar Nusantara',
                'email' => 'sinar.nusantara@e-koperasi.com',
                'phone' => '081234567894',
                'plan' => 'starter',
                'status' => 'expired',
            ],
        ];

        foreach ($clients as $data) {
            $plan = $data['plan'] ?? 'premium';
            $status = $data['status'] ?? 'active';

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'role' => 'client',
            ]);

            $startedAt = now()->subMonths(6);
            $endsAt = $status === 'active' ? now()->addMonths(6) : now()->subMonth();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'status' => $status,
                'started_at' => $startedAt,
                'ends_at' => $endsAt,
                'renewed_at' => $startedAt,
            ]);

            // Create payment history (6 months) via Invoice + PaymentTransaction
            for ($i = 5; $i >= 0; $i--) {
                $paidAt = now()->subMonths($i);

                $price = match ($plan) {
                    'starter' => 499000,
                    'enterprise' => 5000000,
                    default => 1500000,
                };

                $paymentStatus = $i === 0 && $status === 'expired' ? 'pending' : 'paid';

                $receiptNumber = 'INV-' . $paidAt->format('Ym') . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

                // Create invoice first
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'tenant_id' => $subscription->tenant_id,
                    'status' => $paymentStatus === 'paid' ? 'paid' : 'pending',
                    'total_amount' => $price,
                    'name' => $user->name,
                    'domain' => $subscription->tenant?->domain ?? 'manual',
                    'invoice_number' => $receiptNumber,
                    'due_date' => $paidAt->copy()->addDays(14),
                    'paid_at' => $paymentStatus === 'paid' ? $paidAt : null,
                ]);

                PaymentTransaction::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $price,
                    'base_amount' => $price,
                    'fee_amount' => 0,
                    'status' => $paymentStatus === 'paid' ? PaymentTransaction::STATUS_SUCCESS : PaymentTransaction::STATUS_PENDING,
                    'payment_type' => 'manual',
                    'payment_method_name' => 'manual_transfer',
                    'paid_at' => $paymentStatus === 'paid' ? $paidAt : null,
                    'notes' => $paymentStatus === 'pending' ? 'Menunggu konfirmasi pembayaran' : null,
                    'receipt_number' => $receiptNumber,
                ]);
            }
        }
    }
}