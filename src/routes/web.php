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
use App\Http\Controllers\Admin\AdminStaffAttendanceController;
use App\Http\Controllers\Admin\AdminStampCorrectionRequestController;


// ==========================================
// 👤 一般ユーザー（Staff）用の本物ルート
// ==========================================

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [UserLoginController::class, 'create'])->name('login'); 
    Route::post('/login', [UserLoginController::class, 'store']); 
});

// 👇 🌟 変更ポイント①：「ログインさえしていればOK」なグループ
// （未認証で閉じ込められても、ログアウトだけはできるように逃げ道を作っておきます！）
Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserLoginController::class, 'destroy']); 
});

// 👇 🌟 変更ポイント②：最強の関所「verified」を追加したグループ！
// （ログインしていて、かつ「メール認証済み」の人だけが入れるVIPルームです！）
Route::middleware(['auth', 'verified'])->group(function () {
    
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
Route::get('/admin/attendance/{id}', [AdminAttendanceDetailController::class, 'show']);
Route::post('/admin/attendance/{id}', [AdminAttendanceDetailController::class, 'update']);
Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);
Route::get('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index']);
Route::get('/admin/attendance/staff/{id}/export', [AdminStaffAttendanceController::class, 'exportCsv']);


// ==========================================
// 🌟 難関！「申請一覧」と「承認画面」のルート
// ==========================================

Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'show']);
Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionRequestController::class, 'approve']);

Route::get('/stamp_correction_request/list', function () {
    
    // 区別1：店長（admin）ならそのまま通す
    if (Auth::guard('admin')->check()) {
        return app()->make(AdminStampCorrectionRequestController::class)->index();
    }
    
    // 区別2：一般ユーザー（web）の場合
    if (Auth::check()) {
        // 👇 🌟 変更ポイント③：URLを直接打ち込んで突破しようとする未認証ユーザーを弾き飛ばす関所！
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // 🌟 ここを Auth::user() ではなく $user に変更！
        if (!$user->hasVerifiedEmail()) {
            return redirect('/email/verify'); // 誘導画面へ強制送還！
        }
        return app()->make(App\Http\Controllers\StampCorrectionRequestController::class)->index();
    }

    // どちらの認証も通っていなければログイン画面へ
    return redirect('/login');
});
