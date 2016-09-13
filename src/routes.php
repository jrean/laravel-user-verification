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
Route::group(['middleware' => 'web'], function () {
    Route::get('email-verification/error', 'Jrean\UserVerification\Controllers\UserVerificationController@getVerificationError')->name('email-verification.error');
    Route::get('email-verification/check/{token}', 'Jrean\UserVerification\Controllers\UserVerificationController@getVerification')->name('email-verification.check');
    Route::post('email-verification/resend', 'Jrean\UserVerification\Controllers\UserVerificationController@postVerification')->name('email-verification.resend');
});
