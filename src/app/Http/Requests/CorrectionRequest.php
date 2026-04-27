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
            // シンプルな基本ルールだけにします！
            'date' => ['bail', 'required', 'date'],
            'start_time' => ['bail', 'required', 'date_format:H:i'],
            'end_time' => ['bail', 'required', 'date_format:H:i'],
            'break_times.*.start_time' => ['bail', 'nullable', 'date_format:H:i'],
            'break_times.*.end_time' => ['bail', 'nullable', 'date_format:H:i'],
            'reason' => ['bail', 'required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'date.required' => '日付を入力してください',
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は255文字以内で入力してください',
        ];
    }

    // 🌟 どんなケースも逃さない最強のチェック機能（Adminと同じもの）
    public function withValidator($validator)
    {
        $start = $this->input('start_time');
        $end = $this->input('end_time');
        $breaks = $this->input('break_times', []);

        $validator->after(function ($validator) use ($start, $end, $breaks) {
            
            // 時間を「分」に変えて比べる魔法
            $toMin = function($time) {
                if (!$time) return null;
                $parts = explode(':', $time);
                if (count($parts) !== 2) return null;
                return (int)$parts[0] * 60 + (int)$parts[1];
            };

            $startMin = $toMin($start);
            $endMin = $toMin($end);

            // 1. 出勤・退勤のチェック
            if ($startMin !== null && $endMin !== null && $endMin < $startMin) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $prevEndMin = null;

            if (is_array($breaks)) {
                foreach ($breaks as $index => $break) {
                    $bStartMin = $toMin($break['start_time'] ?? null);
                    $bEndMin = $toMin($break['end_time'] ?? null);

                    // 2. 休憩【開始】のチェック
                    if ($bStartMin !== null) {
                        // 出勤より前、または出勤と同じはNG
                        if ($startMin !== null && $bStartMin <= $startMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩時間が不適切な値です');
                        }
                        // 退勤より後はNG
                        if ($endMin !== null && $bStartMin > $endMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩時間が不適切な値です');
                        }
                        // 前の休憩とかぶっている
                        if ($prevEndMin !== null && $bStartMin < $prevEndMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩の時間が前の休憩と重なっています');
                        }
                    }

                    // 3. 休憩【終了】のチェック
                    if ($bEndMin !== null) {
                        // 開始より前はNG（同じ時間は0分休憩でOK）
                        if ($bStartMin !== null && $bEndMin < $bStartMin) {
                            $validator->errors()->add("break_times.{$index}.end_time", '休憩時間が不適切な値です');
                        }
                        // 退勤より後はNG（退勤と同じ時間はOK）
                        if ($endMin !== null && $bEndMin > $endMin) {
                            $validator->errors()->add("break_times.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }

                    if ($bEndMin !== null) {
                        $prevEndMin = $bEndMin;
                    }
                }
            }
        });
    }
}
