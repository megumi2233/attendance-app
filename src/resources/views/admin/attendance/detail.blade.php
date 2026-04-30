@extends('layouts.app')

@section('title', '勤怠詳細（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-page">
    <h1 class="section-title">勤怠詳細</h1>

    <form class="detail-form" action="/admin/attendance/{{ $attendance->id }}" method="post">
        @csrf
        <input type="hidden" name="date" value="{{ $attendance->date }}">

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>
                    <p class="detail-text">{{ $attendance->user->name }}</p>
                </td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="date-display">
                        <span class="date-item">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span class="date-separator wide-date-separator"></span>
                        <span class="date-item">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{-- ✅ 承認待ちの場合は文字だけ表示 --}}
                    @if ($hasPendingRequest)
                        <div class="time-display">
                            <span class="detail-text">{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</span>
                            <span class="time-separator">～</span>
                            <span class="detail-text">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</span>
                        </div>
                    @else
                        <div class="time-inputs">
                            <input type="time" name="start_time" class="time-input" value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                            <span class="time-separator">～</span>
                            <input type="time" name="end_time" class="time-input" value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                        </div>
                        @if ($errors->has('start_time'))
                            <p class="error-message">{{ $errors->first('start_time') }}</p>
                        @elseif ($errors->has('end_time'))
                            <p class="error-message">{{ $errors->first('end_time') }}</p>
                        @endif
                    @endif
                </td>
            </tr>

            {{-- 既存の休憩データ --}}
            @foreach ($attendance->breakTimes as $index => $breakTime)
            <tr>
                <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                <td>
                    @if ($hasPendingRequest)
                        <div class="time-display">
                            <span class="detail-text">{{ \Carbon\Carbon::parse($breakTime->start_time)->format('H:i') }}</span>
                            <span class="time-separator">～</span>
                            <span class="detail-text">{{ $breakTime->end_time ? \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') : '' }}</span>
                        </div>
                    @else
                        <div class="time-inputs">
                            <input type="time" name="break_times[{{ $index }}][start_time]" class="time-input" value="{{ old('break_times.' . $index . '.start_time', \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')) }}">
                            <span class="time-separator">～</span>
                            <input type="time" name="break_times[{{ $index }}][end_time]" class="time-input" value="{{ old('break_times.' . $index . '.end_time', empty($breakTime->end_time) ? '' : \Carbon\Carbon::parse($breakTime->end_time)->format('H:i')) }}">
                        </div>
                        @if ($errors->has('break_times.' . $index . '.start_time'))
                            <p class="error-message">{{ $errors->first('break_times.' . $index . '.start_time') }}</p>
                        @elseif ($errors->has('break_times.' . $index . '.end_time'))
                            <p class="error-message">{{ $errors->first('break_times.' . $index . '.end_time') }}</p>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach

            {{-- ✅ 追加用の空の休憩枠は、承認待ちの時はまるごと隠す！ --}}
            @if (!$hasPendingRequest)
            <tr>
                <th>休憩{{ $attendance->breakTimes->count() > 0 ? $attendance->breakTimes->count() + 1 : '' }}</th>
                <td>
                    <div class="time-inputs">
                        <input type="time" name="break_times[{{ $attendance->breakTimes->count() }}][start_time]" class="time-input" value="{{ old('break_times.' . $attendance->breakTimes->count() . '.start_time') }}">
                        <span class="time-separator">～</span>
                        <input type="time" name="break_times[{{ $attendance->breakTimes->count() }}][end_time]" class="time-input" value="{{ old('break_times.' . $attendance->breakTimes->count() . '.end_time') }}">
                    </div>
                    @if ($errors->has('break_times.' . $attendance->breakTimes->count() . '.start_time'))
                        <p class="error-message">{{ $errors->first('break_times.' . $attendance->breakTimes->count() . '.start_time') }}</p>
                    @elseif ($errors->has('break_times.' . $attendance->breakTimes->count() . '.end_time'))
                        <p class="error-message">{{ $errors->first('break_times.' . $attendance->breakTimes->count() . '.end_time') }}</p>
                    @endif
                </td>
            </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if ($hasPendingRequest)
                        <p class="detail-text">{{ $attendance->reason }}</p>
                    @else
                        <textarea name="reason" class="remark-textarea" rows="3">{{ old('reason', $attendance->reason) }}</textarea>
                        @error('reason')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    @endif
                </td>
            </tr>
        </table>

        {{-- めぐみさん実装済みの完璧なボタン切り替え --}}
        @if ($hasPendingRequest)
            <p class="pending-message">* 承認待ちのため修正はできません。</p>
        @else
            <div class="detail-actions">
                <button type="submit" class="action-button action-button--black">修正</button>
            </div>
        @endif
    </form>
</div>
@endsection
