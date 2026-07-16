<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantAutoSuspend extends Command
{
    protected $signature = 'tenant:auto-suspend
                            {--grace-days=7 : Grace period after expiry before suspend}';

    protected $description = 'Auto-suspend tenants whose subscription has expired past grace period';

    public function handle(): int
    {
        $graceDays = (int) $this->option('grace-days');

        $expired = Subscription::where('type', 'ksu')
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now()->subDays($graceDays))
            ->get();

        $count = 0;
        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);
            Tenant::where('id', $sub->tenant_id)->update(['status' => 'suspended']);
            $count++;
        }

        // Reactivate tenants that just got renewed
        $renewed = Subscription::where('type', 'ksu')
            ->where('status', 'expired')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', now())
            ->get();

        foreach ($renewed as $sub) {
            $sub->update(['status' => 'active']);
            Tenant::where('id', $sub->tenant_id)->update(['status' => 'active']);
            $count++;
        }

        $this->info("Processed {$count} tenant(s).");

        return Command::SUCCESS;
    }
}
