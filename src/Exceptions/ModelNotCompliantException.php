<?php

namespace Jrean\UserVerification\Exceptions;

use Exception;

class ModelNotCompliantException extends Exception
{
    /**
     * The exception description.
     *
     * @var string
     */
    protected $message = 'The model instance provided is not compliant with this package.';
}
