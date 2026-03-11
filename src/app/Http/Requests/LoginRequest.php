<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * 1. 誰でもこのリクエストを送れるように true にする
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 2. 要件定義書通りのバリデーションルール
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 3. 要件定義書(FN009)で指定された日本語のエラーメッセージ
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
