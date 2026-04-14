<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Livewire\Livewire; // 🌟 忘れずに追加済み！

class AdminCorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストケース ID: 15 (1行目)
     * 管理者が承認待ちの修正申請一覧を開いたとき、全ユーザーの未承認申請が表示される
     */
    public function test_admin_can_see_all_pending_requests()
    {
        // 1. 準備：店長さんとスタッフを2人作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user1 = User::create(['name' => '山田太郎', 'email' => 'taro@example.com', 'password' => bcrypt('password')]);
        $user2 = User::create(['name' => '山田花子', 'email' => 'hanako@example.com', 'password' => bcrypt('password')]);

        // 🌟 準備①：山田太郎さんの「承認待ち」申請
        $attendance1 = Attendance::create(['user_id' => $user1->id, 'date' => '2026-05-01', 'start_time' => '09:00', 'end_time' => '18:00']);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'reason' => '太郎の打刻忘れのため',
            'status' => '承認待ち', // 👈 英語の 'pending' から日本語の '承認待ち' に変更！
            'date' => '2026-05-01', 
            'start_time' => '09:00', 
            'end_time' => '18:30',
        ]);

        // 🌟 準備②：山田花子さんの「承認待ち」申請
        $attendance2 = Attendance::create(['user_id' => $user2->id, 'date' => '2026-05-02', 'start_time' => '10:00', 'end_time' => '19:00']);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'reason' => '花子の電車遅延のため',
            'status' => '承認待ち', // 👈 ここも日本語に！
            'date' => '2026-05-02', 
            'start_time' => '10:30', 
            'end_time' => '19:00',
        ]);

        // 2. 【Livewire専用の魔法】部品を直接テストします！
        Livewire::actingAs($admin, 'admin')
            ->test('request-tabs') 
            // ✅ 太郎さんの情報があるか？
            ->assertSee('山田太郎') 
            ->assertSee('太郎の打刻忘れのため')
            // ✅ 花子さんの情報もあるか？（「全ユーザー」の確認になります！）
            ->assertSee('山田花子')
            ->assertSee('花子の電車遅延のため')
            // ✅ 状態の列に「承認待ち」と出ているか？
            ->assertSee('承認待ち'); 
    }

    /**
     * テストケース ID: 15 (2行目)
     * 管理者が承認済みの修正申請一覧を開いたとき、全ユーザーの承認済み申請が表示される
     */
    public function test_admin_can_see_all_approved_requests()
    {
        // 1. 準備：店長さんとスタッフ2人を作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin_approved@example.com',
            'password' => bcrypt('password'),
        ]);

        $user1 = User::create(['name' => '佐藤一郎', 'email' => 'ichiro_sato@example.com', 'password' => bcrypt('password')]);
        $user2 = User::create(['name' => '鈴木二郎', 'email' => 'jiro_suzuki@example.com', 'password' => bcrypt('password')]);

        // 🌟 準備：今回は「承認済み」のデータを作ります！
        $attendance1 = Attendance::create(['user_id' => $user1->id, 'date' => '2026-05-10', 'start_time' => '09:00', 'end_time' => '18:00']);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'reason' => '一郎の修正完了分',
            'status' => '承認済み', // 👈 ここを「承認済み」に！
            'date' => '2026-05-10', 'start_time' => '09:00', 'end_time' => '18:30',
        ]);

        $attendance2 = Attendance::create(['user_id' => $user2->id, 'date' => '2026-05-11', 'start_time' => '09:00', 'end_time' => '18:00']);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'reason' => '二郎の修正完了分',
            'status' => '承認済み', // 👈 ここも「承認済み」に！
            'date' => '2026-05-11', 'start_time' => '09:00', 'end_time' => '19:00',
        ]);

        // 2. 【Livewire専用の魔法】タブを切り替えてテスト！
        Livewire::actingAs($admin, 'admin')
            ->test('request-tabs')
            // 💡 魔法：まず「承認済み」タブをクリックしたことにします！
            // めぐみさんのコードにある changeTab('approved') を呼び出します
            ->call('changeTab', 'approved') 
            
            // ✅ 佐藤一郎さんの承認済みデータがあるか？
            ->assertSee('佐藤一郎')
            ->assertSee('一郎の修正完了分')
            
            // ✅ 鈴木二郎さんの承認済みデータもあるか？
            ->assertSee('鈴木二郎')
            ->assertSee('二郎の修正完了分')
            
            // ✅ 状態が「承認済み」になっているか？
            ->assertSee('承認済み');
    }

    /**
     * テストケース ID: 15 (3行目)
     * 管理者が修正申請の詳細画面を開くと、申請内容が正しく表示されている
     */
    public function test_admin_can_see_correction_request_detail()
    {
        // 1. 準備：店長さんとスタッフ（山田太郎さん）を作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin_detail@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備：太郎さんの「打刻忘れ」の申請データを作ります
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-20',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'reason' => '電車遅延のため1時間遅れました', // 👈 これが表示されるかチェック！
            'date' => '2026-05-20',
            'start_time' => '10:00', // 修正後の開始時間
            'end_time' => '18:00',
        ]);

        // 2. 店長さんでログイン！
        $this->actingAs($admin, 'admin');

        // 3. 【設計書 PG13】詳細画面（承認画面）のURLへワープ！
        // URLパス: /stamp_correction_request/approve/{attendance_correct_request_id}
        $response = $this->get('/stamp_correction_request/approve/' . $request->id);

        // ✅ 期待挙動①：ページがちゃんと開けたか？
        $response->assertStatus(200);

        // ✅ 期待挙動②：申請者の名前が出ているか？
        $response->assertSee('山田太郎');

        // ✅ 期待挙動③：修正したい理由がちゃんと出ているか？
        $response->assertSee('電車遅延のため1時間遅れました');

        // ✅ 期待挙動④：修正後の時間が表示されているか？
        $response->assertSee('10:00');
    }

    /**
     * テストケース ID: 15 (4行目)
     * 管理者が修正申請を承認すると、ステータスが更新され勤怠情報が上書きされる
     */
    public function test_admin_can_approve_correction_request()
    {
        // 1. 準備：店長さんとスタッフを作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin_approve_final@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '山田太郎',
            'email' => 'yamada_final@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備：もともとの勤怠データ（9時〜18時）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-25',
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        // 🌟 準備：修正申請（10時〜19時にしてほしい！）
        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'status' => '承認待ち', // 👈 めぐみさんの blade の @if 条件に合わせます！
            'reason' => '修正の最終確認です',
            'date' => '2026-05-25',
            'start_time' => '10:00',
            'end_time' => '19:00',
        ]);

        // 2. 店長さんでログイン！
        $this->actingAs($admin, 'admin');

        // 3. 承認実行！
        // 画像1枚目（PG13）の設計通り、/stamp_correction_request/approve/{id} へ POST します
        $response = $this->post('/stamp_correction_request/approve/' . $request->id);

        // ✅ 期待挙動①：処理後に一覧画面などへリダイレクトしたか？
        $response->assertRedirect(); 

        // ✅ 期待挙動②：ステータスが「承認済み」に変わったか？
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => '承認済み',
        ]);

        // ✅ 期待挙動③：元の勤怠データが申請通りの時間に上書きされたか？
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);
    }
}
