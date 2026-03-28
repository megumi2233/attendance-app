@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
    <div class="attendance-list-page">
        {{-- ① 誰の勤怠かがわかるように、本物の名前に変更！ --}}
        <h1 class="section-title">{{ $user->name }}さんの勤怠</h1>

        <div class="date-selector">
            {{-- ② 「前月」「翌月」の矢印に、URLパラメータ（?month=...）をくっつける！ --}}
            <a href="/admin/attendance/staff/{{ $user->id }}?month={{ $prevMonth }}" class="date-selector-btn">← 前月</a>
            <span class="date-selector-current">
                <svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 -960 960 960"
                    width="26" fill="#333333">
                    <path
                        d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm80-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80ZM280-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80Z" />
                </svg>
                {{ $currentMonthDisplay }}
            </span>
            <a href="/admin/attendance/staff/{{ $user->id }}?month={{ $nextMonth }}" class="date-selector-btn">翌月
                →</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                {{-- ③ 🌟 ここからが「魔法のループ」！一般ユーザーの画面と全く同じです！ --}}
                @foreach ($attendanceData as $date => $data)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $data['start_time'] ?? '' }}</td>
                        <td>{{ $data['end_time'] ?? '' }}</td>
                        <td>{{ $data['break_time'] ?? '' }}</td>
                        <td>{{ $data['work_time'] ?? '' }}</td>
                        <td>
                            {{-- 詳細リンクは、出勤データ（id）が存在する日だけ表示する！ --}}
                            @if ($data && $data['id'])
                                <a href="/admin/attendance/detail/{{ $data['id'] }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="export-actions">
            {{-- ④ 🌟 CSV出力ボタンを <form> で囲んで、さっき作った「専用ルート」に繋ぐ！ --}}
            <form action="/admin/attendance/staff/{{ $user->id }}/export" method="GET">
                {{-- URLに「?month=今表示している月」を付けてあげると、ちゃんとその月のデータがダウンロードされます！ --}}
                <input type="hidden" name="month" value="{{ $targetMonth }}">
                <button type="submit" class="export-btn">CSV出力</button>
            </form>
        </div>
    </div>
@endsection
