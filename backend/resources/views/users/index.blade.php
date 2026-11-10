@extends('layouts.app')

@section('title', 'Manajemen Akun Petugas & Admin')
@section('page-title', 'Manajemen Akun Pengguna & Petugas')
@section('page-subtitle', 'Kelola hak akses Role-Based Access Control (RBAC) antara Administrator dan Petugas Kebersihan')

@section('content')
<div class="space-y-6">

    <!-- 1. STATS METRIK AKUN -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-card-slide">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Akun Terdaftar</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $users->total() }} Pengguna</div>
                <span class="text-[11px] text-slate-500">Akses sistem internal</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Administrator</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $adminCount }} Akun</div>
                <span class="text-[11px] text-slate-500">Akses konfigurasi & laporan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs interactive-card flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Petugas Lapangan</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $petugasCount }} Akun</div>
                <span class="text-[11px] text-slate-500">Menerima alert & pengosongan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- 2. TABEL PENGGUNA -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs interactive-card space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Akun Pengguna</h2>
                <p class="text-xs text-slate-500">Pengaturan kredensial, peran operasional, dan kontak darurat</p>
            </div>
            <button onclick="openAddUserModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-xs transition interactive-card">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Akun Baru</span>
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3.5">Nama & Profil</th>
                        <th class="py-3 px-3.5">Peran / Role</th>
                        <th class="py-3 px-3.5">Kontak Telepon</th>
                        <th class="py-3 px-3.5">Status Akun</th>
                        <th class="py-3 px-3.5">Waktu Pendaftaran</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $user)
                        <tr class="table-row-standard">
                            <td class="py-3 px-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <strong class="text-slate-900 block text-xs">{{ $user->name }}</strong>
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3.5">
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i data-lucide="shield" class="w-3 h-3"></i> ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="user" class="w-3 h-3"></i> PETUGAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 font-mono text-slate-600">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="py-3 px-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Dinonaktifkan' }}
                                </span>
                            </td>
                            <td class="py-3 px-3.5 text-slate-400 font-mono text-[11px]">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="openEditUserModal('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}', '{{ $user->phone }}', '{{ $user->is_active ? 1 : 0 }}')"
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Edit Data">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('Hapus akun pengguna {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada akun pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-md w-full mx-4 shadow-2xl animate-modal-pop relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Tambah Akun Petugas / Admin</h3>
            <button onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap:</label>
                <input type="text" name="name" placeholder="Contoh: Ahmad Fadli" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat Email:</label>
                <input type="email" name="email" placeholder="Contoh: ahmad@smartwaste.id" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Peran / Role Akses:</label>
                <select name="role" required class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="petugas">Petugas Lapangan (Tugas Pengosongan Tong)</option>
                    <option value="admin">Administrator (Akses Penuh & Laporan)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp:</label>
                <input type="text" name="phone" placeholder="Contoh: 081234567890"
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password:</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold shadow-xs">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs hidden transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-md w-full mx-4 shadow-2xl animate-modal-pop relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Edit Akun Pengguna</h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editUserForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap:</label>
                <input type="text" id="editName" name="name" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat Email:</label>
                <input type="email" id="editEmail" name="email" required
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Peran / Role Akses:</label>
                <select id="editRole" name="role" required class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="petugas">Petugas Lapangan</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp:</label>
                <input type="text" id="editPhone" name="phone"
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Akun:</label>
                <select id="editStatus" name="is_active" class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif / Blokir</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password Baru (Kosongkan jika tidak diubah):</label>
                <input type="password" name="password" minlength="6" placeholder="Biarkan kosong untuk password lama"
                       class="w-full p-2.5 rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold shadow-xs">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddUserModal() { document.getElementById('addUserModal').classList.remove('hidden'); }
    function closeAddUserModal() { document.getElementById('addUserModal').classList.add('hidden'); }

    function openEditUserModal(id, name, email, role, phone, active) {
        document.getElementById('editUserForm').action = '/users/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        document.getElementById('editPhone').value = phone;
        document.getElementById('editStatus').value = active;
        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() { document.getElementById('editUserModal').classList.add('hidden'); }
</script>
@endsection

