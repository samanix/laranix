<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
        <meta name="application-name" content="{{ $config->get('app.name') }} v{{ $config->get('appsettings.version') }}" />
        <meta name="keywords" content="@yield('meta-keywords', $config->get('app.name'))" />
        <meta name="description" content="@yield('meta-description', $config->get('app.name'))" />
        <meta name="robots" content="@yield('meta-robots', 'index,follow')" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        @yield('meta-tags', '')

        <title>@yield('pagetitle', $config->get('app.name')) - {{ $config->get('app.name') }}</title>

        {!! $styles->output() !!}

        <script type="application/javascript">
            let siteUrl = "{{ $config->get('app.url') }}",
                siteLocale = "{{ $config->get('app.locale') }}";
        </script>

        {!! $scripts->output() !!}

    </head>
    <body>
        <a id="top"></a>
        <div class="ui container" id="wrapper">
            <div id="header-wrapper">
                @include('layout.header')
            </div>
            <div id="main-menu-wrapper">
                @include('layout.mainmenu')
            </div>
            <div id="content-wrapper">
                @include('layout.loginnotice')

                @yield('content')
            </div>
        </div>
        <div id="footer-wrapper">
            @include('layout.footer')
        </div>

        {!! $scripts->output(['head' => false]) !!}
        @stack('inlinejs')

    </body>
</html>
