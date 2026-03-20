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
            <a href="/attendance/list?month={{ $prevMonth }}" class="date-selector-btn">← 前月</a>
            
            <span class="date-selector-current">
                <svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 -960 960 960" width="26" fill="#333333">
                    <path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm80-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80ZM280-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80ZM160-800h-80v-80h-80v80Z" />
                </svg>
                {{-- 🌟 変更点2：「2023/06」と手書きだった部分を、コントローラーから来た変月に！ --}}
                {{ $currentMonthDisplay }}
            </span>
            
            {{-- 🌟 変更点3：コントローラーから来た $nextMonth を使って翌月へ移動！ --}}
            <a href="/attendance/list?month={{ $nextMonth }}" class="date-selector-btn">翌月 →</a>
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
