**jrean/laravel-user-verification** is a PHP package built for Laravel 5.* to
easily handle a user verification and validate the e-mail.

[![Latest Stable Version](https://poser.pugx.org/jrean/laravel-user-verification/v/stable)](https://packagist.org/packages/jrean/laravel-user-verification) [![Total Downloads](https://poser.pugx.org/jrean/laravel-user-verification/downloads)](https://packagist.org/packages/jrean/laravel-user-verification) [![License](https://poser.pugx.org/jrean/laravel-user-verification/license)](https://packagist.org/packages/jrean/laravel-user-verification)

## Versions
**This package is Laravel 5.3 compliant.**

For Laravel 5.3.*, use branch
[3.0](https://github.com/jrean/laravel-user-verification/tree/3.0)
and/or [master](https://github.com/jrean/laravel-user-verification/tree/master)

For Laravel 5.0.* | 5.1.* | 5.2.*, use branch
[2.2](https://github.com/jrean/laravel-user-verification/tree/2.2)

## About

- [x] Generate and store a verification token for a registered user
- [x] Send or queue an e-mail with the verification token link
- [x] Handle the token verification
- [x] Set the user as verified
- [x] Relaunch the process anytime

## Installation

This project can be installed via [Composer](http://getcomposer.org). To get
the latest version of Laravel User Verification, add the following line to the
require block of your composer.json file:

    {
        "require": {
                "jrean/laravel-user-verification": "^2.0"
        }

    }

You'll then need to run `composer install` or `composer update` to download the
package and have the autoloader updated.

Or run the following command:

    "composer require jrean/laravel-user-verification"


### Add the Service Provider

Once Larvel User Verification is installed, you need to register the service provider.

Open up `config/app.php` and add the following to the `providers` key:

* `Jrean\UserVerification\UserVerificationServiceProvider::class`

### Add the Facade/Alias

Open up `config/app.php` and add the following to the `aliases` key:

* `'UserVerification' => Jrean\UserVerification\Facades\UserVerification::class`

## Configuration

Prior to use this package, the table representing the user must be updated with
two new columns, `verified` and `verification_token`.

**It is mandatory to add the two columns on the same table and where the user's
e-mail is stored.**

The model representing the `User` must implement the authenticatable
interface `Illuminate\Contracts\Auth\Authenticatable` which is the default with
the Eloquent `User` model.

### Migration

Generate the migration file with the following artisan command:

```
php artisan make:migration add_verification_to_:table_table --table=":table"
```

Where `:table` is replaced by the table name of your choice.

For instance, if you want to keep the default Eloquent `User` table:

```
php artisan make:migration add_verification_to_users_table --table="users"
```

Once the migration is generated, edit the generated migration file in
`database/migration` with the following lines:

```
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(':table', function (Blueprint $table) {
            $table->boolean('verified')->default(false);
            $table->string('verification_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(':table', function (Blueprint $table) {
            $table->dropColumn('verified');
            $table->dropColumn('verification_token');
        });
    }

```

Where `:table` is replaced by the table name of your choice.

Migrate the migration with the following command:

```
php artisan migrate
```

## E-mail

This package provides a method to send an e-mail with a link containing the verification token.

- `send(AuthenticatableContract $user, $subject = null, $from = null, $name =
    null)`

By default the package will use the `from` and `name` values defined into the
`config/mail.php` file:

    'from' => ['address' => '', 'name' => ''],

If you want to override the values, simply set the `$from` and (optional)
`$name` parameters.

Refer to the Laravel [documentation](https://laravel.com/docs/) for the
proper e-mail component configuration.

### E-mail View

The user will receive an e-mail with a link leading to the `getVerification()`
method (endpoint). The view will receive a `$user` variable which contains the
user details such as the verification token.

By default the package sets the e-mail view as `emails.user-verification`.
Create a view for this e-mail at `resources/views/emails/user-verification.blade.php`.

If you want to customize the e-mail view location you can create the view file
wherever you want and call `UserVerification::emailView('directory.your-view-name')`.

Here is a sample e-mail view content to get you started with:
**The link url must contain the verification token as parameter + (mandatory) a
query string with the user's e-mail as parameter.**

```
Click here to verify your account: <a href="{{ $link = url('verification', $user->verification_token) . '?email=' . urlencode($user->email) }}"> {{ $link }}</a>
```

## Errors

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

* `UserNotFoundException`

No user found for the given e-mail adresse.

### Error View

Create a view for the default verification error route `/verification/error` at
`resources/views/errors/user-verification.blade.php`. If an error occurs, the
user will be redirected to this route and this view will be rendered. **You
must implement and customize this view to your needs.** For instance you may
wish to display a short message saying that something went wrong and then ask
for the user's e-mail again and start the process from scratch (generate, send,
verify, ...).

## Usage

### Routes

Add the two (2) default routes to the `app\Http\routes.php` file. Routes are
customizable.

```
    Route::get('verification/error', 'Auth\AuthController@getVerificationError');
    Route::get('verification/{token}', 'Auth\AuthController@getVerification');
```

### Trait

The package offers two (2) traits for a quick implementation.
**Only `VerifiesUsers` must be included.**

`Jrean\UserVerification\Traits\VerifiesUsers`

which includes:

`Jrean\UserVerification\Traits\RedirectsUsers`

### Endpoints

The two (2) following methods are included into the `VerifiesUsers` trait and
called by the default package routes.

* `getVerification(Request $request, $token)`

Handle the user verification.

* `getVerificationError()`

Do something if the verification fails.

### API

The package public API offers height (8) methods.

* `generate(AuthenticatableContract $user)`

Generate and save a verification token for the given user.

* `send(AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Send by e-mail a link containing the verification token.

* `sendQueue(AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Queue and send by e-mail a link containing the verification token.

* `sendQueueOn($queue, AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Queue on the given queue and send by e-mail a link containing the verification token.

* `sendLater($seconds, AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Send later by e-mail a link containing the verification token.

* `sendLaterOn($queue, $seconds, AuthenticatableContract $user, $subject = null, $from = null, $name = null)`

Send later on the given queue by e-mail a link containing the verification token.

* `process($email, $token, $userTable)`

Process the token verification for the given e-mail and token.

* `emailView($name)`

Set the e-mail view name.

For the `sendQueue`, `sendQueueOn`, `sendLater` and
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

* `$redirectIfVerificationFails = '/verification/error';`

Where to redirect after a failing token verification.

* `$verificationErrorView = 'errors.user-verification';`

Name of the view returned by the getVerificationError method.

* `$verificationEmailView = 'emails.user-verification';`

Name of the default e-mail view.

* `$userTable = 'users';`

Name of the default table used for managing users.

### Customize

You can customize the package behaviour by overriding/overwriting the
public methods and the attributes/properties. Dig into the source.

## Guidelines

**This package doesn't require the user to be authenticated to perform the
verification. You are free to implement any flow you may want to achieve.**

This package whishes to let you be creative while offering you a predefined
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

Edit the `app\Http\routes.php` file.

- Define two (2) new routes.

```
    Route::get('verification/error', 'Auth\AuthController@getVerificationError');
    Route::get('verification/{token}', 'Auth\AuthController@getVerification');
```

- Define the e-mail view.

Edit the `app\Http\Controllers\Auth\AuthController.php` file.

- [x] Import the `VerifiesUsers` trait (mandatory)
- [ ] Overwrite and customize the redirect attributes/properties paths
    available within the `RedirectsUsers` trait included by the
    `VerifiesUsers` trait. (not mandatory)
- [ ] Overwrite the default error view name used by the `getVerificationError()` method
    (not mandatory)
- [x] Create the verification error view at
    `resources/views/errors/user-verification.blade.php` (mandatory)
- [ ] Overwrite the contructor (not mandatory)
- [x] Overwrite the `postRegister()`/`register()` method depending on the
    Laravel version you use (mandatory)

```

    namespace App\Http\Controllers\Auth;

    use App\User;
    use Validator;
    use App\Http\Controllers\Controller;
    use Illuminate\Foundation\Auth\RegistersUsers;
    use Illuminate\Http\Request;

    use Jrean\UserVerification\Traits\VerifiesUsers;
    use Jrean\UserVerification\Facades\UserVerification;

    class AuthController extends Controller
    {
        /*
        |--------------------------------------------------------------------------
        | Registration & Login Controller
        |--------------------------------------------------------------------------
        |
        | This controller handles the registration of new users, as well as the
        | authentication of existing users. By default, this controller uses
        | a simple trait to add these behaviors. Why don't you explore it?
        |
        */

        use AuthenticatesAndRegistersUsers, ThrottlesLogins;

        use VerifiesUsers;

        /**
        * Create a new authentication controller instance.
        *
        * @return void
        */
        public function __construct()
        {
            // Based on the workflow you want you may update and customize the following lines.

            // Laravel 5.0.*|5.1.*
            $this->middleware('guest', ['except' => ['getLogout', 'getVerification', 'getVerificationError']]);

            // Laravel 5.2.*
            $this->middleware('guest', ['except' => ['logout', 'getVerification, 'getVerificationError']]);
            //or
            $this->middleware($this->guestMiddleware(), ['except' => ['logout', 'getVerification', 'getVerificationError']]);
        }

        // Laravel 5.0.*|5.1.*
        /**
        * Handle a registration request for the application.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        */
        public function postRegister(Request $request)
        {
            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            $user = $this->create($request->all());

            // Authenticating the user is not mandatory at all.
            Auth::login($user);

            UserVerification::generate($user);

            UserVerification::send($user, 'My Custom E-mail Subject');

            return redirect($this->redirectPath());
        }

        // Laravel 5.2.*
        /**
        * Handle a registration request for the application.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\Response
        */
        public function register(Request $request)
        {
            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            $user = $this->create($request->all());

            // Authenticating the user is not mandatory at all.

            // Laravel <= 5.2.7
            // Auth::login($user);

            // Laravel > 5.2.7
            Auth::guard($this->getGuard())->login($user);

            UserVerification::generate($user);

            UserVerification::send($user, 'My Custom E-mail Subject');

            return redirect($this->redirectPath());
        }
    }

```

At this point, after registration, an e-mail is sent to the user.
Click the link within the e-mail and the user will be verified against the
token.

If you want to perform the verification against an authenticated user you must
update the middleware exception to allow `getVerification` and
`getVerificationError` routes to be accessed.

```
$this->middleware($this->guestMiddleware(), ['except' => ['logout', 'getVerification', 'getVerificationError']]);
```

## Contribute

Feel free to comment, contribute and help. 1 PR = 1 feature.

## License

Laravel User Verification is licensed under [The MIT License (MIT)](LICENSE).
