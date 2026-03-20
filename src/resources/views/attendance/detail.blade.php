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
                            <span class="date-separator"></span>
                            <span class="date-item">{{ $monthDay }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-inputs">
                            <input type="time" name="start_time" class="time-input" value="{{ $startTime }}" {{ $is_pending ? 'disabled' : '' }}>
                            <span class="time-separator">～</span>
                            <input type="time" name="end_time" class="time-input" value="{{ $endTime }}" {{ $is_pending ? 'disabled' : '' }}>
                        </div>
                        {{-- 🌟 出勤・退勤のエラー表示（スッキリ！） --}}
                        @error('start_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('end_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>

                {{-- 🌟 既存の休憩データ --}}
                @foreach($attendance->breakTimes as $index => $breakTime)
                    <tr>
                        <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                        <td>
                            <div class="time-inputs">
                                <input type="time" name="break_times[{{ $index }}][start_time]" class="time-input" value="{{ \Carbon\Carbon::parse($breakTime->start_time)->format('H:i') }}" {{ $is_pending ? 'disabled' : '' }}>
                                <span class="time-separator">～</span>
                                <input type="time" name="break_times[{{ $index }}][end_time]" class="time-input" value="{{ $breakTime->end_time ? \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') : '' }}" {{ $is_pending ? 'disabled' : '' }}>
                            </div>
                            {{-- 🌟 休憩（ループ内）のエラー表示（スッキリ！） --}}
                            @error('break_times.'.$index.'.start_time')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            @error('break_times.'.$index.'.end_time')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                @endforeach

                {{-- 🌟 追加用の空の休憩枠 --}}
                @php $nextIndex = $attendance->breakTimes->count(); @endphp
                <tr>
                    <th>休憩{{ $nextIndex == 0 ? '' : $nextIndex + 1 }}</th>
                    <td>
                        <div class="time-inputs">
                            <input type="time" name="break_times[{{ $nextIndex }}][start_time]" class="time-input" value="" {{ $is_pending ? 'disabled' : '' }}>
                            <span class="time-separator">～</span>
                            <input type="time" name="break_times[{{ $nextIndex }}][end_time]" class="time-input" value="" {{ $is_pending ? 'disabled' : '' }}>
                        </div>
                        {{-- 🌟 休憩（新規追加枠）のエラー表示（スッキリ！） --}}
                        @error('break_times.'.$nextIndex.'.start_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('break_times.'.$nextIndex.'.end_time')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="reason" class="remark-textarea" rows="3" {{ $is_pending ? 'disabled' : '' }}></textarea>
                        {{-- 🌟 備考のエラー表示（スッキリ！） --}}
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
