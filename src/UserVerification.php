<?php

namespace Jrean\UserVerification;

use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Database\Schema\Builder;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Jrean\UserVerification\VerificationException;

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
     * @var \Illuminate\Database\Schema\Builder $schema
     */
    protected $schema;

    /**
     * Constructor.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer $mailer
     * @param  \Illuminate\Database\Schema\Builder $schema
     *
     * @return void
     */
    public function __construct(MailerContract $mailer, Builder $schema)
    {
        $this->mailer = $mailer;

        $this->schema = $schema;
    }

    /**
     * Generate and save a verification token the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @return boolean
     */
    public function generate(AuthenticatableContract $model)
    {
        return $this->saveToken($model, $this->generateToken());
    }

    /**
     * Send by email a link containing the verification token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @returnt boolean
     */
    public function send(AuthenticatableContract $model)
    {
        if ( ! $this->isCompliant($model)) {
            throw new VerificationException;
        }

        return (boolean) $this->emailVerificationLink($model);
    }

    /**
     * Process the token verification.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @param  string $token
     * @return boolean
     */
    public function process(AuthenticatableContract $model, $token)
    {
        if ( ! $this->compareToken($model->verification_token, $token)) {
            return false;
        }

        $this->wasVerified($model);

        return true;
    }

    /**
     * Check if the user is verified.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @return boolean
     */
    public function isVerified(AuthenticatableContract $model)
    {
        return $model->verified == true;
    }

    /**
     * Update and save the model instance has verified.
     *
     * @param AuthenticatableContract $model
     * @return void
     */
    protected function wasVerified(AuthenticatableContract $model)
    {
        $model->verification_token = null;

        $model->verified = true;

        $model->save();
    }

    /**
     * Compare the verification token given by the user with the one stored.
     *
     * @param  string $storedToken
     * @param  string $requestToken
     * @return boolean
     */
    protected function compareToken($storedToken, $requestToken)
    {
        return $storedToken == $requestToken;
    }

    /**
     * Prepare and send the email with the verification token link.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @param  string $token
     * @return mixed
     */
    protected function emailVerificationLink(AuthenticatableContract $model)
    {
        return $this->mailer->send('emails.user-verification', compact('model'), function ($m) use ($model) {
            $m->to($model->email)->subject('Confirm your email address');
        });
    }

    /**
     * Generate the verification token.
     *
     * @return string
     */
    protected function generateToken()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * Update and save the model instance with the verification token.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @param  string $token
     * @return boolean
     * @throws \Jrean\UserVerification\Exceptions\VerificationException
     */
    protected function saveToken(AuthenticatableContract $model, $token)
    {
        if ( ! $this->isCompliant($model)) {
            throw new VerificationException;
        }

        $model->verification_token = $token;

        return $model->save();
    }

    /**
     * Determine if the given model table has the verified and verification_token
     * columns.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @return boolean
     */
    protected function isCompliant(AuthenticatableContract $model)
    {
        if (
            $this->hasColumn($model, 'verified')
            && $this->hasColumn($model, 'verification_token')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if the given model talbe has the given column.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $model
     * @param  string $column
     * @return boolean
     */
    protected function hasColumn(AuthenticatableContract $model, $column)
    {
        return $this->schema->hasColumn($model->getTable(), $column);
    }
}
