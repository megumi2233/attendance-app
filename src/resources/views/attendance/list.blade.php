@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
    <div class="attendance-list-page">
        <h1 class="section-title">勤怠一覧</h1>

        <div class="month-selector">
            <a href="#" class="month-selector-btn">← 前月</a>
            <span class="month-selector-current">
                <svg class="calendar-icon" xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 -960 960 960"
                    width="26" fill="#333333">
                    <path
                        d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm80-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80ZM280-240h80v-80h-80v80Zm160 0h80v-80h-80v80Zm160 0h80v-80h-80v80Z" />
                </svg>
                2023/06
            </span>
            <a href="#" class="month-selector-btn">翌月 →</a>
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
                <tr>
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/02(金)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail" class="detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
