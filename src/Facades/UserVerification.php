<?php

namespace Jrean\UserVerification\Facades;

use Illuminate\Support\Facades\Facade;

class UserVerification extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'user.verification';
    }
}
