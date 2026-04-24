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

        {{-- 🌟 プロのURL判定！ adminから始まるURLなら管理者用ブロックを呼ぶ --}}
        @if (request()->is('admin*'))
            @include('layouts.header-admin')
        
        {{-- 👤 それ以外で、メール認証済みの一般ユーザーなら一般用ブロックを呼ぶ --}}
        @elseif (Auth::check() && Auth::user()->hasVerifiedEmail())
            @include('layouts.header-user')
        @endif
    </header>

    <main>
        @yield('content')
    </main>

    @livewireScripts
</body>

</html>
