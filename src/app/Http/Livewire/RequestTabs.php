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
        $userId = Auth::id();

        // ① 自分の「承認待ち」データを探し出す
        $pendingRequests = StampCorrectionRequest::with('attendance')
            ->where('status', '承認待ち')
            ->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        // ② 自分の「承認済み」データを探し出す
        $approvedRequests = StampCorrectionRequest::with('attendance')
            ->where('status', '承認済み')
            ->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        // 探し出したデータを、Livewire専用の画面（blade）に渡す！
        return view('livewire.request-tabs', compact('pendingRequests', 'approvedRequests'));
    }
}
