@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
    <div class="attendance-list-page">
        <h1 class="section-title">勤怠一覧</h1>

        <div class="date-selector">
            {{-- 🌟 変更点1：コントローラーから来た $prevMonth を使って前月へ移動！ --}}
            <a href="/attendance/list?month={{ $prevMonth }}" class="date-selector-btn">
                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="#B3B3B3" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="20" y1="12" x2="4" y2="12"></line>
                    <polyline points="10 18 4 12 10 6"></polyline>
                </svg>
                <span class="arrow-label">前月</span>
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
                {{-- 🌟 変更点2：「2023/06」と手書きだった部分を、コントローラーから来た変月に！ --}}
                {{ $currentMonthDisplay }}
            </span>

            {{-- 🌟 変更点3：コントローラーから来た $nextMonth を使って翌月へ移動！ --}}
            <a href="/attendance/list?month={{ $nextMonth }}" class="date-selector-btn">
                <span class="arrow-label">翌月</span>
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
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                {{-- 🌟 変更点4：ここからが「ループの魔法」！データがある分だけ <tr> を繰り返す！ --}}
                @foreach ($attendanceData as $date => $data)
                    <tr>
                        {{-- 日付を「06/01(木)」の形に変換して表示！ --}}
                        <td>{{ \Carbon\Carbon::parse($date)->isoFormat('MM/DD(ddd)') }}</td>

                        {{-- データベースから計算した時間を入れる！（?? '' は「もし空っぽなら何も表示しない」というおまじない） --}}
                        <td>{{ $data['start_time'] ?? '' }}</td>
                        <td>{{ $data['end_time'] ?? '' }}</td>
                        <td>{{ $data['break_time'] ?? '' }}</td>
                        <td>{{ $data['work_time'] ?? '' }}</td>

                        {{-- 詳細リンクは、出勤データ（id）が存在する日だけ表示する！ --}}
                        <td>
                            @if ($data && $data['id'])
                                <a href="/attendance/detail/{{ $data['id'] }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
