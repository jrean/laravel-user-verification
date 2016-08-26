<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Traits;

trait RedirectsUsers
{

    /**
     * Get the redirect path for a successful verification token verification.
     *
     * @return string
     */
    public function redirectAfterVerification()
    {
        return property_exists($this, 'redirectAfterVerification') ? $this->redirectAfterVerification : '/';
    }

}
