@extends('layout.app')

@section('pagetitle', 'Password Reset')

@section('meta-keywords', 'reset, password')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">

        @include('layout.formerrors')

        <div class="ui icon attached message">
            <i class="undo icon"></i>
            <div class="content">
                <div class="header">
                    Password Reset
                </div>
                Enter your new password. For additional security, please also re-enter your email.
            </div>
        </div>

        <form class="ui form attached segment" method="post" action="{{ $url->to('password/reset') }}" id="pass-reset-form" autocomplete="off">

            {!! csrf_field() !!}
            {!! $sequence->render() !!}

            <div class="field">
                <label>Token</label>
                <div class="description">
                    Enter the token sent to you.
                </div>
                <div class="ui left icon input">
                    <i class="tag icon"></i>
                    <input type="text" name="token" placeholder="Token" value="{{ $token }}" maxlength="64" minlength="64" required>
                </div>
            </div>

            <div class="field">
                <label>Email</label>
                <div class="description">
                    Enter the email you have active on your account.
                </div>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email" placeholder="my@email.com" value="{{ $email }}" maxlength="255" required>
                </div>
            </div>

            <div class="field">
                <label>New Password</label>
                <div class="description">
                    Choose a secure, yet easy to remember password.
                    <br />
                    The only requirement is that your password is at least 6 characters in length.
                </div>
                <div class="ui left icon input">
                    <i class="privacy icon"></i>
                    <input type="password" name="password" placeholder="New Password" minlength="6" required>
                </div>
            </div>

            <div class="field">
                <div class="description">
                    Confirm your new password.
                </div>
                <div class="ui left icon input">
                    <i class="privacy icon"></i>
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" minlength="6" required>
                </div>
            </div>

            {!! $recaptcha->render() !!}

            <div class="ui center aligned container">
                <button class="large ui button" type="submit" tabindex="0" id="submit-pass-reset-form">
                    <i class="undo icon"></i> Reset Password
                </button>
            </div>
        </form>

        <div class="ui bottom attached warning message">
            <div class="ui link list">
                <a class="item" href="{{ $url->to('password/forgot') }}">
                    <div class="content">Need a new reset token?</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
