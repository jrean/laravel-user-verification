@extends('layouts.app')

<!-- Main Content -->
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {!! ($header) ?: trans('laravel-user-verification::user-verification.verification_error_header') !!}</div>
                <div class="panel-body">
                    <span class="help-block">
                        <strong>
                            {!! ($message) ?: trans('laravel-user-verification::user-verification.verification_error_message') !!}
                        </strong>
                    </span>
                    <div class="form-group">
                        <div class="col-md-12">
                            <a href="{{url('/')}}" class="btn btn-primary">
                                {!! ($back_button) ?: trans('laravel-user-verification::user-verification.verification_error_back_button') !!}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
