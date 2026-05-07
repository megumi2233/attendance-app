<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestBreakTime;
use App\Http\Requests\CorrectionRequest;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

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

    public function store(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $correctionRequest = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => '承認待ち',
        ]);

        if ($request->has('break_times')) {
            foreach ($request->break_times as $breakTime) {
                if (!empty($breakTime['start_time']) && !empty($breakTime['end_time'])) {
                    StampCorrectionRequestBreakTime::create([
                        'stamp_correction_request_id' => $correctionRequest->id,
                        'start_time' => $breakTime['start_time'],
                        'end_time' => $breakTime['end_time'],
                    ]);
                }
            }
        }

        return back()->with('success', '修正申請を送信しました');
    }
}
