<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class BillingCancelExpired extends Command
{
    protected $signature = 'billing:cancel-expired';

    protected $description = 'Cancel unpaid invoices past due_date + 7 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(7);

        $invoices = Invoice::where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($invoices as $inv) {
            $inv->update(['status' => 'cancelled']);

            try {
                app(NotificationService::class)->send(
                    $inv->user,
                    'billing',
                    'Invoice Dibatalkan — ' . $inv->invoice_number,
                    "Invoice {$inv->invoice_number} dibatalkan karena melewati batas waktu pembayaran.",
                    '/client/invoices',
                    $inv
                );
            } catch (\Throwable $e) {}

            $count++;
        }

        $this->info("Cancelled {$count} expired invoice(s).");
        return Command::SUCCESS;
    }
}