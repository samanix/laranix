@extends('layout.app')

@section('pagetitle', 'Forgotten Password')

@section('meta-keywords', 'forgot, password')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">

        @isset($forgot_password_message))
            <div class="ui visible success message">
                {{ $forgot_password_message }}
            </div>
        @endisset

        @include('layout.formerrors')

        <div class="ui icon attached message">
            <i class="help circle icon"></i>
            <div class="content">
                <div class="header">
                    Forgotten Password
                </div>
                Request a password reset if you've forgotten your login.
            </div>
        </div>

        <form class="ui form attached segment" method="post" action="{{ $url->to('password/forgot') }}" id="pass-forgot-form" autocomplete="off">

            {!! csrf_field() !!}
            {!! $sequence->render() !!}

            <div class="field">
                <label>Email</label>
                <div class="description">
                    Enter the email you have active on your account.
                </div>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email" placeholder="my@email.com" value="{{ old('email') }}" maxlength="255" required>
                </div>
            </div>

            {!! $recaptcha->render() !!}

            <div class="ui center aligned container">
                <button class="large ui button" type="submit" tabindex="0" id="submit-pass-forgot-form">
                    <i class="undo icon"></i> Reset Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
