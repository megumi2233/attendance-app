<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    // データを探すお仕事はLivewireに任せたので、ここは画面を開くだけでOK！
    public function index()
    {
        return view('request.list');
    }
}
