@extends('layouts.app')

@section('title', 'Master Tempat Sampah')
@section('page-title', 'Master Data Tempat Sampah Kampus')
@section('page-subtitle', 'Manajemen node tempat sampah cerdas multi-titik, lokasi, dan kalibrasi ketinggian wadah')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Node Terdaftar:</span>
            <span class="text-lg font-extrabold text-slate-900">{{ count($bins) }} Titik</span>
        </div>
        <button onclick="openAddBinModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-xs transition interactive-card">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Titik Tempat Sampah</span>
        </button>
    </div>

    <!-- Grid Kartu Tempat Sampah Multi-Titik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-card-slide">
        @foreach($bins as $bin)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card space-y-4 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                                <i data-lucide="archive" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base font-mono">{{ $bin->id }}</h3>
                                <p class="text-xs text-slate-500 font-medium">{{ $bin->location_name }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $bin->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                            {{ $bin->is_active ? 'Online' : 'Nonaktif' }}
                        </span>
                    </div>

                    <!-- Ketinggian & Sensor Specs -->
                    <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-semibold uppercase block">Tinggi Fisik Tong</span>
                            <span class="text-sm font-bold text-slate-800 font-mono">{{ $bin->bin_height_cm }} cm</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-semibold uppercase block">Total Dipilah</span>
                            <span class="text-sm font-bold text-blue-700 font-mono">{{ $bin->logs_count }} kali</span>
                        </div>
                    </div>

                    <!-- Progress Bar Kapasitas Terkini (3 Kompartemen) -->
                    <div class="mt-4 space-y-2">
                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="font-semibold text-emerald-700">1. Organik:</span>
                                <strong class="font-mono text-slate-800">{{ $bin->current_organik }}%</strong>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, $bin->current_organik) }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="font-semibold text-sky-700">2. Daur Ulang:</span>
                                <strong class="font-mono text-slate-800">{{ $bin->current_anorganik }}%</strong>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-sky-500 h-1.5 rounded-full" style="width: {{ min(100, $bin->current_anorganik) }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="font-semibold text-rose-700">3. Residu & B3:</span>
                                <strong class="font-mono text-slate-800">{{ $bin->current_residu }}%</strong>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ min(100, $bin->current_residu) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[10px] text-slate-400">
                        Aktif: {{ $bin->last_active_at ? \Carbon\Carbon::parse($bin->last_active_at)->diffForHumans() : 'Belum pernah' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button onclick="openEditBinModal('{{ $bin->id }}', '{{ $bin->location_name }}', '{{ $bin->bin_height_cm }}', '{{ $bin->is_active ? 1 : 0 }}')"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Edit">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                        @if($bin->id !== 'BIN-01')
                            <form method="POST" action="{{ route('bins.destroy', $bin->id) }}" onsubmit="return confirm('Hapus tempat sampah ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Tambah Bin -->
<div id="addBinModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-md w-full mx-4 shadow-2xl animate-modal-pop relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Tambah Titik Tempat Sampah</h3>
            <button onclick="closeAddBinModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('bins.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">ID Perangkat (Harus Unik):</label>
                <input type="text" name="id" placeholder="Contoh: BIN-04" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 font-mono outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lokasi / Area Kampus:</label>
                <input type="text" name="location_name" placeholder="Contoh: Gedung Rektorat Lantai 2" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tinggi Fisik Wadah (cm):</label>
                <input type="number" step="0.5" name="bin_height_cm" value="30.0" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
                <span class="text-[10px] text-slate-400">Digunakan untuk rumus kalibrasi persentase sensor ultrasonik.</span>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeAddBinModal()" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold shadow-xs">Simpan Titik</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Bin -->
<div id="editBinModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-md w-full mx-4 shadow-2xl animate-modal-pop relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Edit Tempat Sampah <span id="editBinTitleId" class="font-mono text-blue-700"></span></h3>
            <button onclick="closeEditBinModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editBinForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lokasi / Area Kampus:</label>
                <input type="text" id="editLocation" name="location_name" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tinggi Fisik Wadah (cm):</label>
                <input type="number" step="0.5" id="editHeight" name="bin_height_cm" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Operasional:</label>
                <select id="editActive" name="is_active" class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="1">Aktif / Online</option>
                    <option value="0">Nonaktif / Dalam Perawatan</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeEditBinModal()" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold shadow-xs">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddBinModal() { document.getElementById('addBinModal').classList.remove('hidden'); }
    function closeAddBinModal() { document.getElementById('addBinModal').classList.add('hidden'); }

    function openEditBinModal(id, loc, height, active) {
        document.getElementById('editBinForm').action = '/bins/' + id;
        document.getElementById('editBinTitleId').innerText = id;
        document.getElementById('editLocation').value = loc;
        document.getElementById('editHeight').value = height;
        document.getElementById('editActive').value = active;
        document.getElementById('editBinModal').classList.remove('hidden');
    }
    function closeEditBinModal() { document.getElementById('editBinModal').classList.add('hidden'); }
</script>
@endsection
