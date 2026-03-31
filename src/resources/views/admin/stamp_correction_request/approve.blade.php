@extends('layouts.app')

@section('title', '修正申請承認（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
    <div class="attendance-detail-page">
        <h1 class="section-title">勤怠詳細</h1>

        {{-- 🌟 後で承認処理（POST）をするための準備！ --}}
        <form class="detail-form" action="/stamp_correction_request/approve/{{ $correctionRequest->id }}" method="post">
            @csrf
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>
                        <p class="detail-text">{{ $correctionRequest->attendance->user->name }}</p>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="date-display">
                            <span
                                class="date-item">{{ \Carbon\Carbon::parse($correctionRequest->date)->format('Y年') }}</span>
                            <span class="date-separator"></span>
                            <span
                                class="date-item">{{ \Carbon\Carbon::parse($correctionRequest->date)->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="date-display">
                            <span
                                class="date-item">{{ \Carbon\Carbon::parse($correctionRequest->start_time)->format('H:i') }}</span>
                            <span class="time-separator">〜</span>
                            <span
                                class="date-item">{{ \Carbon\Carbon::parse($correctionRequest->end_time)->format('H:i') }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 🌟 休憩データはいくつあるか分からないので、@foreachでくるくる回して表示！ --}}
                @foreach ($correctionRequest->stampCorrectionRequestBreakTimes as $index => $breakTime)
                    <tr>
                        <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                        <td>
                            <div class="date-display">
                                <span
                                    class="date-item">{{ \Carbon\Carbon::parse($breakTime->start_time)->format('H:i') }}</span>
                                <span class="time-separator">〜</span>
                                <span
                                    class="date-item">{{ \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') }}</span>
                            </div>
                        </td>
                    </tr>
                @endforeach

                {{-- 👇 🌟 ココを追加！休憩が1回しかなかった場合、強制的に空の「休憩2」を作る！ --}}
                @if ($correctionRequest->stampCorrectionRequestBreakTimes->count() === 1)
                    <tr>
                        <th>休憩2</th>
                        <td>
                            <div class="date-display">
                                <span class="date-item"></span>
                                <span class="time-separator"></span>
                                <span class="date-item"></span>
                            </div>
                        </td>
                    </tr>
                @endif

                <tr>
                    <th>備考</th>
                    <td>
                        <p class="reason-text">{{ $correctionRequest->reason }}</p>
                    </td>
                </tr>
            </table>

            <div class="detail-actions">
                {{-- 🌟 状態が「承認待ち」なら黒いボタン、「承認済み」ならグレーのボタンを出す！ --}}
                @if ($correctionRequest->status === '承認待ち')
                    <button type="submit" class="action-button action-button--black">承認</button>
                @else
                    <button type="button" class="action-button action-button--gray" disabled>承認済み</button>
                @endif
            </div>
        </form>
    </div>
@endsection
