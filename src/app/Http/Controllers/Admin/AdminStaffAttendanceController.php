<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffAttendanceController extends Controller
{
    // ==========================================
    // 🌟 指定されたスタッフの「月次勤怠一覧」を表示するお仕事
    // ==========================================
    public function index(Request $request, $id)
    {
        // ① 誰の勤怠を見るか特定する！（URLから送られてきた $id を使ってユーザーを探す）
        $user = User::findOrFail($id);

        // ② どの月を表示するか決める（デフォルトは「今月」）
        $targetMonth = $request->query('month', Carbon::now()->format('Y-m'));

        // ③ 月の最初の日と最後の日を計算する
        $firstDayOfMonth = Carbon::parse($targetMonth . '-01');
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        // ④ その人の、その月の勤怠データを全部取ってくる！（休憩データも一緒に！）
        $attendances = Attendance::with('breakTimes')
            // 👇 🌟 ここが一般ユーザーと違うところ！ログイン中の自分ではなく、「$user->id」を指定！
            ->where('user_id', $user->id)
            ->whereBetween('date', [$firstDayOfMonth->format('Y-m-d'), $lastDayOfMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        // ⑤ 画面でカレンダーを作りやすいようにデータを整理する（一般ユーザーと全く同じ魔法！）
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

            // --- 時間のフォーマット（H:i） ---
            $formattedBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
            $formattedWorkTime = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

            $attendanceData[$dateString] = [
                'id' => $attendance->id, // 詳細リンク用に必要
                'start_time' => $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                'end_time' => $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                'break_time' => $totalBreakMinutes > 0 ? $formattedBreakTime : '',
                'work_time' => $totalWorkMinutes > 0 ? $formattedWorkTime : '',
            ];
        }

        // ⑥ 画面に渡す「前月」と「翌月」のURL用パラメータを作る
        $prevMonth = $firstDayOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $firstDayOfMonth->copy()->addMonth()->format('Y-m');
        $currentMonthDisplay = $firstDayOfMonth->format('Y/m');

        // ⑦ 画面（show.blade.php）にデータを渡して表示！
        return view('admin.staff.show', compact(
            'user',
            'attendanceData',
            'currentMonthDisplay',
            'prevMonth',
            'nextMonth',
            'targetMonth' // 👈 🌟 これを追加！
        ));
    }

    // ==========================================
    // 🌟 CSVを出力（ダウンロード）するお仕事
    // ==========================================
    public function exportCsv(Request $request, $id)
    {
        // ① 誰の、いつのデータか特定する（indexと全く同じ！）
        $user = User::findOrFail($id);
        $targetMonth = $request->query('month', Carbon::now()->format('Y-m'));
        $firstDayOfMonth = Carbon::parse($targetMonth . '-01');
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        // ② 勤怠データを取得（indexと全く同じ！）
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$firstDayOfMonth->format('Y-m-d'), $lastDayOfMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        // ③ CSVのファイル名を作る（例: attendance_2023-06_西怜奈.csv）
        $fileName = 'attendance_' . $targetMonth . '_' . $user->name . '.csv';

        // ④ CSVに書き込むための「行（リスト）」を準備する
        $csvData = [];
        
        // 1行目（見出し）をセット！
        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計'];

        // 1日〜月末までのループでデータを詰めていく！
        for ($date = $firstDayOfMonth->copy(); $date->lte($lastDayOfMonth); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            
            // その日のデータが $attendances の中にあるか探す
            $attendance = $attendances->firstWhere('date', $dateString);

            // 日付の文字を作る（例: 06/01(木) ）
            $displayDate = $date->format('m/d') . '(' . $date->isoFormat('ddd') . ')';

            if ($attendance) {
                // 休憩と実働の計算（indexと全く同じ！）
                $totalBreakMinutes = 0;
                foreach ($attendance->breakTimes as $breakTime) {
                    if ($breakTime->start_time && $breakTime->end_time) {
                        $start = Carbon::parse($breakTime->start_time);
                        $end = Carbon::parse($breakTime->end_time);
                        $totalBreakMinutes += $end->diffInMinutes($start);
                    }
                }

                $totalWorkMinutes = 0;
                if ($attendance->start_time && $attendance->end_time) {
                    $workStart = Carbon::parse($attendance->start_time);
                    $workEnd = Carbon::parse($attendance->end_time);
                    $totalWorkMinutes = $workEnd->diffInMinutes($workStart) - $totalBreakMinutes;
                }

                $formattedBreakTime = $totalBreakMinutes > 0 ? sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60) : '';
                $formattedWorkTime = $totalWorkMinutes > 0 ? sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60) : '';

                // データがある日は、時間をセット！
                $csvData[] = [
                    $displayDate,
                    $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                    $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                    $formattedBreakTime,
                    $formattedWorkTime,
                ];
            } else {
                // データがない日（お休み）は、日付以外を空欄にしてセット！
                $csvData[] = [
                    $displayDate, '', '', '', ''
                ];
            }
        }

        // ⑤ 最後に、作ったデータをCSVファイルとしてダウンロードさせる魔法！
        return response()->streamDownload(function () use ($csvData) {
            $stream = fopen('php://output', 'w');
            
            // 👇 🌟 ここが超重要！「BOM（ボム）」という魔法の粉を振りかけて、Excelで開いた時の文字化けを防ぐ！
            fwrite($stream, "\xEF\xBB\xBF");
            
            foreach ($csvData as $row) {
                fputcsv($stream, $row);
            }
            fclose($stream);
        }, $fileName);
    }
}
