<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'date',
        'start_time',
        'end_time',
        'reason',
        'status',
    ];

    // Attendance（勤怠）との関係：「この修正申請は、1つの勤怠データに対するものです（belongsTo）」
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
