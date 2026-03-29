<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class RequestTabs extends Component
{
    // 🌟 現在のタブを記憶する変数（最初は 'pending'）
    public $tab = 'pending';

    // 🌟 超絶魔法：この1行を書くだけで、自動的にURL（?tab=...）と連動します！！
    protected $queryString = ['tab'];

    // 🌟 ボタンが押されたら、タブの中身（$tab）を書き換える機能
    public function changeTab($newTab)
    {
        $this->tab = $newTab;
    }

    public function render()
    {
        // ========================================================
        // 🕵️‍♀️ ① まずは「承認待ち」と「承認済み」を探す基本の準備！
        // ========================================================
        // （※ with('attendance.user') とすることで、後で画面で名前を出す時にシステムが重くならないプロの裏技です！）
        $pendingQuery = StampCorrectionRequest::with('attendance.user')->where('status', '承認待ち');
        $approvedQuery = StampCorrectionRequest::with('attendance.user')->where('status', '承認済み');


        // ========================================================
        // 👑 ② 【ここがハイブリッドの魔法！】ログインしてるのは誰だ！？
        // ========================================================
        // もしログインしているのが「店長（admin）」じゃなかったら（＝一般スタッフなら）
        if (!Auth::guard('admin')->check()) {
            
            // 自分のIDを取得して…
            $userId = Auth::id();
            
            // 「自分の出勤データに紐づく申請だけ」に絞り込む！（店長の場合はここを無視するので全員分になります！）
            $pendingQuery->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
            $approvedQuery->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
        }


        // ========================================================
        // 📦 ③ 準備した条件で、実際にデータベースから持ってくる！
        // ========================================================
        $pendingRequests = $pendingQuery->get();
        $approvedRequests = $approvedQuery->get();

        // 探し出したデータを、Livewire専用の画面（blade）に渡す！
        return view('livewire.request-tabs', compact('pendingRequests', 'approvedRequests'));
    }
}
