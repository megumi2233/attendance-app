<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequestBreakTime extends Model
{
    use HasFactory;

    // 👇 これが「保存していいよ！」という許可リストです！
    protected $fillable = [
        'stamp_correction_request_id',
        'start_time',
        'end_time',
    ];
}
