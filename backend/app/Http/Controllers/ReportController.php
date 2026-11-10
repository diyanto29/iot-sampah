<?php

namespace App\Http\Controllers;

use App\Models\WasteLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $fileName = "laporan-pemilahan-sampah-{$startDate}-sd-{$endDate}.csv";

        return response()->streamDownload(function () use ($startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Header CSV
            fputcsv($handle, ['ID Log', 'ID Tempat Sampah', 'Kategori', 'Akurasi AI', 'Waktu Pemilahan']);

            // Stream chunk
            WasteLog::whereBetween('sorted_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->id,
                            $log->waste_bin_id,
                            strtoupper($log->category),
                            ($log->confidence * 100) . '%',
                            $log->sorted_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}

