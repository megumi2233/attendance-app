<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
    @livewireStyles
</head>

<body>
    <header class="header">
        <div class="header-logo">
            <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
        </div>

        @if (Auth::guard('admin')->check())
            @include('layouts.header-admin')
        @elseif (Auth::guard('web')->check() && Auth::user()->hasVerifiedEmail())
            @include('layouts.header-user')
        @endif
    </header>

    <main>
        @yield('content')
    </main>

    @livewireScripts
</body>

</html>
