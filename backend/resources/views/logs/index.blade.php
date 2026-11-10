@extends('layouts.app')

@section('title', 'Riwayat Pemilahan Sampah')
@section('page-title', 'Riwayat Pemilahan Terinci (3 Jenis Sampah)')
@section('page-subtitle', 'Log data transaksi pemilahan Organik, Anorganik Daur Ulang, dan Residu/B3 dari seluruh node IoT')

@section('content')
<div class="space-y-6">

    <!-- 1. STATS RINGKASAN DATA (4 Kolom dengan p-5) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-card-slide">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Data Terekam</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalFiltered }}</div>
                <span class="text-[11px] text-slate-500">Berdasarkan filter aktif</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                <i data-lucide="database" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Sampah Organik</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $organikCount }}</div>
                <span class="text-[11px] text-slate-500">Sisa makanan, daun & buah</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="leaf" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-600">Daur Ulang</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $anorganikCount }}</div>
                <span class="text-[11px] text-slate-500">Plastik, botol, kaleng & kertas</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Residu & B3</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $residuCount }}</div>
                <span class="text-[11px] text-slate-500">Baterai, sachet & styrofoam</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- 2. FILTER MULTIFAKTOR & PENCARIAN -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card space-y-4">
        <form method="GET" action="{{ route('logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Dari Tanggal -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Dari Tanggal:</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full p-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <!-- Sampai Tanggal -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Sampai Tanggal:</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="w-full p-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Kategori Sampah:</label>
                <select name="category" class="w-full p-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Kategori</option>
                    <option value="organik" {{ $category === 'organik' ? 'selected' : '' }}>Organik (Hijau)</option>
                    <option value="anorganik" {{ $category === 'anorganik' ? 'selected' : '' }}>Daur Ulang (Kuning/Biru)</option>
                    <option value="residu" {{ $category === 'residu' ? 'selected' : '' }}>Residu & B3 (Merah)</option>
                </select>
            </div>

            <!-- Titik Tempat Sampah -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Titik Tempat Sampah:</label>
                <select name="bin_id" class="w-full p-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua Lokasi</option>
                    @foreach($bins as $b)
                        <option value="{{ $b->id }}" {{ $binId === $b->id ? 'selected' : '' }}>{{ $b->id }} - {{ $b->location_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('logs.index') }}" class="p-2 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-100 transition" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>

        <!-- Tabel Riwayat Data Padat -->
        <div class="overflow-x-auto rounded-xl border border-slate-200/80 pt-2">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3.5">ID Log</th>
                        <th class="py-3 px-3.5">Waktu Pemilahan</th>
                        <th class="py-3 px-3.5">Tempat Sampah</th>
                        <th class="py-3 px-3.5">Klasifikasi AI</th>
                        <th class="py-3 px-3.5">Tingkat Keyakinan</th>
                        <th class="py-3 px-3.5 text-right">Aksi Servo Mekanik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                        <tr class="table-row-standard">
                            <td class="py-3 px-3.5 font-mono text-slate-400">#{{ str_pad($log->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3 px-3.5 font-mono text-[11px]">
                                <span class="font-bold text-slate-700 block">{{ $log->sorted_at->format('d M Y') }}</span>
                                <span class="text-slate-400">{{ $log->sorted_at->format('H:i:s') }}</span>
                            </td>
                            <td class="py-3 px-3.5">
                                <span class="font-bold font-mono text-slate-900 block">{{ $log->waste_bin_id }}</span>
                                <span class="text-[11px] text-slate-400">{{ $log->bin->location_name ?? 'Lokasi Kampus' }}</span>
                            </td>
                            <td class="py-3 px-3.5">
                                @if($log->category === 'organik')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="leaf" class="w-3 h-3"></i> ORGANIK
                                    </span>
                                @elseif($log->category === 'residu')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i> RESIDU / B3
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        <i data-lucide="package" class="w-3 h-3"></i> DAUR ULANG
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                @php
                                    $barBg = $log->category === 'organik' ? 'bg-emerald-500' : ($log->category === 'residu' ? 'bg-rose-500' : 'bg-sky-500');
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold w-10">{{ number_format($log->confidence * 100, 0) }}%</span>
                                    <div class="w-20 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $barBg }}" style="width: {{ $log->confidence * 100 }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3.5 text-right font-mono font-semibold text-[11px]">
                                @if($log->category === 'organik')
                                    <span class="text-emerald-700">BUKA SERVO 1 (TONG 1)</span>
                                @elseif($log->category === 'residu')
                                    <span class="text-rose-700">BUKA SERVO 3 (TONG 3)</span>
                                @else
                                    <span class="text-sky-700">BUKA SERVO 2 (TONG 2)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <span class="font-semibold block text-slate-700">Tidak ada riwayat pemilahan yang cocok.</span>
                                <span class="text-xs">Coba ubah filter rentang tanggal atau kata kunci pencarian.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
