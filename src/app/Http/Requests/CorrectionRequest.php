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
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after_or_equal:start_time'],
            'break_times.*.start_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'break_times.*.end_time' => [
                'nullable', 
                'date_format:H:i', 
                'after_or_equal:break_times.*.start_time', 
                'before:end_time'
            ],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'date.required' => '日付を入力してください',    
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください', 
            'end_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_times.*.start_time.after' => '休憩時間が不適切な値です',
            'break_times.*.start_time.before' => '休憩時間が不適切な値です',
            'break_times.*.end_time.after_or_equal' => '休憩時間が不適切な値です',
            'break_times.*.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }
}
