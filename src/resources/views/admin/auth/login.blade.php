@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="login-form">
    <h1 class="main-title">管理者ログイン</h1>

    <form action="/admin/login" method="post" novalidate>
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
            <button class="form-button" type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection
