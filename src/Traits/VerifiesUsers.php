<?php

namespace Jrean\UserVerification\Traits;

use Jrean\UserVerification\Facades\UserVerification;

trait VerifiesUsers
{
    use RedirectsUsers;

    /**
     * Handle the user verification.
     *
     * @param  string  $token
     * @return Response
     */
    public function getVerification($token)
    {
        $user = UserVerification::getUser($token, $this->userTable());

        if (UserVerification::isVerified($user)) {
            return redirect($this->redirectIfVerified());
        }

        if (! UserVerification::process($user, $token)) {
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

    /**
     * Get the user table name.
     *
     * @return string
     */
    public function userTable()
    {
        return property_exists($this, 'userTable') ? $this->userTable : 'users';
    }
}
