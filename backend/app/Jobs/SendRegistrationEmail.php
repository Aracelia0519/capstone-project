<?php

namespace App\Jobs;

use App\Mail\UserRegistrationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class SendRegistrationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userData;
    public $tries = 3;
    public $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->userData['email'])
                ->send(new UserRegistrationEmail($this->userData));
        } catch (\Exception $e) {
            Log::error('Failed to send registration email: ' . $e->getMessage(), [
                'user_email' => $this->userData['email']
            ]);
            
            // Retry logic is handled by Laravel
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Registration email job failed: ' . $exception->getMessage(), [
            'user_email' => $this->userData['email']
        ]);
    }
}