<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class BillingTrialExpire extends Command
{
    protected $signature = 'billing:trial-expire';

    protected $description = 'Expire trial subscriptions past their trial_ends_at';

    public function handle(): int
    {
        $count = Subscription::where('status', Subscription::STATUS_TRIALING)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->update(['status' => Subscription::STATUS_EXPIRED]);

        $this->info("Trial expired: {$count}");

        return Command::SUCCESS;
    }
}
