<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_selected_attendance_detail()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:05:00',
            'end_time' => '18:15:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('09:05');
        $response->assertSee('18:15');
        $response->assertSee('2026年');
        $response->assertSee('4月15日');
    }

    public function test_admin_cannot_save_attendance_when_start_time_is_after_end_time()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '19:00',
            'end_time' => '18:00',
            'reason' => 'Admin test update',
        ]);

        $response->assertInvalid([
            'end_time' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_admin_cannot_save_when_break_start_time_is_after_end_time()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'break@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                [
                    'start_time' => '18:30',
                    'end_time' => '19:00',
                ]
            ],
            'reason' => 'Break time validation test',
        ]);

        $response->assertInvalid([
            'break_times.0.start_time' => '休憩時間が不適切な値です'
        ]);
    }

    public function test_admin_cannot_save_when_break_end_time_is_after_end_time()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'break_end@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_times' => [
                [
                    'start_time' => '17:30',
                    'end_time' => '18:30',
                ]
            ],
            'reason' => 'Break end time validation test',
        ]);

        $response->assertInvalid([
            'break_times.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_admin_cannot_save_when_reason_is_empty()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'noreason@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '',
        ]);

        $response->assertInvalid([
            'reason' => '備考を記入してください'
        ]);
    }
}
