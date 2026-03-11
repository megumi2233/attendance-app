<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; // 👈 手作りのチェック係を呼び出す！
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    /**
     * ログイン画面を表示する (createアクション)
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理を実行する (storeアクション)
     */
    public function store(LoginRequest $request) // 👈 ここでチェック係を通す！
    {
        // フォームから送信されたメールとパスワードを受け取る
        $credentials = $request->only('email', 'password');

        // 一般ユーザーの名簿（データベース）と照合する
        if (Auth::attempt($credentials)) {
            // ログイン成功！セッションを新しくする
            $request->session()->regenerate();

            // 打刻画面へジャンプ！
            return redirect('/attendance');
        }

        // 💡 ログイン失敗時：めぐみさんが見つけた要件定義書通りのエラーを返す！
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /**
     * ログアウト処理を実行する (destroyアクション)
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後はログイン画面へ戻す
        return redirect('/login');
    }
}
