@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-page">
    <h1 class="section-title">勤怠詳細</h1>

    <form class="detail-form" action="/admin/attendance/detail/{{ $attendance->id }}" method="post">
        @csrf
        
        <input type="hidden" name="date" value="{{ $attendance->date }}">

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td><p class="detail-text">{{ $attendance->user->name }}</p></td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="date-display">
                        <span class="date-item">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span class="date-item">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" name="start_time" class="time-input" value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                        <span class="time-separator">～</span>
                        <input type="time" name="end_time" class="time-input" value="{{ old('end_time', \Carbon\Carbon::parse($attendance->end_time)->format('H:i')) }}">
                    </div>
                    {{-- 👇 🌟 めぐみさんの .error-message クラスを使用！ --}}
                    @error('start_time')<p class="error-message">{{ $message }}</p>@enderror
                    @error('end_time')<p class="error-message">{{ $message }}</p>@enderror
                </td>
            </tr>

            @foreach($attendance->breakTimes as $index => $breakTime)
            <tr>
                <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" name="break_times[{{ $index }}][start_time]" class="time-input" value="{{ old('break_times.'.$index.'.start_time', \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')) }}">
                        <span class="time-separator">～</span>
                        <input type="time" name="break_times[{{ $index }}][end_time]" class="time-input" value="{{ old('break_times.'.$index.'.end_time', \Carbon\Carbon::parse($breakTime->end_time)->format('H:i')) }}">
                    </div>
                    {{-- 👇 🌟 ここも .error-message クラスに変更！ --}}
                    @error("break_times.{$index}.start_time")<p class="error-message">{{ $message }}</p>@enderror
                    @error("break_times.{$index}.end_time")<p class="error-message">{{ $message }}</p>@enderror
                </td>
            </tr>
            @endforeach
            
            <tr>
                <th>休憩{{ $attendance->breakTimes->count() > 0 ? $attendance->breakTimes->count() + 1 : '' }}</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" name="break_times[{{ $attendance->breakTimes->count() }}][start_time]" class="time-input" value="{{ old('break_times.'.$attendance->breakTimes->count().'.start_time') }}">
                        <span class="time-separator">～</span>
                        <input type="time" name="break_times[{{ $attendance->breakTimes->count() }}][end_time]" class="time-input" value="{{ old('break_times.'.$attendance->breakTimes->count().'.end_time') }}">
                    </div>
                    {{-- 👇 🌟 ここです！！エラーの表示コードを追加しました！！ --}}
                    @error('break_times.' . $attendance->breakTimes->count() . '.start_time')<p class="error-message">{{ $message }}</p>@enderror
                    @error('break_times.' . $attendance->breakTimes->count() . '.end_time')<p class="error-message">{{ $message }}</p>@enderror
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" class="remark-textarea" rows="3">{{ old('reason') }}</textarea>
                    {{-- 👇 🌟 ここも .error-message クラスに変更！ --}}
                    @error('reason')<p class="error-message">{{ $message }}</p>@enderror
                </td>
            </tr>
        </table>

        @if($hasPendingRequest)
            {{-- 👇 🌟 めぐみさんが準備してくれた .pending-message クラスを大抜擢！ --}}
            <p class="pending-message">* 承認待ちのため修正はできません。</p>
        @else
            <div class="detail-actions">
                <button type="submit" class="action-button action-button--black">修正</button>
            </div>
        @endif
        
    </form>
</div>
@endsection
