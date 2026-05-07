@extends('layouts.app')

@section('title', 'メール認証誘導')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="verify-container">
    <div class="verify-message-box">
        @if (session('status') == 'verification-link-sent')
            <div class="verify-success-message">
                新しい認証メールを送信しました！
            </div>
        @endif

        <p class="verify-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="verify-actions">
            <a href="http://localhost:8025" target="_blank" class="verify-button">認証はこちらから</a>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend-link">認証メールを再送する</button>
            </form>
        </div>
    </div>
</div>
@endsection
