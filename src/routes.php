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
    'middleware' => 'web',
    'prefix' => 'email-verification',
], function () {

    Route::get('error', 'App\Http\Controllers\Auth\RegisterController@getVerificationError')
        ->name('email-verification.error');

    Route::get('check/{token}', 'App\Http\Controllers\Auth\RegisterController@getVerification')
        ->name('email-verification.check');

    Route::get('is-verified', 'App\Http\Controllers\Auth\RegisterController@getIsVerifiedView')
        ->name('email-verification.is-verified');

    Route::get('after-verification', 'App\Http\Controllers\Auth\RegisterController@getAfterVerificationView')
        ->name('email-verification.after-verification');

});
