<?php

namespace Jrean\UserVerification;

class VerificationException extends \Exception
{
    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'You must provide an Eloquent User instance.';

}
