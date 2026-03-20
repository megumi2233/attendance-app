<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest; // 🌟 追加ポイント①：修正申請のモデルを呼び出す！

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // ① 【管理者】の作成（めぐみさんが書いた設計図をそのまま1回使う！）
        Admin::factory()->create();

        // ② 【一般ユーザー（テスト用）】の作成（ここで特別注文を出します！）
        $testUser = User::factory()->create([
            'name' => '一般テスト',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // 暗号化してパスワードを設定
        ]);

        // ③ 【モブ一般ユーザー】を10人大量生産！
        $mobUsers = User::factory(10)->create();

        // ④ テスト用ユーザーの【勤怠と休憩データ】をまとめて30日分作る魔法！
        Attendance::factory(30)->create([
            'user_id' => $testUser->id, // 全部テストユーザーの勤怠にする！
        ])->each(function ($attendance) {
            // 勤怠1日分が作られるたびに、それに紐づく「休憩データ」を1回分作る！
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);

            // 🌟 追加ポイント②：3回に1回くらいの確率で、テストユーザーの「修正申請」も作る！
            if (rand(1, 3) === 1) {
                StampCorrectionRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'date' => $attendance->date, // 日付は元の勤怠に合わせる！
                ]);
            }
        });
        
        // ⑤ ついでにモブユーザー10人にも、適当に5日分くらい勤怠を作っておく（見栄えのため！）
        foreach ($mobUsers as $mob) {
            Attendance::factory(5)->create([
                'user_id' => $mob->id,
            ])->each(function ($attendance) {
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                ]);

                // 🌟 追加ポイント③：モブユーザーにもランダムで申請を出させておく！（後で店長画面を見た時、見栄えが良くなります！）
                if (rand(1, 3) === 1) {
                    StampCorrectionRequest::factory()->create([
                        'attendance_id' => $attendance->id,
                        'date' => $attendance->date,
                    ]);
                }
            });
        }
    }
}
