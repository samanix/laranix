<div class="ui inverted menu main-menu">
    <a class="item" href="{{ $url->to('/') }}">Home</a>
    <a class="item" href="{{ $url->to('about') }}">About</a>
    <a class="item" href="{{ $url->to('contact') }}">Contact</a>
    @if ($auth->guest())
        <div class="right menu">
            <a class="item" href="{{ $url->to('login') }}">
                Login
            </a>
            <a class="item" href="{{ $url->to('register') }}">
                Register
            </a>
        </div>
    @else
        <div class="ui simple right dropdown item" id="account-dropdown">
            {{ $auth->user()->username }}
            <i class="dropdown icon"></i>
            <div class="ui inverted menu">
                <div class="item">
                    <form class="ui form" id="logout-form" action="{{ $url->to('logout') }}" method="post">
                        <div class="ui transparent input">
                            {!! csrf_field() !!}
                            <input type="submit" value="Logout" id="submit-logout-form">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
