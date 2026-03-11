@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="register-form">
    <h1 class="main-title">会員登録</h1>
    
    <form action="/register" method="post" novalidate>
        @csrf
        <div class="form-group">
            <label class="form-label" for="name">名前</label>
            <input class="form-input" type="text" name="name" id="name" value="{{ old('name') }}">
            @error('name')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
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
            <label class="form-label" for="password_confirmation">パスワード確認</label>
            <input class="form-input" type="password" name="password_confirmation" id="password_confirmation">
        </div>
        <div class="form-group">
            <button class="form-button" type="submit">登録する</button>
        </div>
    </form>
    
    <div class="register-link">
        <a class="link-text" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection
