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
            {{-- 🌟 「前日」を押したら、URLの合言葉を前日の日付にする！ --}}
            <a href="/admin/attendance/list?date={{ $prevDate->format('Y-m-d') }}" class="date-selector-btn">← 前日</a>
            
            <span class="date-selector-current">
                <svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 -960 960 960" width="26" fill="#333333">
                    <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm80-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80ZM280-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80Z"/>
                </svg>
                {{ $displayDate->format('Y/m/d') }}
            </span>

            {{-- 🌟 「翌日」を押したら、URLの合言葉を翌日の日付にする！ --}}
            <a href="/admin/attendance/list?date={{ $nextDate->format('Y-m-d') }}" class="date-selector-btn">翌日 →</a>
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
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    {{-- 👇 時間が記録されていれば表示、まだなら空っぽ（空白）にする魔法の三項演算子！ --}}
                    <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    
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
