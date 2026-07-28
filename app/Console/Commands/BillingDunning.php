<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class BillingDunning extends Command
{
    protected $signature = 'billing:dunning';

    protected $description = 'Send payment reminders for pending invoices';

    public function handle(): int
    {
        $invoices = Invoice::where('status', 'pending')
            ->whereNotNull('due_date')
            ->get();

        $count = 0;
        foreach ($invoices as $inv) {
            try {
                $daysLeft = now()->diffInDays($inv->due_date, false);

                $message = match (true) {
                    $daysLeft == 7 => "Invoice {$inv->invoice_number} akan jatuh tempo dalam 7 hari.",
                    $daysLeft == 3 => "Invoice {$inv->invoice_number} akan jatuh tempo dalam 3 hari.",
                    $daysLeft == 0 => "Invoice {$inv->invoice_number} sudah jatuh tempo hari ini.",
                    $daysLeft < 0 && abs($daysLeft) == 1 => "Invoice {$inv->invoice_number} sudah lewat 1 hari dari jatuh tempo.",
                    $daysLeft < 0 && abs($daysLeft) == 7 => "Tenant akan dinonaktifkan dalam 7 hari jika belum bayar.",
                    default => null,
                };

                if ($message) {
                    app(NotificationService::class)->send(
                        $inv->user,
                        'billing',
                        'Pengingat Pembayaran — ' . $inv->invoice_number,
                        $message,
                        '/client/invoices',
                        $inv
                    );
                    $count++;
                }
            } catch (\Throwable $e) {
                $this->error("Dunning failed for invoice {$inv->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$count} reminder(s).");
        return Command::SUCCESS;
    }
}