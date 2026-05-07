<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; 

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function getRestTime()
    {
        $totalRestSeconds = 0;

        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->start_time && $breakTime->end_time) {
                $start = Carbon::parse($breakTime->start_time);
                $end = Carbon::parse($breakTime->end_time);
                $totalRestSeconds += $end->diffInSeconds($start);
            }
        }

        $hours = floor($totalRestSeconds / 3600);
        $minutes = floor(($totalRestSeconds % 3600) / 60);

        return sprintf("%d:%02d", $hours, $minutes);
    }

    public function getWorkTime()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        $totalWorkSeconds = $end->diffInSeconds($start);

        $totalRestSeconds = 0;
        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->start_time && $breakTime->end_time) {
                $restStart = Carbon::parse($breakTime->start_time);
                $restEnd = Carbon::parse($breakTime->end_time);
                $totalRestSeconds += $restEnd->diffInSeconds($restStart);
            }
        }

        $actualWorkSeconds = $totalWorkSeconds - $totalRestSeconds;

        $hours = floor($actualWorkSeconds / 3600);
        $minutes = floor(($actualWorkSeconds % 3600) / 60);

        return sprintf("%d:%02d", $hours, $minutes);
    }
}
