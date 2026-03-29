<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client\ClientSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class SubscriptionReminderCommand extends Command
{
    protected $signature = 'subscription:reminders';
    protected $description = 'Send email reminders for expiring client subscriptions';

    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');

        $subscriptions = ClientSubscription::where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No active future subscriptions found in the database.');
        }

        foreach ($subscriptions as $subscription) {
            $user = User::find($subscription->client_id);
            if (!$user || empty($user->email)) continue;

            $daysLeft = (int) Carbon::now()->startOfDay()->diffInDays(Carbon::parse($subscription->end_date)->startOfDay(), false);
            
            // DEBUG LINE 1: Print the calculated days left
            $this->info("-> Found Sub ID {$subscription->id} for {$user->email}. Calculated Days Left: {$daysLeft}");

            $reminderType = null;
            $timeframe = "";

            if ($daysLeft === 30) {
                $reminderType = 30;
                $timeframe = "1 Month";
            } elseif ($daysLeft === 7) {
                $reminderType = 7;
                $timeframe = "1 Week";
            } elseif ($daysLeft === 1) {
                $reminderType = 1;
                $timeframe = "1 Day";
            }

            if ($reminderType !== null) {
                // DEBUG LINE 2: Print that a match was found
                $this->info("   -> Math matches for {$timeframe} reminder!");
                
                $cacheKey = "sub_reminder_{$subscription->id}_{$reminderType}";
                
                if (!Cache::has($cacheKey)) {
                    try {
                        $userName = $user->first_name ?? 'Valued Client';
                        $expiryDate = Carbon::parse($subscription->end_date)->format('F d, Y');
                        
                        Mail::html("
                            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 10px;'>
                                <h2 style='color: #0ea5e9; margin-top: 0;'>CaviteGoPaint Subscription Reminder</h2>
                                <p>Hello <strong>{$userName}</strong>,</p>
                                <p>This is a friendly reminder that your <strong>" . ucfirst($subscription->plan_name) . "</strong> subscription is expiring in <strong>{$timeframe}</strong>.</p>
                                <div style='background-color: #f8fafc; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #0ea5e9;'>
                                    <p style='margin: 0;'><strong>Plan:</strong> " . ucfirst($subscription->plan_name) . "</p>
                                    <p style='margin: 5px 0 0 0;'><strong>Expiry Date:</strong> {$expiryDate}</p>
                                </div>
                                <p>To avoid any interruption in your services, please log in to your dashboard to renew your plan.</p>
                                <br>
                                <p style='margin-bottom: 0;'>Thank you,<br><strong>CaviteGoPaint Team</strong></p>
                            </div>
                        ", function ($message) use ($user, $timeframe) {
                            $message->to($user->email)
                                    ->subject("CaviteGoPaint - Subscription Expiring in {$timeframe}");
                        });

                        Cache::put($cacheKey, true, now()->addDays(2));
                        $this->info("   -> SUCCESS: Reminder sent to {$user->email}");
                        
                    } catch (\Exception $e) {
                        $this->error("   -> ERROR: Mail sending failed: " . $e->getMessage());
                    }
                } else {
                    // DEBUG LINE 3: Print if the cache blocked it
                    $this->info("   -> BLOCKED: The cache says this email was already sent recently.");
                }
            }
        }

        $this->info('Subscription reminder check completed.');
    }
}