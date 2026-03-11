<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\UserLoginController; // 👈 変更：新しい本名に！
use App\Http\Controllers\Admin\AdminLoginController; // 👈 変更：新しい本名に！あだ名（as）も不要に！


// ==========================================
// 👤 一般ユーザー（Staff）用の本物ルート
// ==========================================

// 未ログインの人だけが入れるルート（登録とログイン）
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [UserLoginController::class, 'create'])->name('login'); // 👈 変更
    Route::post('/login', [UserLoginController::class, 'store']); // 👈 変更
});

// ログイン済みの一般ユーザーだけが入れるルート
Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserLoginController::class, 'destroy']); // 👈 変更
    
    // 👇 / にアクセスしたら打刻画面へ自動移動させる
    Route::get('/', function () {
        return redirect('/attendance');
    });

    // 👇 テスト用の一般ユーザー向け仮ルート
    Route::get('/attendance', function () {
        return view('attendance.index');
    });
    Route::get('/attendance/list', function () {
        return view('attendance.list');
    });
    Route::get('/attendance/detail', function () {
        return view('attendance.detail');
    });
    Route::get('/request/list', function () {
        return view('request.list');
    });
});


// ==========================================
// 👑 管理者（Admin）用の本物ルート
// ==========================================

// 画面表示(GET)、ログイン処理(POST)、ログアウト処理(POST)をそれぞれ本物へ接続！
Route::get('/admin/login', [AdminLoginController::class, 'create']); // 👈 変更
Route::post('/admin/login', [AdminLoginController::class, 'store']); // 👈 変更
Route::post('/admin/logout', [AdminLoginController::class, 'destroy']); // 👈 変更

// テスト用の管理者向け仮ルート
Route::get('/admin/attendance/list', function () {
    return view('admin.attendance.index');
});
Route::get('/admin/attendance/detail/{id}', function () {
    return view('admin.attendance.detail');
});
Route::get('/admin/staff/list', function () {
    return view('admin.staff.index');
});
Route::get('/admin/staff/{id}', function () {
    return view('admin.staff.show');
});
Route::get('/admin/request/list', function () {
    return view('admin.request.index');
});
Route::get('/admin/request/approve/{id}', function () {
    return view('admin.request.approve');
});
