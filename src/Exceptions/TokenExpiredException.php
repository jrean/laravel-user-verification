<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Exceptions;

use Exception;
use Illuminate\Auth\Authenticatable;
use Jrean\UserVerification\ConfirmationToken;

class TokenExpiredException extends Exception
{
    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'Token expired.';

    /**
     * @var Authenticatable
     */
    protected $user;

    /**
     * @var ConfirmationToken
     */
    protected $token;

    public function __construct($user, ConfirmationToken $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * @return Authenticatable
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return ConfirmationToken
     */
    public function getToken()
    {
        return $this->token;
    }
}
