@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list-page">
    <h1 class="section-title">スタッフ一覧</h1>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            {{-- 👇 🌟 ここが魔法！データベースから取ってきた人数の分だけ行（tr）を自動で増やす！ --}}
            @foreach($users as $user)
            <tr>
                {{-- 本物の「名前」と「メールアドレス」を表示 --}}
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                
                {{-- 👇 🌟 その人のID（$user->id）をURLに埋め込んで、個別の詳細画面へ案内する！ --}}
                <td><a href="/admin/staff/{{ $user->id }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
