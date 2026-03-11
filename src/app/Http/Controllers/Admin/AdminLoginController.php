<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    /**
     * 1. 管理者のログイン画面を表示する (設計書の create アクション)
     */
    public function create()
    {
        return view('admin.auth.login');
    }

    /**
     * 2. 管理者のログイン処理を実行する (store アクション)
     */
    public function store(LoginRequest $request)
    {
        // フォームから送信されたメールアドレスとパスワードを受け取る
        $credentials = $request->only('email', 'password');

        // 💡 超重要ポイント： Auth::guard('admin') を使って、一般ではなく「管理者名簿」と照合する！
        if (Auth::guard('admin')->attempt($credentials)) {
            // ログイン成功！セッションを新しくしてセキュリティを高める
            $request->session()->regenerate();

            // 管理者用の勤怠一覧画面へスパーン！とジャンプさせる！
            return redirect('/admin/attendance/list');
        }

        // ログイン失敗時：要件定義書(FN016)の通り、エラーメッセージを添えてログイン画面に戻す
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /**
     * 3. 管理者のログアウト処理を実行する (destroy アクション)
     */
    public function destroy(Request $request)
    {
        // 管理者としてログアウト
        Auth::guard('admin')->logout();

        // セッションを破壊して安全にする
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後は管理者のログイン画面へ戻す
        return redirect('/admin/login');
    }
}
