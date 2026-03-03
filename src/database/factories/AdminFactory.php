<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ];
    }
}
