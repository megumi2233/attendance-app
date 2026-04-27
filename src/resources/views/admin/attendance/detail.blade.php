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

                            {{-- 🌟 管理者専用のクラス「admin-date-separator」を追加！ --}}
                            <span class="date-separator wide-date-separator"></span>

                            <span class="date-item">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-inputs">
                            {{-- 出勤時間は今まで通りでOK --}}
                            <input type="time" name="start_time" class="time-input"
                                value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">

                            <span class="time-separator">～</span>

                            {{-- 🌟 退勤時間の value を修正！ --}}
                            <input type="time" name="end_time" class="time-input"
                                value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                        </div>

                        {{-- 👇 🌟 ここがポイント！2つの @error を @if 〜 @elseif の魔法に合体させます！ --}}
                        @if ($errors->has('start_time'))
                            <p class="error-message">{{ $errors->first('start_time') }}</p>
                        @elseif ($errors->has('end_time'))
                            <p class="error-message">{{ $errors->first('end_time') }}</p>
                        @endif
                    </td>
                </tr>

                @foreach ($attendance->breakTimes as $index => $breakTime)
                    <tr>
                        <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                        <td>
                            <div class="time-inputs">
                                {{-- 休憩開始時間 --}}
                                <input type="time" name="break_times[{{ $index }}][start_time]"
                                    class="time-input"
                                    value="{{ old('break_times.' . $index . '.start_time', \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')) }}">

                                <span class="time-separator">～</span>

                                {{-- 👇 🌟 【ここが本命！】休憩1の終了時間にガード魔法をかけます！ --}}
                                <input type="time" name="break_times[{{ $index }}][end_time]" class="time-input"
                                    value="{{ old('break_times.' . $index . '.end_time', !empty($breakTime->end_time) ? \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') : '') }}">
                            </div>

                            {{-- 🌟 修正後：休憩1などのエラー表示をこれに入れ替え！ --}}
                            @if ($errors->has("break_times.{$index}.start_time"))
                                <p class="error-message">{{ $errors->first("break_times.{$index}.start_time") }}</p>
                            @elseif ($errors->has("break_times.{$index}.end_time"))
                                <p class="error-message">{{ $errors->first("break_times.{$index}.end_time") }}</p>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- 👇 新しい休憩を追加する枠（休憩2など） --}}
                <tr>
                    <th>休憩{{ $attendance->breakTimes->count() > 0 ? $attendance->breakTimes->count() + 1 : '' }}</th>
                    <td>
                        <div class="time-inputs">
                            {{-- 新規の開始時間（oldだけ） --}}
                            <input type="time" name="break_times[{{ $attendance->breakTimes->count() }}][start_time]"
                                class="time-input"
                                value="{{ old('break_times.' . $attendance->breakTimes->count() . '.start_time') }}">

                            <span class="time-separator">～</span>

                            {{-- 👇 🌟 【ここも修正！】新規の終了時間はCarbonを使わずシンプルに真っ白にします！ --}}
                            <input type="time" name="break_times[{{ $attendance->breakTimes->count() }}][end_time]"
                                class="time-input"
                                value="{{ old('break_times.' . $attendance->breakTimes->count() . '.end_time') }}">
                        </div>

                        {{-- 🌟 修正後：一番下の追加枠のエラー表示をこれに入れ替え！ --}}
                        @if ($errors->has('break_times.' . $attendance->breakTimes->count() . '.start_time'))
                            <p class="error-message">
                                {{ $errors->first('break_times.' . $attendance->breakTimes->count() . '.start_time') }}</p>
                        @elseif ($errors->has('break_times.' . $attendance->breakTimes->count() . '.end_time'))
                            <p class="error-message">
                                {{ $errors->first('break_times.' . $attendance->breakTimes->count() . '.end_time') }}</p>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="reason" class="remark-textarea" rows="3">{{ old('reason') }}</textarea>
                        {{-- 👇 🌟 ここも .error-message クラスに変更！ --}}
                        @error('reason')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>

            @if ($hasPendingRequest)
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
