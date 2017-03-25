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
        $subject,
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

        $this->subject($this->subject);

        if (config('user-verification.email.type') == 'markdown') {
            $this->markdown('laravel-user-verification::email-markdown');
        } else {
            $this->view('laravel-user-verification::email');
        }

        return $this;
    }
}
