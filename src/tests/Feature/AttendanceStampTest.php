<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
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

    public function test_current_date_and_time_is_displayed()
    {
        Carbon::setTestNow('2026-04-07 15:00:00');
        Carbon::setLocale('ja');

        $user = $this->createVerifiedUser();
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('2026年4月7日(火)');
        $response->assertSee('15:00');
    }

    public function test_status_is_off_duty_when_no_attendance_record()
    {
        $user = $this->createVerifiedUser();
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務外');
    }

    public function test_status_is_working_when_clocked_in()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time'   => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_status_is_resting_when_on_break()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time'   => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time'    => '12:00:00',
            'end_time'      => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_status_is_clocked_out_after_work()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_user_can_clock_in()
    {
        Carbon::setTestNow('2026-04-07 09:00:00');
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $this->actingAs($user)->post('/attendance/start');

        $this->assertDatabaseHas('attendances', [
            'user_id'    => $user->id,
            'date'       => '2026-04-07',
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_clock_in_only_once_a_day()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertDontSee('出勤');
    }

    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow('2026-04-07 09:00:00');
        $user = $this->createVerifiedUser();

        $this->actingAs($user)->post('/attendance/start');
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('09:00');
    }

    public function test_user_can_start_break()
    {
        Carbon::setTestNow('2026-04-07 12:00:00');
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-07',
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');

        $this->assertDatabaseHas('break_times', [
            'start_time' => '12:00:00',
            'end_time'   => null,
        ]);
    }

    public function test_user_can_start_break_multiple_times()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time'   => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time'    => '12:00:00',
            'end_time'      => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function test_user_can_end_break()
    {
        Carbon::setTestNow('2026-04-07 13:00:00');
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-07',
            'start_time' => '09:00:00',
            'end_time'   => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time'    => '12:00:00',
            'end_time'      => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');

        $this->assertDatabaseHas('break_times', [
            'start_time' => '12:00:00',
            'end_time'   => '13:00:00',
        ]);
    }

    public function test_user_can_end_break_multiple_times()
    {
        Carbon::setTestNow('2026-04-07 15:00:00');
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-07',
            'start_time' => '09:00:00',
            'end_time'   => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time'    => '12:00:00',
            'end_time'      => '13:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time'    => '14:00:00',
            'end_time'      => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_break_time_is_displayed_on_attendance_list()
    {
        $user = $this->createVerifiedUser();

        Carbon::setTestNow('2026-04-07 09:00:00');
        $this->actingAs($user)->post('/attendance/start');

        Carbon::setTestNow('2026-04-07 12:00:00');
        $this->actingAs($user)->post('/attendance/break/start');

        Carbon::setTestNow('2026-04-07 13:00:00');
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('01:00');
    }

    public function test_user_can_clock_out()
    {
        Carbon::setTestNow('2026-04-07 18:00:00');
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-07',
            'start_time' => '09:00:00',
            'end_time'   => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/end');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');

        $this->assertDatabaseHas('attendances', [
            'user_id'    => $user->id,
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);
    }

    public function test_clock_out_time_is_displayed_on_attendance_list()
    {
        $user = $this->createVerifiedUser();

        Carbon::setTestNow('2026-04-07 09:00:00');
        $this->actingAs($user)->post('/attendance/start');

        Carbon::setTestNow('2026-04-07 18:00:00');
        $this->actingAs($user)->post('/attendance/end');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('18:00');
    }
}
