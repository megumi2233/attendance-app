<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'start_time' => $this->faker->dateTimeBetween('08:30:00', '09:00:00')->format('H:i:s'),
            'end_time' => $this->faker->dateTimeBetween('17:30:00', '18:30:00')->format('H:i:s'),
        ];
    }
}
