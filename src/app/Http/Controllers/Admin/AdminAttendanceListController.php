<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListController extends Controller
{
    public function index(Request $request)
    {
        // 🌟 1. URLの合言葉（?date=〇〇）を受け取る！なければ「今日」を基準にする
        $dateParam = $request->query('date');
        $displayDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();

        // 🌟 2. 「前日」と「翌日」の日付を計算しておく
        $prevDate = $displayDate->copy()->subDay();
        $nextDate = $displayDate->copy()->addDay();

        // 🌟 3. その日（$displayDate）の「全ユーザーの勤怠データ」をデータベースから探してくる！
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('date', $displayDate->format('Y-m-d'))
            ->get();

        // 🌟 4. 計算した日付とデータを、Blade（画面）に渡す！
        return view('admin.attendance.index', compact('displayDate', 'prevDate', 'nextDate', 'attendances'));
    }
}
