@extends('layout.app')

@section('pagetitle', 'Register')

@section('meta-keywords', 'register')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">

        @include('layout.formerrors')

        <div class="ui icon attached message">
            <i class="users icon"></i>
            <div class="content">
                <div class="header">
                    Register
                </div>
                Register a new account with us.
            </div>
        </div>
        <form class="ui form attached segment" method="post" action="{{ $url->to('register') }}" id="register-form" autocomplete="off">

            {!! csrf_field() !!}
            {!! $sequence->render() !!}

            <div class="ui dividing first section header">About You</div>

            <div class="field">
                <label>First Name</label>
                <div class="ui left icon input">
                    <i class="user icon"></i>
                    <input type="text" name="first_name" placeholder="First name" value="{{ old('first_name') }}" maxlength="64" required>
                </div>
            </div>

            <div class="field">
                <label>Last Name</label>
                <div class="ui left icon input">
                    <i class="user icon"></i>
                    <input type="text" name="last_name" placeholder="Last name" value="{{ old('last_name') }}" maxlength="64" required>
                </div>
            </div>

            <div class="field">
                <label>Email</label>
                <div class="description">
                    If your email is already registered, please <a href="{{ $url->to('password/reset') }}">reset your password</a> or <a href="{{ $url->to('contact') }}">contact us</a>.
                </div>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email" placeholder="your@email.com" value="{{ old('email') }}" maxlength="255" required>
                </div>
            </div>

            <div class="field">
                <div class="description">
                    Confirm your email address.
                </div>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email_confirmation" placeholder="your@email.com" value="{{ old('email_confirmation') }}" maxlength="255" required>
                </div>
            </div>

            <div class="field">
                <label>Company (optional)</label>
                <div class="description">
                    Enter an optional company name.
                </div>
                <div class="ui left icon input">
                    <i class="travel icon"></i>
                    <input type="text" name="company" placeholder="Company Ltd." value="{{ old('company') }}" maxlength="64">
                </div>
            </div>

            <div class="ui dividing section header">Your Account</div>

            <div class="field">
                <label>Username</label>
                <div class="description">
                    Your username must be unique, contain between 3 and 64 characters using only alphanumeric, underscores, and dashes.
                </div>
                <div class="ui left icon input">
                    <i class="user icon"></i>
                    <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" minlength="3" maxlength="64" required>
                </div>
            </div>

            <div class="field">
                <label>Password</label>
                <div class="description">
                    Choose a secure, yet easy to remember password.
                    <br />
                    The only requirement is that your password is at least 6 characters in length.
                </div>
                <div class="ui left icon input">
                    <i class="privacy icon"></i>
                    <input type="password" name="password" placeholder="Password" minlength="6" required>
                </div>
            </div>

            <div class="field">
                <div class="description">
                    Confirm your chosen password.
                </div>
                <div class="ui left icon input">
                    <i class="privacy icon"></i>
                    <input type="password" name="password_confirmation" placeholder="Confirm Password" minlength="6" required>
                </div>
            </div>

            <div class="top spaced field">
                <div class="ui center aligned container">
                    <div class="ui checkbox">
                        <input title="Terms" type="checkbox" name="terms" tabindex="0" id="terms" {{ old('terms', false) ? 'checked' : ''}}>
                        <label>I have read and accept the <a href="{{ $url->to('terms') }}" target="_blank">terms and conditions</a></label>
                    </div>
                </div>
            </div>

            {!! $recaptcha->render() !!}

            <div class="ui center aligned container">
                <button class="large ui button" type="submit" tabindex="0" id="submit-register-form">
                    <i class="add user icon"></i> Register
                </button>
            </div>
        </form>

        <div class="ui bottom attached warning message">
            <i class="info icon"></i>
            We will never share your details with anyone.
        </div>
    </div>
</div>
@endsection
