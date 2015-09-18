<?php

namespace Jrean\UserVerification;

use Jrean\UserVerification\Facades\UserVerification;

trait VerifiesUsers
{
    /**
     * Where to reditect if the user is already verified.
     *
     * @var string
     */
    protected $redirectIfVerified = '/';

    /**
     * Where to redirect after a successful verification token generation.
     *
     * @var string
     */
    protected $redirectAfterTokenGeneration = '/';

    /**
     * Where to redirect after a successful verification token verification.
     *
     * @var string
     */
    protected $redirectAfterVerification = '/';

    /**
     *  Where to redirect after a failling verification token verification.
     *
     * @var string
     */
    protected $redirectIfVerificationFails = '/auth/verification/error';

    /**
     * Name of the view returned by the getVerificationError method.
     *
     * @var mixed
     */
    protected $verificationErrorView = '';

    /**
     * Handle the verification token generation.
     *
     * @return Response
     */
    public function getVerificationToken()
    {
        $user = auth()->user();

        UserVerification::generate($user);

        UserVerification::send($user);

        return redirect($this->redirectAfterTokenGeneration);
    }

    /**
     * Handle the user verification.
     *
     * @param  string $token
     * @return Response
     */
    public function getVerification($token)
    {
        $user = auth()->user();

        if (UserVerification::isVerified($user)) {
            return redirect($this->redirectIfVerified);
        }

        if ( ! UserVerification::process($user, $token)) {
            return redirect($this->redirectIfVerificationFails);
        }

        return redirect($this->redirectAfterVerification);
    }

    /**
     * Do something if the verification fails.
     *
     * @return Response
     */
    public function getVerificationError()
    {
        $user = auth()->user();

        if ( ! UserVerification::isVerified($user)) {
            return view($this->verificationErrorView);
        }

        return redirect($this->redirectIfVerified);
    }
}
