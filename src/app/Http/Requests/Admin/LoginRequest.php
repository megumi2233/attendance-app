<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * 1. このリクエスト（送信）を許可するかどうか
     */
    public function authorize()
    {
        // 💡 超重要：最初は false（全員拒否）になっているので、必ず true（許可） に変更する！
        return true;
    }

    /**
     * 2. チェックするルール（バリデーションルール）
     */
    public function rules()
    {
        return [
            // メールアドレスとパスワードは、絶対に必要（required）だよ！というルール
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 3. ルールを破った時に出す「日本語のエラーメッセージ」
     */
    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.string' => 'メールアドレスは文字列で入力してください',
            // 👇 この1行を追加！
            'email.email' => 'メールアドレスは正しい形式で入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.string' => 'パスワードは文字列で入力してください',
        ];
    }
}
