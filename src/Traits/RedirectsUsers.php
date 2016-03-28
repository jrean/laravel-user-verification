<?php

namespace Jrean\UserVerification\Traits;

trait RedirectsUsers
{
    /**
     * Get the redirect path if the user is already verified.
     *
     * @return string
     */
    public function redirectIfVerified()
    {
        return property_exists($this, 'redirectIfVerified') ? $this->redirectIfVerified : '/';
    }

    /**
     * Get the redirect path for a successful verification token generation.
     *
     * @return string
     */
    public function redirectAfterTokenGeneration()
    {
        return property_exists($this, 'redirectAfterTokenGeneration') ? $this->redirectAfterTokenGeneration : '/';
    }

    /**
     * Get the redirect path for a successful verification token verification.
     *
     * @return string
     */
    public function redirectAfterVerification()
    {
        return property_exists($this, 'redirectAfterVerification') ? $this->redirectAfterVerification : '/';
    }

    /**
     * Get the redirect path for a failing verification token verification.
     *
     * @return string
     */
    public function redirectIfVerificationFails()
    {
        return property_exists($this, 'redirectIfVerificationFails') ? $this->redirectIfVerificationFails : '/verification/error';
    }
}
