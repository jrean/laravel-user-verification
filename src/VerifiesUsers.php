<?php

namespace Jrean\UserVerification;

use Jrean\UserVerification\Facades\UserVerification;

trait VerifiesUsers
{
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

        return redirect($this->redirectAfterTokenGeneration());
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
            return redirect($this->redirectIfVerified());
        }

        if ( ! UserVerification::process($user, $token)) {
            return redirect($this->redirectIfVerificationFails());
        }

        return redirect($this->redirectAfterVerification());
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
            return view($this->verificationErrorView());
        }

        return redirect($this->redirectIfVerified());
    }

    /**
     * Where to reditect if the user is already verified.
     *
     * @return string
     */
    public function redirectIfVerified()
    {
        return property_exists($this, 'redirectIfVerified') ? $this->redirectIfVerified : '/';
    }

    /**
     * Where to redirect after a successful verification token generation.
     *
     * @return string
     */
    public function redirectAfterTokenGeneration()
    {
        return property_exists($this, 'redirectAfterTokenGeneration') ? $this->redirectAfterTokenGeneration : '/';
    }

    /**
     * Where to redirect after a successful verification token verification.
     *
     * @return string
     */
    public function redirectAfterVerification()
    {
        return property_exists($this, 'redirectAfterVerification') ? $this->redirectAfterVerification : '/';
    }

    /**
     * Where to redirect after a failling verification token verification.
     *
     * @return string
     */
    public function redirectIfVerificationFails()
    {
        return property_exists($this, 'redirectIfVerificationFails') ? $this->redirectIfVerificationFails : '/auth/verification/error';
    }

    /**
     * Name of the view returned by the getVerificationError method.
     *
     * @return string
     */
    public function verificationErrorView()
    {
        return property_exists($this, 'verificationErrorView') ? $this->verificationErrorView : 'errors.user-verification';
    }
}
