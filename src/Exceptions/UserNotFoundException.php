<?php

namespace Jrean\UserVerification\Exceptions;

class UserNotFoundException extends \Exception
{
    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'No user found for that email adresse.';
}
