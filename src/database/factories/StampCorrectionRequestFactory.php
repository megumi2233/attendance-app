<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class StampCorrectionRequestFactory extends Factory
{
    
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'start_time' => $this->faker->dateTimeBetween('08:30:00', '09:00:00')->format('H:i:s'),
            'end_time' => $this->faker->dateTimeBetween('17:30:00', '18:30:00')->format('H:i:s'),
            // 👇 🌟 めぐみさんのアイデアを追加！
            'reason' => $this->faker->randomElement(['電車遅延のため', '打刻忘れのため', '体調不良のため早退', 'システムエラーのため']),
            'status' => $this->faker->randomElement(['承認待ち', '承認済み']),
        ];
    }
}
