<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    /**
     * 勤怠一覧画面（月切替）を表示する
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ① どの月を表示するか決める（デフォルトは「今月」）
        $targetMonth = $request->query('month', Carbon::now()->format('Y-m'));

        // ② 月の最初の日（1日）と、最後の日（月末）を計算する
        $firstDayOfMonth = Carbon::parse($targetMonth . '-01');
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        // ③ その月の勤怠データを全部取ってくる！（休憩データも一緒に！）
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$firstDayOfMonth->format('Y-m-d'), $lastDayOfMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        // ④ 画面でカレンダーを作りやすいようにデータを整理する
        $attendanceData = [];
        for ($date = $firstDayOfMonth->copy(); $date->lte($lastDayOfMonth); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $attendanceData[$dateString] = null;
        }

        foreach ($attendances as $attendance) {
            $dateString = $attendance->date;

            // --- 休憩時間の合計を計算 ---
            $totalBreakMinutes = 0;
            foreach ($attendance->breakTimes as $breakTime) {
                if ($breakTime->start_time && $breakTime->end_time) {
                    $start = Carbon::parse($breakTime->start_time);
                    $end = Carbon::parse($breakTime->end_time);
                    $totalBreakMinutes += $end->diffInMinutes($start);
                }
            }

            // --- 実労働時間の合計を計算 ---
            $totalWorkMinutes = 0;
            if ($attendance->start_time && $attendance->end_time) {
                $workStart = Carbon::parse($attendance->start_time);
                $workEnd = Carbon::parse($attendance->end_time);
                $totalWorkMinutes = $workEnd->diffInMinutes($workStart) - $totalBreakMinutes;
            }

            $formattedBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
            $formattedWorkTime = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

            $attendanceData[$dateString] = [
                'id' => $attendance->id,
                'start_time' => $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                'end_time' => $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                'break_time' => $totalBreakMinutes > 0 ? $formattedBreakTime : '',
                'work_time' => $totalWorkMinutes > 0 ? $formattedWorkTime : '',
            ];
        }

        // ⑤ 画面に渡す「前月」と「翌月」のURL用パラメータを作る
        $prevMonth = $firstDayOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $firstDayOfMonth->copy()->addMonth()->format('Y-m');
        $currentMonthDisplay = $firstDayOfMonth->format('Y/m');

        // ⑥ 画面（list.blade.php）にデータを渡して表示！
        return view('attendance.list', compact(
            'attendanceData',
            'currentMonthDisplay',
            'prevMonth',
            'nextMonth'
        ));
    }
}
