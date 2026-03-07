@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-page">
    <h1 class="section-title">勤怠詳細</h1>

    <form class="detail-form" action="#" method="post">
        @csrf
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td><p class="detail-text">西 伶奈</p></td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="date-display">
                        <span class="date-item">2023年</span>
                        <span class="date-separator"></span>
                        <span class="date-item">6月1日</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" class="time-input" value="09:00">
                        <span class="time-separator">～</span>
                        <input type="time" class="time-input" value="20:00">
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" class="time-input" value="12:00">
                        <span class="time-separator">～</span>
                        <input type="time" class="time-input" value="13:00">
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" class="time-input">
                        <span class="time-separator">～</span>
                        <input type="time" class="time-input">
                    </div>
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea class="remark-textarea" rows="3"></textarea>
                </td>
            </tr>
        </table>

        <div class="detail-actions">
            <button type="submit" class="action-button action-button--black">修正</button>
        </div>
    </form>
</div>
@endsection
