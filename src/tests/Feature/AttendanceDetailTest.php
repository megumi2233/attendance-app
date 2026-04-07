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

    /**
     * 🌟 おなじみの「VIPパスポート」魔法を用意！
     */
    private function createVerifiedUser()
    {
        $user = User::create([
            'name' => 'テストユーザー', // 👈 今回はこの名前が画面に出るかチェックします！
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->markEmailAsVerified();
        return $user;
    }

    /**
     * テストケース ID: 10 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_user_name_is_displayed_on_attendance_detail_screen()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        // （まずはVIPパスポートを持った「テストユーザー」さんを作ります）
        $user = $this->createVerifiedUser();

        // 🌟 詳細ページを開くための「勤怠データ（出席番号）」を1つ作ります
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 勤怠詳細ページを開く
        // （さっき作った出席番号のURLに行きます！）
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        // ✅ 3. 名前欄を確認する：名前がログインユーザーの名前になっているか？
        
        $response->assertStatus(200); // ページが無事に開けたかチェック！
        
        // 画面の中に、ログインした人の名前（今回は「テストユーザー」）が表示されているか目を凝らしてチェック！
        $response->assertSee($user->name); 
    }

    /**
     * テストケース ID: 10 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_selected_date_is_displayed_on_attendance_detail_screen()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();

        // 🌟 今回チェックしたい「特定の日付」のデータを作ります！
        // （例として 2026年4月15日のデータにしますね）
        $targetDate = '2026-04-15';
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $targetDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 勤怠詳細ページを開く
        // （「2026-04-15」のデータのURLへレッツゴー！）
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        // ✅ 3. 日付欄を確認する：日付が選択した日付になっているか？
        
        $response->assertStatus(200);

        // 画面の中に、さっき決めた「2026-04-15」という文字がちゃんと入っているかチェック！
        // 💡 もし画面の表示が「2026年04月15日」などになっている場合は、ここを書き換えてみてくださいね。
        $response->assertSee($targetDate);
    }

    /**
     * テストケース ID: 10 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_times_match_the_recorded_data()
    {
        // 1. ログインする
        $user = $this->createVerifiedUser();

        // 🌟 テストデータを作る
        $attendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => '2026-04-15',
            'start_time' => '09:00:00', // データは秒まで
            'end_time'   => '18:00:00',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        // ✅ 3. 出勤・退勤欄を確認する
        $response->assertStatus(200);

        // Bladeの <input type="time"> の値（value）に
        // ちゃんと時間が入っているかロボットにチェックさせます！
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * テストケース ID: 10 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_break_times_match_the_recorded_data()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();

        // 🌟 勤怠データを作ります
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 🌟 休憩データを作ります（打刻データ）
        // さっき確認した通り、モデル名は「BreakTime」を使います！
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        // ✅ 3. 休憩欄を確認する：時間が打刻と一致しているか？
        $response->assertStatus(200);

        // Bladeで @foreach を使って表示させている「12:00」と「13:00」を探します！
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /**
     * テストケース ID: 11 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_message_is_displayed_when_start_time_is_after_end_time()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3 & 4. 出勤時間を退勤時間より後に設定して保存処理をする
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '19:00', // 👈 退勤より遅い「出勤時間」
            'end_time' => '18:00',
            'reason' => 'テスト修正', 
        ]);

        $response->assertStatus(302); 

        // ✅ 期待挙動：テストケース通り、「start_time」に正しいメッセージが出ること！
        $response->assertInvalid([
            'start_time' => '出勤時間が不適切な値です'
        ]);
    }

    /**
     * テストケース ID: 11 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_message_when_break_start_time_is_after_end_time()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3 & 4. 休憩開始時間を退勤時間より後に設定して保存処理をする
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
            // 🌟 休憩データは配列（まとまり）で送ります！
            'break_times' => [
                [
                    'start_time' => '19:00', // 👈 退勤(18:00)より遅い「休憩開始時間」
                    'end_time' => '19:30',
                ]
            ],
            'reason' => 'テスト修正', 
        ]);

        // エラーが出て元のページに押し返されたかチェック（302リダイレクト）
        $response->assertStatus(302); 

        // ✅ 期待挙動：テストケース通り、正しいメッセージが出ること！
        // Laravelでは、配列の1番目のエラーは「名前.0.項目名」という風に探します
        $response->assertInvalid([
            'break_times.0.start_time' => '休憩時間が不適切な値です'
        ]);
    }

    /**
     * テストケース ID: 11 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_message_when_break_end_time_is_after_work_end_time()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00', // 👈 退勤時間は18:00
        ]);

        // 2 & 3 & 4. 休憩終了時間を退勤時間より後に設定して保存処理をする
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
            // 🌟 休憩データ（終了時間を退勤の18:00より遅くする！）
            'break_times' => [
                [
                    'start_time' => '12:00', 
                    'end_time' => '19:00', // 👈 退勤より遅い「休憩終了時間」
                ]
            ],
            'reason' => 'テスト修正', 
        ]);

        // エラーが出て元のページに押し返されたかチェック
        $response->assertStatus(302); 

        // ✅ 期待挙動：テストケース通り、正しいメッセージが出ること！
        // 今回は「0番目の休憩の、end_time（終了時間）」のエラーをチェックします
        $response->assertInvalid([
            'break_times.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * テストケース ID: 11 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_error_message_when_reason_is_empty()
    {
        // 1. ログインをする
        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3. 備考欄を未入力（空っぽ）にして保存処理をする
        // 💡 'reason' => '' とすることで、わざと空の状態にして送ります
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '', // 👈 ここを空っぽにします！
        ]);

        // 元のページに押し返されたかチェック（302リダイレクト）
        $response->assertStatus(302); 

        // ✅ 期待挙動：テストケース通り、「備考を記入してください」と表示されること！
        $response->assertInvalid([
            'reason' => '備考を記入してください'
        ]);
    }

    /**
     * テストケース ID: 11 修正申請処理が実行される（管理者の画面に表示される）
     */
    public function test_correction_request_is_processed_and_displayed_to_admin()
    {
        // ==========================================
        // 👩‍💼 【前半】一般ユーザーの操作（修正申請をする）
        // ==========================================
        $user = $this->createVerifiedUser();

        // 1. 元の勤怠データを作ります
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 一般ユーザーとしてログインし、出勤時間を「09:30」に修正して保存！
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:30', // 👈 09:00 から 09:30 に修正
            'end_time' => '18:00',
            'reason' => '電車遅延のため', // 👈 理由もしっかり書きます
        ]);

        // エラーなく処理され、元のページ等に戻されたかチェック
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors(); // バリデーションエラーが起きていないこと！

        // 🌟 データベースに「修正申請」のデータが本当に作られたかロボットに確認させます！
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'reason' => '電車遅延のため',
        ]);

        // ==========================================
        // 👑 【後半】管理者の操作（申請を確認する）
        // ==========================================

        // さっき作られた申請書のデータを、データベースから引っ張ってきます
        $correctionRequest = StampCorrectionRequest::first();

        // 3. 管理者ユーザー（店長さん）を作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 管理者としてログインします！（💡 'admin' という専用の入り口を使います）
        $this->actingAs($admin, 'admin');

        // ✅ 管理者の「申請一覧画面」を開いて確認
        $listResponse = $this->get('/stamp_correction_request/list');
        $listResponse->assertStatus(200);
        $listResponse->assertSee($user->name); // 申請した人の名前が出ているか？
        $listResponse->assertSee('電車遅延のため'); // 理由が出ているか？

        // ✅ 管理者の「承認画面（詳細）」を開いて確認
        // web.php の通り「/stamp_correction_request/approve/{id}」にアクセスします
        $approveResponse = $this->get('/stamp_correction_request/approve/' . $correctionRequest->id);
        $approveResponse->assertStatus(200);
        $approveResponse->assertSee('09:30'); // 修正後の時間（09:30）がちゃんと見えているか？
        $approveResponse->assertSee('電車遅延のため');
    }

    /**
     * テストケース ID: 11 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_user_can_see_all_their_own_correction_requests()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();

        // 🌟 「すべて（複数）」表示されるか確認するために、2日分のデータを作ります
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-02',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする（2日分とも申請を出す！）
        
        // 1回目：4月1日の修正申請
        $this->actingAs($user)->post('/attendance/detail/' . $attendance1->id, [
            'date' => '2026-04-01',
            'start_time' => '09:30',
            'end_time' => '18:00',
            'reason' => '1つ目の申請理由',
        ]);

        // 2回目：4月2日の修正申請
        $this->actingAs($user)->post('/attendance/detail/' . $attendance2->id, [
            'date' => '2026-04-02',
            'start_time' => '09:30',
            'end_time' => '18:00',
            'reason' => '2つ目の申請理由',
        ]);

        // 3. 申請一覧画面を確認する
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        // ✅ 期待挙動：申請一覧に自分の「1つ目」と「2つ目」の申請が両方表示されている！
        $response->assertStatus(200);
        
        // 1つ目のデータが画面にあるかな？
        $response->assertSee('2026-04-01');
        $response->assertSee('1つ目の申請理由');
        
        // 2つ目のデータも画面にあるかな？
        $response->assertSee('2026-04-02');
        $response->assertSee('2つ目の申請理由');

        // ついでに「承認待ち」という文字も出ているか確認しておくとより安心です！
        $response->assertSee('承認待ち');
    }

    /**
     * テストケース ID: 11 「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function test_user_can_see_approved_requests_in_list()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする（修正申請を出す）
        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:30',
            'end_time' => '18:00',
            'reason' => '承認済みの確認テスト',
        ]);

        // 🌟 データベースから申請を見つけ出して、「承認済み」に書き換えます！
        $correctionRequest = StampCorrectionRequest::where('reason', '承認済みの確認テスト')->first();
        $correctionRequest->update(['status' => '承認済み']); 

        // 3 & 4. 申請一覧画面を開き、承認された申請が表示されているか確認！
        // 👑 ここを修正！絶対に迷子にならない「正式な住所と名前」で呼び出します！
        Livewire::test(\App\Http\Livewire\RequestTabs::class) // 👈 ここが進化しました！
            ->set('tab', 'approved')   // 👈 「承認済み」タブをポチッと押す操作の代わり！
            ->assertSee('2026-04-15')
            ->assertSee('承認済みの確認テスト')
            ->assertSee('承認済み');
    }

    /**
     * テストケース ID: 11 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_user_can_navigate_to_attendance_detail_from_request_list()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = $this->createVerifiedUser();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする（修正申請を出す）
        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:30',
            'end_time' => '18:00',
            'reason' => '詳細ボタンのテスト',
        ]);

        // 3. 申請一覧画面を開く
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        // 🌟 ここがポイント！画面の中に「詳細ボタンのリンク（URL）」がちゃんとあるかチェック！
        // Bladeファイルで設定した通り '/attendance/detail/勤怠データのID' を探します
        $targetUrl = '/attendance/detail/' . $attendance->id;
        $response->assertSee($targetUrl);

        // 4. 「詳細」ボタンを押す（＝見つけたURLへワープしてみる！）
        $detailResponse = $this->actingAs($user)->get($targetUrl);

        // ✅ 期待挙動：勤怠詳細画面に無事に遷移（表示）できたかチェック！
        $detailResponse->assertStatus(200); // ページが開けた！
        $detailResponse->assertSee('2026-04-15'); // 日付が見える！
        $detailResponse->assertSee($user->name); // 自分の名前が見える！
    }
}
