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

    // (いま書かれている public function stampCorrectionRequests() {...} の下に追加！)

    // 🌟 特技①：休憩時間の合計を計算する！
    public function getRestTime()
    {
        $totalRestSeconds = 0; // 最初は0秒
        
        // 紐づいている休憩データを全部ループして、かかった秒数を足していく
        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->start_time && $breakTime->end_time) {
                $start = \Carbon\Carbon::parse($breakTime->start_time);
                $end = \Carbon\Carbon::parse($breakTime->end_time);
                $totalRestSeconds += $end->diffInSeconds($start); // 差分（秒）を足す！
            }
        }
        
        // 秒を「〇:〇〇」の形式に直す
        $hours = floor($totalRestSeconds / 3600);
        $minutes = floor(($totalRestSeconds % 3600) / 60);
        return sprintf("%d:%02d", $hours, $minutes);
    }

    // 🌟 特技②：実働時間（出勤〜退勤 - 休憩）を計算する！
    public function getWorkTime()
    {
        // まだ退勤していない場合は計算できないので空っぽにする
        if (!$this->start_time || !$this->end_time) {
            return ''; 
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        // 出勤から退勤までのトータル秒数
        $totalWorkSeconds = $end->diffInSeconds($start);
        
        // 休憩時間の合計（秒）をもう一度計算
        $totalRestSeconds = 0;
        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->start_time && $breakTime->end_time) {
                $restStart = \Carbon\Carbon::parse($breakTime->start_time);
                $restEnd = \Carbon\Carbon::parse($breakTime->end_time);
                $totalRestSeconds += $restEnd->diffInSeconds($restStart);
            }
        }

        // 実際の労働時間（秒） ＝ トータル秒数 － 休憩の秒数
        $actualWorkSeconds = $totalWorkSeconds - $totalRestSeconds;

        // 秒を「〇:〇〇」の形式に直す
        $hours = floor($actualWorkSeconds / 3600);
        $minutes = floor(($actualWorkSeconds % 3600) / 60);
        return sprintf("%d:%02d", $hours, $minutes);
    }
} // 👈 Attendanceクラスの一番最後の閉じカッコ
