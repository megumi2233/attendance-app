@extends('layouts.app')

@section('title', '勤怠一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
    <div class="attendance-list-page">
        {{-- 🌟 Controllerから渡された日付を表示する！ --}}
        <h1 class="section-title">{{ $displayDate->format('Y年n月j日') }}の勤怠</h1>

        <div class="date-selector">
            <a href="/admin/attendance/list?date={{ $prevDate }}" class="date-selector-btn">
                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="#B3B3B3" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="20" y1="12" x2="4" y2="12"></line>
                    <polyline points="10 18 4 12 10 6"></polyline>
                </svg>
                <span class="arrow-label">前日</span>
            </a>

            <span class="date-selector-current">
                <svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                    viewBox="0 0 24 24" fill="none">
                    <rect x="2" y="4" width="20" height="18" rx="3" stroke="#4d4d4d" stroke-width="1.2"
                        fill="white"></rect>
                    <path d="M2 7V7a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v2H2V7z" fill="#4d4d4d"></path>
                    <rect x="6" y="1.5" width="4" height="6" rx="2" fill="white"></rect>
                    <rect x="7.3" y="2.8" width="1.4" height="3.4" rx="0.7" fill="#4d4d4d"></rect>
                    <rect x="14" y="1.5" width="4" height="6" rx="2" fill="white"></rect>
                    <rect x="15.3" y="2.8" width="1.4" height="3.4" rx="0.7" fill="#4d4d4d"></rect>
                    <g fill="#4d4d4d">
                        <rect x="9.5" y="11" width="2" height="2"></rect>
                        <rect x="13.5" y="11" width="2" height="2"></rect>
                        <rect x="17.5" y="11" width="2" height="2"></rect>
                        <rect x="5.5" y="14" width="2" height="2"></rect>
                        <rect x="9.5" y="14" width="2" height="2"></rect>
                        <rect x="17.5" y="14" width="2" height="2"></rect>
                        <rect x="5.5" y="17" width="2" height="2"></rect>
                        <rect x="9.5" y="17" width="2" height="2"></rect>
                        <rect x="13.5" y="17" width="2" height="2"></rect>
                    </g>
                    <polyline points="13.2 15.2 14.5 16.5 16.5 14.5" stroke="#4d4d4d" stroke-width="1.2"
                        stroke-linecap="round" stroke-linejoin="round"></polyline>
                </svg>
                {{ $displayDate->format('Y/m/d') }}
            </span>

            <a href="/admin/attendance/list?date={{ $nextDate }}" class="date-selector-btn">
                <span class="arrow-label">翌日</span>
                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="#B3B3B3" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="12" x2="20" y2="12"></line>
                    <polyline points="14 6 20 12 14 18"></polyline>
                </svg>
            </a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                {{-- 🌟 データベースから持ってきた人数分だけ、自動で行（<tr>）をループして作る！ --}}
                @foreach ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        {{-- 👇 時間が記録されていれば表示、まだなら空っぽ（空白）にする魔法の三項演算子！ --}}
                        <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}
                        </td>
                        <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}
                        </td>

                        {{-- 🌟 さっきAttendanceモデルに教えた特技を呼び出すだけ！ --}}
                        <td>{{ $attendance->getRestTime() }}</td>
                        <td>{{ $attendance->getWorkTime() }}</td>

                        <td><a href="/admin/attendance/{{ $attendance->id }}" class="detail-link">詳細</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
