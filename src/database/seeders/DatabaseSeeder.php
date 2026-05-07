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
        $faker = \Faker\Factory::create();

        Admin::factory()->create();

        $testUser = User::factory()->create([
            'name' => '一般テスト',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $mobUsers = User::factory(10)->create();

        Attendance::factory(30)->create([
            'user_id' => $testUser->id,
        ])->each(function ($attendance) use ($faker) {
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);

            if (rand(1, 3) === 1) {
                $request = StampCorrectionRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'date' => $attendance->date,
                ]);

                StampCorrectionRequestBreakTime::create([
                    'stamp_correction_request_id' => $request->id,
                    'start_time' => $faker->dateTimeBetween('12:00:00', '12:15:00')->format('H:i:s'),
                    'end_time' => $faker->dateTimeBetween('12:45:00', '13:15:00')->format('H:i:s'),
                ]);
            }
        });

        foreach ($mobUsers as $mob) {
            Attendance::factory(5)->create([
                'user_id' => $mob->id,
            ])->each(function ($attendance) use ($faker) {
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                ]);

                if (rand(1, 3) === 1) {
                    $request = StampCorrectionRequest::factory()->create([
                        'attendance_id' => $attendance->id,
                        'date' => $attendance->date,
                    ]);

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
