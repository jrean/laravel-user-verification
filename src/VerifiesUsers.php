<?php

namespace Jrean\UserVerification;

use Jrean\UserVerification\Facades\UserVerification;

trait VerifiesUsers
{
    protected $redirectIfVerified = '/';

    protected $redirectAfterTokenGeneration = '/';

    protected $redirectAfterVerification = '/';

    protected $redirectIfVerificationFails = '/auth/verification/error';


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
            //
        }

        return redirect($this->redirectIfVerified);
    }
}
