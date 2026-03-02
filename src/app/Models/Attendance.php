<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
    ];

    // ① User（ユーザー）との関係：「この勤怠は、1人のユーザーのものです（belongsTo）」
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ② BreakTime（休憩）との関係：「この勤怠には、複数の休憩があります（hasMany）」
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    // ③ StampCorrectionRequest（修正申請）との関係：「この勤怠には、複数の修正申請があります（hasMany）」
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }
}
