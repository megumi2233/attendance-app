@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="login-form">
    <h1 class="main-title">ログイン</h1>
    
    <form action="/login" method="post" novalidate>
        @csrf
        <div class="form-group">
            <label class="form-label" for="email">メールアドレス</label>
            <input class="form-input" type="text" name="email" id="email" value="{{ old('email') }}">
            @error('email')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-label" for="password">パスワード</label>
            <input class="form-input" type="password" name="password" id="password">
            @error('password')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <button class="form-button" type="submit">ログインする</button>
        </div>
    </form>
    
    <div class="login-link">
        <a class="link-text" href="/register">会員登録はこちら</a>
    </div>
</div>
@endsection
