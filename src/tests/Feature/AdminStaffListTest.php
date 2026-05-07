<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_staff_names_and_emails()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Test User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('Test User 1');
        $response->assertSee('user1@example.com');
        $response->assertSee('Test User 2');
        $response->assertSee('user2@example.com');
    }

    public function test_admin_can_see_specific_user_attendance_details()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('Test User 3');
        $response->assertSee('04/15');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_admin_can_see_previous_month_attendance()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User 4',
            'email' => 'user4@example.com',
            'password' => bcrypt('password'),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('04/15');
        $response->assertDontSee('05/15');
    }

    public function test_admin_can_see_next_month_attendance()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User 5',
            'email' => 'user5@example.com',
            'password' => bcrypt('password'),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-06-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('06/15');
        $response->assertDontSee('05/15');
    }

    public function test_admin_can_navigate_to_attendance_detail()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User 6',
            'email' => 'user6@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $listResponse = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('/admin/attendance/' . $attendance->id);

        $detailResponse = $this->get('/admin/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('2026年');
        $detailResponse->assertSee('4月15日');
    }
}
