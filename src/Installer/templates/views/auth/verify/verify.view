@extends('layout.app')

@section('pagetitle', 'Verify Email')

@section('meta-keywords', 'verify, email')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui centered grid">
    <div class="eight wide computer twelve wide tablet sixteen wide mobile column">

        @include('layout.formerrors')

        <div class="ui icon attached message">
            <i class="checkmark icon"></i>
            <div class="content">
                <div class="header">
                    Verify Email
                </div>
                Verify your email address.
            </div>
        </div>

        <form class="ui form attached segment" method="post" action="{{ $url->to('email/verify') }}" id="verify-email-form" autocomplete="off">

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
                    Enter the email you are trying to verify.
                </div>
                <div class="ui left icon input">
                    <i class="mail icon"></i>
                    <input type="email" name="email" placeholder="my@email.com" value="{{ $email }}" maxlength="255" required>
                </div>
            </div>

            {!! $recaptcha->render() !!}

            <div class="ui center aligned container">
                <button class="large ui button" type="submit" tabindex="0" id="submit-verify-email-form">
                    <i class="checkmark icon"></i> Verify Email
                </button>
            </div>
        </form>

        <div class="ui bottom attached warning message">
            <div class="ui link list">
                <a class="item" href="{{ $url->to('email/verify/refresh') }}">
                    <div class="content">Need a new verification email?</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
