<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase; // 🌟 テストの前にデータベースを綺麗にお掃除するおまじない

    /**
     * テストケース ID: 14 (1行目)
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_see_all_staff_names_and_emails()
    {
        // 1. 準備：管理者（店長さん）を1人作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備：一覧画面のテストなので、スタッフを「2人」作ります！
        $user1 = User::create([
            'name' => 'スタッフ 太郎',
            'email' => 'taro@example.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'スタッフ 花子',
            'email' => 'hanako@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. 店長さんでログインして、スタッフ一覧ページ（/admin/staff/list）を開く！
        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');

        // ✅ 期待挙動：ページがちゃんと開けたか？（200 OK）
        $response->assertStatus(200);

        // ✅ 期待挙動：太郎さんの名前とメールアドレスが画面にあるか？
        $response->assertSee('スタッフ 太郎');
        $response->assertSee('taro@example.com');

        // ✅ 期待挙動：花子さんの名前とメールアドレスも画面にあるか？
        $response->assertSee('スタッフ 花子');
        $response->assertSee('hanako@example.com');
    }

    /**
     * テストケース ID: 14 (2行目)
     * 管理者が選択したユーザーの勤怠一覧ページを開き、勤怠情報が正しく表示される
     */
    public function test_admin_can_see_specific_user_attendance_details()
    {
        // 1. 準備：店長さんと、スタッフ（一郎さん）を作ります
        // 💡 めぐみさんの気づき通り、1つ目と同じメールアドレスでOKです！
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com', 
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'スタッフ 一郎',
            'email' => 'ichiro@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備：一郎さんの「今日のタイムカード（勤怠データ）」を作ります！
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00', 
            'end_time' => '18:00',
        ]);

        // 2. 店長さんでログインして、一郎さんの勤怠一覧ページを開く！
        // 💡 web.php の設定と一致しているので、このURLでバッチリです！
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id);

        // ✅ 期待挙動：ページがちゃんと開けたか？（200 OK）
        $response->assertStatus(200);

        // ✅ 期待挙動：一郎さんの名前が画面にあるか？
        $response->assertSee('スタッフ 一郎');

        // ✅ 期待挙動：さっき作ったタイムカードの時間が画面にあるか？
        $response->assertSee('04/15'); 
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

   /**
     * テストケース ID: 14 (3行目)
     * 管理者が「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_see_previous_month_attendance()
    {
        // 1. 準備：店長さんと、スタッフ（一郎さん）を作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'スタッフ 一郎',
            'email' => 'ichiro@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備①：今月（例：5月）の勤怠データ
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-15',
            'start_time' => '09:00', 
            'end_time' => '18:00',
        ]);

        // 🌟 準備②：前月（例：4月）の勤怠データ
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00', 
            'end_time' => '18:00',
        ]);

        // 2. 店長さんでログイン！
        $this->actingAs($admin, 'admin');

        // 3. 「前月ボタン」を押した先のURLへ直接ワープする！
        // 💡 show.blade.php の href の書き方と一致しているので、このURLでバッチリです！
        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        // ✅ 期待挙動：ページがちゃんと開けたか？
        $response->assertStatus(200);

        // ✅ 期待挙動：前月（4月）のデータがちゃんと表示されているか？
        // ※show.blade.phpを見ると 'MM/DD(ddd)' の形式（例: 04/15(水)）で表示しているようなので、
        // もし以下の '2026/04/15' でエラーになったら、'04/15' だけを探すように直せばOKです！
        $response->assertSee('04/15');

        // ✅ 期待挙動：今月（5月）のデータが「表示されていない」ことも確認！
        $response->assertDontSee('05/15'); 
    }

    /**
     * テストケース ID: 14 (4行目)
     * 管理者が「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_admin_can_see_next_month_attendance()
    {
        // 1. 準備：店長さんと、スタッフ（一郎さん）を作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'スタッフ 一郎',
            'email' => 'ichiro@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備①：今月（例：5月）の勤怠データ
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-15',
            'start_time' => '09:00', 
            'end_time' => '18:00',
        ]);

        // 🌟 準備②：翌月（例：6月）の勤怠データを作ります！
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-06-15',
            'start_time' => '09:00', 
            'end_time' => '18:00',
        ]);

        // 2. 店長さんでログイン！
        $this->actingAs($admin, 'admin');

        // 3. 「翌月ボタン」を押した先のURLへ直接ワープする！
        // 💡 前回の結果から ?month= のルールだと分かっているので、今度は 6月 を指定します！
        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=2026-06');

        // ✅ 期待挙動：ページがちゃんと開けたか？
        $response->assertStatus(200);

        // ✅ 期待挙動：翌月（6月）のデータがちゃんと表示されているか？
        $response->assertSee('06/15');

        // ✅ 期待挙動（おまけの完璧チェック！）：今月（5月）のデータが「表示されていない」ことも確認！
        $response->assertDontSee('05/15'); 
    }

    /**
     * テストケース ID: 14 (5行目)
     * 管理者が「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_can_navigate_to_attendance_detail()
    {
        // 1. 準備：店長さんと、スタッフ（一郎さん）を作ります
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => 'スタッフ 一郎',
            'email' => 'ichiro@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 準備：一郎さんの勤怠データを作ります！（このデータのIDを使います）
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-15',
            'start_time' => '09:00', 
            'end_time' => '18:00',
        ]);

        // 2. 店長さんでログイン！
        $this->actingAs($admin, 'admin');

        // 3. まずは、一郎さんの「勤怠一覧ページ」を開きます
        $listResponse = $this->get('/admin/attendance/staff/' . $user->id);
        $listResponse->assertStatus(200);

        // ✅ 期待挙動①：一覧ページに、ちゃんと「詳細画面へのリンク」が貼られているかチェック！
        $listResponse->assertSee('/admin/attendance/' . $attendance->id);

        // 4. 「詳細」ボタンをカチッと押したと仮定して、詳細画面のURLへ直接ワープ！
        $detailResponse = $this->get('/admin/attendance/' . $attendance->id);

        // ✅ 期待挙動②：詳細ページがちゃんと開けたか？
        $detailResponse->assertStatus(200);

        // ✅ 期待挙動③：詳細ページに、対象の日付が表示されているか？
        // 💡 detail.blade.php のフォーマット（Y年 / n月j日）に合わせて、2つの文字を探します！
        $detailResponse->assertSee('2026年');
        $detailResponse->assertSee('4月15日'); // 04ではなく4になります！
    }
}
