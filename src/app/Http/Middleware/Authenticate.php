<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // 👇 🌟 ここから追加
            // もし「admin」から始まるURLに行こうとしていたら、管理者のログイン画面へ
            if ($request->is('admin*')) {
                return url('/admin/login');
            }
            // 👆 🌟 ここまで追加

            // それ以外（一般ユーザー）は、いつものログイン画面へ
            return route('login');
        }
    }
}
