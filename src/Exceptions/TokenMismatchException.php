<?php

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
