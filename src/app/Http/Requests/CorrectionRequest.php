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
            
            // 🌟 ここを 'required'（必須）に戻しました！
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            
            // ※休憩は「追加枠（空っぽ）」が送られてくることがあるので nullable のままで正解です！
            'break_times.*.start_time' => ['nullable', 'date_format:H:i', 'before:end_time'],
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
            
            // 🌟 必須に戻したので、未入力の時のメッセージも追加！
            'end_time.required' => '退勤時間を入力してください', 
            
            'end_time.after' => '出勤時間が不適切な値です',
            'break_times.*.start_time.before' => '休憩時間が不適切な値です',
            'break_times.*.end_time.after' => '休憩時間が不適切な値です',
            'break_times.*.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }
}
