@extends('layouts.app')

<!-- Main Content -->
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{!! trans('laravel-user-verification::user-verification.token_expired_error_header') !!}</div>
                <div class="panel-body">
                    <span class="help-block">
                        <strong>{!! trans('laravel-user-verification::user-verification.token_expired_generate_new_token') !!}</strong>
                    </span>
                    <div class="form-group">
                        <div class="col-md-12">
                            <form method="POST" action="{{ route('email-verification.resend-token', ['email' => $email, 'token' => $token]) }}">
                                @csrf
                                <input type="submit" class="btn btn-inverse" value="{!! trans('laravel-user-verification::user-verification.token_expired_generate_new_token_action_text') !!}" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
