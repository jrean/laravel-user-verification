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
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        $defaults = [static::CREATED_AT, static::UPDATED_AT];

        return $this->usesTimestamps()
                    ? array_unique(array_merge($this->dates, ['verified_at'], $defaults))
                    : $this->dates;
    }

    /**
     * Check if the user is verified.
     *
     * @return boolean
     */
    public function isVerified()
    {
        return ! $this->isNotVerified();
    }

    /**
     * Check if the user is not verified.
     *
     * @return boolean
     */
    public function isNotVerified()
    {
        return is_null($this->verified_at);
    }

    /**
     * Returns the verification date
     *
     * @return mixed
     */
    public function wasVerifiedAt()
    {
        return $this->verified_at;
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
