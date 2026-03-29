<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\UserLoginController; 
use App\Http\Controllers\Admin\AdminLoginController; 
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\StampCorrectionRequestController; 
use App\Http\Controllers\Admin\AdminAttendanceListController;
use App\Http\Controllers\Admin\AdminAttendanceDetailController;
use App\Http\Controllers\Admin\AdminStaffController;
// 👇 🌟 追加！スタッフ別勤怠用の頭脳を呼び出す！
use App\Http\Controllers\Admin\AdminStaffAttendanceController;


// ==========================================
// 👤 一般ユーザー（Staff）用の本物ルート
// ==========================================

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [UserLoginController::class, 'create'])->name('login'); 
    Route::post('/login', [UserLoginController::class, 'store']); 
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserLoginController::class, 'destroy']); 
    
    Route::get('/', function () {
        return redirect('/attendance');
    });

    Route::get('/attendance', [AttendanceController::class, 'index']); 
    Route::post('/attendance/start', [AttendanceController::class, 'startWork']); 
    Route::post('/attendance/end', [AttendanceController::class, 'endWork']); 
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak']); 
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak']); 

    Route::get('/attendance/list', [AttendanceListController::class, 'index']);
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show']);
    Route::post('/attendance/detail/{id}', [AttendanceDetailController::class, 'store']);

});


// ==========================================
// 👑 管理者（Admin）用の本物ルート
// ==========================================

Route::get('/admin/login', [AdminLoginController::class, 'create']); 
Route::post('/admin/login', [AdminLoginController::class, 'store']); 
Route::post('/admin/logout', [AdminLoginController::class, 'destroy']); 

Route::get('/admin/attendance/list', [AdminAttendanceListController::class, 'index']);

// =======================================
// 管理者の「勤怠詳細・直接修正」ルート
// =======================================
Route::get('/admin/attendance/detail/{id}', [AdminAttendanceDetailController::class, 'show']);
Route::post('/admin/attendance/detail/{id}', [AdminAttendanceDetailController::class, 'update']);


// =======================================
// 管理者の「スタッフ一覧」ルート
// =======================================
Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);

// 👇 🌟 ここを書き換えました！仮ルートから本物へ進化！
// ==========================================
// 👤 管理者の「スタッフ別勤怠詳細・CSV出力」ルート
// ==========================================
// ① 画面を表示するルート（設計書どおりのURLに修正！）
Route::get('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index']);

// ② CSVをダウンロードする専用ルート（新しく追加！）
Route::get('/admin/attendance/staff/{id}/export', [AdminStaffAttendanceController::class, 'exportCsv']);


// ==========================================
// 🌟 難関！「申請一覧」と「承認画面」のルート
// ==========================================

// ① 管理者の「承認画面」（設計書PG13通り！）
Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
    return view('admin.stamp_correction_request.approve');
});

// ② 難関！「申請一覧」（設計書PG06・PG12通り！）
// 📝 要件：「一般ユーザーと同じパスを使用。認証ミドルウェアで区別」
Route::get('/stamp_correction_request/list', function () {
    
    // 区別1：もし店長（admin）の認証ミドルウェアを通っていたら…
    if (Auth::guard('admin')->check()) {
        // 👇 🌟 修正！ コントローラーを作って(make)から、index()を呼び出す！
        return app()->make(App\Http\Controllers\Admin\AdminStampCorrectionRequestController::class)->index();
    }
    
    // 区別2：もし一般ユーザー（web）の認証ミドルウェアを通っていたら…
    if (Auth::check()) {
        // 👇 🌟 修正！ こっちも作って(make)から、index()を呼び出す！
        return app()->make(App\Http\Controllers\StampCorrectionRequestController::class)->index();
    }

    // どちらの認証も通っていなければ、ログイン画面へ弾く！
    return redirect('/login');
});
