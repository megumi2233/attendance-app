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
