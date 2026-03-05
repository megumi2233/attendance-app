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
