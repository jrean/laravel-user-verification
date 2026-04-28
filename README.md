# Laravel User Verification

A PHP package built for Laravel to easily handle user verification and email validation.

[![Latest Stable Version](https://poser.pugx.org/jrean/laravel-user-verification/v/stable)](https://packagist.org/packages/jrean/laravel-user-verification)
[![Total Downloads](https://poser.pugx.org/jrean/laravel-user-verification/downloads)](https://packagist.org/packages/jrean/laravel-user-verification)
[![License](https://poser.pugx.org/jrean/laravel-user-verification/license)](https://packagist.org/packages/jrean/laravel-user-verification)

## Features

- Generate and store verification tokens for registered users
- Send or queue verification emails with token links
- Handle token verification process
- Set users as verified
- Relaunch the verification process anytime

## Laravel Compatibility

| Laravel Version | Package Version                                                                              |
|-----------------|----------------------------------------------------------------------------------------------|
| 5.0.* - 5.2.*   | [2.2](https://github.com/jrean/laravel-user-verification/tree/2.2)                           |
| 5.3.*           | [3.0](https://github.com/jrean/laravel-user-verification/tree/3.0)                           |
| 5.4.*           | [4.1](https://github.com/jrean/laravel-user-verification/tree/4.1)                           |
| 5.5.*           | [5.0](https://github.com/jrean/laravel-user-verification/tree/5.0)                           |
| 5.6.*           | [6.0](https://github.com/jrean/laravel-user-verification/tree/6.0)                           |
| 5.7.* - 5.8.*   | [7.0](https://github.com/jrean/laravel-user-verification/tree/7.0)                           |
| 6.0.*           | [8.0](https://github.com/jrean/laravel-user-verification/tree/8.0)                           |
| 7.0.* - 11.0.*  | Use [master](https://github.com/jrean/laravel-user-verification/tree/master) or check below: |
| 7.0.*           | [master](https://github.com/jrean/laravel-user-verification/tree/master)                     |
| 8.0.*           | [9.0](https://github.com/jrean/laravel-user-verification/tree/9.0)                           |
| 9.0.*           | [10.0](https://github.com/jrean/laravel-user-verification/tree/10.0)                         |
| 10.0.*          | [11.0](https://github.com/jrean/laravel-user-verification/tree/11.0)                         |
| 11.0.*          | [12.0](https://github.com/jrean/laravel-user-verification/tree/12.0)                         |
| 12.0.*          | [13.0](https://github.com/jrean/laravel-user-verification/tree/13.0)                         |
| 13.0.*          | [14.0](https://github.com/jrean/laravel-user-verification/tree/14.0)                         |

**This package is now Laravel 13.0 compliant with v14.0.0.**

## Installation

### Via Composer

```bash
composer require jrean/laravel-user-verification
```

### Register Service Provider & Facade

Add the service provider to `config/app.php`. Make sure to add it **above** the `RouteServiceProvider`.

```php
'providers' => [
    // ...
    Jrean\UserVerification\UserVerificationServiceProvider::class,
    // ...
    App\Providers\RouteServiceProvider::class,
],
```

Optionally, add the facade:

```php
'aliases' => [
    // ...
    'UserVerification' => Jrean\UserVerification\Facades\UserVerification::class,
],
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="config"
```

## Database Configuration

The package requires adding two columns to your users table: `verified` and `verification_token`.

### Run Migrations

```bash
php artisan migrate --path="/vendor/jrean/laravel-user-verification/src/resources/migrations"
```

Or publish and customize the migrations:

```bash
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="migrations"
```

## Middleware

### Default Middleware

Register the included middleware in `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'isVerified' => \Jrean\UserVerification\Middleware\IsVerified::class,
];
```

Apply it to routes:

```php
Route::group(['middleware' => ['isVerified']], function () {
    // Protected routes...
});
```

### Custom Middleware

```bash
php artisan make:middleware IsVerified
```

## Email Configuration

### Default Email View

The package includes a basic email template. The view receives a `$user` variable containing user details and the verification token.

### Customize Email View

Publish the views:

```bash
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="views"
```

The views will be available in `resources/views/vendor/laravel-user-verification/`.

### Markdown Email Support

To use Markdown instead of Blade templates, update the `user-verification.php` config:

```php
'email' => [
    'type' => 'markdown',
],
```

### Email Sending Methods

```php
// Send immediately
UserVerification::send($user, 'Email Verification');

// Queue for sending
UserVerification::sendQueue($user, 'Email Verification');

// Send later
UserVerification::sendLater($seconds, $user, 'Email Verification');
```

## Usage

### Routes

The package provides two default routes:

```php
Route::get('email-verification/error', 'Auth\RegisterController@getVerificationError')->name('email-verification.error');
Route::get('email-verification/check/{token}', 'Auth\RegisterController@getVerification')->name('email-verification.check');
```

### Required Trait

Add the `VerifiesUsers` trait to your registration controller:

```php
use Jrean\UserVerification\Traits\VerifiesUsers;

class RegisterController extends Controller
{
    use RegistersUsers, VerifiesUsers;
    
    // ...
}
```

### Integration Example

Here's a typical implementation in `RegisterController.php`:

```php
public function register(Request $request)
{
    $this->validator($request->all())->validate();

    $user = $this->create($request->all());

    event(new Registered($user));

    $this->guard()->login($user);

    UserVerification::generate($user);
    UserVerification::send($user, 'Please Verify Your Email');

    return $this->registered($request, $user)
                    ?: redirect($this->redirectPath());
}
```

### Auto-Login After Verification

Enable auto-login after verification in the config:

```php
'auto-login' => true,
```

### Translations

Publish translation files:

```bash
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="translations"
```

## API Reference

### Core Methods

- `generate(AuthenticatableContract $user)` - Generate and save a verification token
- `send(AuthenticatableContract $user, $subject = null, $from = null, $name = null)` - Send verification email
- `process($email, $token, $userTable)` - Process the token verification

### Model Traits

Add the `UserVerification` trait to your User model for these methods:

- `isVerified()` - Check if user is verified
- `isPendingVerification()` - Check if verification is pending

## Error Handling

The package throws the following exceptions:

- `ModelNotCompliantException`
- `TokenMismatchException`
- `UserIsVerifiedException`
- `UserNotVerifiedException`
- `UserNotFoundException`
- `UserHasNoEmailException`

## License

Laravel User Verification is licensed under [The MIT License (MIT)](LICENSE).
