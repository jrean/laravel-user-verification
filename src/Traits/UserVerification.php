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
        return $this->verified === 1;
    }

    /**
     * Check if the user verification is pending.
     *
     * @return boolean
     */
    public function isPendingVerification()
    {
        return $this->verified === 0 && $this->verification_token !== null;
    }
}
