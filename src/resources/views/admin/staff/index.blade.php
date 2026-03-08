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
                <tr>
                    <td>西 怜奈</td>
                    <td>reina.n@coachtech.com</td>
                    <td><a href="/admin/staff/1" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>山田 太郎</td>
                    <td>taro.y@coachtech.com</td>
                    <td><a href="/admin/staff/2" class="detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
