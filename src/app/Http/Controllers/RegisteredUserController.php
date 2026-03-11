<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest; // 👈 手作りのチェック係を呼び出す！
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
    public function store(RegisterRequest $request) // 👈 ここでチェック係を通す！
    {
        // チェックを通過したら、データベースに新しいユーザーを登録する
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // パスワードは暗号化（Hash）して保存！
        ]);

        // 登録が完了したら、そのまま自動的にログイン状態にする
        Auth::login($user);

        // 打刻画面（勤怠登録画面）へジャンプ！
        return redirect('/attendance');
    }
}
