<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Schema\Builder;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jrean\UserVerification\Events\UserVerified;
use Jrean\UserVerification\Events\VerificationEmailSent;
use Jrean\UserVerification\Exceptions\ModelNotCompliantException;
use Jrean\UserVerification\Exceptions\TokenMismatchException;
use Jrean\UserVerification\Exceptions\UserHasNoEmailException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Mail\VerificationTokenGenerated;

class UserVerification
{
    /**
     * Mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Schema builder instance.
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    /**
     * Create a new instance.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  \Illuminate\Database\Schema\Builder  $schema
     * @return void
     */
    public function __construct(Mailer $mailer, Builder $schema)
    {
        $this->mailer = $mailer;
        $this->schema = $schema;

        if (! $this->isCompliant()) {
            throw new ModelNotCompliantException();
        }
    }

    /**
     * Generate and save a verification token for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return bool
     */
    public function generate(AuthenticatableContract $user)
    {
        if (empty($user->email)) {
            throw new UserHasNoEmailException();
        }

        return $this->saveToken($user, $this->generateToken());
    }

    /**
     * Generate the verification token.
     *
     * @return string|bool
     */
    protected function generateToken()
    {
        return hash_hmac('sha256', Str::random(40), config('app.key'));
    }

    /**
     * Update and save the model instance with the verification token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return bool
     *
     * @throws \Jrean\UserVerification\Exceptions\ModelNotCompliantException
     */
    protected function saveToken(AuthenticatableContract $user, $token)
    {
        $user->verified = false;

        $user->verification_token = $token;

        return $user->save();
    }

    /**
     * Send by e-mail a link containing the verification token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $name
     * @return void
     *
     * @throws \Jrean\UserVerification\Exceptions\ModelNotCompliantException
     */
    public function send(
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        $this->emailVerificationLink($user, $subject, $from, $name);

        event(new VerificationEmailSent($user));
    }

    /**
     * Queue and send by e-mail a link containing the verification token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $name
     * @return void
     *
     * @throws \Jrean\UserVerification\Exceptions\ModelNotCompliantException
     */
    public function sendQueue(
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        $this->emailQueueVerificationLink($user, $subject, $from, $name);

        event(new VerificationEmailSent($user));
    }

    /**
     * Send later by e-mail a link containing the verification token.
     *
     * @param  \DateTime  $delay
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $name
     * @return void
     *
     * @throws \Jrean\UserVerification\Exceptions\ModelNotCompliantException
     */
    public function sendLater(
        $delay,
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        $this->emailLaterVerificationLink($delay, $user, $subject, $from, $name);

        event(new VerificationEmailSent($user));
    }

    /**
     * Prepare and send the e-mail with the verification token link.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $name
     * @return mixed
     */
    protected function emailVerificationLink(
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        return $this->mailer
            ->to($user)
            ->send(new VerificationTokenGenerated($user, $subject, $from, $name));
    }

    /**
     * Prepare and push a job onto the queue to send the e-mail with the verification token link.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $name
     * @return mixed
     */
    protected function emailQueueVerificationLink(
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        return $this->mailer
            ->to($user)
            ->queue(new VerificationTokenGenerated($user, $subject, $from, $name));
    }

    /**
     * Prepare and delay the sending of the e-mail with the verification token link.
     *
     * @param  \DateTime  $delay
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $name
     * @return mixed
     */
    protected function emailLaterVerificationLink(
        $delay,
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        return $this->mailer
            ->to($user)
            ->later($delay, new VerificationTokenGenerated($user, $subject, $from, $name));
    }

    /**
     * Process the user verification for the given e-mail and token.
     *
     * @param  string  $email
     * @param  string  $token
     * @param  string  $userTable
     * @return stdClass
     */
    public function process($email, $token, $userTable)
    {
        $user = $this->getUserByEmail($email, $userTable);

        unset($user->{"password"});

        // Check if the given user is already verified.
        // If he is, we stop here.
        $this->isVerified($user);

        $this->verifyToken($user->verification_token, $token);

        $this->wasVerified($user);

        return $user;
    }

    /**
     * Get the user by e-mail.
     *
     * @param  string  $email
     * @param  string  $table
     * @return stdClass
     *
     * @throws \Jrean\UserVerification\Exceptions\UserNotFoundException
     */
    protected function getUserByEmail($email, $table)
    {
        $user = DB::table($table)
            ->where('email', $email)
            ->first();

        if ($user === null) {
            throw new UserNotFoundException();
        }

        $user->table = $table;

        return $user;
    }

    /**
     * Check if the given user is verified.
     *
     * @param  stdClass  $user
     * @return void
     *
     * @throws \Jrean\UserVerification\Exceptions\UserIsVerifiedException
     */
    protected function isVerified($user)
    {
        if ($user->verified == true) {
            throw new UserIsVerifiedException();
        }
    }

    /**
     * Compare the two given tokens.
     *
     * @param  string  $storedToken
     * @param  string  $requestToken
     * @return void
     *
     * @throws \Jrean\UserVerification\Exceptions\TokenMismatchException
     */
    protected function verifyToken($storedToken, $requestToken)
    {
        if ($storedToken != $requestToken) {
            throw new TokenMismatchException();
        }
    }

    /**
     * Update and save the given user as verified.
     *
     * @param  stdClass  $user
     * @return void
     */
    protected function wasVerified($user)
    {
        $user->verification_token = null;

        $user->verified = true;

        $user->verified_at = now();

        $this->updateUser($user);

        event(new UserVerified($user));
    }

    /**
     * Update and save user object.
     *
     * @param  stdClass  $user
     * @return void
     */
    protected function updateUser($user)
    {
        DB::table($user->table)
            ->where('email', $user->email)
            ->update([
                'verification_token' => $user->verification_token,
                'verified' => $user->verified,
                'email_verified_at' => $user->verified_at,
            ]);
    }

    /**
     * Determine if the given model table has the verified and verification_token
     * columns.
     *
     * @return  bool
     */
    protected function isCompliant()
    {
        $user = config('auth.providers.users.model', App\User::class);

        return $this->schema->hasColumns((new $user())->getTable(), ['verified', 'verification_token'])? true : false;
    }
}
