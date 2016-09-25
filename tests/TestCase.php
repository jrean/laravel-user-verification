<?php

namespace Jrean\UserVerification\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        // add any setup functions here
    }

    public function tearDown()
    {
        parent::tearDown();
        // add any tearDown functions here
    }
}
