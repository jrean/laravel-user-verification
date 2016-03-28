<?php

namespace Jrean\UserVerification;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jrean\UserVerification\Exceptions\ModelNotCompliantException;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;

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
     * Constructor.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  \Illuminate\Database\Schema\Builder  $schema
     * @return void
     */
    public function __construct(MailerContract $mailer, Builder $schema)
    {
        $this->mailer = $mailer;
        $this->schema = $schema;
    }

    /**
     * Generate and save a verification token for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return bool
     */
    public function generate(AuthenticatableContract $user)
    {
        return $this->saveToken($user, $this->generateToken());
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
        if (! $this->isCompliant($user)) {
            throw new ModelNotCompliantException();
        }

        $user->verified = false;

        $user->verification_token = $token;

        return $user->save();
    }

    /**
     * Generate the verification token.
     *
     * @return string
     */
    protected function generateToken()
    {
        return hash_hmac('sha256', Str::random(40), config('app.key'));
    }

    /**
     * Send by e-mail a link containing the verification token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @return bool
     */
    public function send(AuthenticatableContract $user, $subject = null)
    {
        if (! $this->isCompliant($user)) {
            throw new VerificationException();
        }

        return (boolean) $this->emailVerificationLink($user, $subject);
    }

    /**
     * Process the user verification for the given e-mail and token.
     *
     * @param  string  $email
     * @param  string  $token
     * @param  string  $userTable
     * @return void
     */
    public function process($email, $token, $userTable)
    {
        $user = $this->getUser($email, $userTable);

        $this->isVerified($user);

        $this->verifyToken($user->verification_token, $token);

        $this->wasVerified($user);
    }

    /**
     * Get the user instance.
     *
     * @param  string  $email
     * @param  string  $table
     * @return stdClass
     */
    protected function getUser($email, $table)
    {
        return $this->getUserByEmail($email, $table);
    }

    /**
     * Fetch the user by e-mail.
     *
     * @param  string  $email
     * @param  string  $table
     * @return stdClass
     *
     * @throws \Jrean\UserVerification\Exceptions\UserNotFoundException
     */
    protected function getUserByEmail($email, $table)
    {
        $user = DB::table($table)->where('email', $email)->first(['id', 'email', 'verified', 'verification_token']);

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

        $this->updateUser($user);
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
                'verified' => $user->verified
            ]);
    }

    /**
     * Prepare and send the e-mail with the verification token link.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $subject
     * @return mixed
     */
    protected function emailVerificationLink(AuthenticatableContract $user, $subject)
    {
        return $this->mailer->send('emails.user-verification', compact('user'), function ($m) use ($user, $subject) {
            $m->to($user->email);

            $m->subject(is_null($subject) ? 'Your Account Verification Link' : $subject);
        });
    }

    /**
     * Determine if the given model table has the verified and verification_token
     * columns.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return  bool
     */
    protected function isCompliant(AuthenticatableContract $user)
    {
        if (
            $this->hasColumn($user, 'verified')
            && $this->hasColumn($user, 'verification_token')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if the given model talbe has the given column.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $column
     * @return bool
     */
    protected function hasColumn(AuthenticatableContract $user, $column)
    {
        return $this->schema->hasColumn($user->getTable(), $column);
    }
}
