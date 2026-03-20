<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestBreakTime; // 🌟 休憩の修正用モデルを追加！
use App\Http\Requests\CorrectionRequest; // 🌟 完璧に鍛え上げた門番を呼ぶ！
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    // ① 画面を表示するアクション（フェーズ1で作ったもの）
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

        // すでに承認待ちの申請があるかチェック
        $is_pending = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->exists();

        $date = Carbon::parse($attendance->date);
        $year = $date->format('Y年');
        $monthDay = $date->format('n月j日');

        $startTime = $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '';
        $endTime = $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '';

        return view('attendance.detail', compact(
            'attendance',
            'is_pending',
            'year',
            'monthDay',
            'startTime',
            'endTime'
        ));
    }

    // ② 【NEW】修正申請を保存するアクション！
    // 引数に「CorrectionRequest $request」と書くことで、門番が自動でチェックしてくれます！
    public function store(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 1. まず、大元の「修正申請」を保存する！
        $correctionRequest = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => '承認待ち', // 初期状態は必ず「承認待ち」
        ]);

        // 2. 次に、画面から送られてきた「休憩」のデータをループして保存する！
        if ($request->has('break_times')) {
            foreach ($request->break_times as $breakTime) {
                // 開始時間と終了時間が両方とも入力されている場合のみ保存する（空枠は無視！）
                if (!empty($breakTime['start_time']) && !empty($breakTime['end_time'])) {
                    StampCorrectionRequestBreakTime::create([
                        'stamp_correction_request_id' => $correctionRequest->id,
                        'start_time' => $breakTime['start_time'],
                        'end_time' => $breakTime['end_time'],
                    ]);
                }
            }
        }

        // 3. 保存が終わったら、元の詳細画面に戻る！
        return back()->with('success', '修正申請を送信しました');
    }
}
