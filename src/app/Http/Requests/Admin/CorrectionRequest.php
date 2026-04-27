<?php

namespace App\Http\Requests\Admin; // 🌟 ここが超重要！ Admin が必要です！

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
            // 🌟 ややこしいルールは全削除！時間チェックは下の魔法で行います
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

    // 🌟 ここからが最強の魔法（プロフェッショナルな時間チェック）です！
    public function withValidator($validator)
    {
        $start = $this->input('start_time');
        $end = $this->input('end_time');
        $breaks = $this->input('break_times', []);

        $validator->after(function ($validator) use ($start, $end, $breaks) {
            
            // 時間を「分」という数字に変換する魔法（絶対に計算を間違えなくなります）
            $toMin = function($time) {
                if (!$time) return null;
                $parts = explode(':', $time);
                if (count($parts) !== 2) return null;
                return (int)$parts[0] * 60 + (int)$parts[1];
            };

            $startMin = $toMin($start);
            $endMin = $toMin($end);

            // 🌟 1. 出勤・退勤のチェック
            if ($startMin !== null && $endMin !== null && $endMin < $startMin) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $prevEndMin = null;

            if (is_array($breaks)) {
                foreach ($breaks as $index => $break) {
                    $bStartMin = $toMin($break['start_time'] ?? null);
                    $bEndMin = $toMin($break['end_time'] ?? null);

                    // 🌟 2. 休憩【開始】のチェック
                    if ($bStartMin !== null) {
                        // ① 出勤時間と同じ、または前ならエラー（READMEの「出勤より後のみ許可」を実現）
                        if ($startMin !== null && $bStartMin <= $startMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩時間が不適切な値です');
                        }
                        // ② 退勤時間より後ならエラー
                        if ($endMin !== null && $bStartMin > $endMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩時間が不適切な値です');
                        }
                        // ③ 前の休憩とかぶっているならエラー
                        if ($prevEndMin !== null && $bStartMin < $prevEndMin) {
                            $validator->errors()->add("break_times.{$index}.start_time", '休憩の時間が前の休憩と重なっています');
                        }
                    }

                    // 🌟 3. 休憩【終了】のチェック
                    if ($bEndMin !== null) {
                        // ① 自分の開始より前ならエラー（同じ時間は0分休憩としてスルー！）
                        if ($bStartMin !== null && $bEndMin < $bStartMin) {
                            $validator->errors()->add("break_times.{$index}.end_time", '休憩時間が不適切な値です');
                        }
                        // ② 退勤より後ならエラー（退勤と同じ時間はスルー！）
                        if ($endMin !== null && $bEndMin > $endMin) {
                            $validator->errors()->add("break_times.{$index}.end_time", '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }

                    // 次の休憩のチェックのために、今回の終了時間を覚えておく
                    if ($bEndMin !== null) {
                        $prevEndMin = $bEndMin;
                    }
                }
            }
        });
    }
}
