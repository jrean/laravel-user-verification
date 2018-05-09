<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Jrean\UserVerification\ConfirmationToken;

trait UserVerification
{
    /**
     * Check if the user is verified.
     *
     * @return boolean
     */
    public function isVerified()
    {
        return (bool) $this->verified;
    }

    /**
     * Check if the user verification is pending.
     *
     * @return boolean
     */
    public function isPendingVerification()
    {
        return ! $this->isVerified() && $this->hasVerificationToken();
    }

    /**
     * Checks if the user has a verification token.
     *
     * @return bool
     */
    public function hasVerificationToken()
    {
        return ! is_null($this->verification_token);
    }

    /**
     * Defines the relationship to the ConfirmationToken
     *
     * @return HasOne
     */
    public function confirmationToken()
    {
        return $this->hasOne(ConfirmationToken::class);
    }

    /**
     * Returns the confirmation token expiration timestamp
     *
     * @return mixed
     */
    public function getConfirmationTokenExpiry()
    {
        return $this->freshTimestamp()->addDays(10);
    }
}
