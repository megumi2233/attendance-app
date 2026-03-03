<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(), 
            'start_time' => $this->faker->dateTimeBetween('12:00:00', '12:15:00')->format('H:i:s'), 
            'end_time' => $this->faker->dateTimeBetween('12:45:00', '13:15:00')->format('H:i:s'),
        ];
    }
}
