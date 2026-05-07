<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use App\Http\Requests\Admin\CorrectionRequest;

class AdminAttendanceDetailController extends AdminBaseController
{
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes', 'stampCorrectionRequests'])->findOrFail($id);

        $hasPendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', '承認待ち')
            ->exists();

        return view('admin.attendance.detail', compact('attendance', 'hasPendingRequest'));
    }

    public function update(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

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

        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'approved',
        ]);

        return redirect('/admin/attendance/list')->with('success', '勤怠情報を直接修正しました！');
    }
}
