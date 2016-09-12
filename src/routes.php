<?php

/*
|--------------------------------------------------------------------------
| Laravel user verification routes
|--------------------------------------------------------------------------
*/
Route::get('email-verification/error', 'App\Http\Controllers\Auth\RegisterController@getVerificationError')->name('email-verification.error');
Route::get('email-verification/check/{token}', 'App\Http\Controllers\Auth\RegisterController@getVerification')->name('email-verification.check');
