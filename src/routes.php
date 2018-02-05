<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */

/*
|--------------------------------------------------------------------------
| Laravel user verification routes
|--------------------------------------------------------------------------
*/

Route::group([
    'middleware' => 'web'
], function () {
    Route::get('email-verification/error', 'Auth\RegisterController@getVerificationError')
        ->name('email-verification.error');

    Route::get('email-verification/check/{token}', 'Auth\RegisterController@getVerification')
        ->name('email-verification.check');
});
