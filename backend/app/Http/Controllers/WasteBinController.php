<?php

namespace App\Http\Controllers;

use App\Models\WasteBin;
use App\Models\BinCapacity;
use Illuminate\Http\Request;

class WasteBinController extends Controller
{
    public function index()
    {
        $bins = WasteBin::withCount(['logs', 'alerts'])->get()->map(function ($bin) {
            $latest = BinCapacity::where('waste_bin_id', $bin->id)->latest('recorded_at')->first();
            $bin->current_organik = $latest ? (float)$latest->organik_percent : 0;
            $bin->current_anorganik = $latest ? (float)$latest->anorganik_percent : 0;
            $bin->current_residu = $latest ? (float)$latest->residu_percent : 0;
            return $bin;
        });

        return view('bins.index', compact('bins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:30|unique:waste_bins,id',
            'location_name' => 'required|string|max:150',
            'bin_height_cm' => 'required|numeric|min:10|max:200',
        ]);

        $validated['is_active'] = true;
        $validated['last_active_at'] = now();

        WasteBin::create($validated);

        return redirect()->route('bins.index')->with('success', "Tempat sampah {$validated['id']} berhasil ditambahkan!");
    }

    public function update(Request $request, $id)
    {
        $bin = WasteBin::findOrFail($id);

        $validated = $request->validate([
            'location_name' => 'required|string|max:150',
            'bin_height_cm' => 'required|numeric|min:10|max:200',
            'is_active' => 'required|boolean',
        ]);

        $bin->update($validated);

        return redirect()->route('bins.index')->with('success', "Tempat sampah {$bin->id} berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $bin = WasteBin::findOrFail($id);
        $binId = $bin->id;
        $bin->delete();

        return redirect()->route('bins.index')->with('success', "Tempat sampah {$binId} berhasil dihapus!");
    }
}

