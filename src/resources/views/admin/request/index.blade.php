@extends('layouts.app')

@section('title', '申請一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endsection

@section('content')
    <div class="request-list-page">
        <h1 class="section-title">申請一覧</h1>

        <div class="request-tabs">
            <a href="#" class="request-tab active">承認待ち</a>
            <a href="#" class="request-tab">承認済み</a>
        </div>

        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>承認待ち</td>
                    <td>西 怜奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <td><a href="/admin/request/approve/1" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>山田 太郎</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/08/02</td>
                    <td><a href="/admin/request/approve/2" class="detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
