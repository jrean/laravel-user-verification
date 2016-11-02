<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Traits;

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
}
