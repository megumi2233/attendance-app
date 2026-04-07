<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin; // 🌟 今度は「管理者」の役者を呼び出す準備！

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストケース1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required_for_login()
    {
        // 1. テスト用の「管理者」をデータベースに準備する
        $admin = Admin::create([
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. 管理者用のログイン画面（/admin/login）に、わざと空のメールを送る
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // 3. エラーが出ているかチェック！
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * テストケース2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required_for_login()
    {
        $admin = Admin::create([
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '', // わざと空にする
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * テストケース3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        $admin = Admin::create([
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword', // わざと間違えたパスワードを入れる
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * おまけ: 正しい情報ならちゃんと管理者としてログインできること
     */
    public function test_admin_can_login_with_correct_credentials()
    {
        $admin = Admin::create([
            'name' => '管理者テスト',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123', // 完璧な情報を入れる
        ]);

        // ログイン成功したら、エラーがないはず！
        $response->assertSessionHasNoErrors();
        // ルート設計書通りなら、管理者の勤怠一覧画面に飛ぶはず！
        $response->assertRedirect('/admin/attendance/list');
    }
}
