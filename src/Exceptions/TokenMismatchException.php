<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Exceptions;

use Exception;

class TokenMismatchException extends Exception
{
    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'Wrong verification token.';
}
