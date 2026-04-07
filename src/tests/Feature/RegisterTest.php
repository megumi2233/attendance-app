<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    // 👇 🌟超重要魔法！テストが終わるたびにDBを「空っぽの更地」に戻してくれます！
    use RefreshDatabase;

    /**
     * テストケース1: 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required()
    {
        // わざと名前を空にして登録ボタンを押す（POST送信）
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 名前のエラーが出ていること、かつ指定のメッセージであるかをチェック！
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * テストケース2: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '', // 空にする
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * テストケース3: パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'short', // 8文字未満にする
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * テストケース4: パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_passwords_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password', // 確認用をわざと間違える
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /**
     * テストケース5: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '', // 空にする
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * テストケース6: フォームに内容が正しく入力されていた場合、データが正常に保存される
     */
    public function test_user_can_register()
    {
        // 完璧なデータを入力して登録！
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 🌟 データベースのusersテーブルに、本当に保存されたかをチェック！
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 登録後は、メール認証誘導画面にリダイレクトされるはず！
        $response->assertRedirect('/email/verify');
    }
}
