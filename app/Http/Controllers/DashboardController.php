<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Dipanggil setelah login (route 'dashboard') - gantikan redirectByRole()
    public function redirect(Request $request)
    {
        return match ($request->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'pengepul' => redirect()->route('pengepul.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    }
}
