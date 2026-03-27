<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // 👇 🌟 一般ユーザー（スタッフ）のモデル社長を呼び出す！

class AdminStaffController extends Controller
{
    // ==========================================
    // 🌟 スタッフ一覧画面を表示するお仕事
    // ==========================================
    public function index()
    {
        // Userモデル社長に「スタッフ全員分のデータを取ってきて！」とお願いする
        $users = User::all();

        // 取得した「$users」のデータを、めぐみさんが作った画面（Blade）に渡してあげる！
        return view('admin.staff.index', compact('users'));
    }
}
