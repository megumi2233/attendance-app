<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminStampCorrectionRequestController extends Controller
{
    // 🌟 店長が来たら、ただ「申請一覧画面（index）」を開くだけの超シンプルなお仕事！
    public function index()
    {
        return view('admin.stamp_correction_request.index');
    }
}
