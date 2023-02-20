**jrean/laravel-user-verification** is a PHP package built for Laravel 5.*, 6.*, 7.*, 8.*, 9.* & 10.* to
easily handle a user verification and validate the e-mail.

[![Latest Stable Version](https://poser.pugx.org/jrean/laravel-user-verification/v/stable)](https://packagist.org/packages/jrean/laravel-user-verification) [![Total Downloads](https://poser.pugx.org/jrean/laravel-user-verification/downloads)](https://packagist.org/packages/jrean/laravel-user-verification) [![License](https://poser.pugx.org/jrean/laravel-user-verification/license)](https://packagist.org/packages/jrean/laravel-user-verification)

## VERSIONS

**This package is Laravel 10.0 compliant.**

| laravel/branch | [2.2](https://github.com/jrean/laravel-user-verification/tree/2.2) | [3.0](https://github.com/jrean/laravel-user-verification/tree/3.0) | [4.1](https://github.com/jrean/laravel-user-verification/tree/4.1) | [5.0](https://github.com/jrean/laravel-user-verification/tree/5.0) | [6.0](https://github.com/jrean/laravel-user-verification/tree/6.0) | [7.0](https://github.com/jrean/laravel-user-verification/tree/7.0) | [8.0](https://github.com/jrean/laravel-user-verification/tree/8.0) | [9.0](https://github.com/jrean/laravel-user-verification/tree/9.0) | [10.0](https://github.com/jrean/laravel-user-verification/tree/10.0) | [11.0](https://github.com/jrean/laravel-user-verification/tree/11.0) | [master](https://github.com/jrean/laravel-user-verification/tree/master)
|---------|-----|-----|-----|-----|-----|-----|-----|-------|-------|-------|-------|
| 5.0.*   |  x  |     |     |     |     |     |     |       |       |       |       |
| 5.1.*   |  x  |     |     |     |     |     |     |       |       |       |       |
| 5.2.*   |  x  |     |     |     |     |     |     |       |       |       |       |
| 5.3.*   |     |  x  |     |     |     |     |     |       |       |       |       |
| 5.4.*   |     |     |  x  |     |     |     |     |       |       |       |       |
| 5.5.*   |     |     |     |  x  |     |     |     |       |       |       |       |
| 5.6.*   |     |     |     |     |  x  |     |     |       |       |       |       |
| 5.7.*   |     |     |     |     |     |  x  |     |       |       |       |       |
| 5.8.*   |     |     |     |     |     |  x  |     |       |       |       |       |
| 6.0.*   |     |     |     |     |     |     |  x  |       |       |       |       |
| 7.0.*   |     |     |     |     |     |     |     |       |       |       |   x   |
| 8.0.*   |     |     |     |     |     |     |     |   x   |       |       |   x   |
| 9.0.*   |     |     |     |     |     |     |     |       |   x   |       |   x   |
| 10.0.*  |     |     |     |     |     |     |     |       |       |   x   |   x   |

## ABOUT

- [x] Generate and store a verification token for a registered user
- [x] Send or queue an e-mail with the verification token link
- [x] Handle the token verification
- [x] Set the user as verified
- [x] Relaunch the process anytime

## Table of Contents

- [INSTALLATION](#installation)
  * [Add the Service Provider & Facade/Alias](#add-the-service-provider---facade-alias)
- [CONFIGURATION](#configuration)
  * [Migration](#migration)
- [Middleware](#middleware)
  * [Default middleware](#default-middleware)
  * [Custom middleware](#custom-middleware)
- [E-MAIL](#e-mail)
  * [E-mail View](#e-mail-view)
- [ERRORS](#errors)
  * [Error View](#error-view)
- [USAGE](#usage)
  * [Routes](#routes)
    + [Overriding package routes](#overriding-package-routes)
  * [Traits](#traits)
  * [Endpoints](#endpoints)
  * [API](#api)
  * [Facade](#facade)
  * [Attributes/Properties](#attributes-properties)
  * [Translations](#translations)
  * [Auto-login](#auto-login)
  * [Customize](#customize)
- [GUIDELINES](#guidelines)
  * [Example](#example)
- [RELAUNCH THE PROCESS ANYTIME](#relaunch-the-process-anytime)
- [LARAVEL SPARK](#laravel-spark)
- [CONTRIBUTE](#contribute)
- [LICENSE](#license)

## INSTALLATION

This project can be installed via [Composer](http://getcomposer.org). To get
the latest version of Laravel User Verification, add the following line to the
require block of your composer.json file:

    {
        "require": {
            "jrean/laravel-user-verification": "dev-master"
        }

    }

You'll then need to run `composer install` or `composer update` to download the
package and have the autoloader updated.

Or run the following command:

    composer require jrean/laravel-user-verification

### Add the Service Provider & Facade/Alias

Once Larvel User Verification is installed, you need to register the service provider in `config/app.php`.
Make sure to add the following line **above** the `RouteServiceProvider`.

```PHP
Jrean\UserVerification\UserVerificationServiceProvider::class,
```

You may add the following `aliases` to your `config/app.php`:

```PHP
'UserVerification' => Jrean\UserVerification\Facades\UserVerification::class,
```

Publish the package config file by running the following command:

```
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="config"
```

## CONFIGURATION
The model representing the `User` must implement the authenticatable
interface `Illuminate\Contracts\Auth\Authenticatable` which is the default with
the Eloquent `User` model.

### Migration

The table representing the user must be updated with two new columns, `verified` and `verification_token`.
This update will be performed by the migrations included with this package.

**It is mandatory that the two columns are on the same table where the user's
e-mail is stored. Please make sure you do not already have those fields on
your user table.**

To run the migrations from this package use the following command:

```
php artisan migrate --path="/vendor/jrean/laravel-user-verification/src/resources/migrations"
```

The package tries to guess your `user` table by checking what is set in the auth providers users settings.
If this key is not found, the default `App\User` will be used to get the table name.

To customize the migration, publish it with the following command:

```
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="migrations"
```

## Middleware

### Default middleware

This package provides an optional middleware throwing a `UserNotVerifiedException`.
Please refer to the [Laravel Documentation](https://laravel.com/docs/master/errors#the-exception-handler) to learn more about how to work with the exception handler.

To register the default middleware add the following lines to the `$routeMiddleware` array within the `app/Http/Kernel.php` file:

```php
protected $routeMiddleware = [
    // …
    'isVerified' => \Jrean\UserVerification\Middleware\IsVerified::class,
```

Apply the middleware on your routes:

```php
Route::group(['middleware' => ['isVerified']], function () {
    // …
```

### Custom middleware

Create your own custom middleware using the following artisan command:

```
php artisan make:middleware IsVerified
```

For more details about middlewares, please refer to the [Laravel Documentation](https://laravel.com/docs/5.3/middleware).

## E-MAIL

This package provides a method to send an e-mail with a link containing the verification token.

- `send(AuthenticatableContract $user, $subject, $from = null, $name =
    null)`

By default the package will use the `from` and `name` values defined into the
`config/mail.php` file:

```php
    'from' => ['address' => '', 'name' => ''],
```

If you want to override the values, simply set the `$from` and (optional)
`$name` parameters.

Refer to the Laravel [documentation](https://laravel.com/docs/) for the
proper e-mail component configuration.


### E-mail View

The user will receive an e-mail with a link leading to the `getVerification()`
method (endpoint). The view will receive a `$user` variable which contains the
user details such as the verification token.

The package allow you to use both traditional blade view files and markdown.

By default a sample e-mail view is loaded to get you started with:

```
Click here to verify your account: <a href="{{ $link = route('email-verification.check', $user->verification_token) . '?email=' . urlencode($user->email) }}">{{ $link }}</a>
```

If you prefer to use Markdown instead, update the package config file
`user-verification.php` in the `config` directory and replace the following:

```PHP
'email' => [
    'type' => 'default',
],
```

by:

```PHP
'email' => [
    'type' => 'markdown',
],
```

If you want to customize the e-mail views, run the following command to publish
them and edit them to your needs:

**The URL must contain the verification token as parameter + (mandatory) a
query string with the user's e-mail as parameter.**

```
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="views"
```

The view will be available in the `resources/views/vendor/laravel-user-verification/` directory.

## ERRORS

This package throws several exceptions. You are free to use `try/catch`
statements or to rely on the Laravel built-in exceptions handler.

* `ModelNotCompliantException`

The model instance provided is not compliant with this package. It must
implement the authenticatable interface
`Illuminate\Contracts\Auth\Authenticatable`

* `TokenMismatchException`

Wrong verification token.

* `UserIsVerifiedException`

The given user is already verified.

* `UserNotVerifiedException`

The given user is not yet verified.

* `UserNotFoundException`

No user found for the given e-mail address.

* `UserHasNoEmailException`

User email property is null or empty.

### Error View

By default the `user-verification.blade.php` view will be loaded for the verification error route `/email-verification/error`. If an error occurs, the user will be redirected to this route and this view will be rendered.

To customize the view, publish it with the following command:

```
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="views"
```

The view will be available in the `resources/views/vendor/laravel-user-verification/` directory and can be customized.

## USAGE

### Routes

By default this packages ships with two routes.

```PHP
Route::get('email-verification/error', 'Auth\RegisterController@getVerificationError')->name('email-verification.error');
Route::get('email-verification/check/{token}', 'Auth\RegisterController@getVerification')->name('email-verification.check');
```

#### Overriding package routes

To define your own custom routes, put the package service provider call before the `RouteServiceProvider` call in the `config/app.php` file.

```PHP
   /*
    * Package Service Providers...
    */
    Jrean\UserVerification\UserVerificationServiceProvider::class,

   /*
    * Application Service Providers...
    */
    // ...
    App\Providers\RouteServiceProvider::class,
```

Then, add your custom routes in your route file.

### Traits

The package offers three (3) traits for a quick implementation.
**Only `VerifiesUsers` trait is mandatory** and includes `RedirectsUsers`.

`Jrean\UserVerification\Traits\VerifiesUsers`

`Jrean\UserVerification\Traits\RedirectsUsers`

and:

`Jrean\UserVerification\Traits\UserVerification`

This last one offers two methods that can be added to the `User` model.

- `isVerified` checks if a user is marked as verified.
- `isPendingVerification` checks if a verification process has been initiated for a user.

Add the use statement to your `User` model and use the `UserVerification` within the class:

### Endpoints

The two (2) following methods are included into the `VerifiesUsers` trait and
called by the default package routes.

* `getVerification(Request $request, $token)`

Handle the user verification.

* `getVerificationError()`

Do something if the verification fails.

### API

The package public API offers eight (8) methods.

* `generate(AuthenticatableContract $user)`

Generate and save a verification token for the given user.

* `send(AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Send by e-mail a link containing the verification token.

* `sendQueue(AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Queue and send by e-mail a link containing the verification token.

* `sendLater($seconds, AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Send later by e-mail a link containing the verification token.

* `process($email, $token, $userTable)`

Process the token verification for the given e-mail and token.

For the `sendQueue`, `sendLater` and
`sendLaterOn` methods, you must [configure your queues](https://laravel.com/docs/)
before using this feature.

### Facade

The package offers a facade `UserVerification::`.

### Attributes/Properties

To customize the package behaviour and the redirects you can implement and
customize six (6) attributes/properties:

* `$redirectIfVerified = '/';`

Where to reditect if the authenticated user is already verified.

* `$redirectAfterVerification = '/';`

Where to redirect after a successful verification token verification.

* `$redirectIfVerificationFails = '/email-verification/error';`

Where to redirect after a failling token verification.

* `$verificationErrorView = 'laravel-user-verification::user-verification';`

Name of the view returned by the getVerificationError method.

* `$verificationEmailView = 'laravel-user-verification::email'`

Name of the default e-mail view.

* `$userTable = 'users';`

Name of the default table used for managing users.

### Translations

To customize the translations you may publish the files to your `resources/lang/vendor` folder using the following command:

```
php artisan vendor:publish --provider="Jrean\UserVerification\UserVerificationServiceProvider" --tag="translations"
```

This will add `laravel-user-verification/en/user-verification.php` to your vendor folder. By creating new language folders, like `de` or `fr` and placing a `user-verification.php` with the translations inside, you can add translations for other languages. You can find out more about localization in the [Laravel documentation](https://laravel.com/docs/5.3/localization).

### Auto-login

If you wish to automaticaly log in the user after the verification process, update the package config file `user-verification.php` in the config directory and replace the following:

```PHP
'auto-login' => false,
```

by:

```PHP
'auto-login' => true,
```

### Customize

You can customize the package behaviour by overriding/overwriting the
public methods and the attributes/properties. Dig into the source.

## GUIDELINES

**This package doesn't require the user to be authenticated to perform the
verification. You are free to implement any flow you may want to achieve.**

This package wishes to let you be creative while offering you a predefined
path. **The following guidelines assume you have configured Laravel for the
package as well as created and migrated the migration according to this
documentation and the previous documented steps.**

Note that by default the behaviour of Laravel is to return an authenticated
user after the registration step.

### Example

The following code sample aims to showcase a quick and basic implementation
following Laravel logic. You are free to implement the way you want.
It is highly recommended to read and to understand the way Laravel implements
registration/authentication.

- Define the e-mail view.

Edit the `app\Http\Controllers\Auth\RegisterController.php` file.

- [x] Import the `VerifiesUsers` trait (mandatory)
- [ ] Overwrite and customize the redirect attributes/properties paths
    available within the `RedirectsUsers` trait included by the
    `VerifiesUsers` trait. (not mandatory)
- [ ] Overwrite the contructor (not mandatory)
- [x] Overwrite the `register()` method (mandatory)

```PHP
    namespace App\Http\Controllers\Auth;

    use App\User;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Foundation\Auth\RegistersUsers;

    use Illuminate\Http\Request;
    use Illuminate\Auth\Events\Registered;
    use Jrean\UserVerification\Traits\VerifiesUsers;
    use Jrean\UserVerification\Facades\UserVerification;

    class RegisterController extends Controller
    {
        /*
        |--------------------------------------------------------------------------
        | Register Controller
        |--------------------------------------------------------------------------
        |
        | This controller handles the registration of new users as well as their
        | validation and creation. By default this controller uses a trait to
        | provide this functionality without requiring any additional code.
        |
        */

        use RegistersUsers;

        use VerifiesUsers;

        /**
         * Where to redirect users after registration.
         *
         * @var string
         */
        protected $redirectTo = '/home';

        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            // Based on the workflow you need, you may update and customize the following lines.

            $this->middleware('guest', ['except' => ['getVerification', 'getVerificationError']]);
        }

        /**
         * Get a validator for an incoming registration request.
         *
         * @param  array  $data
         * @return \Illuminate\Contracts\Validation\Validator
         */
        protected function validator(array $data)
        {
            return Validator::make($data, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
            ]);
        }

        /**
         * Create a new user instance after a valid registration.
         *
         * @param  array  $data
         * @return User
         */
        protected function create(array $data)
        {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
        }

        /**
         * Handle a registration request for the application.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function register(Request $request)
        {
            $this->validator($request->all())->validate();

            $user = $this->create($request->all());

            event(new Registered($user));

            $this->guard()->login($user);

            UserVerification::generate($user);

            UserVerification::send($user, 'My Custom E-mail Subject');

            return $this->registered($request, $user)
                            ?: redirect($this->redirectPath());
        }
    }
```

At this point, after registration, an e-mail is sent to the user.
Click the link within the e-mail and the user will be verified against the
token.

If you want to perform the verification against an authenticated user you must
update the middleware exception to allow `getVerification` and
`getVerificationError` routes to be accessed.

```PHP
$this->middleware('guest', ['except' => ['getVerification', 'getVerificationError']]);
```

## RELAUNCH THE PROCESS ANYTIME

If you want to regenerate and resend the verification token, you can do this with the following two lines:

```PHP
UserVerification::generate($user);
UserVerification::send($user, 'My Custom E-mail Subject');
```

The `generate` method will generate a new token for the given user and change the `verified` column to 0. The `send` method will send a new e-mail to the user.

## LARAVEL SPARK

For Laravel Spark integration, follow this [article from Ian Fagg](https://medium.com/@idff/laravel-spark-user-verification-75441cff5e44)

## CONTRIBUTE

Feel free to comment, contribute and help. 1 PR = 1 feature.

## LICENSE

Laravel User Verification is licensed under [The MIT License (MIT)](LICENSE).
