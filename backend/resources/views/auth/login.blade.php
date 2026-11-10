<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SmartWaste IoT Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        @keyframes cardSlideIn {
            0% { opacity: 0; transform: translateY(16px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-card-slide { animation: cardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 via-slate-50 to-blue-50/30">

    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 p-8 shadow-xl animate-card-slide space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white shadow-md mx-auto">
                <i data-lucide="trash-2" class="w-6 h-6"></i>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">SmartWaste IoT</h1>
            <p class="text-xs text-slate-500">Sistem Informasi Pemantauan & Operasional Kebersihan</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@smartwaste.id') }}" required autofocus
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="password" id="password" name="password" value="password" required
                           class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                <span>Masuk ke Dashboard</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <!-- Demo Quick-Fill Buttons -->
        <div class="pt-4 border-t border-slate-100 text-center space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Akun Demo Sidang:</span>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" onclick="fillCreds('admin@smartwaste.id')" class="p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 transition text-left">
                    <strong class="block text-slate-800">Admin Pengelola</strong>
                    <span class="text-[10px] text-slate-400">admin@smartwaste.id</span>
                </button>
                <button type="button" onclick="fillCreds('petugas@smartwaste.id')" class="p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 transition text-left">
                    <strong class="block text-slate-800">Petugas Lapangan</strong>
                    <span class="text-[10px] text-slate-400">petugas@smartwaste.id</span>
                </button>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Password default: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-700">password</code></p>
        </div>
    </div>

    <script>
        lucide.createIcons();
        function fillCreds(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>

