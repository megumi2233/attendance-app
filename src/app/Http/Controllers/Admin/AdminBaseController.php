<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminBaseController extends Controller
{
    public function __construct()
    {
        // 🛡️ 鉄壁の守り：この親玉を継承するコントローラーは、
        // 自動的に「管理者ログイン」が必須になります！
        $this->middleware('auth:admin');
    }
}
