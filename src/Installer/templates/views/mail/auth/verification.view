@component('mail::message')
# {{ $options->subject }}

Verify your email by clicking the button below.

@component('mail::button', ['url' => $options->url])
Verify My Email
@endcomponent

Your token will be valid for **{{ $options->humanExpiry }}**, until {{ $options->expiry }}.

@component('mail::subcopy')
Alternatively, visit {{ $options->baseurl }} and enter code:

`{{ $options->token }}`
@endcomponent

@component('mail::subcopy')
Kind Regards,

{{ config('app.name', 'Laranix') }}
@endcomponent
@endcomponent
