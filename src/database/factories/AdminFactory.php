<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ];
    }
}
