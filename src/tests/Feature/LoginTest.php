<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User; // 🌟 ユーザー作成の魔法を使うための準備！

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストケース1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required_for_login()
    {
        // 1. テスト用の役者（ユーザー）をデータベースに準備する
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'), // パスワードは暗号化して保存！
        ]);

        // 2. わざとメールアドレスを空にしてログインボタンを押す
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // 3. 正しいエラーメッセージが出ているかチェック！
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * テストケース2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required_for_login()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '', // わざと空にする
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * テストケース3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword', // わざと間違えたパスワードを入れる
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    /**
     * おまけ（でも超重要！）: 正しい情報ならちゃんとログインできること
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123', // 完璧な情報を入れる
        ]);

        // ログイン状態になっていることを確認！（認証チェック）
        $this->assertAuthenticatedAs($user);
    }
}
