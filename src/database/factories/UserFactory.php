<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User; // 👈 忘れずに！

class UserFactory extends Factory
{
    // この工場で作るモデル（データ）はUserだよ、という宣言
    protected $model = User::class;

    public function definition()
    {
        return [
            // 日本人っぽい名前を自動生成！
            'name' => $this->faker->name(),
            // ランダムで安全なメールアドレスを自動生成！
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            // パスワードは全員共通で 'password' にしておく（ログインテストしやすいように！）
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
