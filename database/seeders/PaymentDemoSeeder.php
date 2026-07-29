<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentChannel;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class PaymentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $channels = PaymentChannel::all();
        if ($channels->isEmpty()) {
            $this->command?->warn('No payment channels found. Sync from Duitku admin panel, or seed channels manually.');

            // fallback: seed dummy channels
            $channels = collect();
            foreach ([
                ['code' => 'M1', 'name' => 'Mandiri VA', 'type' => 'va', 'fee_fixed' => 4000, 'fee_percent' => 0],
                ['code' => 'M2', 'name' => 'BCA VA', 'type' => 'va', 'fee_fixed' => 4000, 'fee_percent' => 0],
                ['code' => 'BRIVA', 'name' => 'BRI VA', 'type' => 'va', 'fee_fixed' => 3500, 'fee_percent' => 0],
                ['code' => 'BNIVA', 'name' => 'BNI VA', 'type' => 'va', 'fee_fixed' => 4000, 'fee_percent' => 0],
                ['code' => 'QRIS', 'name' => 'QRIS', 'type' => 'qris', 'fee_fixed' => 0, 'fee_percent' => 1],
                ['code' => 'GOPAY', 'name' => 'GoPay', 'type' => 'ewallet', 'fee_fixed' => 0, 'fee_percent' => 2],
            ] as $ch) {
                $channels->push(PaymentChannel::firstOrCreate(
                    ['code' => $ch['code']],
                    $ch
                ));
            }
        }

        $subscriptions = Subscription::whereIn('status', ['active', 'expired', 'trialing'])
            ->with('user')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->command?->warn('No subscriptions found. Run ClientSeeder + TenantSeeder first.');
            return;
        }

        $this->command?->info("Found {$subscriptions->count()} subscriptions. Generating invoices & payments...");

        $count = ['invoices' => 0, 'transactions' => 0, 'payments' => 0, 'pending' => 0];

        foreach ($subscriptions as $sub) {
            // Generate 3-6 months of invoices
            $historyMonths = match ($sub->status) {
                'trialing' => 1,
                default => random_int(3, 6),
            };

            for ($i = $historyMonths - 1; $i >= 0; $i--) {
                $invoiceDate = now()->subMonths($i);
                $isCurrentMonth = $i === 0;
                $isExpiredSub = $sub->status === 'expired';

                // If last month + expired = include unpaid invoice
                $status = ($isCurrentMonth && $isExpiredSub) ? 'pending' : 'paid';

                // Trialing: only first month is pending/paid, rest don't exist
                if ($sub->status === 'trialing' && $i > 0) {
                    continue;
                }

                // Create invoice
                $cycleMonths = 1;
                $qty = $sub->max_resorts ?? 1;
                $price = $sub->price_per_resort ?? 100000;
                $subtotal = $qty * (int) $price * $cycleMonths;
                $totalAmount = $subtotal;

                $month = $invoiceDate->format('Ym');
                $seq = random_int(1, 99);

                $invNumber = sprintf('INV-%s-DEMO-%04d', $month, $seq);

                $inv = Invoice::firstOrCreate(
                    ['invoice_number' => $invNumber],
                    [
                        'tenant_request_id' => $sub->tenant_id,
                        'tenant_id' => $sub->tenant_id,
                        'user_id' => $sub->user_id,
                        'requested_by' => $sub->user_id,
                        'name' => $sub->user?->name ?? 'Tenant',
                        'domain' => $sub->tenant?->domain ?? 'unknown',
                        'resort_count' => $qty,
                        'price_per_resort' => $price,
                        'months' => $cycleMonths,
                        'total_amount' => $totalAmount,
                        'subtotal' => $subtotal,
                        'discount_amount' => 0,
                        'due_date' => $invoiceDate->addDays(14),
                        'status' => $status,
                        'payment_channel' => null,
                        'paid_at' => $status === 'paid' ? $invoiceDate->addDays(random_int(0, 5)) : null,
                    ]
                );

                if (!$inv->wasRecentlyCreated) continue; // skip if already seeded

                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'description' => "Langganan {$inv->name} — {$qty} resort × {$cycleMonths} bulan",
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'total_amount' => $subtotal,
                    'type' => 'subscription',
                ]);

                $count['invoices']++;

                if ($status === 'paid') {
                    // Create Payment model (old system)
                    $receipt = 'INV-' . $invoiceDate->format('Ym') . '-' . str_pad((string) random_int(1, 999), 4, '0', STR_PAD_LEFT);
                    Payment::create([
                        'subscription_id' => $sub->id,
                        'amount' => $totalAmount,
                        'status' => 'paid',
                        'payment_method' => 'manual_transfer',
                        'paid_at' => $inv->paid_at,
                        'receipt_number' => $receipt,
                    ]);
                    $count['payments']++;

                    // Create PaymentTransaction (Duitku system) — simulate as success
                    $randomChannel = $channels->random();
                    $feeAmount = $randomChannel->calculateFee($totalAmount);

                    PaymentTransaction::create([
                        'invoice_id' => $inv->id,
                        'duitku_ref' => 'DEMO-' . strtoupper(bin2hex(random_bytes(8))),
                        'amount' => $totalAmount + $feeAmount,
                        'base_amount' => $totalAmount,
                        'fee_amount' => $feeAmount,
                        'channel_code' => $randomChannel->code,
                        'channel_name' => $randomChannel->name,
                        'status' => PaymentTransaction::STATUS_SUCCESS,
                        'paid_at' => $inv->paid_at,
                        'expiry' => $inv->paid_at?->copy()->addHours(24),
                    ]);
                    $count['transactions']++;

                    $inv->update([
                        'payment_channel' => $randomChannel->code,
                        'payment_transaction_id' => PaymentTransaction::latest()->first()?->id,
                    ]);
                } else {
                    // Pending — create PaymentTransaction with simulated Duitku VA
                    $randomChannel = $channels->whereIn('type', ['va', 'qris'])->first() ?? $channels->first();
                    $feeAmount = $randomChannel->calculateFee($totalAmount);
                    $expiry = now()->addHours(24);

                    // VA number sim
                    $vaNumber = match ($randomChannel->code) {
                        'M1' => '88888' . str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                        'M2' => '70000' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                        'BRIVA' => '88888' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                        'BNIVA' => '90000' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                        default => null,
                    };

                    $txn = PaymentTransaction::create([
                        'invoice_id' => $inv->id,
                        'duitku_ref' => 'DEMO-' . strtoupper(bin2hex(random_bytes(8))),
                        'amount' => $totalAmount + $feeAmount,
                        'base_amount' => $totalAmount,
                        'fee_amount' => $feeAmount,
                        'channel_code' => $randomChannel->code,
                        'channel_name' => $randomChannel->name,
                        'status' => PaymentTransaction::STATUS_PENDING,
                        'expiry' => $expiry,
                        'raw_response' => [
                            'vaNumber' => $vaNumber,
                            'reference' => 'DEMO-' . strtoupper(bin2hex(random_bytes(8))),
                            'paymentUrl' => null,
                        ],
                    ]);

                    $inv->update([
                        'payment_channel' => $randomChannel->code,
                        'payment_transaction_id' => $txn->id,
                    ]);

                    $count['transactions']++;
                    $count['pending']++;
                }
            }
        }

        $this->command?->info(sprintf(
            'Done. %d invoices, %d payments, %d transactions (%d pending, %d success)',
            $count['invoices'],
            $count['payments'],
            $count['transactions'],
            $count['pending'],
            $count['transactions'] - $count['pending'],
        ));
    }
}
