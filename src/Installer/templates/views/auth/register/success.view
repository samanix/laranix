@extends('layout.app')

@section('pagetitle', 'Success')

@section('meta-keywords', 'registered, success, register')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">
        <h2 class="ui top attached header">
            Thanks, {{ $registered_username }}
        </h2>
        <div class="ui attached segment">
            <h4 class="ui header">You have registered successfully.</h4>
            <p>
                The next step is to verify your email, we've sent one to the email you provided at <b>{{ $registered_email }}</b>.
                You should receive this within the next 10 minutes.
                <br /><br />
                Once verified, you'll be able to login.
                <br /><br />
                Your code will be valid until {{ $token_expiry }} ({{ $token_valid_for }} from now).
            </p>
        </div>
        <div class="ui bottom attached warning message">
            <div class="ui link list">
                <a class="item" href="{{ $url->to('email/verify/refresh') }}">
                    <div class="content">Need a new verification code?</div>
                </a>
                <a class="item" href="{{ $url->to('contact') }}">
                    <div class="content">Entered the wrong email?</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
