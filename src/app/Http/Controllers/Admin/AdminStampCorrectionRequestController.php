<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
// 👇 🌟 忘れずに！本物のデータを操作するためにモデルを呼び出す！
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminStampCorrectionRequestController extends Controller
{
    // 一覧画面を開く
    public function index()
    {
        return view('admin.stamp_correction_request.index');
    }

    // 承認画面を開く
    public function show($id)
    {
        $correctionRequest = StampCorrectionRequest::with([
            'attendance.user',
            'stampCorrectionRequestBreakTimes'
        ])->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('correctionRequest'));
    }

    // 👇 🌟 最終奥義！「承認」ボタンが押された時のデータ上書き処理（POST）
    public function approve($id)
    {
        // 1. 申請されたデータ（修正案）と、その休憩データを持ってくる
        $correctionRequest = StampCorrectionRequest::with('stampCorrectionRequestBreakTimes')->findOrFail($id);

        // 2. 上書きターゲットとなる「本物の勤怠データ」を持ってくる
        $attendance = Attendance::findOrFail($correctionRequest->attendance_id);

        // 3. 【奥義その1】本物の出勤・退勤時間を、申請された時間に上書き保存！
        $attendance->update([
            'start_time' => $correctionRequest->start_time,
            'end_time' => $correctionRequest->end_time,
        ]);

        // 4. 【奥義その2】今までの古い休憩データを一旦すべて削除（リセット）する！
        $attendance->breakTimes()->delete();

        // 5. 【奥義その3】申請された「新しい休憩データ」を、本物として一から登録し直す！
        foreach ($correctionRequest->stampCorrectionRequestBreakTimes as $breakRequest) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => $breakRequest->start_time,
                'end_time' => $breakRequest->end_time,
            ]);
        }

        // 6. 申請自体のステータスを「承認済み」に変更して、この申請のお役目終了！
        $correctionRequest->update([
            'status' => '承認済み'
        ]);

        // 7. すべて終わったら、申請一覧画面へ胸を張って戻る！
        return back();
    }
}
