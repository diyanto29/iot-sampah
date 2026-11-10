@extends('layouts.app')

@section('title', 'Pusat Alert & Insiden')
@section('page-title', 'Pusat Peringatan & Insiden Kapasitas')
@section('page-subtitle', 'Manajemen tiket insiden tempat sampah penuh dan riwayat pengosongan oleh petugas')

@section('content')
<div class="space-y-6">

    <!-- 1. STATS METRIK INSIDEN -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-card-slide">
        <!-- Menunggu Tindakan -->
        <div class="bg-white border {{ $pendingCount > 0 ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Menunggu Pengosongan</span>
                <div class="text-3xl font-extrabold text-slate-900 mt-1 flex items-baseline gap-2">
                    <span>{{ $pendingCount }}</span>
                    @if($pendingCount > 0)
                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200 animate-pulse">
                            Perlu Tindakan!
                        </span>
                    @endif
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Tong mencapai batas $\ge 90\%$</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Sudah Ditangani -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Selesai Dikosongkan</span>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $handledCount }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Telah diverifikasi petugas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="check-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Insiden Tercatat -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Insiden Terdeteksi</span>
                <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $totalCount }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Riwayat sensor ultrasonik</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                <i data-lucide="bell" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- 2. FILTER & TABEL INSIDEN -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Tiket & Riwayat Peringatan</h2>
                <p class="text-xs text-slate-500">Pantau dan selesaikan peringatan kapasitas tempat sampah kampus</p>
            </div>

            <!-- Filter Status Tab -->
            <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                <a href="{{ route('alerts.index') }}" class="px-3 py-1.5 rounded-lg transition {{ empty($status) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                    Semua ({{ $totalCount }})
                </a>
                <a href="{{ route('alerts.index', ['status' => 'menunggu']) }}" class="px-3 py-1.5 rounded-lg transition {{ $status === 'menunggu' ? 'bg-white text-rose-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                    Menunggu ({{ $pendingCount }})
                </a>
                <a href="{{ route('alerts.index', ['status' => 'ditangani']) }}" class="px-3 py-1.5 rounded-lg transition {{ $status === 'ditangani' ? 'bg-white text-emerald-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                    Ditangani ({{ $handledCount }})
                </a>
            </div>
        </div>

        <!-- Tabel Insiden -->
        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3.5">Waktu Kejadian</th>
                        <th class="py-3 px-3.5">Tempat Sampah</th>
                        <th class="py-3 px-3.5">Kompartemen</th>
                        <th class="py-3 px-3.5">Kapasitas</th>
                        <th class="py-3 px-3.5">Status Tiket</th>
                        <th class="py-3 px-3.5">Petugas Penangan</th>
                        <th class="py-3 px-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($alerts as $alert)
                        <tr class="table-row-standard {{ $alert->status === 'menunggu' ? 'bg-rose-50/20' : '' }}">
                            <td class="py-3 px-3.5 text-slate-500 font-mono text-[11px]">
                                <span class="font-bold text-slate-700 block">{{ $alert->created_at->format('d M Y') }}</span>
                                <span>{{ $alert->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="py-3 px-3.5">
                                <span class="font-bold font-mono text-slate-900 block">{{ $alert->waste_bin_id }}</span>
                                <span class="text-[11px] text-slate-400">{{ $alert->bin->location_name ?? 'Lokasi Kampus' }}</span>
                            </td>
                            <td class="py-3 px-3.5">
                                @if($alert->compartment === 'organik')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="leaf" class="w-3 h-3"></i> ORGANIK
                                    </span>
                                @elseif($alert->compartment === 'residu')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i> RESIDU / B3
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        <i data-lucide="package" class="w-3 h-3"></i> DAUR ULANG
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 font-bold font-mono text-rose-600">
                                {{ number_format($alert->capacity_percent, 1) }}%
                            </td>
                            <td class="py-3 px-3.5">
                                @if($alert->status === 'menunggu')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        MENUNGGU
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3"></i> SELESAI
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                @if($alert->handler)
                                    <span class="font-bold text-slate-800 block">{{ $alert->handler->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $alert->handled_at ? $alert->handled_at->format('H:i - d M') : '-' }}</span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum ditangani</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-right">
                                @if($alert->status === 'menunggu')
                                    <button onclick="openResolveModal('{{ $alert->id }}', '{{ $alert->waste_bin_id }}', '{{ $alert->compartment }}', '{{ $alert->capacity_percent }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-xs transition interactive-card">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        <span>Tandai Dikosongkan</span>
                                    </button>
                                @else
                                    <button onclick="showNotesModal('{{ $alert->notes ?? 'Tidak ada catatan' }}')" class="text-slate-400 hover:text-slate-700 text-xs font-semibold underline">
                                        Lihat Catatan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-500"></i>
                                <span class="font-semibold block text-slate-700">Tidak ada tiket insiden.</span>
                                <span class="text-xs">Semua tempat sampah dalam kondisi kapasitas aman.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $alerts->links() }}
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pengosongan -->
<div id="resolveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-md w-full mx-4 shadow-2xl animate-modal-pop relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Verifikasi Pengosongan Tong</h3>
            </div>
            <button onclick="closeResolveModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="resolveForm" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tempat Sampah:</span>
                        <strong class="font-mono text-slate-800" id="modalBinId">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kompartemen:</span>
                        <strong class="uppercase text-slate-800" id="modalCompartment">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kapasitas Terakhir:</span>
                        <strong class="text-rose-600" id="modalPct">-</strong>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-slate-200">
                        <span class="text-slate-500">Petugas yang Melaporkan:</span>
                        <strong class="text-blue-700">{{ auth()->user()->name ?? 'Petugas Lapangan' }}</strong>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Operasional (Opsional):</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Tong telah dikosongkan dan kantong plastik baru sudah dipasang."
                              class="w-full p-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeResolveModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs">
                        Konfirmasi & Reset Kapasitas
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lihat Catatan -->
<div id="notesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-sm w-full mx-4 shadow-2xl animate-modal-pop relative">
        <h3 class="font-bold text-slate-900 text-sm mb-2">Catatan Penanganan:</h3>
        <p id="notesContent" class="text-xs text-slate-600 p-3 bg-slate-50 rounded-xl border border-slate-200 mb-4 leading-relaxed"></p>
        <button onclick="document.getElementById('notesModal').classList.add('hidden')" class="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            Tutup
        </button>
    </div>
</div>

<script>
    function openResolveModal(id, binId, comp, pct) {
        document.getElementById('resolveForm').action = '/alerts/' + id + '/resolve';
        document.getElementById('modalBinId').innerText = binId;
        document.getElementById('modalCompartment').innerText = comp.toUpperCase();
        document.getElementById('modalPct').innerText = pct + '%';
        document.getElementById('resolveModal').classList.remove('hidden');
    }
    function closeResolveModal() {
        document.getElementById('resolveModal').classList.add('hidden');
    }
    function showNotesModal(notes) {
        document.getElementById('notesContent').innerText = notes;
        document.getElementById('notesModal').classList.remove('hidden');
    }
</script>
@endsection

