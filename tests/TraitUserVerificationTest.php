<?php

namespace Jrean\UserVerification\Tests;

use Jrean\UserVerification\Tests\TestCase;
use Jrean\UserVerification\Tests\TestUser;

class TraitUserVerificationTest extends TestCase
{
    /** @test **/
    public function user_is_not_verified()
    {
        $user = new TestUser(0,null);
        $this->assertEquals($user->isVerified(), false);
    }

    /** @test **/
    public function user_is_verified()
    {
        $user = new TestUser(1,null);
        $this->assertEquals($user->isVerified(), true);
    }

    /** @test **/
    public function user_is_pending()
    {
        $user = new TestUser(0,'123456');
        $this->assertEquals($user->isPendingVerification(), true);
    }

    /** @test **/
    public function user_is_not_pending_already_verified()
    {
        $user = new TestUser(1,'123456');
        $this->assertEquals($user->isPendingVerification(), false);
    }

    /** @test **/
    public function user_is_not_pending_no_token()
    {
        $user = new TestUser(0,null);
        $this->assertEquals($user->isPendingVerification(), false);
    }

}
