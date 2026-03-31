<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestBreakTime;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 👇 🌟 追加！ダミーデータを作る「Faker職人さん」をここで正式に召喚します！
        $faker = \Faker\Factory::create();

        // ①【管理者】の作成
        Admin::factory()->create();

        // ②【一般ユーザー（テスト用）】の作成
        $testUser = User::factory()->create([
            'name' => '一般テスト',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // ③【モブ一般ユーザー】を10人大量生産！
        $mobUsers = User::factory(10)->create();

        // ④ テスト用ユーザーの【勤怠と休憩データ】をまとめて30日分作る！
        Attendance::factory(30)->create([
            'user_id' => $testUser->id,
        // 👇 🌟 修正！ use ($faker) をつけて、職人さんをこのループ（箱）の中に入れます！
        ])->each(function ($attendance) use ($faker) {
            
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);

            // 🌟 もし修正申請を作るなら…
            if (rand(1, 3) === 1) {
                $request = StampCorrectionRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'date' => $attendance->date,
                ]);

                // 👇 🌟 修正！ fake() ではなく、さっき呼んだ $faker を使います！
                StampCorrectionRequestBreakTime::create([
                    'stamp_correction_request_id' => $request->id,
                    'start_time' => $faker->dateTimeBetween('12:00:00', '12:15:00')->format('H:i:s'),
                    'end_time' => $faker->dateTimeBetween('12:45:00', '13:15:00')->format('H:i:s'),
                ]);
            }
        });

        // ⑤ モブユーザー10人にも、適当に5日分くらい勤怠を作っておく
        foreach ($mobUsers as $mob) {
            Attendance::factory(5)->create([
                'user_id' => $mob->id,
            // 👇 🌟 修正！ ここでも use ($faker) をつけて職人さんを入れます！
            ])->each(function ($attendance) use ($faker) {
                
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                ]);

                // 🌟 モブユーザーも申請を出す！
                if (rand(1, 3) === 1) {
                    $request = StampCorrectionRequest::factory()->create([
                        'attendance_id' => $attendance->id,
                        'date' => $attendance->date,
                    ]);

                    // 👇 🌟 修正！ ここも $faker を使います！
                    StampCorrectionRequestBreakTime::create([
                        'stamp_correction_request_id' => $request->id,
                        'start_time' => $faker->dateTimeBetween('12:00:00', '12:15:00')->format('H:i:s'),
                        'end_time' => $faker->dateTimeBetween('12:45:00', '13:15:00')->format('H:i:s'),
                    ]);
                }
            });
        }
    }
}
