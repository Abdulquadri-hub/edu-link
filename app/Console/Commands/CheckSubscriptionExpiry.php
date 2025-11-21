<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';
    protected $description = 'Check for expiring and expired subscriptions';

    public function handle(): int
    {
        $this->info('Checking subscription expiry...');

        // Check for expired subscriptions
        $expired = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->get();

        foreach ($expired as $subscription) {
            $subscription->checkExpiry();
            $this->line("Marked subscription {$subscription->subscription_code} as expired");
        }

        // Check for expiring subscriptions (7 days warning)
        $expiringSoon = Subscription::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->get();

        foreach ($expiringSoon as $subscription) {
            $subscription->checkExpiryWarning(7);
            $daysRemaining = $subscription->end_date->diffInDays(now());
            $this->line("Sent expiry warning for {$subscription->subscription_code} ({$daysRemaining} days remaining)");
        }

        $this->info("Processed {$expired->count()} expired and {$expiringSoon->count()} expiring subscriptions.");

        return Command::SUCCESS;
    }
}
