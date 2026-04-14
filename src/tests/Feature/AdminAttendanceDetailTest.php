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

    /**
     * テストケース ID: 13 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_admin_can_see_selected_attendance_detail()
    {
        // 1. 準備：管理者（店長さん）と、確認されるスタッフを作る
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '詳細 確認マン',
            'email' => 'detail@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. 準備：スタッフの勤怠データを作る（リアルな時間で！）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:05:00', // 9時ちょっと過ぎに出勤
            'end_time' => '18:15:00',   // 少しだけ残業して退勤
        ]);

        // 3. アクション：管理者としてログインし、その勤怠データの「詳細画面」を開く
        // 💡 基本設計書にあった `/admin/attendance/{id}` の URL にアクセスします！
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);

        // 4. チェック：画面がちゃんと開けたか？
        $response->assertStatus(200);

        // ✅ 5. 期待挙動：選択した勤怠のデータが、画面にバッチリ表示されているかチェック！
        $response->assertSee('詳細 確認マン'); // スタッフの名前
        $response->assertSee('09:05');         // 出勤時間
        $response->assertSee('18:15');         // 退勤時間

        // 💡 日付の表示チェック（画面の設計に合わせて探させます）
        // 画面設計書の画像を見ると「2023年」「6月1日」のように分かれているように見えたので、
        // 今回は「年」と「月日」の文字が含まれているかを確認します！
        $response->assertSee('2026年');
        $response->assertSee('4月15日'); 
        
        // ※もし、実際のBladeファイル（admin/attendance/detail.blade.php）で
        // '2026-04-15' のまま表示している場合は、ここを $response->assertSee('2026-04-15'); に直してくださいね！
    }

    /**
     * テストケース ID: 13 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_cannot_save_attendance_when_start_time_is_after_end_time()
    {
        // 1. 管理者ユーザーにログインをする
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3 & 4. 勤怠詳細ページを開き、出勤を退勤より後に設定して保存処理をする
        // 💡 例：出勤 19:00 / 退勤 18:00 という「ありえない時間」を送ります
        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '19:00',
            'end_time' => '18:00',
            'reason' => '管理者の修正テスト',
        ]);

        // ✅ 期待挙動：「出勤時間もしくは退勤時間が不適切な値です」というメッセージが出る！
        $response->assertInvalid([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * テストケース ID: 13 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_cannot_save_when_break_start_time_is_after_end_time()
    {
        // 1. 準備：管理者（店長さん）、スタッフ、勤怠データを作成
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '休憩 テストマン',
            'email' => 'break@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3 & 4. 詳細ページを開き、休憩開始を退勤(18:00)より後に設定して保存処理をする
        // 💡 ちゃんと修正した正しいURL（ /admin/attendance/ID ）を使います！
        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00', // 👈 退勤は18:00
            
            // 🌟 休憩時間は「配列（グループ）」の形で送ります
            'break_times' => [
                [
                    'start_time' => '18:30', // 👈 怒られポイント！退勤(18:00)の後に休憩を開始している
                    'end_time' => '19:00',
                ]
            ],
            'reason' => '休憩開始時間のエラーテスト',
        ]);

        // ✅ 期待挙動：「休憩時間が不適切な値です」というメッセージが出る！
        // 💡 休憩の1番目（プログラムの世界では0番目）の start_time のエラーを探します
        $response->assertInvalid([
            'break_times.0.start_time' => '休憩時間が不適切な値です'
        ]);
    }

    /**
     * テストケース ID: 13 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_admin_cannot_save_when_break_end_time_is_after_end_time()
    {
        // 1. 準備：管理者（店長さん）、スタッフ、勤怠データを作成
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '休憩終了 テストマン',
            'email' => 'break_end@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3 & 4. 詳細ページを開き、休憩終了を退勤(18:00)より後に設定して保存処理をする
        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',
            'end_time' => '18:00', // 👈 退勤は18:00
            
            // 🌟 休憩終了時間を退勤より後に設定します
            'break_times' => [
                [
                    'start_time' => '17:30', // 👈 休憩開始は退勤より前だからセーフ！
                    'end_time' => '18:30',   // 👈 怒られポイント！休憩終了が退勤(18:00)を過ぎている
                ]
            ],
            'reason' => '休憩終了時間のエラーテスト',
        ]);

        // ✅ 期待挙動：「休憩時間もしくは退勤時間が不適切な値です」というメッセージが出る！
        // 💡 今度は `start_time` ではなく `end_time` のエラーを探します！
        $response->assertInvalid([
            'break_times.0.end_time' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * テストケース ID: 13 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_admin_cannot_save_when_reason_is_empty()
    {
        // 1. 準備：管理者（店長さん）、スタッフ、勤怠データを作成
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '備考 忘れマン', // 今回のゲストです！
            'email' => 'noreason@example.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 2 & 3. 備考欄（reason）を空っぽにして保存処理（POST）をする
        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'date' => '2026-04-15',
            'start_time' => '09:00',   // 時間は正しい
            'end_time' => '18:00',     // 時間は正しい
            'reason' => '',            // 👈 怒られポイント！備考を空っぽ（未入力）にする
        ]);

        // ✅ 4. 期待挙動：「備考を記入してください」というメッセージが出る！
        $response->assertInvalid([
            'reason' => '備考を記入してください'
        ]);
    }
}
