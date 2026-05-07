<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListController extends AdminBaseController
{
    public function index(Request $request)
    {
        $dateParam = $request->query('date');
        $displayDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();

        $prevDate = $displayDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $displayDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('date', $displayDate->format('Y-m-d'))
            ->get();

        return view('admin.attendance.index', compact('displayDate', 'prevDate', 'nextDate', 'attendances'));
    }
}
