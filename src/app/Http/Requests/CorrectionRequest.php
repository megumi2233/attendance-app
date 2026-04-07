<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => ['required', 'date'],
            
            // 🌟 ここを修正！「退勤時間より前（before:end_time）」というルールを追加しました！
            'start_time' => ['required', 'date_format:H:i', 'before:end_time'],
            
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            
            'break_times.*.start_time' => ['nullable', 'date_format:H:i', 'after:start_time', 'before:end_time'],
            'break_times.*.end_time' => [
                'nullable', 
                'date_format:H:i', 
                'after:break_times.*.start_time', 
                'before:end_time'
            ],
            
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            
            // 🌟 ここを追加！テストケース通り「出勤時間が不適切な値です」というメッセージを設定しました！
            'start_time.before' => '出勤時間が不適切な値です',
            
            'end_time.required' => '退勤時間を入力してください', 
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_times.*.start_time.after' => '休憩時間が不適切な値です',
            'break_times.*.start_time.before' => '休憩時間が不適切な値です',
            'break_times.*.end_time.after' => '休憩時間が不適切な値です',
            'break_times.*.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',
            
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }
}
