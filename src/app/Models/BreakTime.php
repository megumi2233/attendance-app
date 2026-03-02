<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
    ];

    // Attendance（勤怠）との関係：「この休憩は、1つの勤怠データに属しています（belongsTo）」
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
