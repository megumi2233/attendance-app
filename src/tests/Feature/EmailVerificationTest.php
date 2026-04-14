<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL; // 👈 🌟 この1行を新しく追加してください！
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストケース ID: 16 (1行目)
     * 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        // 1. 準備：お知らせ（メール）が飛んでいくのを待ち構えます
        Notification::fake();

        // 2. 実行：会員登録をします
        // READMEに書いてあったテスト用アカウントの情報で登録してみましょう！
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 3. 登録後の動きを確認
        // 以前の web.php の写真を見ると、登録後は /email/verify にリダイレクトされるはずです
        $response->assertRedirect('/email/verify');

        // ✅ 期待挙動：登録したメールアドレス宛に、認証メールが送信されているか？
        // データベースから今登録したユーザーを探してきます
        $user = User::where('email', 'test@example.com')->first();

        // ロボットが「このユーザーに認証メールを送ったよ！」という証拠を見つけたかチェック
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /**
     * テストケース ID: 16 (2行目)
     * メール認証誘導画面に「認証はこちらから（Mailhog）」へのリンクが正しく表示されている
     */
    public function test_email_verification_screen_has_link_to_mailhog()
    {
        // 1. 準備：未認証のユーザーを作ります
        $user = User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => null,
        ]);

        // 2. 実行：ログインして誘導画面を表示する
        $response = $this->actingAs($user)->get('/email/verify');

        // ✅ 期待挙動①：誘導画面がちゃんと開けるか？
        $response->assertStatus(200);

        // ✅ 期待挙動②：画面の中に「認証はこちらから」というボタン（リンク）があるか？
        $response->assertSee('認証はこちらから');

        // ✅ 期待挙動③：そのリンク先が、設計書通りの Mailhog（8025ポート）になっているか？
        $response->assertSee('http://localhost:8025');
    }

    /**
     * テストケース ID: 16 (3行目)
     * メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_email_can_be_verified_and_redirects_to_attendance_screen()
    {
        // 1. 準備：未認証のユーザーを作ります
        $user = User::create([
            'name' => '認証完了テストユーザー',
            'email' => 'verify-done@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => null, // まだ未認証！
        ]);

        // 2. 魔法：Laravelの「署名付き認証URL」をロボットに作らせます
        // これがメールの中に届く「本物の認証リンク」の代わりになります
        // ※ファイルの上のほうに「use Illuminate\Support\Facades\URL;」があれば、ここはそのまま使えます
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 3. 実行：そのリンクをポチッと踏んだことにします
        $response = $this->actingAs($user)->get($verificationUrl);

        // ✅ 期待挙動①：認証後、自動で「勤怠登録画面（/attendance）」へ遷移（リダイレクト）するか？
       // こっちの書き方なら、オマケがついても文句を言いません！
        $response->assertRedirectContains('/attendance');

        // ✅ 期待挙動②：データベースの「email_verified_at」にちゃんと日付が入ったか？
        // これが「認証済み」になった動かぬ証拠です！
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
