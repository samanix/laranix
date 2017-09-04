@if (session('login_notice', false))
    <div class="ui grid">
        <div class="doubling two column centered row">
            <div class="column">
                <div class="ui {{ session('login_notice_is_error') ? 'negative' : 'success' }} message">
                    <div class="header">
                        {{ session('login_notice_header') }}
                    </div>
                    <p>{!! session('login_notice_message') !!}</p>
                </div>
            </div>
        </div>
    </div>
@endif
