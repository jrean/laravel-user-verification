<?php
/**
 * This file is part of Jrean\UserVerification package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\UserVerification\Middleware;

use Closure;
use Jrean\UserVerification\Exceptions\TokenExpiredException;

class ChecksExpiredVerificationTokens
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws TokenExpiredException
     */
    public function handle($request, Closure $next, $redirect)
    {
        if ($request->confirmation_token->hasExpired()) {
            throw new TokenExpiredException;
        }

        return $next($request);
    }
}
