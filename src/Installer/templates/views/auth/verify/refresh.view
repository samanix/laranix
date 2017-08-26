@extends('layout.app')

@section('pagetitle', 'Resend Verification Email')

@section('meta-keywords', 'verify, email, resend')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">

        @isset($verify_refresh_message))
            <div class="ui visible success message">
                {{ $verify_refresh_message }}
            </div>
        @endisset

        @include('layout.formerrors')

        <div class="ui icon attached message">
            <i class="mail icon"></i>
            <div class="content">
                <div class="header">
                    New Verification Email
                </div>
                Request a new verification code for your email. Remember to check spam and junk folders.
                <br /><br />
                If you think you might have entered the wrong email, please <a href="{{ $url->to('contact') }}">contact us</a>.
            </div>
        </div>

        <form class="ui form attached segment" method="post" action="{{ $url->to('email/verify/refresh') }}" id="verify-email-refresh-form" autocomplete="off">

            {!! csrf_field() !!}
            {!! $sequence->render() !!}

            <div class="field">
                <label>Email</label>
                <div class="description">
                    Enter the email you want to verify.
                </div>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email" placeholder="my@email.com" value="{{ old('email') }}" maxlength="255" required>
                </div>
            </div>

            {!! $recaptcha->render() !!}

            <div class="ui center aligned container">
                <button class="large ui button" type="submit" tabindex="0" id="submit-verify-email-refresh-form">
                    <i class="send icon"></i> Resend Verification Email
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
