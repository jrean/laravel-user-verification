<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'No user found for the given email address.';
}
