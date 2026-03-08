<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// 👇これはもともとある警備員さんの壁
Route::middleware('auth')->group(function () {
    Route::get('/', [AuthController::class, 'index']);
}); // 👈 ここで壁（グループ）は終わり！

// ==========================================
// 👇テスト用の仮ルートは、壁の「外（一番下）」に書く！
Route::get('/attendance', function () {
    return view('attendance.index');
});

// テスト用の仮ルート（勤怠一覧・詳細画面用）
Route::get('/attendance/list', function () {
    return view('attendance.list');
});
Route::get('/attendance/detail', function () {
    return view('attendance.detail');
});

// テスト用の仮ルート（申請一覧画面用）
Route::get('/request/list', function () {
    return view('request.list');
});

// テスト用の仮ルート（管理者ログイン画面用）
Route::get('/admin/login', function () {
    return view('admin.auth.login');
});

// テスト用の仮ルート（管理者 勤怠一覧・詳細画面用）
Route::get('/admin/attendance/list', function () {
    return view('admin.attendance.index');
});

Route::get('/admin/attendance/detail/{id}', function () {
    return view('admin.attendance.detail');
});

// 【管理者】スタッフ一覧画面（仮）
Route::get('/admin/staff/list', function () {
    return view('admin.staff.index');
});

// 【管理者】スタッフ別勤怠一覧画面（仮）
// ※ {id} には 1 や 2 などの数字が入ります
Route::get('/admin/staff/{id}', function () {
    return view('admin.staff.show');
});

// 【管理者】申請一覧画面（仮）
Route::get('/admin/request/list', function () {
    return view('admin.request.index');
});

// 【管理者】修正申請承認画面（仮）
Route::get('/admin/request/approve/{id}', function () {
    return view('admin.request.approve');
});
