<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\WasteBin;
use App\Models\WasteLog;
use App\Models\BinCapacity;
use App\Models\CapacityAlert;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin & Petugas
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartwaste.id'],
            [
                'name' => 'Administrator Pengelola Fasilitas',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $petugas = User::firstOrCreate(
            ['email' => 'petugas@smartwaste.id'],
            [
                'name' => 'Budi Santoso (Petugas Lapangan)',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'phone' => '089876543210',
                'is_active' => true,
            ]
        );

        // 2. Master Tempat Sampah Multi-Titik Kampus
        $bins = [
            [
                'id' => 'BIN-01',
                'location_name' => 'Lobby Gedung Utama Fasilkom',
                'bin_height_cm' => 30.0,
                'is_active' => true,
                'last_active_at' => now(),
            ],
            [
                'id' => 'BIN-02',
                'location_name' => 'Area Kantin Mahasiswa',
                'bin_height_cm' => 35.0,
                'is_active' => true,
                'last_active_at' => now()->subMinutes(5),
            ],
            [
                'id' => 'BIN-03',
                'location_name' => 'Lantai 1 Perpustakaan Pusat',
                'bin_height_cm' => 30.0,
                'is_active' => true,
                'last_active_at' => now()->subMinutes(12),
            ],
        ];

        foreach ($bins as $binData) {
            WasteBin::updateOrCreate(['id' => $binData['id']], $binData);
        }

        // 3. Telemetri Awal 3 Kompartemen
        BinCapacity::updateOrCreate(
            ['waste_bin_id' => 'BIN-01'],
            [
                'organik_percent' => 38.0,
                'anorganik_percent' => 45.0,
                'residu_percent' => 92.5,
                'recorded_at' => now(),
            ]
        );

        // 4. Alert Kapasitas Awal untuk Demo (Insiden Kompartemen Residu Penuh)
        CapacityAlert::firstOrCreate(
            [
                'waste_bin_id' => 'BIN-01',
                'compartment' => 'residu',
                'status' => 'menunggu',
            ],
            [
                'capacity_percent' => 92.5,
                'created_at' => now()->subMinutes(8),
            ]
        );

        CapacityAlert::firstOrCreate(
            [
                'waste_bin_id' => 'BIN-02',
                'compartment' => 'organik',
                'status' => 'ditangani',
            ],
            [
                'capacity_percent' => 91.0,
                'handled_by' => $petugas->id,
                'handled_at' => now()->subHours(2),
                'notes' => 'Telah dikosongkan dan kantong sampah diganti baru.',
                'created_at' => now()->subHours(2)->subMinutes(15),
            ]
        );

        // 5. Seed beberapa sampel log riwayat pemilahan 3 jenis sampah
        if (WasteLog::where('category', 'residu')->count() == 0) {
            $categories = ['organik', 'anorganik', 'residu'];
            $binIds = ['BIN-01', 'BIN-02', 'BIN-03'];
            for ($i = 0; $i < 18; $i++) {
                $cat = $categories[$i % 3];
                WasteLog::create([
                    'waste_bin_id' => $binIds[array_rand($binIds)],
                    'category' => $cat,
                    'confidence' => mt_rand(85, 98) / 100.0,
                    'sorted_at' => now()->subMinutes($i * 12 + 5),
                ]);
            }
        }
    }
}
