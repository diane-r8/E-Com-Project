<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Example for fetching users

class AdminController extends Controller
{
    public function __construct()
{
    $this->middleware(['auth', 'admin']); // Ensure authentication first
}

    public function dashboard()
    {
        $usersCount = User::count(); // Example: count total users
        return view('admin.dashboard', compact('usersCount'));
    }
}
