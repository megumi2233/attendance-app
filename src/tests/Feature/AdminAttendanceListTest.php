<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストケース ID: 12 その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        // 1. 準備：管理者（店長さん）を作る
        // 🌟 READMEに合わせて、パスワードを 'password' にしました！
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'), 
        ]);

        // 2. 準備：一般ユーザー（スタッフ）を「2人」作る
        $user1 = User::create([
            'name' => '山田 太郎',
            'email' => 'yamada@example.com',
            'password' => bcrypt('password'),
        ]);
        $user2 = User::create([
            'name' => '佐藤 花子',
            'email' => 'sato@example.com',
            'password' => bcrypt('password'),
        ]);

        $today = Carbon::today()->format('Y-m-d');

        // 3. 準備：スタッフ2人分の「リアルな時間の勤怠データ」を作る！
        
        // 山田さん（早出・ちょい残業）
        Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'start_time' => '08:50:00',
            'end_time' => '18:05:00',
        ]);

        // 佐藤さん（ちょい遅刻・早上がり）
        Attendance::create([
            'user_id' => $user2->id,
            'date' => $today,
            'start_time' => '09:12:00',
            'end_time' => '17:55:00',
        ]);

        // 4. アクション：管理者としてログインして、一覧画面を開く
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        // 5. チェック：画面がちゃんと開けたか？
        $response->assertStatus(200);

        // ✅ 6. 期待挙動：全員の名前と、さっき設定した「リアルな時間」が表示されているかチェック！
        
        // 山田さんのチェック
        $response->assertSee('山田 太郎');
        $response->assertSee('08:50'); 
        $response->assertSee('18:05');

        // 佐藤さんのチェック
        $response->assertSee('佐藤 花子');
        $response->assertSee('09:12');
        $response->assertSee('17:55');
    }

    /**
     * テストケース ID: 12 遷移した際に現在の日付が表示される
     */
    public function test_admin_attendance_list_displays_current_date_initially()
    {
        // 1. 準備：管理者（店長さん）を作る
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 画像（index.blade.php）で見つけた「2つの形」で今日の日付を準備します！
        // 1つ目：タイトル用（例：2026年4月11日）※nとjを使うと 04月 ではなく 4月 になります
        $todayKanji = Carbon::now()->format('Y年n月j日');
        
        // 2つ目：カレンダー横用（例：2026/04/11）
        $todaySlash = Carbon::now()->format('Y/m/d');

        // 2. アクション：管理者としてログインして、一覧画面を開く
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        // 3. チェック：画面がちゃんと開けたか？
        $response->assertStatus(200);

        // ✅ 4. 期待挙動：今日の日付が「2つの形」両方とも画面に出ているかチェック！
        $response->assertSee($todayKanji); // 「〇年〇月〇日の勤怠」があるかな？
        $response->assertSee($todaySlash); // カレンダー横の「〇/〇/〇」があるかな？
    }

    /**
     * テストケース ID: 12 「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function test_admin_can_see_previous_day_attendance()
    {
        // 1. 準備：管理者（店長さん）と、スタッフを作る
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '昨日 働いたマン', // わかりやすい名前にしておきます！
            'email' => 'yesterday@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 魔法の時計（Carbon）を使って、「昨日」の日付を3つの形で用意します！
        $yesterday = Carbon::yesterday();
        $yesterdayParam = $yesterday->format('Y-m-d'); // 1. URLの ?date= 用（例: 2026-04-10）
        $yesterdayKanji = $yesterday->format('Y年n月j日'); // 2. タイトル用（例: 2026年4月10日）
        $yesterdaySlash = $yesterday->format('Y/m/d'); // 3. カレンダー用（例: 2026/04/10）

        // 2. 準備：スタッフの「昨日」の勤怠データを作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterdayParam,
            'start_time' => '08:58:00', // 👈 9時前ギリギリの優秀な出勤！
            'end_time' => '18:02:00',   // 👈 定時を少し過ぎてからの退勤！
        ]);

        // 3. アクション：管理者としてログインし、「前日」ボタンを押す！
        // 💡 URLの最後に「?date=昨日の日付」をくっつけて、過去のページへワープします
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=' . $yesterdayParam);

        // 4. チェック：無事に過去のページが開けたか？
        $response->assertStatus(200);

        // ✅ 5. 期待挙動：前日の日付と、前日のデータがちゃんと出ているかチェック！
        
        // 日付が「昨日」のものに変わっているかな？
        $response->assertSee($yesterdayKanji);
        $response->assertSee($yesterdaySlash);
        
        // 昨日のスタッフの名前と時間が表示されているかな？
        $response->assertSee('昨日 働いたマン');
        $response->assertSee('08:58'); // 👈 ここも修正！
        $response->assertSee('18:02'); // 👈 ここも修正！
    }

    /**
     * テストケース ID: 12 「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function test_admin_can_see_next_day_attendance()
    {
        // 1. 準備：管理者（店長さん）と、スタッフを作る
        $admin = Admin::create([
            'name' => 'テスト店長',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::create([
            'name' => '明日 働くマン', // わかりやすい名前にしておきます！
            'email' => 'tomorrow@example.com',
            'password' => bcrypt('password'),
        ]);

        // 🌟 魔法の時計（Carbon）を使って、「明日」の日付を3つの形で用意します！
        // 前日の yesterday() の代わりに、tomorrow() を使います！
        $tomorrow = Carbon::tomorrow();
        $tomorrowParam = $tomorrow->format('Y-m-d'); // 1. URLの ?date= 用（例: 2026-04-12）
        $tomorrowKanji = $tomorrow->format('Y年n月j日'); // 2. タイトル用（例: 2026年4月12日）
        $tomorrowSlash = $tomorrow->format('Y/m/d'); // 3. カレンダー用（例: 2026/04/12）

        // 2. 準備：スタッフの「明日」のリアルな勤怠データを作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrowParam,
            'start_time' => '08:55:00', // 👈 9時の5分前に出勤！
            'end_time' => '18:10:00',   // 👈 18時の10分後に退勤！
        ]);

        // 3. アクション：管理者としてログインし、「翌日」ボタンを押す！
        // 💡 URLの最後に「?date=明日の日付」をくっつけて、未来のページへワープします
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=' . $tomorrowParam);

        // 4. チェック：無事に未来のページが開けたか？
        $response->assertStatus(200);

        // ✅ 5. 期待挙動：翌日の日付と、翌日のデータがちゃんと出ているかチェック！
        
        // 日付が「明日」のものに変わっているかな？
        $response->assertSee($tomorrowKanji);
        $response->assertSee($tomorrowSlash);
        
        // 明日のスタッフの名前と時間が表示されているかな？
        $response->assertSee('明日 働くマン');
        $response->assertSee('08:55'); // 👈 ここも修正！
        $response->assertSee('18:10'); // 👈 ここも修正！
    }
}
