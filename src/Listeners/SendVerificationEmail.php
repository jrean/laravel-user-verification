<?php

namespace Jrean\UserVerification\Listeners;

use Illuminate\Auth\Events\Registered;
use Jrean\UserVerification\Facades\UserVerification;

class SendVerificationEmail
{
    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        // generate Token
        UserVerification::generate($event->user);
        // send verification
        UserVerification::send($event->user);
    }
}
