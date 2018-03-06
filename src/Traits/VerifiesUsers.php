<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jrean\UserVerification\Exceptions\TokenMismatchException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Facades\UserVerification as UserVerificationFacade;

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
        if (!$this->validateRequest($request)) {
            return redirect($this->redirectIfVerificationFails());
        }

        try {
            $user = UserVerificationFacade::process(
                $request->input('email'), $token, $this->userTable(), $this->mustUpdate()
            );
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
     * Method for showing a view after the verification process.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAfterVerificationView()
    {
        try {
            $user = $this->getVerifiedUser();
        } catch (UserNotFoundException $error) {
            return redirect('/');
        }
        $header = trans(
            'laravel-user-verification::user-verification.verification_verification_header',
            ['name' => $user->{$this->userName()}]
        );
        $message = trans(
            'laravel-user-verification::user-verification.verification_verification_message'
        );
        $back_button = trans(
            'laravel-user-verification::user-verification.verification_verification_back_button'
        );
        return view(
            $this->userVerificationView(),
            compact('header', 'message', 'back_button')
        );
    }

    /**
     * Method for showing a view when a user is already verified.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIsVerifiedView()
    {
        try {
            $user = $this->getVerifiedUser();
        } catch (UserNotFoundException $error) {
            return redirect('/');
        }
        $header = trans(
            'laravel-user-verification::user-verification.verification_verified_header',
            ['name' => $user->{$this->userName()}]
        );
        $message = trans(
            'laravel-user-verification::user-verification.verification_verified_message'
        );
        $back_button = trans(
            'laravel-user-verification::user-verification.verification_verified_back_button'
        );
        return view(
            $this->userVerificationView(),
            compact('header', 'message', 'back_button')
        );
    }

    /**
     * Returns the verified user, coming from the verification.
     * @return stdClass
     */
    protected function getVerifiedUser()
    {
        $previousQueryParams = [];
        // When you redirect, you can get the previous url.
        // With it, you can obtain the email in the query string of the url.
        parse_str(
            parse_url(url()->previous())['query'],
            $previousQueryParams
        );
        $user = UserVerificationFacade::getUserByEmail(
            $previousQueryParams['email'], $this->userTable
        );
        return $user;
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
            'email' => 'required|email',
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
     * Get the default verification view for error, verified and after verification.
     *
     * @return string
     */
    protected function userVerificationView()
    {
        return property_exists($this, 'userVerificationView')
        ? $this->userVerificationView
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

    /**
     * Get the user name.
     *
     * @return string
     */
    protected function userName()
    {
        return property_exists($this, 'userName') ? $this->userName : 'name';
    }

    /**
     * Get the fields that should also be updated when a user is verified.
     *
     * @return array
     */
    protected function mustUpdate()
    {
        return property_exists($this, 'mustUpdate') ? $this->mustUpdate : [];
    }
}
