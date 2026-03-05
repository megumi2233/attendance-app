@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-page">
    <h1 class="visually-hidden">勤怠登録</h1>

    @php $status = 'done'; @endphp

    <div class="attendance-panel">
        @if($status === 'off_duty')
            <span class="status-badge">勤務外</span>
        @elseif($status === 'working')
            <span class="status-badge">出勤中</span>
        @elseif($status === 'on_break')
            <span class="status-badge">休憩中</span>
        @elseif($status === 'done')
            <span class="status-badge">退勤済</span>
        @endif

        <p class="current-date">2023年6月1日(木)</p>
        <p class="current-time">08:00</p>

        <div class="attendance-actions">
            @if($status === 'off_duty')
                <form class="attendance-form" action="/attendance/start" method="post">
                    @csrf
                    <button class="action-button action-button--black" type="submit">出勤</button>
                </form>

            @elseif($status === 'working')
                <form class="attendance-form" action="/attendance/end" method="post">
                    @csrf
                    <button class="action-button action-button--black" type="submit">退勤</button>
                </form>
                <form class="attendance-form" action="/attendance/break/start" method="post">
                    @csrf
                    <button class="action-button action-button--white" type="submit">休憩入</button>
                </form>

            @elseif($status === 'on_break')
                <form class="attendance-form" action="/attendance/break/end" method="post">
                    @csrf
                    <button class="action-button action-button--white" type="submit">休憩戻</button>
                </form>

            @elseif($status === 'done')
                <p class="done-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>
@endsection
