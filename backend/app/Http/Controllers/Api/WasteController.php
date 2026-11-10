<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WasteBin;
use App\Models\WasteLog;
use App\Models\BinCapacity;
use App\Models\CapacityAlert;

class WasteController extends Controller
{
    private string $aiServiceUrl;

    public function __construct()
    {
        $this->aiServiceUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:8001/detect');
    }

    public function predict(Request $request)
    {
        $request->validate([
            'file' => 'required|image',
            'device_id' => 'nullable|string'
        ]);

        $deviceId = $request->input('device_id', 'BIN-01');
        $file = $request->file('file');

        // Pastikan device terdaftar
        WasteBin::firstOrCreate(
            ['id' => $deviceId],
            [
                'location_name' => 'Lokasi ' . $deviceId,
                'bin_height_cm' => 30.0,
                'is_active' => true,
                'last_active_at' => now(),
            ]
        );

        try {
            // Forward file ke Python AI Worker via internal HTTP (RAM in-memory)
            $response = Http::timeout(15)->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($this->aiServiceUrl);

            if ($response->failed()) {
                $kategori = 'anorganik';
                $confidence = 0.50;
                $activeServo = 'SERVO_2';
                $targetBin = 2;
                $servoAction = 'BUKA TUTUP TONG 2 (DAUR ULANG)';
                $servoStates = ['servo_1' => 0, 'servo_2' => 90, 'servo_3' => 0];
            } else {
                $aiResult = $response->json();
                $kategori = $aiResult['kategori'] ?? 'anorganik';
                $confidence = $aiResult['confidence'] ?? 0.85;
                $activeServo = $aiResult['active_servo'] ?? ($kategori === 'organik' ? 'SERVO_1' : ($kategori === 'residu' ? 'SERVO_3' : 'SERVO_2'));
                $targetBin = $aiResult['target_bin'] ?? ($kategori === 'organik' ? 1 : ($kategori === 'residu' ? 3 : 2));
                $servoAction = $aiResult['servo_action'] ?? ($kategori === 'organik' ? 'BUKA TUTUP TONG 1 (ORGANIK)' : ($kategori === 'residu' ? 'BUKA TUTUP TONG 3 (RESIDU)' : 'BUKA TUTUP TONG 2 (DAUR ULANG)'));
                $servoStates = $aiResult['servo_states'] ?? [
                    'servo_1' => $kategori === 'organik' ? 90 : 0,
                    'servo_2' => $kategori === 'anorganik' ? 90 : 0,
                    'servo_3' => $kategori === 'residu' ? 90 : 0,
                ];
            }

            // Simpan riwayat pemilahan ke Database
            WasteLog::create([
                'waste_bin_id' => $deviceId,
                'category' => $kategori,
                'confidence' => $confidence,
                'sorted_at' => now(),
            ]);

            WasteBin::where('id', $deviceId)->update(['last_active_at' => now()]);

            return response()->json([
                'status' => 'success',
                'device_id' => $deviceId,
                'kategori' => $kategori,
                'confidence' => (float)$confidence,
                'target_bin' => $targetBin,
                'active_servo' => $activeServo,
                'servo_action' => $servoAction,
                'servo_states' => $servoStates,
                'detection_source' => $aiResult['detection_source'] ?? 'ai_model',
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function recordCapacity(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'organik_percent' => 'required|numeric',
            'anorganik_percent' => 'required|numeric',
            'residu_percent' => 'nullable|numeric',
        ]);

        $deviceId = $request->input('device_id');
        $orgPct = (float)$request->input('organik_percent');
        $anorgPct = (float)$request->input('anorganik_percent');
        $resPct = (float)$request->input('residu_percent', 0.0);

        WasteBin::firstOrCreate(
            ['id' => $deviceId],
            ['location_name' => 'Lokasi ' . $deviceId, 'bin_height_cm' => 30.0, 'is_active' => true]
        );

        BinCapacity::create([
            'waste_bin_id' => $deviceId,
            'organik_percent' => $orgPct,
            'anorganik_percent' => $anorgPct,
            'residu_percent' => $resPct,
            'recorded_at' => now(),
        ]);

        WasteBin::where('id', $deviceId)->update(['last_active_at' => now()]);

        // Otomatis Buat Tiket Alert di Database jika >= 90%
        $alertCreated = false;

        $checkCompartments = [
            'organik' => $orgPct,
            'anorganik' => $anorgPct,
            'residu' => $resPct,
        ];

        foreach ($checkCompartments as $comp => $pct) {
            if ($pct >= 90.0) {
                $existing = CapacityAlert::where('waste_bin_id', $deviceId)
                    ->where('compartment', $comp)
                    ->where('status', 'menunggu')
                    ->first();
                if (!$existing) {
                    CapacityAlert::create([
                        'waste_bin_id' => $deviceId,
                        'compartment' => $comp,
                        'capacity_percent' => $pct,
                        'status' => 'menunggu',
                    ]);
                    $alertCreated = true;
                }
            }
        }

        // Peringatan Telegram
        $telegramToken = env('TELEGRAM_BOT_TOKEN');
        $telegramChatId = env('TELEGRAM_CHAT_ID');

        if ($telegramToken && $telegramChatId && ($orgPct >= 90.0 || $anorgPct >= 90.0 || $resPct >= 90.0)) {
            $msg = "⚠️ *PERINGATAN KAPASITAS TEMPAT SAMPAH (3 KOMPARTEMEN)!*\n";
            $msg .= "ID Titik: `{$deviceId}`\n";
            if ($orgPct >= 90.0) $msg .= "🟢 *Kompartemen Organik PENUH:* {$orgPct}%\n";
            if ($anorgPct >= 90.0) $msg .= "🔵 *Kompartemen Anorganik/Daur Ulang PENUH:* {$anorgPct}%\n";
            if ($resPct >= 90.0) $msg .= "🔴 *Kompartemen Residu/B3 PENUH:* {$resPct}%\n";
            $msg .= "Waktu: " . now()->format('Y-m-d H:i:s');

            try {
                Http::timeout(3)->post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
                    'chat_id' => $telegramChatId,
                    'text' => $msg,
                    'parse_mode' => 'Markdown'
                ]);
            } catch (\Exception $e) {}
        }

        return response()->json([
            'status' => 'success',
            'device_id' => $deviceId,
            'organik_percent' => $orgPct,
            'anorganik_percent' => $anorgPct,
            'residu_percent' => $resPct,
            'alert_created' => $alertCreated,
        ]);
    }

    public function stats()
    {
        $latestCapacity = BinCapacity::latest('recorded_at')->first();
        $recentLogs = WasteLog::latest('sorted_at')->take(10)->get();
        $totalLogs = WasteLog::count();
        $totalOrganik = WasteLog::where('category', 'organik')->count();
        $totalAnorganik = WasteLog::where('category', 'anorganik')->count();
        $totalResidu = WasteLog::where('category', 'residu')->count();
        $pendingAlertsCount = CapacityAlert::where('status', 'menunggu')->count();

        return response()->json([
            'capacity' => [
                'organik' => $latestCapacity ? (float)$latestCapacity->organik_percent : 0,
                'anorganik' => $latestCapacity ? (float)$latestCapacity->anorganik_percent : 0,
                'residu' => $latestCapacity ? (float)$latestCapacity->residu_percent : 0,
                'updated_at' => $latestCapacity ? $latestCapacity->recorded_at->toIso8601String() : null,
            ],
            'total_sorted' => $totalLogs,
            'ratio' => [
                'organik' => $totalOrganik,
                'anorganik' => $totalAnorganik,
                'residu' => $totalResidu,
            ],
            'pending_alerts_count' => $pendingAlertsCount,
            'recent_logs' => $recentLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'device_id' => $log->waste_bin_id,
                    'category' => $log->category,
                    'confidence' => (float)$log->confidence,
                    'timestamp' => $log->sorted_at->toIso8601String(),
                ];
            }),
        ]);
    }
}
