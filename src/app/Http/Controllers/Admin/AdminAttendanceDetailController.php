<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
// 👇 🌟 めぐみさんが作ったルールの番人を呼び出す！
use App\Http\Requests\CorrectionRequest; 

class AdminAttendanceDetailController extends Controller
{
    // ==========================================
    // 🌟 ① 画面を表示するお仕事
    // ==========================================
    public function show($id)
    {
        // 勤怠データと、紐づく休憩データを一緒に探してくる
        $attendance = Attendance::with(['user', 'breakTimes', 'stampCorrectionRequests'])->findOrFail($id);

        // 👇 🌟 ここがポイント！「承認待ち（pending）」の申請があるかチェック！
        $hasPendingRequest = $attendance->stampCorrectionRequests()
                                        ->where('status', 'pending')
                                        ->exists();

        // 画面にデータと「承認待ちかどうか」の結果を渡す
        return view('admin.attendance.detail', compact('attendance', 'hasPendingRequest'));
    }

    // ==========================================
    // 🌟 ② 修正ボタンが押された時の「データ上書き」のお仕事
    // ==========================================
    public function update(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 1. 勤怠テーブルの出勤・退勤時間を直接上書きする！
        $attendance->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        // 2. 休憩時間の更新（一度古いものを消して、新しいものを登録する安全な方法）
        $attendance->breakTimes()->delete(); 

        if ($request->filled('break_times')) {
            foreach ($request->break_times as $breakTime) {
                if (!empty($breakTime['start_time']) && !empty($breakTime['end_time'])) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $breakTime['start_time'],
                        'end_time' => $breakTime['end_time'],
                    ]);
                }
            }
        }

        // 3. 備考（理由）を残すために、裏で「承認済み（approved）」の修正申請データを作っておく！
        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'approved', // 店長の直接修正なので最初から「承認済み」！
        ]);

        // 勤怠一覧画面に戻る
        return redirect('/admin/attendance/list')->with('success', '勤怠情報を直接修正しました！');
    }
}
