Click here to verify your account: <a href="{{ $link = route('email-verification.check', $user->confirmationToken->token) . '?email=' . urlencode($user->email) }}">{{ $link }}</a>
