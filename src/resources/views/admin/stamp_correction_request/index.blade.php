@extends('layouts.app')

@section('title', '申請一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endsection

@section('content')
<div class="request-list-page">
    <h1 class="section-title">申請一覧</h1>

    @livewire('request-tabs')
</div>
@endsection
