<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
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

    public function test_all_own_attendance_records_are_displayed()
    {
        Carbon::setTestNow('2026-04-15 12:00:00');
        Carbon::setLocale('ja');

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '10:00:00',
            'end_time'   => '19:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('04/01(水)');
        $response->assertSee('04/15(水)');
    }

    public function test_user_can_see_attendance_of_selected_month()
    {
        Carbon::setTestNow('2026-05-20 12:00:00');
        Carbon::setLocale('ja');

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-05-20',
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('05/20(水)');
        $response->assertSee('09:00');
    }

    public function test_transitions_to_attendance_detail_screen()
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

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');
        $response->assertStatus(200);

        $targetUrl = '/attendance/detail/' . $attendance->id;
        $response->assertSee($targetUrl);

        $detailResponse = $this->actingAs($user)->get($targetUrl);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($user->name);
    }
}
