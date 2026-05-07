<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Admin;
use App\Models\StampCorrectionRequest;
use Livewire\Livewire;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser()
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->markEmailAsVerified();

        return $user;
    }

    public function test_user_name_is_displayed_on_attendance_detail_screen()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_selected_date_is_displayed_on_attendance_detail_screen()
    {
        $user = $this->createVerifiedUser();

        $targetDate = '2026-04-15';
        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => $targetDate,
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee($targetDate);
    }

    public function test_attendance_times_match_the_recorded_data()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_break_times_match_the_recorded_data()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time'    => '12:00:00',
            'end_time'      => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_error_message_is_displayed_when_start_time_is_after_end_time()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'       => '2026-04-15',
            'start_time' => '19:00',
            'end_time'   => '18:00',
            'reason'     => 'Test reason',
        ]);

        $response->assertStatus(302);
        $response->assertInvalid([
            'end_time' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_error_message_when_break_start_time_is_after_end_time()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'        => '2026-04-15',
            'start_time'  => '09:00',
            'end_time'    => '18:00',
            'break_times' => [
                [
                    'start_time' => '19:00',
                    'end_time'   => '19:30',
                ]
            ],
            'reason'      => 'Test reason',
        ]);

        $response->assertStatus(302);
        $response->assertInvalid([
            'break_times.0.start_time' => '休憩時間が不適切な値です'
        ]);
    }

    public function test_error_message_when_break_end_time_is_after_work_end_time()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'        => '2026-04-15',
            'start_time'  => '09:00',
            'end_time'    => '18:00',
            'break_times' => [
                [
                    'start_time' => '12:00',
                    'end_time'   => '19:00',
                ]
            ],
            'reason'      => 'Test reason',
        ]);

        $response->assertStatus(302);
        $response->assertInvalid([
            'break_times.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_error_message_when_reason_is_empty()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'       => '2026-04-15',
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'reason'     => '',
        ]);

        $response->assertStatus(302);
        $response->assertInvalid([
            'reason' => '備考を記入してください'
        ]);
    }

    public function test_correction_request_is_processed_and_displayed_to_admin()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'       => '2026-04-15',
            'start_time' => '09:30',
            'reason'     => 'Test reason',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'reason'        => 'Test reason',
        ]);

        $correctionRequest = StampCorrectionRequest::first();

        $admin = Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin, 'admin');

        $listResponse = $this->get('/stamp_correction_request/list');
        $listResponse->assertStatus(200);
        $listResponse->assertSee($user->name);
        $listResponse->assertSee('Test reason');

        $approveResponse = $this->get('/stamp_correction_request/approve/' . $correctionRequest->id);
        $approveResponse->assertStatus(200);
        $approveResponse->assertSee('09:30');
        $approveResponse->assertSee('Test reason');
    }

    public function test_user_can_see_all_their_own_correction_requests()
    {
        $user = $this->createVerifiedUser();

        $attendance1 = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-02',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance1->id, [
            'date'       => '2026-04-01',
            'start_time' => '09:30',
            'end_time'   => '18:00',
            'reason'     => 'Test reason 1',
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance2->id, [
            'date'       => '2026-04-02',
            'start_time' => '09:30',
            'end_time'   => '18:00',
            'reason'     => 'Test reason 2',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('2026/04/01');
        $response->assertSee('Test reason 1');
        $response->assertSee('2026/04/02');
        $response->assertSee('Test reason 2');
        $response->assertSee('承認待ち');
    }

    public function test_user_can_see_approved_requests_in_list()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'       => '2026-04-15',
            'start_time' => '09:30',
            'end_time'   => '18:00',
            'reason'     => 'Approved test reason',
        ]);

        $correctionRequest = StampCorrectionRequest::where('reason', 'Approved test reason')->first();
        $correctionRequest->update(['status' => '承認済み']);

        Livewire::test(\App\Http\Livewire\RequestTabs::class)
            ->set('tab', 'approved')
            ->assertSee('2026/04/15')
            ->assertSee('Approved test reason')
            ->assertSee('承認済み');
    }

    public function test_user_can_navigate_to_attendance_detail_from_request_list()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date'       => '2026-04-15',
            'start_time' => '09:30',
            'end_time'   => '18:00',
            'reason'     => 'Navigation test reason',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $targetUrl = '/attendance/detail/' . $attendance->id;
        $response->assertSee($targetUrl);

        $detailResponse = $this->actingAs($user)->get($targetUrl);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('2026-04-15');
        $detailResponse->assertSee($user->name);
    }
}
