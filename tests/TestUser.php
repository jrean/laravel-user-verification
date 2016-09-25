<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Tests;

use Jrean\UserVerification\Traits\UserVerification;

class TestUser{

    use UserVerification;

    protected $verified;

    protected $verification_token;

    public function __construct($verified = 0, $token = null)
    {
        $this->verified = $verified;
        $this->verification_token = $token;
    }
}
