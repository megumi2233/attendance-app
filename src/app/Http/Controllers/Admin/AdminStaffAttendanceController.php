<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffAttendanceController extends AdminBaseController
{
    public function index(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $targetMonth = $request->query('month', Carbon::now()->format('Y-m'));

        $firstDayOfMonth = Carbon::parse($targetMonth . '-01');
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$firstDayOfMonth->format('Y-m-d'), $lastDayOfMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $attendanceData = [];
        for ($date = $firstDayOfMonth->copy(); $date->lte($lastDayOfMonth); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $attendanceData[$dateString] = null;
        }

        foreach ($attendances as $attendance) {
            $dateString = $attendance->date;

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

        $prevMonth = $firstDayOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $firstDayOfMonth->copy()->addMonth()->format('Y-m');
        $currentMonthDisplay = $firstDayOfMonth->format('Y/m');

        return view('admin.staff.show', compact(
            'user',
            'attendanceData',
            'currentMonthDisplay',
            'prevMonth',
            'nextMonth',
            'targetMonth'
        ));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $targetMonth = $request->query('month', Carbon::now()->format('Y-m'));
        $firstDayOfMonth = Carbon::parse($targetMonth . '-01');
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$firstDayOfMonth->format('Y-m-d'), $lastDayOfMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $fileName = 'attendance_' . $targetMonth . '_' . $user->name . '.csv';

        $csvData = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計'];

        for ($date = $firstDayOfMonth->copy(); $date->lte($lastDayOfMonth); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $attendance = $attendances->firstWhere('date', $dateString);
            $displayDate = $date->format('m/d') . '(' . $date->isoFormat('ddd') . ')';

            if ($attendance) {
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

                $csvData[] = [
                    $displayDate,
                    $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                    $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                    $formattedBreakTime,
                    $formattedWorkTime,
                ];
            } else {
                $csvData[] = [
                    $displayDate, '', '', '', ''
                ];
            }
        }

        return response()->streamDownload(function () use ($csvData) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            foreach ($csvData as $row) {
                fputcsv($stream, $row);
            }
            fclose($stream);
        }, $fileName);
    }
}
