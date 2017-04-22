<?php

namespace Jrean\UserVerification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerificationTokenGenerated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * User instance.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * The subject of the message.
     *
     * @var string
     */
    public $subject;

    /**
     * The person the message is from.
     *
     * @var mixed
     */
    public $from;

    /**
     * The person name the message is from.
     *
     * @var mixed
     */
    public $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        AuthenticatableContract $user,
        $subject = null,
        $from = null,
        $name = null
    )
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->from = $from;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (! empty($this->from)) {
            $this->from($this->from, $this->name);
        }

        $this->subject(is_null($this->subject)
            ? trans('laravel-user-verification::user-verification.verification_email_subject')
            : $this->subject);

        if (config('user-verification.email.type') == 'markdown') {
            is_null($view = config('user-verification.email.view'))
                ? $this->markdown('laravel-user-verification::email-markdown')
                : $this->markdown($view);
        } else {
            is_null($view = config('user-verification.email.view'))
                ? $this->view('laravel-user-verification::email')
                : $this->view($view);
        }

        return $this;
    }
}
