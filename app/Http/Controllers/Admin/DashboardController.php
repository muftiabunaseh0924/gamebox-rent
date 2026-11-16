<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $layanan): View
    {
        $periode = in_array($request->query('periode'), ['7', '30', 'bulan'], true) ? $request->query('periode') : 'bulan';

        return view('admin.dashboard', ['d' => $layanan->ambil($periode)]);
    }
}