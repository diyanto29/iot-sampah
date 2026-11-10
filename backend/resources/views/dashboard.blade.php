@extends('layouts.app')

@section('title', 'Dashboard Monitoring 3 Kompartemen')
@section('page-title', 'Dashboard Pemantauan & Kapasitas (3 Kompartemen)')
@section('page-subtitle', 'Monitoring kapasitas real-time 3 jenis sampah (Organik, Anorganik Daur Ulang, Residu & B3) dan telemetri IoT kampus')

@section('content')
<div class="space-y-6">

    <!-- ALERT BANNER JIKA ADA TONG PENUH -->
    @if($pendingCount > 0)
        <div class="p-4 rounded-2xl bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-card-slide">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center font-bold text-lg shrink-0 animate-pulse">
                    ⚠️
                </div>
                <div>
                    <h4 class="font-extrabold text-sm tracking-tight">Perhatian: Terdapat {{ $pendingCount }} Insiden Kompartemen Sampah Penuh!</h4>
                    <p class="text-xs text-rose-100">Kapasitas volume telah mencapai $\ge 90\%$. Petugas kebersihan diharapkan segera melakukan pengosongan.</p>
                </div>
            </div>
            <a href="{{ route('alerts.index') }}" class="px-4 py-2 rounded-xl bg-white text-rose-700 hover:bg-rose-50 text-xs font-bold transition shadow-xs whitespace-nowrap self-start sm:self-auto flex items-center gap-1.5">
                <span>Buka Tiket Pengosongan</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    @endif

    <!-- 1. STATS METRIC STRIP (5 Kolom Terstruktur dengan Padding p-5) -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 animate-card-slide">
        <!-- Stat 1: Total Sampah Dipilah -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Dipilah</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1" id="stat-total">{{ $totalLogs }}</div>
                <span class="text-[11px] font-medium text-emerald-600 flex items-center gap-1 mt-0.5">
                    <i data-lucide="activity" class="w-3 h-3"></i> Real-time Telemetri
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 2: Sampah Organik -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Organik</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1" id="stat-organik">{{ $totalOrganik }}</div>
                <span class="text-[11px] text-slate-500 mt-0.5 block" id="stat-organik-sub">
                    {{ $totalLogs > 0 ? round(($totalOrganik / $totalLogs) * 100) : 0 }}% dari total
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i data-lucide="apple" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 3: Sampah Anorganik (Daur Ulang) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-sky-600">Daur Ulang</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1" id="stat-anorganik">{{ $totalAnorganik }}</div>
                <span class="text-[11px] text-slate-500 mt-0.5 block" id="stat-anorganik-sub">
                    {{ $totalLogs > 0 ? round(($totalAnorganik / $totalLogs) * 100) : 0 }}% dari total
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 4: Sampah Residu & B3 -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Residu & B3</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1" id="stat-residu">{{ $totalResidu }}</div>
                <span class="text-[11px] text-slate-500 mt-0.5 block" id="stat-residu-sub">
                    {{ $totalLogs > 0 ? round(($totalResidu / $totalLogs) * 100) : 0 }}% dari total
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 5: Node IoT Aktif -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Node IoT Aktif</span>
                <div class="text-xl font-extrabold text-slate-900 mt-1 font-mono">BIN-01</div>
                <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ count($bins) }} Titik Terdaftar
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i data-lucide="cpu" class="w-5 h-5"></i>
            </div>
        </div>
    </section>

    <!-- 2. TRIPLE BIN LIVE CAPACITY GAUGES (3 KOMPARTEMEN) -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Kompartemen Organik -->
        <div id="card-organik" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card relative overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i data-lucide="leaf" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">1. Organik (Hijau)</h2>
                        <p class="text-xs text-slate-500">Sisa makanan, kulit buah & dedaunan</p>
                    </div>
                </div>
                <span id="badge-org" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Status Aman
                </span>
            </div>

            <div class="mt-6 flex items-baseline justify-between">
                <div>
                    <span id="text-org-pct" class="text-4xl font-black text-emerald-600 tracking-tight">
                        {{ $latestCapacity ? $latestCapacity->organik_percent : 0 }}%
                    </span>
                    <span class="text-xs font-medium text-slate-400 ml-1">Kapasitas Terisi</span>
                </div>
                <div class="text-right text-xs text-slate-500 font-medium">
                    Sensor: <span class="font-bold text-slate-700 font-mono">HC-SR04P #1</span>
                </div>
            </div>

            <div class="w-full bg-slate-100 rounded-full h-3.5 mt-3 overflow-hidden p-0.5 border border-slate-200/80">
                <div id="bar-org" class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2.5 rounded-full transition-all duration-700"
                     style="width: {{ $latestCapacity ? min(100, $latestCapacity->organik_percent) : 0 }}%"></div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <i data-lucide="unlock" class="w-3.5 h-3.5 text-emerald-600"></i>
                    Aktuator: <strong class="text-slate-700 font-semibold">Servo 1 (Tutup Organik)</strong>
                </span>
                <span class="text-[11px] font-medium text-slate-400">Ambang: 90%</span>
            </div>
        </div>

        <!-- 2. Kompartemen Anorganik / Daur Ulang -->
        <div id="card-anorganik" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card relative overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <i data-lucide="package" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">2. Daur Ulang (Kuning/Biru)</h2>
                        <p class="text-xs text-slate-500">Botol PET, kaleng, kardus & kertas</p>
                    </div>
                </div>
                <span id="badge-anorg" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                    Status Aman
                </span>
            </div>

            <div class="mt-6 flex items-baseline justify-between">
                <div>
                    <span id="text-anorg-pct" class="text-4xl font-black text-sky-600 tracking-tight">
                        {{ $latestCapacity ? $latestCapacity->anorganik_percent : 0 }}%
                    </span>
                    <span class="text-xs font-medium text-slate-400 ml-1">Kapasitas Terisi</span>
                </div>
                <div class="text-right text-xs text-slate-500 font-medium">
                    Sensor: <span class="font-bold text-slate-700 font-mono">HC-SR04P #2</span>
                </div>
            </div>

            <div class="w-full bg-slate-100 rounded-full h-3.5 mt-3 overflow-hidden p-0.5 border border-slate-200/80">
                <div id="bar-anorg" class="bg-gradient-to-r from-sky-500 to-blue-500 h-2.5 rounded-full transition-all duration-700"
                     style="width: {{ $latestCapacity ? min(100, $latestCapacity->anorganik_percent) : 0 }}%"></div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <i data-lucide="unlock" class="w-3.5 h-3.5 text-sky-600"></i>
                    Aktuator: <strong class="text-slate-700 font-semibold">Servo 2 (Tutup Daur Ulang)</strong>
                </span>
                <span class="text-[11px] font-medium text-slate-400">Ambang: 90%</span>
            </div>
        </div>

        <!-- 3. Kompartemen Residu & B3 -->
        <div id="card-residu" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card relative overflow-hidden transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">3. Residu & B3 (Merah)</h2>
                        <p class="text-xs text-slate-500">Baterai, sachet laminasi, styrofoam</p>
                    </div>
                </div>
                <span id="badge-res" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    Status Aman
                </span>
            </div>

            <div class="mt-6 flex items-baseline justify-between">
                <div>
                    <span id="text-res-pct" class="text-4xl font-black text-rose-600 tracking-tight">
                        {{ $latestCapacity ? $latestCapacity->residu_percent : 0 }}%
                    </span>
                    <span class="text-xs font-medium text-slate-400 ml-1">Kapasitas Terisi</span>
                </div>
                <div class="text-right text-xs text-slate-500 font-medium">
                    Sensor: <span class="font-bold text-slate-700 font-mono">HC-SR04P #3</span>
                </div>
            </div>

            <div class="w-full bg-slate-100 rounded-full h-3.5 mt-3 overflow-hidden p-0.5 border border-slate-200/80">
                <div id="bar-res" class="bg-gradient-to-r from-rose-500 to-red-600 h-2.5 rounded-full transition-all duration-700"
                     style="width: {{ $latestCapacity ? min(100, $latestCapacity->residu_percent) : 0 }}%"></div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <i data-lucide="unlock" class="w-3.5 h-3.5 text-rose-600"></i>
                    Aktuator: <strong class="text-slate-700 font-semibold">Servo 3 (Tutup Residu)</strong>
                </span>
                <span class="text-[11px] font-medium text-slate-400">Ambang: 90%</span>
            </div>
        </div>
    </section>

    <!-- 3. SPLIT-VIEW ANALYTICS & RECENT LOGS TABLE -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kiri: Donut Ratio Chart (3 Kategori) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card lg:col-span-1 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-slate-900">Distribusi 3 Jenis Sampah</h3>
                    <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Rasio Volume</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">Komposisi sampah Organik, Daur Ulang, dan Residu/B3.</p>
            </div>

            <div class="w-56 h-56 mx-auto relative flex items-center justify-center my-2">
                <canvas id="ratioChart"></canvas>
            </div>

            <div class="pt-4 border-t border-slate-100 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <span class="block text-emerald-700 font-bold text-base" id="chart-org-val">{{ $totalOrganik }}</span>
                    <span class="text-[10px] text-emerald-600 font-medium">Organik</span>
                </div>
                <div class="p-2.5 rounded-xl bg-sky-50/60 border border-sky-100">
                    <span class="block text-sky-700 font-bold text-base" id="chart-anorg-val">{{ $totalAnorganik }}</span>
                    <span class="text-[10px] text-sky-600 font-medium">Daur Ulang</span>
                </div>
                <div class="p-2.5 rounded-xl bg-rose-50/60 border border-rose-100">
                    <span class="block text-rose-700 font-bold text-base" id="chart-res-val">{{ $totalResidu }}</span>
                    <span class="text-[10px] text-rose-600 font-medium">Residu/B3</span>
                </div>
            </div>
        </div>

        <!-- Kanan: Dense Activity Data Table -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card lg:col-span-2 flex flex-col justify-between overflow-hidden">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Riwayat Pemilahan Terkini</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            In-Memory AI
                        </span>
                    </div>
                    <a href="{{ route('logs.index') }}" class="text-xs text-blue-700 font-bold hover:underline flex items-center gap-1">
                        <span>Lihat Semua Log</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <p class="text-xs text-slate-500 mb-4">Log keputusan motor servo 3 arah dan klasifikasi AI.</p>
            </div>

            <div class="overflow-x-auto flex-1 rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3.5">Waktu</th>
                            <th class="py-2.5 px-3.5">ID Titik</th>
                            <th class="py-2.5 px-3.5">Klasifikasi AI</th>
                            <th class="py-2.5 px-3.5">Confidence</th>
                            <th class="py-2.5 px-3.5 text-right">Aksi Servo</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body" class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentLogs as $log)
                            <tr class="table-row-standard">
                                <td class="py-2.5 px-3.5 text-slate-500 font-mono text-[11px]">{{ $log->sorted_at->format('H:i:s') }}</td>
                                <td class="py-2.5 px-3.5 font-bold font-mono text-slate-800">{{ $log->waste_bin_id }}</td>
                                <td class="py-2.5 px-3.5">
                                    @if($log->category === 'organik')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="leaf" class="w-3 h-3"></i> ORGANIK
                                        </span>
                                    @elseif($log->category === 'residu')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i> RESIDU / B3
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                            <i data-lucide="package" class="w-3 h-3"></i> DAUR ULANG
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 font-medium">
                                    @php
                                        $barBg = $log->category === 'organik' ? 'bg-emerald-500' : ($log->category === 'residu' ? 'bg-rose-500' : 'bg-sky-500');
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 font-mono">{{ number_format($log->confidence * 100, 0) }}%</span>
                                        <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $barBg }}" style="width: {{ $log->confidence * 100 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3.5 text-right font-mono font-medium text-[11px] text-slate-600">
                                    @if($log->category === 'organik')
                                        <span class="text-emerald-700 font-bold">SERVO 1 BUKA</span>
                                    @elseif($log->category === 'residu')
                                        <span class="text-rose-700 font-bold">SERVO 3 BUKA</span>
                                    @else
                                        <span class="text-sky-700 font-bold">SERVO 2 BUKA</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada aktivitas pemilahan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi Donut Chart 3 Kompartemen
    const ctx = document.getElementById('ratioChart').getContext('2d');
    const ratioChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Organik', 'Daur Ulang', 'Residu/B3'],
            datasets: [{
                data: [{{ $totalOrganik }}, {{ $totalAnorganik }}, {{ $totalResidu }}],
                backgroundColor: ['#10b981', '#0ea5e9', '#f43f5e'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#64748b', font: { size: 10, weight: 'bold' }, padding: 12 }
                }
            },
            cutout: '70%'
        }
    });

    function updateGaugeCard(cardId, badgeId, barId, textId, pct, defaultBorderClass, defaultBadgeClass, defaultBadgeText) {
        document.getElementById(textId).innerText = `${pct}%`;
        const bar = document.getElementById(barId);
        bar.style.width = `${Math.min(100, pct)}%`;
        const card = document.getElementById(cardId);
        const badge = document.getElementById(badgeId);

        if (pct >= 90) {
            card.className = "bg-white border-2 border-rose-400 rounded-2xl p-6 shadow-xs interactive-card relative overflow-hidden animate-pulse-glow";
            badge.className = "px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-300";
            badge.innerText = "⚠️ Kritis / Penuh";
        } else if (pct >= 60) {
            card.className = "bg-white border border-amber-300 rounded-2xl p-6 shadow-xs interactive-card relative overflow-hidden";
            badge.className = "px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200";
            badge.innerText = "Waspada Sedang";
        } else {
            card.className = "bg-white " + defaultBorderClass + " rounded-2xl p-6 shadow-xs interactive-card relative overflow-hidden";
            badge.className = "px-2.5 py-1 rounded-full text-xs font-semibold " + defaultBadgeClass;
            badge.innerText = defaultBadgeText;
        }
    }

    // Real-Time Polling Dashboard Data
    async function syncDashboard() {
        try {
            const res = await fetch('/api/dashboard/stats');
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('stat-total').innerText = data.total_sorted;
            document.getElementById('stat-organik').innerText = data.ratio.organik;
            document.getElementById('stat-anorganik').innerText = data.ratio.anorganik;
            document.getElementById('stat-residu').innerText = data.ratio.residu || 0;

            const total = data.total_sorted || 1;
            document.getElementById('stat-organik-sub').innerText = `${Math.round((data.ratio.organik / total) * 100)}% dari total`;
            document.getElementById('stat-anorganik-sub').innerText = `${Math.round((data.ratio.anorganik / total) * 100)}% dari total`;
            document.getElementById('stat-residu-sub').innerText = `${Math.round(((data.ratio.residu || 0) / total) * 100)}% dari total`;

            document.getElementById('chart-org-val').innerText = data.ratio.organik;
            document.getElementById('chart-anorg-val').innerText = data.ratio.anorganik;
            document.getElementById('chart-res-val').innerText = data.ratio.residu || 0;

            ratioChart.data.datasets[0].data = [data.ratio.organik, data.ratio.anorganik, data.ratio.residu || 0];
            ratioChart.update();

            // Kapasitas 3 Kompartemen
            updateGaugeCard('card-organik', 'badge-org', 'bar-org', 'text-org-pct', data.capacity.organik || 0, 'border-slate-200', 'bg-emerald-50 text-emerald-700 border-emerald-200', 'Status Aman');
            updateGaugeCard('card-anorganik', 'badge-anorg', 'bar-anorg', 'text-anorg-pct', data.capacity.anorganik || 0, 'border-slate-200', 'bg-sky-50 text-sky-700 border-sky-200', 'Status Aman');
            updateGaugeCard('card-residu', 'badge-res', 'bar-res', 'text-res-pct', data.capacity.residu || 0, 'border-slate-200', 'bg-rose-50 text-rose-700 border-rose-200', 'Status Aman');

            // Update Logs Table
            const tbody = document.getElementById('logs-table-body');
            if (data.recent_logs && data.recent_logs.length > 0) {
                tbody.innerHTML = data.recent_logs.map(log => {
                    let badgeClass, iconName, barColor, servoAction, labelText;

                    if (log.category === 'organik') {
                        badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        iconName = 'leaf';
                        barColor = 'bg-emerald-500';
                        servoAction = '<span class="text-emerald-700 font-bold">SERVO 1 BUKA</span>';
                        labelText = 'ORGANIK';
                    } else if (log.category === 'residu') {
                        badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                        iconName = 'trash-2';
                        barColor = 'bg-rose-500';
                        servoAction = '<span class="text-rose-700 font-bold">SERVO 3 BUKA</span>';
                        labelText = 'RESIDU / B3';
                    } else {
                        badgeClass = 'bg-sky-50 text-sky-700 border-sky-200';
                        iconName = 'package';
                        barColor = 'bg-sky-500';
                        servoAction = '<span class="text-sky-700 font-bold">SERVO 2 BUKA</span>';
                        labelText = 'DAUR ULANG';
                    }

                    return `
                        <tr class="table-row-standard">
                            <td class="py-2.5 px-3.5 text-slate-500 font-mono text-[11px]">${new Date(log.timestamp).toLocaleTimeString()}</td>
                            <td class="py-2.5 px-3.5 font-bold font-mono text-slate-800">${log.device_id}</td>
                            <td class="py-2.5 px-3.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border ${badgeClass}">
                                    <i data-lucide="${iconName}" class="w-3 h-3"></i> ${labelText}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 font-mono">${Math.round(log.confidence * 100)}%</span>
                                    <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full ${barColor}" style="width: ${log.confidence * 100}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-3.5 text-right font-mono font-medium text-[11px] text-slate-600">
                                ${servoAction}
                            </td>
                        </tr>
                    `;
                }).join('');
                lucide.createIcons();
            }
        } catch (err) {}
    }

    setInterval(syncDashboard, 3000);
</script>
@endpush
