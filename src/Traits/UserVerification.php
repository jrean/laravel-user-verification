<?php

namespace Jrean\UserVerification\Traits;

trait UserVerification
{
    /**
     * returns true if the user is verified and false if not
     * @method isVerified
     *
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified === 1;
    }
    /**
     * returns true if a verification is pending for the user
     * @method verificationPending
     *
     * @return boolean
     */
    public function verificationPending()
    {
        return $this->verified === 0 && $this->verification_token !== null;
    }
}
