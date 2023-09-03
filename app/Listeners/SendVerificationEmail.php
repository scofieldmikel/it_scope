<?php

namespace App\Listeners;

use App\Services\TotpService;
use App\Events\UserRegistered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Auth\VerifyEmailNotify;

class SendVerificationEmail
{

    public TotpService $totpService;

    /**
     * Create the event listener.
     */
    public function __construct(TotpService $totpService)
    {
        $this->totpService = $totpService;
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new VerifyEmailNotify($event->user, $this->totpService));
    }
}
