<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $status = 'off_duty';

        if ($attendance) {
            if ($attendance->end_time) {
                $status = 'done';
            } else {
                $currentBreak = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('end_time')
                    ->first();

                if ($currentBreak) {
                    $status = 'on_break';
                } else {
                    $status = 'working';
                }
            }
        }

        Carbon::setLocale('ja');
        $currentDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->format('H:i');

        return view('attendance.index', compact('status', 'currentDate', 'currentTime'));
    }

    public function startWork()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => Carbon::now()->toTimeString(),
            ]);
        }

        return redirect()->back();
    }

    public function endWork()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && !$attendance->end_time) {
            $attendance->update([
                'end_time' => Carbon::now()->toTimeString(),
            ]);
        }

        return redirect()->back();
    }

    public function startBreak()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && !$attendance->end_time) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now()->toTimeString(),
            ]);
        }

        return redirect()->back();
    }

    public function endBreak()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && !$attendance->end_time) {
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
