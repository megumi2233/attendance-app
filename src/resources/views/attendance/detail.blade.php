@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
    <div class="attendance-detail-page">
        <h1 class="section-title">勤怠詳細</h1>

        @if (session('success'))
            <div class="alert alert-success success-message">
                {{ session('success') }}
            </div>
        @endif

        <form class="detail-form" action="/attendance/detail/{{ $attendance->id }}" method="post">
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
                            <span class="date-item">{{ $year }}</span>

                            {{-- 🌟 めぐみさん考案！一般ユーザー専用のクラス「general-date-separator」を追加！ --}}
                            <span class="date-separator wide-date-separator"></span>

                            <span class="date-item">{{ $monthDay }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        @if ($is_pending)
                            {{-- 🌟 承認待ちは文字だけ表示 --}}
                            <div class="time-display">
                                <span class="detail-text">{{ $startTime }}</span>
                                <span class="time-separator">～</span>
                                <span class="detail-text">{{ $endTime }}</span>
                            </div>
                        @else
                            {{-- 🌟 修正可能なときは入力ボックスを表示 --}}
                            <div class="time-inputs">
                                {{-- 👇 🌟 ここに old() の魔法をかけました！ --}}
                                <input type="time" name="start_time" class="time-input"
                                    value="{{ old('start_time', $startTime) }}">
                                <span class="time-separator">～</span>
                                <input type="time" name="end_time" class="time-input"
                                    value="{{ old('end_time', $endTime) }}">
                            </div>
                        @endif

                        {{-- 👇 🌟 2つ並んでいたエラーを、順番待ちの魔法（@if 〜 @elseif）で合体！ --}}
                        @if ($errors->has('start_time'))
                            <p class="error-message">{{ $errors->first('start_time') }}</p>
                        @elseif ($errors->has('end_time'))
                            <p class="error-message">{{ $errors->first('end_time') }}</p>
                        @endif
                    </td>
                </tr>

                {{-- 🌟 既存の休憩データ --}}
                @foreach ($attendance->breakTimes as $index => $breakTime)
                    <tr>
                        <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                        <td>
                            @if ($is_pending)
                                <div class="time-display">
                                    <span
                                        class="detail-text">{{ \Carbon\Carbon::parse($breakTime->start_time)->format('H:i') }}</span>
                                    <span class="time-separator">～</span>
                                    <span
                                        class="detail-text">{{ $breakTime->end_time ? \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') : '' }}</span>
                                </div>
                            @else
                                <div class="time-inputs">
                                    {{-- 👇 🌟 ここに old() の魔法をかけました！ --}}
                                    <input type="time" name="break_times[{{ $index }}][start_time]"
                                        class="time-input"
                                        value="{{ old('break_times.' . $index . '.start_time', \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')) }}">
                                    <span class="time-separator">～</span>
                                    <input type="time" name="break_times[{{ $index }}][end_time]"
                                        class="time-input"
                                        value="{{ old('break_times.' . $index . '.end_time', $breakTime->end_time ? \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') : '') }}">
                                </div>
                            @endif

                            {{-- 👇 🌟 2つ並んでいたエラーを、順番待ちの魔法（@if 〜 @elseif）で合体！ --}}
                            @if ($errors->has('break_times.' . $index . '.start_time'))
                                <p class="error-message">{{ $errors->first('break_times.' . $index . '.start_time') }}</p>
                            @elseif ($errors->has('break_times.' . $index . '.end_time'))
                                <p class="error-message">{{ $errors->first('break_times.' . $index . '.end_time') }}</p>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- 🌟 追加用の空の休憩枠（承認待ちのときは表示しない） --}}
                @if (!$is_pending)
                    @php $nextIndex = $attendance->breakTimes->count(); @endphp
                    <tr>
                        <th>休憩{{ $nextIndex == 0 ? '' : $nextIndex + 1 }}</th>
                        <td>
                            <div class="time-inputs">
                                {{-- 👇 🌟 ここに old() の魔法をかけました！最初は空っぽ、エラー時は入力した時間を表示 --}}
                                <input type="time" name="break_times[{{ $nextIndex }}][start_time]"
                                    class="time-input" value="{{ old('break_times.' . $nextIndex . '.start_time') }}">
                                <span class="time-separator">～</span>
                                <input type="time" name="break_times[{{ $nextIndex }}][end_time]" class="time-input"
                                    value="{{ old('break_times.' . $nextIndex . '.end_time') }}">
                            </div>

                            {{-- 👇 🌟 2つ並んでいたエラーを、順番待ちの魔法（@if 〜 @elseif）で合体！ --}}
                            @if ($errors->has('break_times.' . $nextIndex . '.start_time'))
                                <p class="error-message">{{ $errors->first('break_times.' . $nextIndex . '.start_time') }}
                                </p>
                            @elseif ($errors->has('break_times.' . $nextIndex . '.end_time'))
                                <p class="error-message">{{ $errors->first('break_times.' . $nextIndex . '.end_time') }}
                                </p>
                            @endif
                        </td>
                    </tr>
                @endif

                <tr>
                    <th>備考</th>
                    <td>
                        @if ($is_pending)
                            <p class="detail-text">{{ $attendance->reason }}</p>
                        @else
                            {{-- 👇 🌟 ここに old() の魔法をかけました！ --}}
                            <textarea name="reason" class="remark-textarea" rows="3">{{ old('reason', $attendance->reason) }}</textarea>
                        @endif

                        @error('reason')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>

            @if ($is_pending)
                <p class="pending-message">*承認待ちのため修正はできません。</p>
            @else
                <div class="detail-actions">
                    <button type="submit" class="action-button action-button--black">修正</button>
                </div>
            @endif
        </form>
    </div>
@endsection
