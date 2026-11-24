<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ShopCommission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckMarketplaceDueLimitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:check-due-limits
                            {--force-billing : Force trigger monthly billing cycle evaluation regardless of day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate shop commission balances against dual due limits (Min & Max) and handle monthly billing cycles.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== Starting Mega Marketplace Due Limits & Billing Evaluation ===");

        $todayDay = (int)now()->day;
        $force = (bool)$this->option('force-billing');

        $commissions = ShopCommission::with('tenant')->get();

        $pausedCount = 0;
        $billedCount = 0;
        $rolloverCount = 0;

        foreach ($commissions as $comm) {
            $shopName = $comm->tenant?->shop_name ?? $comm->tenant_id;

            // 1. Hard Credit Limit Check (Max Due Limit)
            if ($comm->current_due >= $comm->max_due_limit) {
                if ($comm->marketplace_status === 'active') {
                    $comm->marketplace_status = 'paused_due_limit';
                    $comm->save();
                    $pausedCount++;

                    $this->warn("   [PAUSED] Shop [{$shopName}]: Current Due (৳{$comm->current_due}) >= Max Due Limit (৳{$comm->max_due_limit}). Auto-paused on Mega Marketplace.");
                    Log::warning("Marketplace Due Limit: Shop [{$comm->tenant_id}] auto-paused on marketplace. Due: ৳{$comm->current_due}");
                }
            } else {
                // If due was brought down and shop was paused due to limit, restore active
                if ($comm->marketplace_status === 'paused_due_limit' && $comm->current_due < $comm->max_due_limit) {
                    $comm->marketplace_status = 'active';
                    $comm->save();
                    $this->info("   [RESUMED] Shop [{$shopName}]: Current Due (৳{$comm->current_due}) < Max Due Limit (৳{$comm->max_due_limit}). Restored to Active.");
                }
            }

            // 2. Monthly Settlement & Min Due Limit Check
            if ($comm->billing_day === $todayDay || $force) {
                if ($comm->current_due < $comm->min_due_limit) {
                    // Due is below minimum billing threshold -> Roll over to next month without notice
                    $rolloverCount++;
                    $this->line("   [ROLLOVER] Shop [{$shopName}]: Due (৳{$comm->current_due}) < Min Due Limit (৳{$comm->min_due_limit}). Rolled over to next month.");
                } else {
                    // Due meets/exceeds minimum threshold -> Generate billing cycle notice
                    $billedCount++;
                    $comm->last_billed_at = now()->toDateString();
                    $comm->save();

                    $this->info("   [BILLED] Shop [{$shopName}]: Due (৳{$comm->current_due}) >= Min Due Limit (৳{$comm->min_due_limit}). Monthly bill statement updated.");
                }
            }
        }

        $this->newLine();
        $this->info("=== Evaluation Summary ===");
        $this->line("Total Shops Evaluated: {$commissions->count()}");
        $this->line("Shops Paused (Over Max Limit): {$pausedCount}");
        $this->line("Monthly Bills Generated: {$billedCount}");
        $this->line("Minor Dues Rolled Over: {$rolloverCount}");

        return self::SUCCESS;
    }
}
