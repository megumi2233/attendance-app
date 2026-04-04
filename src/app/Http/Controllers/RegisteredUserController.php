<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest; // 手作りのバリデーション係
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered; // 👈 🌟 追加！メール配達員への合図係

class RegisteredUserController extends Controller
{
    /**
     * 会員登録画面を表示する (createアクション)
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * 会員登録処理を実行する (storeアクション)
     */
    public function store(RegisterRequest $request)
    {
        // 1. チェックを通過したら、データベースに新しいユーザーを登録する
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // パスワードは暗号化（Hash）して保存！
        ]);

        // 👇 🌟 追加！「新しいユーザーが登録されたよ！」と大声で発表する（メール送信の合図）
        event(new Registered($user));

        // 2. 登録が完了したら、そのまま自動的にログイン状態にする
        Auth::login($user);

        // 3. 👇 🌟 変更！勤怠画面ではなく、メール認証誘導画面へジャンプ！
        return redirect('/email/verify');
    }
}
