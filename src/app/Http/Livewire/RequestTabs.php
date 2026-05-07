<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class RequestTabs extends Component
{
    public $tab = 'pending';

    protected $queryString = ['tab'];

    public function changeTab($newTab)
    {
        $this->tab = $newTab;
    }

    public function render()
    {
        $pendingQuery = StampCorrectionRequest::with('attendance.user')->where('status', '承認待ち');
        $approvedQuery = StampCorrectionRequest::with('attendance.user')->where('status', '承認済み');

        if (!Auth::guard('admin')->check()) {
            $userId = Auth::id();

            $pendingQuery->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
            $approvedQuery->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
        }

        $pendingRequests = $pendingQuery->get();
        $approvedRequests = $approvedQuery->get();

        return view('livewire.request-tabs', compact('pendingRequests', 'approvedRequests'));
    }
}
