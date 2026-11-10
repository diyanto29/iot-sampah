<?php

namespace App\Http\Controllers;

use App\Models\WasteBin;
use App\Models\WasteLog;
use App\Models\BinCapacity;
use App\Models\CapacityAlert;

class DashboardController extends Controller
{
    public function index()
    {
        $bins = WasteBin::all();
        $totalLogs = WasteLog::count();
        $totalOrganik = WasteLog::where('category', 'organik')->count();
        $totalAnorganik = WasteLog::where('category', 'anorganik')->count();
        $totalResidu = WasteLog::where('category', 'residu')->count();
        $latestCapacity = BinCapacity::latest('recorded_at')->first();
        $recentLogs = WasteLog::latest('sorted_at')->take(10)->get();
        $pendingAlerts = CapacityAlert::with('bin')->where('status', 'menunggu')->latest()->get();
        $pendingCount = $pendingAlerts->count();

        return view('dashboard', compact(
            'bins',
            'totalLogs',
            'totalOrganik',
            'totalAnorganik',
            'totalResidu',
            'latestCapacity',
            'recentLogs',
            'pendingAlerts',
            'pendingCount'
        ));
    }
}
