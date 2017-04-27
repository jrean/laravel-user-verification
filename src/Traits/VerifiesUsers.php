<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jrean\UserVerification\Facades\UserVerification as UserVerificationFacade;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;
use Jrean\UserVerification\Exceptions\TokenMismatchException;

trait VerifiesUsers
{
    use RedirectsUsers;

    /**
     * Handle the user verification.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function getVerification(Request $request, $token)
    {
        if (! $this->validateRequest($request)) {
            return redirect($this->redirectIfVerificationFails());
        }

        try {
            $user = UserVerificationFacade::process($request->input('email'), $token, $this->userTable());
        } catch (UserNotFoundException $e) {
            return redirect($this->redirectIfVerificationFails());
        } catch (UserIsVerifiedException $e) {
            return redirect($this->redirectIfVerified());
        } catch (TokenMismatchException $e) {
            return redirect($this->redirectIfVerificationFails());
        }

        if (config('user-verification.auto-login') === true) {
            auth()->loginUsingId($user->id);
        }

        return redirect($this->redirectAfterVerification());
    }

    /**
     * Show the verification error view.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVerificationError()
    {
        return view($this->verificationErrorView());
    }

    /**
     * Validate the verification link.
     *
     * @param  string  $token
     * @return bool
     */
    protected function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        return $validator->passes();
    }

    /**
     * Get the verification error view name.
     *
     * @return string
     */
    protected function verificationErrorView()
    {
        return property_exists($this, 'verificationErrorView')
            ? $this->verificationErrorView
            : 'laravel-user-verification::user-verification';
    }

    /**
     * Get the verification e-mail view name.
     *
     * @return string
     */
    protected function verificationEmailView()
    {
        return property_exists($this, 'verificationEmailView')
            ? $this->verificationEmailView
            : 'emails.user-verification';
    }

    /**
     * Get the user table name.
     *
     * @return string
     */
    protected function userTable()
    {
        return property_exists($this, 'userTable') ? $this->userTable : 'users';
    }
}
