<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use Illuminate\Console\Command;

class BillingExpireTransactions extends Command
{
    protected $signature = 'billing:expire-transactions';
    protected $description = 'Expire pending payment transactions past their expiry date';

    public function handle(): int
    {
        $count = PaymentTransaction::where('status', PaymentTransaction::STATUS_PENDING)
            ->whereNotNull('expiry')
            ->where('expiry', '<=', now())
            ->update(['status' => PaymentTransaction::STATUS_EXPIRED]);

        $this->info("Expired {$count} transactions.");

        return Command::SUCCESS;
    }
}
