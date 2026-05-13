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

    public function withValidator($validator)
    {
        $start = $this->input('start_time');
        $end = $this->input('end_time');
        $breaks = $this->input('break_times', []);

        $validator->after(function ($validator) use ($start, $end, $breaks) {
            $toMin = function ($time) {
                if (!$time) {
                    return null;
                }
                $parts = explode(':', $time);
                if (count($parts) !== 2) {
                    return null;
                }
                return (int)$parts[0] * 60 + (int)$parts[1];
            };

            $startMin = $toMin($start);
            $endMin = $toMin($end);

            if ($startMin !== null && $endMin !== null && $endMin < $startMin) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $prevEndMin = null;

            if (is_array($breaks)) {
                foreach ($breaks as $index => $break) {
                    $bStartMin = $toMin($break['start_time'] ?? null);
                    $bEndMin = $toMin($break['end_time'] ?? null);

                    if ($bStartMin !== null) {
                        if ($startMin !== null && $bStartMin < $startMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩時間が不適切な値です');
                        }
                        if ($endMin !== null && $bStartMin > $endMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩時間が不適切な値です');
                        }
                        if ($prevEndMin !== null && $bStartMin < $prevEndMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩の時間が前の休憩と重なっています');
                        }
                    }

                    if ($bEndMin !== null) {
                        if ($bStartMin !== null && $bEndMin < $bStartMin) {
                            $validator->errors()->add("break_times.{$index}.end_time", '休憩時間が不適切な値です');
                        }
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
