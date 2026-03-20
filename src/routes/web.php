<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // 🌟 追加ポイント①：誰がログインしているか確認するツール
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\UserLoginController; 
use App\Http\Controllers\Admin\AdminLoginController; 
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\StampCorrectionRequestController; 
use App\Http\Controllers\Admin\AdminAttendanceListController;


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

    // ※ ここにあった一般ユーザーの「申請一覧」は、管理者とURLが被るため、一番下に移動しました！
});


// ==========================================
// 👑 管理者（Admin）用の本物ルート
// ==========================================

Route::get('/admin/login', [AdminLoginController::class, 'create']); 
Route::post('/admin/login', [AdminLoginController::class, 'store']); 
Route::post('/admin/logout', [AdminLoginController::class, 'destroy']); 

Route::get('/admin/attendance/list', [AdminAttendanceListController::class, 'index']);


// テスト用の管理者向け仮ルート（残りの画面用）
Route::get('/admin/attendance/detail/{id}', function () {
    return view('admin.attendance.detail');
});
Route::get('/admin/staff/list', function () {
    return view('admin.staff.index');
});
Route::get('/admin/staff/{id}', function () {
    return view('admin.staff.show');
});


// ==========================================
// 🌟 難関！「申請一覧」と「承認画面」のルート
// ==========================================

// 👇 ① 管理者の「承認画面」（設計書PG13通り！）
Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
    return view('admin.stamp_correction_request.approve');
});

// 👇 ② 難関！「申請一覧」（設計書PG06・PG12通り！）
// 📝 要件：「一般ユーザーと同じパスを使用。認証ミドルウェアで区別」
Route::get('/stamp_correction_request/list', function () {
    
    // 🌟 区別1：もし店長（admin）の認証ミドルウェアを通っていたら…
    if (Auth::guard('admin')->check()) {
        // 管理者用の本物頭脳（AdminStampCorrectionRequestController）を呼び出す！
        return app()->call([App\Http\Controllers\Admin\AdminStampCorrectionRequestController::class, 'index']);
    }
    
    // 🌟 区別2：もし一般ユーザー（web）の認証ミドルウェアを通っていたら…
    if (Auth::check()) {
        // 一般ユーザー用の本物頭脳（StampCorrectionRequestController）を呼び出す！
        return app()->call([App\Http\Controllers\StampCorrectionRequestController::class, 'index']);
    }

    // どちらの認証も通っていなければ、ログイン画面へ弾く！
    return redirect('/login');
});
