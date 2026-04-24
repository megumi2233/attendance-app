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
