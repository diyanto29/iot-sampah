<?php

namespace App\Http\Controllers;

use App\Models\CapacityAlert;
use App\Models\BinCapacity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $compartment = $request->query('compartment');
        $binId = $request->query('bin_id');

        $query = CapacityAlert::with(['bin', 'handler'])->latest();

        if ($status && in_array($status, ['menunggu', 'ditangani'])) {
            $query->where('status', $status);
        }

        if ($compartment && in_array($compartment, ['organik', 'anorganik', 'residu'])) {
            $query->where('compartment', $compartment);
        }

        if ($binId) {
            $query->where('waste_bin_id', $binId);
        }

        $alerts = $query->paginate(15)->withQueryString();

        $pendingCount = CapacityAlert::where('status', 'menunggu')->count();
        $handledCount = CapacityAlert::where('status', 'ditangani')->count();
        $totalCount = CapacityAlert::count();

        return view('alerts.index', compact('alerts', 'pendingCount', 'handledCount', 'totalCount', 'status', 'compartment', 'binId'));
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $alert = CapacityAlert::findOrFail($id);

        $alert->update([
            'status' => 'ditangani',
            'handled_by' => Auth::id() ?? 2, // fallback to petugas seeder if unauth
            'handled_at' => now(),
            'notes' => $request->input('notes', 'Tong telah dikosongkan dan kantong diganti baru.'),
        ]);

        // Reset nilai kapasitas di database agar dashboard kembali hijau
        $latest = BinCapacity::where('waste_bin_id', $alert->waste_bin_id)->latest('recorded_at')->first();
        if ($latest) {
            $org = ($alert->compartment === 'organik') ? 5.0 : $latest->organik_percent;
            $anorg = ($alert->compartment === 'anorganik') ? 5.0 : $latest->anorganik_percent;
            $res = ($alert->compartment === 'residu') ? 5.0 : $latest->residu_percent;

            BinCapacity::create([
                'waste_bin_id' => $alert->waste_bin_id,
                'organik_percent' => $org,
                'anorganik_percent' => $anorg,
                'residu_percent' => $res,
                'recorded_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', "Insiden tong {$alert->waste_bin_id} (Kompartemen: {$alert->compartment}) berhasil diselesaikan oleh " . (Auth::user()->name ?? 'Petugas') . "!");
    }
}
