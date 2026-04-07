<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime; // 🌟 ここを追加しました！休憩のモデルを使えるようにする魔法の呪文です
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 🌟 新しいファイルでもおなじみの「VIPパスポート」魔法を用意！
     */
    private function createVerifiedUser()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->markEmailAsVerified();
        return $user;
    }

    /**
     * テストケース ID: 9 自分が行った勤怠情報が全て表示されている
     */
    public function test_all_own_attendance_records_are_displayed()
    {
        // 1. 今月を「2026年4月」に固定する！（一覧画面は「今月」を表示するため）
        Carbon::setTestNow('2026-04-15 12:00:00');
        Carbon::setLocale('ja'); // 曜日を日本語にする

        $user = $this->createVerifiedUser();

        // 2. 「全て表示されるか」を証明するために、わざと複数（2日分）のデータを作ります！
        
        // 1日目：4月10日のデータ
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2日目：4月11日のデータ
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-11',
            'start_time' => '09:30:00',
            'end_time' => '18:30:00',
        ]);

        // 3. ログインして、勤怠一覧ページを開く
        $response = $this->actingAs($user)->get('/attendance/list');

        // ✅ 4. 期待挙動：自分の勤怠情報が「全て（2日分とも）」画面に出ているか確認！
        
        // めぐみさんのBladeの形式「MM/DD(ddd)」に合わせてチェック！
        // 2026年4月10日は金曜日、11日は土曜日です
        $response->assertSee('04/10(金)');
        $response->assertSee('09:00');

        $response->assertSee('04/11(土)');
        $response->assertSee('09:30');
    }

    /**
     * テストケース ID: 9 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_on_transition()
    {
        // 1. 魔法で時間を「2026年04月07日」に固定！
        Carbon::setTestNow('2026-04-07 10:00:00');

        $user = $this->createVerifiedUser();

        // 2. ログインして、一覧画面をガチャっと開く
        $response = $this->actingAs($user)->get('/attendance/list');

        // ✅ 3. 期待挙動：画面に「2026/04」という文字が出ているか確認！
        // めぐみさんのコントローラーの「$currentMonthDisplay」の形式に合わせます
        $response->assertSee('2026/04');
    }

    /**
     * テストケース ID: 9 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_information_is_displayed()
    {
        // 1. 時間の魔法！今日を「2026年4月7日」に固定します
        Carbon::setTestNow('2026-04-07 10:00:00');

        // ログインする人（VIPパスポート）を用意します
        $user = $this->createVerifiedUser();

        // 🌟 前月（3月）のデータがちゃんと出るか確認するため、3月のデータを作っておきます！
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-15', // ここが前月（3月）の日付！
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3. ログインして、一覧ページに行き、「前月」ボタンを押したときのURLを開きます！
        // web.phpの通り「/attendance/list」のURLに、おまけのメモ（?month=2026-03）をくっつけます！
        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        // ✅ 4. 期待挙動：前月（3月）の情報がちゃんと出ているかチェック！
        
        // 画面の月の表示が「2026/03」になっているか？
        $response->assertSee('2026/03');
        
        // さっき作った3月15日のデータが表示されているか？（曜日は2026年3月15日＝日曜日です）
        $response->assertSee('03/15(日)');
        $response->assertSee('09:00');
    }

    /**
     * テストケース ID: 9 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_information_is_displayed()
    {
        // 1. 時間の魔法！今日を「2026年4月7日」に固定します
        Carbon::setTestNow('2026-04-07 10:00:00');

        // ログインする人（VIPパスポート）を用意します
        $user = $this->createVerifiedUser();

        // 🌟 翌月（5月）のデータがちゃんと出るか確認するため、5月のデータを作っておきます！
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-20', // ここが翌月（5月）の日付！
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3. ログインして、一覧ページに行き、「翌月」ボタンを押したときのURLを開きます！
        // 「5月」を見たいので、おまけのメモを「?month=2026-05」にします！
        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');

        // ✅ 4. 期待挙動：翌月（5月）の情報がちゃんと出ているかチェック！
        
        // 画面の月の表示が「2026/05」になっているか？
        $response->assertSee('2026/05');
        
        // さっき作った5月20日のデータが表示されているか？（曜日は2026年5月20日＝水曜日です）
        $response->assertSee('05/20(水)');
        $response->assertSee('09:00');
    }

    /**
     * テストケース ID: 9 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_transitions_to_attendance_detail_screen()
    {
        // 1. ログインする人（VIPパスポート）を用意します
        $user = $this->createVerifiedUser();

        // 🌟 1つ目のデータ：勤怠データを作ります！
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00', // 出勤は09:00
            'end_time' => '18:00:00',   // 退勤は18:00
        ]);

        // 🌟 2つ目のデータ：休憩データを作ります！（本物のモデル名「BreakTime」に直しました！）
        // さっきの「$attendance->id（出席番号）」を使って紐づけます。
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00', // 休憩スタート
            'end_time' => '13:00:00',   // 休憩おわり
        ]);

        // 2 & 3. ログインして、「詳細」ボタンを押したときのURLを開きます！
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        // ✅ 4. 期待挙動：詳細画面に情報が全部出ているかチェック！
        
        $response->assertStatus(200); // ページが無事に開けたか
        
        // 日付や出退勤の時間が表示されているか？
        $response->assertSee('2026-04-15'); // ※画面の表記に合わせて変えてね
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // ✨ 休憩の時間がちゃんと表示されているかチェック！
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
