<?php

namespace Jrean\UserVerification\Controllers;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jrean\UserVerification\Facades\UserVerification;
use Jrean\UserVerification\Exceptions\ModelNotCompliantException;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;
use Jrean\UserVerification\Exceptions\TokenMismatchException;
use Jrean\UserVerification\Traits\RedirectsUsers;

class UserVerificationController extends Controller
{
    use RedirectsUsers;
    /**
     * Handle the user verification.
     *
     * @param  string  $token
     * @return Response
     */
    public function getVerification(Request $request, $token)
    {
        $this->validateRequest($request);

        try {
            UserVerification::process($request->input('email'), $token, $this->userTable());
        } catch (UserNotFoundException $e) {
            return redirect($this->redirectIfVerificationFails());
        } catch (UserIsVerifiedException $e) {
            return redirect($this->redirectIfVerified());
        } catch (TokenMismatchException $e) {
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
     * Sends a verification request
     *
     * @return void
     */
    public function postVerification(Request $request)
    {
        try{
            $user_id = $request->get('user_id');
            // get the user model from auth settings or fall back to App\User
            $user_model = config('auth.providers.users.model', App\User::class);
            // retrieve user
            $user = $user_model::findOrFail($user_id);
        }
        catch(\Exception $e){
            \Log::error("laravel-user-verification error: Trying to send verification for non-existant user: $user_id.");
            return back()->with(['error' => trans('verification_resend_error')]);
        }
        // generate token & send
        UserVerification::generate($user);
        UserVerification::send($user);

        return back();
    }

    /**
     * Validate the verification link.
     *
     * @param  string  $token
     * @return Response
     */
    protected function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return redirect($this->redirectIfVerificationFails());
        }
    }

    /**
     * Get the verification error view name.
     *
     * @return string
     */
    protected function verificationErrorView()
    {
        return property_exists($this, 'verificationErrorView') ? $this->verificationErrorView : 'laravel-user-verification::user-verification';
    }

    /**
     * Get the verification e-mail view name.
     *
     * @return string
     */
    protected function verificationEmailView()
    {
        return property_exists($this, 'verificationEmailView') ? $this->verificationEmailView : 'emails.user-verification';
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
