<?php

namespace Jrean\UserVerification\Traits;

use Jrean\UserVerification\Facades\UserVerification;

trait VerifiesUsers
{
    use RedirectsUsers;

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

        if (!UserVerification::process($user, $token)) {
            return redirect($this->redirectIfVerificationFails());
        }

        return redirect($this->redirectAfterVerification());
    }

    /**
     * Show the verification error view.
     *
     * @return Response
     */
    public function getVerificationError()
    {
        if (UserVerification::isVerified(auth()->user())) {
            return redirect($this->redirectIfVerified());
        }

        return view($this->verificationErrorView());
    }

    /**
     * Get the verification error view name.
     *
     * @return string
     */
    public function verificationErrorView()
    {
        return property_exists($this, 'verificationErrorView') ? $this->verificationErrorView : 'errors.user-verification';
    }
}
