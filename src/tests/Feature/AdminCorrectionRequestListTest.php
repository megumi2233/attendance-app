<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Livewire\Livewire;

class AdminCorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_pending_requests()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password')
        ]);

        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password')
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-05-01',
            'start_time' => '09:00',
            'end_time' => '18:00'
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'reason' => 'Request reason 1',
            'status' => '承認待ち',
            'date' => '2026-05-01',
            'start_time' => '09:00',
            'end_time' => '18:30',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-05-02',
            'start_time' => '10:00',
            'end_time' => '19:00'
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'reason' => 'Request reason 2',
            'status' => '承認待ち',
            'date' => '2026-05-02',
            'start_time' => '10:30',
            'end_time' => '19:00',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test('request-tabs')
            ->assertSee('Test User 1')
            ->assertSee('Request reason 1')
            ->assertSee('Test User 2')
            ->assertSee('Request reason 2')
            ->assertSee('承認待ち');
    }

    public function test_admin_can_see_all_approved_requests()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin_approved@example.com',
            'password' => bcrypt('password'),
        ]);

        $user1 = User::create([
            'name' => 'Test User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password')
        ]);

        $user2 = User::create([
            'name' => 'Test User 4',
            'email' => 'user4@example.com',
            'password' => bcrypt('password')
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-05-10',
            'start_time' => '09:00',
            'end_time' => '18:00'
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'reason' => 'Approved reason 1',
            'status' => '承認済み',
            'date' => '2026-05-10',
            'start_time' => '09:00',
            'end_time' => '18:30',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-05-11',
            'start_time' => '09:00',
            'end_time' => '18:00'
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'reason' => 'Approved reason 2',
            'status' => '承認済み',
            'date' => '2026-05-11',
            'start_time' => '09:00',
            'end_time' => '19:00',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test('request-tabs')
            ->call('changeTab', 'approved')
            ->assertSee('Test User 3')
            ->assertSee('Approved reason 1')
            ->assertSee('Test User 4')
            ->assertSee('Approved reason 2')
            ->assertSee('承認済み');
    }

    public function test_admin_can_see_correction_request_detail()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin_detail@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User 5',
            'email' => 'user5@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-20',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'reason' => 'Detail view test reason',
            'date' => '2026-05-20',
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/approve/' . $request->id);

        $response->assertStatus(200);
        $response->assertSee('Test User 5');
        $response->assertSee('Detail view test reason');
        $response->assertSee('10:00');
    }

    public function test_admin_can_approve_correction_request()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin_final@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'Test User 6',
            'email' => 'user6@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-25',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'reason' => 'Final approval test reason',
            'date' => '2026-05-25',
            'start_time' => '10:00',
            'end_time' => '19:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post('/stamp_correction_request/approve/' . $request->id);

        $response->assertRedirect();

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => '承認済み',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);
    }
}
