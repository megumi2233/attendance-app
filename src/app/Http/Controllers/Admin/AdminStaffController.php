<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;

class AdminStaffController extends AdminBaseController
{
    public function index()
    {
        $users = User::all();

        return view('admin.staff.index', compact('users'));
    }
}
