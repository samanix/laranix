@extends('layout.app')

@section('pagetitle', 'Login')

@section('meta-keywords', 'login')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">

        @include('layout.formerrors')

        <div class="ui icon attached message">
            <i class="user circle icon"></i>
            <div class="content">
                <div class="header">
                    Login
                </div>
                Use your email and password to sign in.
            </div>
        </div>

        <form class="ui form attached segment" method="post" action="{{ $url->to('login') }}" id="login-form">

            {!! csrf_field() !!}
            {!! $sequence->render() !!}

            <div class="field">
                <label>Email</label>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email" placeholder="my@email.com" value="{{ old('email') }}" maxlength="255" required>
                </div>
            </div>

            <div class="field">
                <label>Password</label>
                <div class="ui left icon input">
                    <i class="privacy icon"></i>
                    <input type="password" name="password" placeholder="Password" minlength="6" required>
                </div>
            </div>

            <div class="field">
                <div class="ui center aligned container">
                    <div class="ui checkbox">
                        <input type="checkbox" name="remember" tabindex="0" id="remember-me" {{ old('remember', false) ? 'checked' : ''}}>
                        <label for="remember-me">Remember me</label>
                    </div>
                </div>
            </div>

            {!! $recaptcha->render() !!}

            <div class="ui center aligned container">
                <button class="large ui button" type="submit" tabindex="0" id="submit-login-form">
                    <i class="sign in icon"></i> Login
                </button>
            </div>
        </form>

        <div class="ui bottom attached warning message">
            <div class="ui link list">
                <a class="item" href="{{ $url->to('password/forgot') }}">
                    <div class="content">Forgotten your password?</div>
                </a>

                <a class="item" href="{{ $url->to('register') }}">
                    <div class="content">Need to register?</div>
                </a>

                <a class="item" href="{{ $url->to('email/verify/refresh') }}">
                    <div class="content">Need a new verification code?</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
