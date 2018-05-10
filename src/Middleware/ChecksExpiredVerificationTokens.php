<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Middleware;

use Closure;

class ChecksExpiredVerificationTokens
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $redirect)
    {
        if ($request->confirmation_token->hasExpired()) {
            return redirect($redirect)->withError('Token expired.');
        }

        return $next($request);
    }
}
