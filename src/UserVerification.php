<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jrean\UserVerification\Exceptions\ModelNotCompliantException;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;
use Jrean\UserVerification\Exceptions\TokenMismatchException;

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
     * E-mail view name.
     *
     * @var string
     */
    protected $emailView;

    /**
     * Create a new instance.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  \Illuminate\Database\Schema\Builder  $schema
     * @return void
     */
    public function __construct(MailerContract $mailer, Builder $schema)
    {
        $this->mailer    = $mailer;
        $this->schema    = $schema;
        $this->emailView = 'emails.user-verification';
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
        if (! $this->isCompliant($user)) {
            throw new ModelNotCompliantException();
        }

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
     * @return bool
     *
     * @throws \Jrean\UserVerification\Exceptions\ModelNotCompliantException
     */
    public function send(AuthenticatableContract $user, $subject = null, $from = null, $name = null)
    {
        if (! $this->isCompliant($user)) {
            throw new ModelNotCompliantException();
        }

        return (boolean) $this->emailVerificationLink($user, $subject, $from, $name);
    }

    /**
     * Set the e-mail view name.
     *
     * @param  mixed  $name
     * @return \Jrean\UserVerification
     */
    public function emailView($name)
    {
        $this->emailView = $name;

        return $this;
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
        $user = $this->getUserByEmail($email, $userTable);

        $this->isVerified($user);

        $this->verifyToken($user->verification_token, $token);

        $this->wasVerified($user);
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
            ->first(['id', 'email', 'verified', 'verification_token']);

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
     * @param  string  $from
     * @param  string  $name
     * @return mixed
     */
    protected function emailVerificationLink(AuthenticatableContract $user, $subject, $from = null, $name = null)
    {
        return $this->mailer->send($this->emailView, compact('user'), function ($m) use ($user, $subject, $from, $name) {
            if (! empty($from)) {
                $m->from($from, $name);
            }

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
        return $this->hasColumn($user, 'verified')
            && $this->hasColumn($user, 'verification_token') ?
            true : false;
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
