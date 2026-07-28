<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\BillingService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class BillingPreBill extends Command
{
    protected $signature = 'billing:pre-bill';

    protected $description = 'Generate recurring invoices for subscriptions expiring within 7 days';

    public function handle(BillingService $billing): int
    {
        $subscriptions = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('type', 'ksu')
            ->get();

        $count = 0;
        foreach ($subscriptions as $sub) {
            try {
                $invoice = $billing->generateInvoice($sub);
                if ($invoice) {
                    app(NotificationService::class)->send(
                        $sub->user,
                        'billing',
                        'Invoice Baru — ' . $invoice->invoice_number,
                        "Invoice langganan {$sub->tenant?->name} telah terbit. Total: Rp" . number_format($invoice->total_amount, 0, ',', '.'),
                        '/client/invoices',
                        $invoice
                    );
                    $count++;
                }
            } catch (\Throwable $e) {
                $this->error("Failed for subscription {$sub->id}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$count} invoice(s).");
        return Command::SUCCESS;
    }
}