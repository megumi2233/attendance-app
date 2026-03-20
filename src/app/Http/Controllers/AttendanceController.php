<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // 👈 時間を扱うための魔法の杖！

class AttendanceController extends Controller
{
    /**
     * 打刻画面を表示する ＆ 今のステータスを判定する！
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d'); // 今日の日付

        // ① 今日の自分の打刻データを探す
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        // ② 今の自分の状態（ステータス）を判定する！
        $status = 'off_duty'; // 初期値は「勤務外」

        if ($attendance) {
            if ($attendance->end_time) {
                // すでに退勤時間が記録されていれば「退勤済」
                $status = 'done';
            } else {
                // 退勤時間がなく、現在「休憩中」かどうかを調べる
                $currentBreak = BreakTime::where('attendance_id', $attendance->id)
                                         ->whereNull('end_time')
                                         ->first();
                if ($currentBreak) {
                    $status = 'on_break'; // 休憩中
                } else {
                    $status = 'working';  // 出勤中（仕事中）
                }
            }
        }

        // ③ 現在の日付と時刻を作って画面に渡す
        Carbon::setLocale('ja'); // 曜日を日本語にするおまじない
        $currentDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->format('H:i');

        return view('attendance.index', compact('status', 'currentDate', 'currentTime'));
    }

    /**
     * 「出勤」ボタンが押された時の処理
     */
    public function startWork()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        // まだ出勤していない場合のみ記録する（1日1回の制限！）
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => Carbon::now()->toTimeString(), // 今の時間を記録！
            ]);
        }
        return redirect()->back(); // 元の画面に戻る
    }

    /**
     * 「退勤」ボタンが押された時の処理
     */
    public function endWork()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        // 出勤していて、まだ退勤していない場合のみ記録する
        if ($attendance && !$attendance->end_time) {
            $attendance->update([
                'end_time' => Carbon::now()->toTimeString(),
            ]);
        }
        return redirect()->back();
    }

    /**
     * 「休憩入」ボタンが押された時の処理
     */
    public function startBreak()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance && !$attendance->end_time) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now()->toTimeString(),
            ]);
        }
        return redirect()->back();
    }

    /**
     * 「休憩戻」ボタンが押された時の処理
     */
    public function endBreak()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance && !$attendance->end_time) {
            // 今まさに休憩中のデータ（end_timeが空っぽ）を探す
            $currentBreak = BreakTime::where('attendance_id', $attendance->id)
                                     ->whereNull('end_time')
                                     ->first();
            if ($currentBreak) {
                $currentBreak->update([
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            }
        }
        return redirect()->back();
    }
}
