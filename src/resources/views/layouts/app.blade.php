<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-logo">
            <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
        </div>

        {{-- 👇 ① 管理者（admin）がログインしている場合のメニュー --}}
        @if (Auth::guard('admin')->check())
        <nav class="header-nav">
            <ul class="header-nav-list">
                <li class="header-nav-item"><a href="/admin/attendance/list">勤怠一覧</a></li>
                <li class="header-nav-item"><a href="/admin/staff/list">スタッフ一覧</a></li>
                <li class="header-nav-item"><a href="/stamp_correction_request/list">申請一覧</a></li>
                <li class="header-nav-item">
                    <form action="/admin/logout" method="post">
                        @csrf
                        <button class="header-nav-button" type="submit">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>

        {{-- 👇 ② 一般ユーザー（web）がログインしている場合のメニュー --}}
        @elseif (Auth::check())
        <nav class="header-nav">
            <ul class="header-nav-list">
                <li class="header-nav-item"><a href="/attendance">勤怠</a></li>
                <li class="header-nav-item"><a href="/attendance/list">勤怠一覧</a></li>
                <li class="header-nav-item"><a href="/stamp_correction_request/list">申請</a></li>
                <li class="header-nav-item">
                    <form action="/logout" method="post">
                        @csrf
                        <button class="header-nav-button" type="submit">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
        @endif
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
