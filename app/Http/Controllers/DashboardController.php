<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $pesanan = $request->user()
            ->bookings()
            ->with('console')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', ['pesanan' => $pesanan]);
    }
}