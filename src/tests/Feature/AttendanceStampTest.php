<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon; // 🌟 時間を操る魔法！

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 🌟 共通の魔法：メール認証済み（VIPパスポート付き）のユーザーを作る！
     */
    private function createVerifiedUser()
    {
        // 1. まずはユーザーをデータベースに保存する
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 🌟 ここが重要！Laravelの正式な手続きで「メール認証済み」の状態にする
        $user->markEmailAsVerified();

        return $user;
    }

    /**
     * テストケース ID:4 日時取得機能
     */
    public function test_current_date_and_time_is_displayed()
    {
        // 1. 時間を固定する魔法
        Carbon::setTestNow('2026-04-07 15:00:00');
        Carbon::setLocale('ja');

        // 🌟 修正ポイント：共通の魔法「createVerifiedUser」を呼び出すだけ！
        // これだけで、スタンプ済みのユーザーが手に入ります。
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('2026年4月7日(火)'); 
        $response->assertSee('15:00');
    }

    /**
     * テストケース ID:5 ステータス確認機能（勤務外）
     */
    public function test_status_is_off_duty_when_no_attendance_record()
    {
        $user = $this->createVerifiedUser();
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務外');
    }

    /**
     * テストケース ID:5 ステータス確認機能（出勤中）
     */
    public function test_status_is_working_when_clocked_in()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * テストケース ID:5 ステータス確認機能（休憩中）
     */
    public function test_status_is_resting_when_on_break()
    {
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * テストケース ID:5 ステータス確認機能（退勤済）
     */
    public function test_status_is_clocked_out_after_work()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    /**
     * テストケース ID:6 出勤機能
     */
    public function test_user_can_clock_in()
    {
        // 1. 9時ちょうどに時間を固定！
        Carbon::setTestNow('2026-04-07 09:00:00');
        $user = $this->createVerifiedUser();

        // 2. ログインして画面を開き、「出勤」ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤'); // 👈 ここで「ボタンが見えるか」を確認！

        // 3. 出勤ボタンを押す（POST送信）
        $response = $this->actingAs($user)->post('/attendance/start');

        // 4. DBに正しく保存されたか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-04-07',
            'start_time' => '09:00:00',
        ]);

        // 5. 処理後の画面でステータスが「出勤中」になっているか確認
        // ボタンを押した後は元の画面に戻るので、もう一度 get して確認します
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中'); // 👈 ここで「ステータスが変わったか」を確認！
    }

    /**
     * テストケース ID:6 出勤は一日一回のみ
     */
    public function test_clock_in_only_once_a_day()
    {
        // 1. VIPユーザーを用意する
        $user = $this->createVerifiedUser();

        // 2. ステータスを「退勤済」の状態にする
        // (朝9時に来て、18時に帰ったというデータをあらかじめ作っておく)
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 3. 勤怠打刻画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 4. 期待挙動：画面上に「出勤」ボタンが表示されないことを確認！
        $response->assertDontSee('出勤');
    }

    /**
     * テストケース ID:6 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        // 1. 9時ちょうどに時間を固定！
        Carbon::setTestNow('2026-04-07 09:00:00');
        
        // ステータスが勤務外（データなし）のユーザーを用意
        $user = $this->createVerifiedUser();

        // 2. 出勤の処理を行う（ボタンをポチッと押す）
        $this->actingAs($user)->post('/attendance/start');

        // 3. 勤怠一覧画面を開く
        $response = $this->actingAs($user)->get('/attendance/list');

        // ✅ 期待挙動：画面に「09:00」という時刻が正確に表示されているか確認！
        $response->assertSee('09:00');
    }

    /**
     * テストケース ID: 7 休憩機能
     */
    public function test_user_can_start_break()
    {
        // 1. お昼休みの 12:00 に時間を固定！
        Carbon::setTestNow('2026-04-07 12:00:00');
        $user = $this->createVerifiedUser();

        // まずは「出勤中」の状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-07',
            'start_time' => '09:00:00',
        ]);

        // 2. ログインして画面を開き、「休憩入」ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入'); 

        // 3. 休憩の処理を行う（本物のルートへPOST送信！）
        // 🌟 ここをめぐみさんの web.php に合わせて修正しました！
        $response = $this->actingAs($user)->post('/attendance/break/start');

        // ✅ 期待挙動：処理後にステータスが「休憩中」になっているか確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
        
        // 【倉庫確認】DBにちゃんと休憩開始時間が保存されたかチェック！
        $this->assertDatabaseHas('break_times', [
            'start_time' => '12:00:00',
            'end_time' => null,
        ]);
    }

    /**
     * テストケース ID: 7 休憩は一日に何回でもできる
     */
    public function test_user_can_start_break_multiple_times()
    {
        // 1. VIPユーザーを用意する
        $user = $this->createVerifiedUser();

        // 2. 「出勤中」かつ「一度休憩して戻った」状態を作る
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => null, // 👈 こう書いてもOK！意味は同じです
        ]);

        // 1回目の休憩（12:00〜13:00）の記録を入れる
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 3. ログインして画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // ✅ 期待挙動：一度休憩が終わっていても、また「休憩入」ボタンが表示される！
        $response->assertSee('休憩入');
    }

    /**
     * テストケース ID: 7 休憩戻機能
     */
    public function test_user_can_end_break()
    {
        // 1. 休憩から戻る時間（13:00）に時計をセット！
        Carbon::setTestNow('2026-04-07 13:00:00');
        $user = $this->createVerifiedUser();

        // 2. 「出勤中」かつ「今まさに休憩中」の状態をDBに作る
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-07',
            'start_time' => '09:00:00',
            'end_time' => null, // 🌟めぐみさん流！「退勤はまだ」をハッキリ宣言！
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => null, // 👈 まだ休憩中！
        ]);

        // 3. ログインして画面を開き、「休憩戻」ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻'); // 👈 ここでボタンが見えるかチェック！

        // 4. 休憩戻の処理を行う（本物のルートへPOST送信！）
        $response = $this->actingAs($user)->post('/attendance/break/end');

        // ✅ 5. 期待挙動：処理後にステータスが「出勤中」に戻っているか確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');

        // 【倉庫確認】DBにちゃんと「13時に休憩が終わった」と記録されたかチェック！
        $this->assertDatabaseHas('break_times', [
            'start_time' => '12:00:00',
            'end_time' => '13:00:00', // 👈 空っぽだった部分に時間が入った！
        ]);
    }

    /**
     * テストケース ID: 7 休憩戻は一日に何回でもできる
     */
    public function test_user_can_end_break_multiple_times()
    {
        // 1. おやつの時間（15:00）に時計をセット！
        Carbon::setTestNow('2026-04-07 15:00:00');
        $user = $this->createVerifiedUser();

        // 2. 「出勤中（退勤はまだ）」の記録を作る
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-07',
            'start_time' => '09:00:00',
            'end_time' => null, 
        ]);

        // 🌟 ここがポイント！「2回目の休憩中」という状態を作ります

        // 1回目の休憩（12:00〜13:00 お昼休み終わった！）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 2回目の休憩（14:00〜 今まさに休憩中！）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '14:00:00',
            'end_time' => null, // 👈 まだ戻ってない！
        ]);

        // 3. ログインして画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // ✅ 期待挙動：2回目の休憩中でも、ちゃんと「休憩戻」ボタンが表示される！
        $response->assertSee('休憩戻');
    }

    /**
     * テストケース ID: 7 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_is_displayed_on_attendance_list()
    {
        // 1. VIPユーザーを用意する
        $user = $this->createVerifiedUser();

        // 2. 朝9:00に出勤する
        Carbon::setTestNow('2026-04-07 09:00:00');
        $this->actingAs($user)->post('/attendance/start');

        // 3. お昼の12:00に「休憩入」ボタンを押す
        Carbon::setTestNow('2026-04-07 12:00:00');
        $this->actingAs($user)->post('/attendance/break/start');

        // 4. 13:00に「休憩戻」ボタンを押す（※ここで休憩時間が「1時間」になる！）
        Carbon::setTestNow('2026-04-07 13:00:00');
        $this->actingAs($user)->post('/attendance/break/end');

        // 5. 勤怠一覧画面を開く
        $response = $this->actingAs($user)->get('/attendance/list');

        // ✅ 期待挙動：画面の「休憩」の列に、合計時間の「01:00」が表示されているか確認！
        $response->assertSee('01:00');
    }

    /**
     * テストケース ID: 8 退勤機能
     */
    public function test_user_can_clock_out()
    {
        // 1. お仕事終了の 18:00 に時計をセット！
        Carbon::setTestNow('2026-04-07 18:00:00');
        $user = $this->createVerifiedUser();

        // 2. 「出勤中（退勤はまだ）」の状態をDBに作る
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-07',
            'start_time' => '09:00:00',
            'end_time' => null, // 🌟 ここが「お仕事中」の印！
        ]);

        // 3. ログインして画面を開き、「退勤」ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤'); 

        // 4. 退勤の処理を行う（本物のルート /attendance/end へPOST送信！）
        $response = $this->actingAs($user)->post('/attendance/end');

        // ✅ 5. 期待挙動：処理後にステータスが「退勤済」になっているか確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');

        // 【倉庫確認】DBにちゃんと「18時に退勤した」と記録されたかチェック！
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00', // 👈 空っぽだった部分に18時が入った！
        ]);
    }

    /**
     * テストケース ID: 8 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_displayed_on_attendance_list()
    {
        // 1. VIPユーザーを用意する（最初は「勤務外」の状態です）
        $user = $this->createVerifiedUser();

        // 2. 朝9:00にワープして「出勤」の処理を行う
        Carbon::setTestNow('2026-04-07 09:00:00');
        $this->actingAs($user)->post('/attendance/start');

        // 3. 夕方18:00にワープして「退勤」の処理を行う
        Carbon::setTestNow('2026-04-07 18:00:00');
        $this->actingAs($user)->post('/attendance/end');

        // 4. 勤怠一覧画面を開く
        $response = $this->actingAs($user)->get('/attendance/list');

        // ✅ 5. 期待挙動：画面に退勤時刻の「18:00」が正確に表示されているか確認！
        $response->assertSee('18:00');
    }
}
