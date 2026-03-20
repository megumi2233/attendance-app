<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\UserLoginController; 
use App\Http\Controllers\Admin\AdminLoginController; 
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController; // 詳細画面の頭脳
// 👇 🌟 追加ポイント1：申請一覧の頭脳を呼び出す！
use App\Http\Controllers\StampCorrectionRequestController; 


// ==========================================
// 👤 一般ユーザー（Staff）用の本物ルート
// ==========================================

// 未ログインの人だけが入れるルート（登録とログイン）
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [UserLoginController::class, 'create'])->name('login'); 
    Route::post('/login', [UserLoginController::class, 'store']); 
});

// ログイン済みの一般ユーザーだけが入れるルート
Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserLoginController::class, 'destroy']); 
    
    // 👇 / にアクセスしたら打刻画面へ自動移動させる
    Route::get('/', function () {
        return redirect('/attendance');
    });

    // =======================================
    // 🌟 本物の打刻機能のルート！
    // =======================================
    Route::get('/attendance', [AttendanceController::class, 'index']); // 画面表示
    Route::post('/attendance/start', [AttendanceController::class, 'startWork']); // 出勤ボタン
    Route::post('/attendance/end', [AttendanceController::class, 'endWork']); // 退勤ボタン
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak']); // 休憩入ボタン
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak']); // 休憩戻ボタン

    // =======================================
    // 🌟 本物の「勤怠一覧」のルート！
    // =======================================
    Route::get('/attendance/list', [AttendanceListController::class, 'index']);

    // =======================================
    // 🌟 本物の「勤怠詳細」のルート！
    // =======================================
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show']);
    Route::post('/attendance/detail/{id}', [AttendanceDetailController::class, 'store']);

    // =======================================
    // 🌟 本物の「申請一覧」のルート！（書き換え完了！）
    // =======================================
    // 👇 🌟 追加ポイント2：仮ルートを消して、設計書通りの本物ルートに繋ぎました！
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index']);
});


// ==========================================
// 👑 管理者（Admin）用の本物ルート
// ==========================================

// 画面表示(GET)、ログイン処理(POST)、ログアウト処理(POST)をそれぞれ本物へ接続！
Route::get('/admin/login', [AdminLoginController::class, 'create']); 
Route::post('/admin/login', [AdminLoginController::class, 'store']); 
Route::post('/admin/logout', [AdminLoginController::class, 'destroy']); 

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
