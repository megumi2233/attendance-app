@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
    <div class="attendance-detail-page">
        <h1 class="section-title">勤怠詳細</h1>

        @php $is_pending = false; @endphp

        <form class="detail-form" action="#" method="post">
            @csrf
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>
                        <p class="detail-text">西 伶奈</p>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="date-display">
                            <span class="date-item">2023年</span>
                            <span class="date-separator"></span> <span class="date-item">6月1日</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-inputs">
                            <input type="time" class="time-input" value="09:00" {{ $is_pending ? 'disabled' : '' }}>
                            <span class="time-separator">～</span>
                            <input type="time" class="time-input" value="18:00" {{ $is_pending ? 'disabled' : '' }}>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="time-inputs">
                            <input type="time" class="time-input" value="12:00" {{ $is_pending ? 'disabled' : '' }}>
                            <span class="time-separator">～</span>
                            <input type="time" class="time-input" value="13:00" {{ $is_pending ? 'disabled' : '' }}>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        <div class="time-inputs">
                            <input type="time" class="time-input" value="" {{ $is_pending ? 'disabled' : '' }}>
                            <span class="time-separator">～</span>
                            <input type="time" class="time-input" value="" {{ $is_pending ? 'disabled' : '' }}>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea class="remark-textarea" rows="3" {{ $is_pending ? 'disabled' : '' }}>電車遅延のため</textarea>
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
