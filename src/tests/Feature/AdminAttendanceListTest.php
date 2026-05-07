<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        $today = Carbon::today()->format('Y-m-d');

        Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'start_time' => '08:50:00',
            'end_time' => '18:05:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'date' => $today,
            'start_time' => '09:12:00',
            'end_time' => '17:55:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('Test User 1');
        $response->assertSee('08:50');
        $response->assertSee('18:05');

        $response->assertSee('Test User 2');
        $response->assertSee('09:12');
        $response->assertSee('17:55');
    }

    public function test_admin_attendance_list_displays_current_date_initially()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $todayKanji = Carbon::now()->format('Y年n月j日');
        $todaySlash = Carbon::now()->format('Y/m/d');

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee($todayKanji);
        $response->assertSee($todaySlash);
    }

    public function test_admin_can_see_previous_day_attendance()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User Previous',
            'email' => 'previous@example.com',
            'password' => bcrypt('password'),
        ]);

        $yesterday = Carbon::yesterday();
        $yesterdayParam = $yesterday->format('Y-m-d');
        $yesterdayKanji = $yesterday->format('Y年n月j日');
        $yesterdaySlash = $yesterday->format('Y/m/d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterdayParam,
            'start_time' => '08:58:00',
            'end_time' => '18:02:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=' . $yesterdayParam);

        $response->assertStatus(200);

        $response->assertSee($yesterdayKanji);
        $response->assertSee($yesterdaySlash);
        $response->assertSee('Test User Previous');
        $response->assertSee('08:58');
        $response->assertSee('18:02');
    }

    public function test_admin_can_see_next_day_attendance()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User Next',
            'email' => 'next@example.com',
            'password' => bcrypt('password'),
        ]);

        $tomorrow = Carbon::tomorrow();
        $tomorrowParam = $tomorrow->format('Y-m-d');
        $tomorrowKanji = $tomorrow->format('Y年n月j日');
        $tomorrowSlash = $tomorrow->format('Y/m/d');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrowParam,
            'start_time' => '08:55:00',
            'end_time' => '18:10:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=' . $tomorrowParam);

        $response->assertStatus(200);

        $response->assertSee($tomorrowKanji);
        $response->assertSee($tomorrowSlash);
        $response->assertSee('Test User Next');
        $response->assertSee('08:55');
        $response->assertSee('18:10');
    }
}
