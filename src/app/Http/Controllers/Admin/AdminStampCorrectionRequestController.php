<?php

namespace App\Http\Controllers\Admin;

use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminStampCorrectionRequestController extends AdminBaseController
{
    public function index()
    {
        return view('admin.stamp_correction_request.index');
    }

    public function show($id)
    {
        $correctionRequest = StampCorrectionRequest::with([
            'attendance.user',
            'stampCorrectionRequestBreakTimes'
        ])->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('correctionRequest'));
    }

    public function approve($id)
    {
        $correctionRequest = StampCorrectionRequest::with('stampCorrectionRequestBreakTimes')->findOrFail($id);

        $attendance = Attendance::findOrFail($correctionRequest->attendance_id);

        $attendance->update([
            'start_time' => $correctionRequest->start_time,
            'end_time' => $correctionRequest->end_time,
        ]);

        $attendance->breakTimes()->delete();

        foreach ($correctionRequest->stampCorrectionRequestBreakTimes as $breakRequest) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => $breakRequest->start_time,
                'end_time' => $breakRequest->end_time,
            ]);
        }

        $correctionRequest->update([
            'status' => '承認済み'
        ]);

        return back();
    }
}
