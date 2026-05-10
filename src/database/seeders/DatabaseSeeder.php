<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestBreakTime;
use Carbon\Carbon;

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

        $otherUsers = User::factory(10)->create();

        for ($i = 0; $i <= 30; $i++) {
            $date = Carbon::today()->subDays($i);

            if ($date->isWeekend()) {
                continue;
            }

            $attendance = Attendance::factory()->create([
                'user_id' => $testUser->id,
                'date' => $date->format('Y-m-d'),
            ]);

            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
            ]);

            if (($i >= 1 && $i <= 3) || rand(1, 4) === 1) {
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
        }

        foreach ($otherUsers as $otherUser) {
            for ($i = 0; $i <= 10; $i++) {
                $date = Carbon::today()->subDays($i);

                if ($date->isWeekend()) {
                    continue;
                }

                if (rand(1, 10) <= 8) {
                    $attendance = Attendance::factory()->create([
                        'user_id' => $otherUser->id,
                        'date' => $date->format('Y-m-d'),
                    ]);

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
                }
            }
        }
    }
}
