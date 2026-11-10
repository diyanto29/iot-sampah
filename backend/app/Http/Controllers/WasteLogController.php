<?php

namespace App\Http\Controllers;

use App\Models\WasteLog;
use App\Models\WasteBin;
use Illuminate\Http\Request;

class WasteLogController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $category = $request->query('category');
        $binId = $request->query('bin_id');
        $search = $request->query('search');

        $query = WasteLog::with('bin')->latest('sorted_at');

        if ($startDate) {
            $query->whereDate('sorted_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('sorted_at', '<=', $endDate);
        }
        if ($category && in_array($category, ['organik', 'anorganik', 'residu'])) {
            $query->where('category', $category);
        }
        if ($binId) {
            $query->where('waste_bin_id', $binId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('waste_bin_id', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(15)->withQueryString();
        $bins = WasteBin::all();

        $totalFiltered = $logs->total();
        $organikCount = WasteLog::where('category', 'organik')->count();
        $anorganikCount = WasteLog::where('category', 'anorganik')->count();
        $residuCount = WasteLog::where('category', 'residu')->count();

        return view('logs.index', compact('logs', 'bins', 'startDate', 'endDate', 'category', 'binId', 'search', 'totalFiltered', 'organikCount', 'anorganikCount', 'residuCount'));
    }
}
