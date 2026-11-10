<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartWaste IoT') — Sistem Pemilah Sampah Cerdas & Monitoring</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            50: '#f0f5fa',
                            100: '#e1ecf5',
                            600: '#1b4d7e',
                            800: '#14385c',
                            900: '#0f243c',
                        },
                        brand: {
                            DEFAULT: '#004a87',
                            dark: '#0f243c',
                            light: '#e1ecf5',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* =========================================================================
           INTERACTIVE MICRO-ANIMATIONS & TRANSITION SYSTEM (Adopsi simutasi-asn)
        ========================================================================== */
        @keyframes cardSlideIn {
            0% {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes modalPopIn {
            0% {
                opacity: 0;
                transform: scale(0.92) translateY(20px);
            }
            60% {
                opacity: 1;
                transform: scale(1.015) translateY(-3px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.45);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(225, 29, 72, 0);
            }
        }

        @keyframes floatGentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .animate-card-slide {
            animation: cardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .animate-modal-pop {
            animation: modalPopIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-pulse-glow {
            animation: pulseGlow 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
        }

        .animate-float {
            animation: floatGentle 3s ease-in-out infinite;
        }

        .interactive-card {
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.22s ease,
                        background-color 0.22s ease;
        }

        .interactive-card:hover {
            transform: translateY(-3px) scale(1.008);
            box-shadow: 0 12px 28px -6px rgba(15, 36, 60, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.04);
        }

        .interactive-card:active {
            transform: translateY(-1px) scale(0.995);
        }

        .table-row-standard {
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .table-row-standard:hover {
            background-color: rgba(239, 246, 255, 0.75) !important;
            transform: translateX(4px);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50 flex overflow-hidden">

    <!-- Mobile Sidebar Backdrop -->
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity"></div>

    <!-- =========================================================================
         LEFT SIDEBAR NAVIGATION (GovTech / Enterprise SaaS Standard)
    ========================================================================== -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:z-auto">
        <!-- Brand Header -->
        <div class="h-16 px-5 border-b border-slate-200 flex items-center justify-between bg-white shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white shadow-sm font-bold">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-slate-900 text-sm tracking-tight">SmartWaste IoT</span>
                        <span class="bg-blue-50 text-blue-700 text-[10px] uppercase font-bold px-1.5 py-0.2 rounded border border-blue-200">v1.0</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-semibold block leading-tight">Sistem Operasional Kampus</span>
                </div>
            </a>
            <button onclick="toggleSidebar()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 lg:hidden">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Menu Utama</div>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard Monitoring</span>
                </div>
            </a>

            <!-- Pusat Alert & Insiden -->
            @php
                $pendingAlertsCount = \App\Models\CapacityAlert::where('status', 'menunggu')->count();
            @endphp
            <a href="{{ route('alerts.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('alerts.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="bell-ring" class="w-4 h-4"></i>
                    <span>Pusat Alert & Insiden</span>
                </div>
                @if($pendingAlertsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white animate-pulse">
                        {{ $pendingAlertsCount }}
                    </span>
                @endif
            </a>

            <!-- Riwayat Pemilahan -->
            <a href="{{ route('logs.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('logs.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Riwayat Pemilahan</span>
                </div>
            </a>

            <!-- Master Tempat Sampah -->
            <a href="{{ route('bins.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('bins.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                    <span>Master Tempat Sampah</span>
                </div>
            </a>

            <div class="pt-4 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Administrasi</div>

            <!-- Manajemen Akun -->
            <a href="{{ route('users.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('users.*') ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Manajemen Akun</span>
                </div>
            </a>

            <!-- Ekspor Laporan CSV -->
            <a href="{{ route('reports.waste.export') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                    <span>Unduh Rekapitulasi (CSV)</span>
                </div>
                <i data-lucide="download" class="w-3.5 h-3.5 text-slate-400"></i>
            </a>
        </div>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-slate-200 bg-slate-50/60 shrink-0">
            @auth
                <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200 shadow-xs">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0 uppercase">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="truncate">
                            <span class="block text-xs font-bold text-slate-900 truncate leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.2 rounded-sm {{ auth()->user()->isAdmin() ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ auth()->user()->role }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl bg-blue-700 text-white font-semibold text-xs shadow-xs hover:bg-blue-800 transition">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Akun</span>
                </a>
            @endauth
        </div>
    </aside>

    <!-- =========================================================================
         MAIN APPLICATION AREA (Header + Content Area)
    ========================================================================== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Executive Navbar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
            <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-xs text-slate-400 hidden sm:block">@yield('page-subtitle', 'Sistem Monitoring & Pemilah Sampah Cerdas Kampus')</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Edge Server Status -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs font-medium text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Edge AI Online</span>
                    </div>

                    <!-- Tombol Kamera Webcam Laptop -->
                    <button onclick="openWebcamModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition shadow-xs">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Kamera Webcam</span>
                    </button>

                    <!-- Tombol Simulasi Cepat (Selalu Tersedia) -->
                    <button onclick="openSimModal('upload')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-xs">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Uji File Foto</span>
                    </button>

                    <!-- Alert Indicator Link -->
                    <a href="{{ route('alerts.index') }}" class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        @if($pendingAlertsCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500"></span>
                        @endif
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs animate-card-slide">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs animate-card-slide">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1 shadow-xs animate-card-slide">
                    <div class="flex items-center gap-2 font-bold">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                        <span>Terjadi kesalahan:</span>
                    </div>
                    <ul class="list-disc pl-6 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
            @yield('content')
        </main>
    </div>

    <!-- =========================================================================
         MODAL SIMULASI & PENGUJIAN AI YOLO (PRESET, UPLOAD FILE & WEBCAM)
    ========================================================================== -->
    <div id="simModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden transition-opacity overflow-y-auto py-6">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 max-w-2xl w-full mx-4 shadow-2xl animate-modal-pop relative">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shadow-xs">
                        <i data-lucide="scan" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Uji Coba Deteksi AI Pemilah Sampah</h3>
                        <p class="text-xs text-slate-400">Pengujian multi-tier AI: Deep Neural Vision, YOLOv8, Gemini & IoT Actuator</p>
                    </div>
                </div>
                <button onclick="closeSimModal()" class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="flex rounded-2xl bg-slate-100 p-1.5 mb-5 text-xs font-bold text-slate-600">
                <button type="button" id="tab-btn-upload" onclick="switchSimTab('upload')" class="flex-1 py-2 rounded-xl bg-white text-indigo-700 shadow-xs flex items-center justify-center gap-2 transition">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>Upload File Foto</span>
                </button>
                <button type="button" id="tab-btn-webcam" onclick="switchSimTab('webcam')" class="flex-1 py-2 rounded-xl hover:text-slate-900 flex items-center justify-center gap-2 transition">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>Webcam Laptop</span>
                </button>
                <button type="button" id="tab-btn-preset" onclick="switchSimTab('preset')" class="flex-1 py-2 rounded-xl hover:text-slate-900 flex items-center justify-center gap-2 transition">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Preset Cepat (3 Tong)</span>
                </button>
            </div>

            <!-- 1. TAB UPLOAD FOTO (DRAG & DROP) -->
            <div id="tab-content-upload" class="space-y-4">
                <div id="dropzone" onclick="document.getElementById('fileUploadInput').click()" 
                     class="border-2 border-dashed border-indigo-200 hover:border-indigo-500 rounded-3xl p-7 text-center cursor-pointer bg-indigo-50/20 hover:bg-indigo-50/50 transition group">
                    <input type="file" id="fileUploadInput" accept="image/*" class="hidden" onchange="handleFileUpload(event)">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100/70 text-indigo-600 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-xs">
                        <i data-lucide="image-plus" class="w-7 h-7"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-800">Klik untuk memilih foto atau seret file ke area ini</p>
                    <p class="text-xs text-slate-400 mt-1">Mendukung format JPG, PNG, WEBP (Botol plastik, daun, kulit buah, sachet, baterai, dll.)</p>
                </div>

                <!-- Preview Area -->
                <div id="uploadPreviewArea" class="hidden items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <img id="uploadImgThumb" src="" class="w-16 h-16 rounded-xl object-cover border border-slate-200 shadow-2xs">
                    <div class="flex-1 min-w-0">
                        <span id="uploadFileName" class="text-xs font-extrabold text-slate-800 truncate block"></span>
                        <span class="text-[11px] text-slate-400 block mt-0.5">Siap dianalisis oleh Deep Neural Vision & YOLO</span>
                    </div>
                    <button type="button" onclick="submitUploadedFile()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Proses Deteksi</span>
                    </button>
                </div>
            </div>

            <!-- 2. TAB WEBCAM LAPTOP -->
            <div id="tab-content-webcam" class="hidden space-y-4">
                <div class="relative rounded-3xl overflow-hidden bg-slate-900 aspect-video flex items-center justify-center border border-slate-300 shadow-inner">
                    <video id="webcamVideo" autoplay playsinline class="w-full h-full object-cover hidden"></video>
                    <div id="webcamPlaceholder" class="text-center p-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="video-off" class="w-6 h-6"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-300">Kamera laptop belum aktif.</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Izinkan akses kamera di browser Anda untuk deteksi real-time</p>
                        <button type="button" onclick="startWebcam()" class="mt-3 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition inline-flex items-center gap-2">
                            <i data-lucide="video" class="w-4 h-4"></i>
                            <span>Nyalakan Kamera Webcam</span>
                        </button>
                    </div>
                </div>

                <div id="webcamControls" class="hidden items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Arahkan sampel sampah ke tengah kamera</span>
                    </div>
                    <button type="button" onclick="captureWebcam()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>Ambil Foto & Deteksi</span>
                    </button>
                </div>
            </div>

            <!-- 3. TAB PRESET CEPAT -->
            <div id="tab-content-preset" class="hidden space-y-3">
                <p class="text-xs text-slate-500 mb-2 leading-relaxed">
                    Pilih sampel sampah tiruan untuk memverifikasi routing aktuator 3 kompartemen:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button onclick="triggerSim('organik')" class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50/80 text-left transition interactive-card group shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2.5 group-hover:scale-110 transition shadow-2xs">
                            <i data-lucide="leaf" class="w-4 h-4"></i>
                        </div>
                        <span class="block font-black text-sm text-slate-900">1. Organik</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Daun, Buah & Sayur</span>
                        <span class="text-xs text-emerald-700 font-bold block mt-2">Buka Servo 1 (90°)</span>
                    </button>

                    <button onclick="triggerSim('anorganik')" class="p-4 rounded-2xl border border-sky-200 bg-sky-50/40 hover:bg-sky-50/80 text-left transition interactive-card group shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center mb-2.5 group-hover:scale-110 transition shadow-2xs">
                            <i data-lucide="recycle" class="w-4 h-4"></i>
                        </div>
                        <span class="block font-black text-sm text-slate-900">2. Daur Ulang</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Botol PET, Kaleng & Kardus</span>
                        <span class="text-xs text-sky-700 font-bold block mt-2">Buka Servo 2 (90°)</span>
                    </button>

                    <button onclick="triggerSim('residu')" class="p-4 rounded-2xl border border-rose-200 bg-rose-50/40 hover:bg-rose-50/80 text-left transition interactive-card group shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center mb-2.5 group-hover:scale-110 transition shadow-2xs">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </div>
                        <span class="block font-black text-sm text-slate-900">3. Residu & B3</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Sachet Rinso, Baterai & Medis</span>
                        <span class="text-xs text-rose-700 font-bold block mt-2">Buka Servo 3 (90°)</span>
                    </button>
                </div>
            </div>

            <!-- RESULT BANNER (EXPANDED ENTERPRISE HIGH-FIDELITY) -->
            <div id="simResultBox" class="mt-5 rounded-2xl border transition-all duration-300 hidden overflow-hidden shadow-sm animate-card-slide">
                <!-- Header Hasil -->
                <div id="simResultHeader" class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 border-b">
                    <div class="flex items-center gap-3">
                        <div id="simResultIconWrap" class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white shadow-xs">
                            <i id="simResultIcon" data-lucide="check-circle-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Hasil Klasifikasi AI:</span>
                            <span id="simResultCategory" class="text-base font-black"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="simResultSourceBadge" class="text-xs px-3 py-1 rounded-full font-bold shadow-2xs inline-flex items-center gap-1.5"></span>
                    </div>
                </div>

                <!-- 3 Dense Metric Cards -->
                <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white">
                    <!-- Card 1: Kompartemen Target -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-1">Target Kompartemen</span>
                        <span id="simResultTargetBin" class="text-sm font-black text-slate-900 block truncate">Tong 1: Organik</span>
                        <span id="simResultTargetSub" class="text-[11px] font-semibold text-emerald-600 block mt-0.5">Aktuator Servo 1 (Hijau)</span>
                    </div>

                    <!-- Card 2: Keyakinan AI -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-1">Tingkat Keyakinan</span>
                        <div class="flex items-baseline gap-2">
                            <span id="simResultConfidence" class="text-xl font-black font-mono text-slate-900">98%</span>
                            <span class="text-[11px] text-slate-400 font-medium">probabilitas</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2 overflow-hidden">
                            <div id="simResultConfBar" class="h-1.5 rounded-full transition-all duration-500 bg-emerald-500" style="width: 98%"></div>
                        </div>
                    </div>

                    <!-- Card 3: Aksi Servo & Fisik -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider mb-1">Aksi Aktuator Servo</span>
                        <span id="simResultServo" class="text-sm font-black text-indigo-700 block truncate">BUKA TUTUP TONG 1</span>
                        <span id="simResultServoAngle" class="text-[11px] font-mono text-slate-500 block mt-0.5">Rotasi: 90° (Tutup Terbuka)</span>
                    </div>
                </div>

                <!-- Status IoT Dispatch -->
                <div class="px-5 py-2.5 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-slate-600 font-medium">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Telemetri IoT: <strong class="font-bold text-slate-800">Node BIN-01</strong> terhubung</span>
                    </div>
                    <span id="simResultTimestamp" class="text-[11px] font-mono text-slate-500">Real-time</span>
                </div>
            </div>

            <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Node Telemetri: <code class="font-mono text-slate-700 font-bold bg-slate-100 px-2 py-0.5 rounded-md">BIN-01</code></span>
                <span id="sim-status" class="text-indigo-600 font-bold">Siap Menerima Input</span>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        let activeWebcamStream = null;
        let selectedUploadFile = null;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobileBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function openWebcamModal() {
            document.getElementById('simModal').classList.remove('hidden');
            switchSimTab('webcam');
            startWebcam();
        }

        function openSimModal(defaultTab = 'upload') {
            document.getElementById('simModal').classList.remove('hidden');
            switchSimTab(defaultTab);
            if (defaultTab === 'webcam') {
                startWebcam();
            }
        }

        function closeSimModal() {
            document.getElementById('simModal').classList.add('hidden');
            stopWebcam();
            const resBox = document.getElementById('simResultBox');
            if (resBox) resBox.classList.add('hidden');
        }

        function switchSimTab(tab) {
            ['upload', 'webcam', 'preset'].forEach(t => {
                const content = document.getElementById('tab-content-' + t);
                const btn = document.getElementById('tab-btn-' + t);
                if (content && btn) {
                    if (t === tab) {
                        content.classList.remove('hidden');
                        btn.className = "flex-1 py-1.5 rounded-lg bg-white text-indigo-700 shadow-xs flex items-center justify-center gap-1.5 transition";
                    } else {
                        content.classList.add('hidden');
                        btn.className = "flex-1 py-1.5 rounded-lg hover:text-slate-900 flex items-center justify-center gap-1.5 transition";
                    }
                }
            });

            if (tab !== 'webcam') {
                stopWebcam();
            }
        }

        // ==========================================
        // 1. HANDLER UPLOAD FOTO DARI LAPTOP
        // ==========================================
        function handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            selectedUploadFile = file;

            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('uploadImgThumb').src = evt.target.result;
                document.getElementById('uploadFileName').innerText = file.name;
                document.getElementById('uploadPreviewArea').classList.remove('hidden');
                document.getElementById('uploadPreviewArea').classList.add('flex');
            };
            reader.readAsDataURL(file);
        }

        async function submitUploadedFile() {
            if (!selectedUploadFile) return;
            await sendImageFileToApi(selectedUploadFile, selectedUploadFile.name);
        }

        // ==========================================
        // 2. HANDLER WEBCAM LAPTOP LANGSUNG
        // ==========================================
        async function startWebcam() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: { ideal: 640 }, height: { ideal: 480 } } 
                });
                activeWebcamStream = stream;
                const videoEl = document.getElementById('webcamVideo');
                videoEl.srcObject = stream;
                videoEl.classList.remove('hidden');
                document.getElementById('webcamPlaceholder').classList.add('hidden');
                document.getElementById('webcamControls').classList.remove('hidden');
                document.getElementById('webcamControls').classList.add('flex');
            } catch (err) {
                alert("Gagal mengakses webcam laptop: " + err.message + ". Pastikan izin kamera browser diizinkan.");
            }
        }

        function stopWebcam() {
            if (activeWebcamStream) {
                activeWebcamStream.getTracks().forEach(t => t.stop());
                activeWebcamStream = null;
            }
            const videoEl = document.getElementById('webcamVideo');
            if (videoEl) {
                videoEl.srcObject = null;
                videoEl.classList.add('hidden');
            }
            const placeholder = document.getElementById('webcamPlaceholder');
            if (placeholder) placeholder.classList.remove('hidden');
            const controls = document.getElementById('webcamControls');
            if (controls) controls.classList.add('hidden');
        }

        async function captureWebcam() {
            const videoEl = document.getElementById('webcamVideo');
            if (!videoEl || !activeWebcamStream) return;

            const canvas = document.createElement('canvas');
            canvas.width = videoEl.videoWidth || 640;
            canvas.height = videoEl.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async (blob) => {
                await sendImageFileToApi(blob, 'webcam_capture.jpg');
            }, 'image/jpeg', 0.88);
        }

        // ==========================================
        // 3. HANDLER PRESET SIMULASI CEPAT
        // ==========================================
        async function triggerSim(kategori) {
            const canvas = document.createElement('canvas');
            canvas.width = 64;
            canvas.height = 64;
            const c = canvas.getContext('2d');
            if (kategori === 'organik') {
                c.fillStyle = '#15803d';
            } else if (kategori === 'residu') {
                c.fillStyle = '#dc2626';
            } else {
                c.fillStyle = '#0284c7';
            }
            c.fillRect(0, 0, 64, 64);

            canvas.toBlob(async (blob) => {
                await sendImageFileToApi(blob, 'simulasi_' + kategori + '.jpg');
            }, 'image/jpeg');
        }

        // ==========================================
        // 4. COMMON SENDER KE LARAVEL API
        // ==========================================
        async function sendImageFileToApi(fileOrBlob, filename) {
            const statusEl = document.getElementById('sim-status');
            statusEl.innerText = "Memproses YOLO AI...";

            const formData = new FormData();
            formData.append('file', fileOrBlob, filename);
            formData.append('device_id', 'BIN-01');

            try {
                const res = await fetch('/api/predict', {
                    method: 'POST',
                    body: formData
                });
                const resData = await res.json();

                if (resData.status === 'success') {
                    showSimResult(resData);
                    statusEl.innerText = "Selesai: " + resData.servo_action;

                    setTimeout(() => {
                        if (typeof syncDashboard === 'function') {
                            syncDashboard();
                        }
                    }, 500);
                } else {
                    statusEl.innerText = "Gagal memproses gambar!";
                }
            } catch (e) {
                statusEl.innerText = "Terjadi galat koneksi API!";
            }
        }

        function showSimResult(res) {
            const resBox = document.getElementById('simResultBox');
            const resHeader = document.getElementById('simResultHeader');
            const iconWrap = document.getElementById('simResultIconWrap');
            const catEl = document.getElementById('simResultCategory');
            const confEl = document.getElementById('simResultConfidence');
            const confBar = document.getElementById('simResultConfBar');
            const targetBinEl = document.getElementById('simResultTargetBin');
            const targetSubEl = document.getElementById('simResultTargetSub');
            const servoEl = document.getElementById('simResultServo');
            const servoAngleEl = document.getElementById('simResultServoAngle');
            const badgeEl = document.getElementById('simResultSourceBadge');
            const tsEl = document.getElementById('simResultTimestamp');

            resBox.classList.remove('hidden');

            const pct = Math.round((res.confidence || 0.85) * 100);
            confEl.innerText = `${pct}%`;
            if (confBar) confBar.style.width = `${pct}%`;

            if (tsEl) {
                const now = new Date();
                tsEl.innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            }

            if (res.kategori === 'organik') {
                resBox.className = 'mt-5 rounded-2xl border border-emerald-200 transition-all duration-300 overflow-hidden shadow-sm animate-card-slide bg-white';
                resHeader.className = 'px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 border-b border-emerald-100 bg-emerald-50/60';
                iconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-black text-white shadow-xs bg-emerald-600';
                catEl.innerHTML = '<span class="text-emerald-800 font-black text-base">KOMPARTEMEN 1 — ORGANIK</span>';
                targetBinEl.innerText = 'Tong 1: Organik (Hijau)';
                targetSubEl.innerText = 'Aktuator Servo 1 • Pin GPIO 12';
                targetSubEl.className = 'text-[11px] font-semibold text-emerald-700 block mt-0.5';
                servoEl.innerText = res.servo_action || 'BUKA TUTUP TONG 1 (ORGANIK)';
                servoEl.className = 'text-sm font-black text-emerald-700 block truncate';
                servoAngleEl.innerText = 'Sudut Aktuator: 90° (Tutup Terbuka)';
                if (confBar) confBar.className = 'h-1.5 rounded-full transition-all duration-500 bg-emerald-500';
            } else if (res.kategori === 'residu') {
                resBox.className = 'mt-5 rounded-2xl border border-rose-200 transition-all duration-300 overflow-hidden shadow-sm animate-card-slide bg-white';
                resHeader.className = 'px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 border-b border-rose-100 bg-rose-50/60';
                iconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-black text-white shadow-xs bg-rose-600';
                catEl.innerHTML = '<span class="text-rose-800 font-black text-base">KOMPARTEMEN 3 — RESIDU & B3</span>';
                targetBinEl.innerText = 'Tong 3: Residu & B3 (Merah)';
                targetSubEl.innerText = 'Aktuator Servo 3 • Pin GPIO 15';
                targetSubEl.className = 'text-[11px] font-semibold text-rose-700 block mt-0.5';
                servoEl.innerText = res.servo_action || 'BUKA TUTUP TONG 3 (RESIDU/B3)';
                servoEl.className = 'text-sm font-black text-rose-700 block truncate';
                servoAngleEl.innerText = 'Sudut Aktuator: 90° (Tutup Terbuka)';
                if (confBar) confBar.className = 'h-1.5 rounded-full transition-all duration-500 bg-rose-500';
            } else {
                resBox.className = 'mt-5 rounded-2xl border border-sky-200 transition-all duration-300 overflow-hidden shadow-sm animate-card-slide bg-white';
                resHeader.className = 'px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 border-b border-sky-100 bg-sky-50/60';
                iconWrap.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-black text-white shadow-xs bg-sky-600';
                catEl.innerHTML = '<span class="text-sky-800 font-black text-base">KOMPARTEMEN 2 — DAUR ULANG</span>';
                targetBinEl.innerText = 'Tong 2: Daur Ulang (Biru)';
                targetSubEl.innerText = 'Aktuator Servo 2 • Pin GPIO 13';
                targetSubEl.className = 'text-[11px] font-semibold text-sky-700 block mt-0.5';
                servoEl.innerText = res.servo_action || 'BUKA TUTUP TONG 2 (DAUR ULANG)';
                servoEl.className = 'text-sm font-black text-sky-700 block truncate';
                servoAngleEl.innerText = 'Sudut Aktuator: 90° (Tutup Terbuka)';
                if (confBar) confBar.className = 'h-1.5 rounded-full transition-all duration-500 bg-sky-500';
            }

            // Format nama engine deteksi dalam Bahasa Indonesia ramah masyarakat
            const src = (res.detection_source || '').toLowerCase();
            let srcLabel = 'AI Vision';

            const kamusIndo = {
                'beer_glass': 'Gelas / Botol Kaca',
                'beer glass': 'Gelas / Botol Kaca',
                'goblet': 'Gelas Kaca',
                'glass': 'Material Kaca',
                'pop_bottle': 'Botol Plastik Minuman',
                'pop bottle': 'Botol Plastik Minuman',
                'water_bottle': 'Botol Air Mineral',
                'water bottle': 'Botol Air Mineral',
                'nipple': 'Dot Karet Bayi (Residu)',
                'pacifier': 'Empeng Karet (Residu)',
                'balloon': 'Balon Karet (Residu)',
                'rubber': 'Karet / Silikon (Residu)',
                'sponge': 'Spons Cuci (Residu)',
                'wood': 'Ranting / Kayu (Organik)',
                'hay': 'Jerami Kering (Organik)',
                'straw': 'Jerami Padi (Organik)',
                'acorn': 'Biji Buah (Organik)',
                'mushroom': 'Jamur (Sisa Organik)',
                'lemon': 'Kulit Lemon (Organik)',
                'lime': 'Kulit Jeruk Nipis (Organik)',
                'pineapple': 'Kulit Nanas (Organik)',
                'strawberry': 'Sisa Strawberry (Organik)',
                'potato': 'Kulit Kentang (Organik)',
                'potatoes': 'Kulit Kentang (Organik)',
                'cabbage': 'Sisa Sayur Kol (Organik)',
                'broccoli': 'Sisa Sayur Brokoli (Organik)',
                'cucumber': 'Sisa Mentimun (Organik)',
                'bottle': 'Botol Plastik',
                'plate': 'Piring Plastik / Makan',
                'dish': 'Piring / Wadah Makanan',
                'tray': 'Nampan / Piring Saji',
                'saucer': 'Piring Kecil / Tatakan',
                'bowl': 'Mangkuk Plastik / Wadah',
                'platter': 'Piring Besar / Platter',
                'bucket': 'Ember / Wadah Plastik',
                'packet': 'Kemasan Sachet / Bungkus',
                'sachet': 'Kemasan Sachet Multilayer',
                'plastic_bag': 'Kantong Plastik / Kresek',
                'plastic bag': 'Kantong Plastik / Kresek',
                'centipede': 'Dedaunan / Ranting Organik',
                'leaf': 'Dedaunan Segar',
                'cardboard': 'Kardus / Karton Cokelat',
                'carton': 'Karton Minuman',
                'tin_can': 'Kaleng Logam / Minuman',
                'tin can': 'Kaleng Logam / Minuman',
                'can': 'Kaleng Minuman',
                'metal': 'Material Logam',
                'crate': 'Keranjang / Peti Plastik',
                'cellular_telephone': 'Ponsel / Limbah Elektronik',
                'cellular telephone': 'Ponsel / Limbah Elektronik',
                'laptop': 'Laptop / E-Waste',
                'mouse': 'Mouse / Aksesoris Komputer',
                'modem': 'Modem / Alat Elektronik',
                'ipod': 'Gadget / Limbah Elektronik',
                'banana': 'Kulit Pisang / Buah',
                'apple': 'Sisa Apel / Buah',
                'orange': 'Kulit Jeruk / Buah',
                'pizza': 'Sisa Makanan',
                'bread': 'Sisa Roti / Makanan',
                'corn': 'Sisa Jagung / Sayuran',
                'mask': 'Masker Medis (Residu)',
                'syringe': 'Jarum / Limbah Medis (B3)',
                'battery': 'Baterai Bekas (Limbah B3)',
                'styrofoam': 'Styrofoam Kemasan (Residu)',
                'pillow': 'Kain / Busa Residu',
                'sock': 'Kain Bekas / Residu',
                'jersey': 'Tekstil / Pakaian',
                'suit': 'Pakaian / Tekstil',
                'organic': 'Bahan Organik (Sisa Alam)',
                'paper': 'Kertas / Dokumen Bekas',
                'plastic': 'Sampah Plastik Daur Ulang',
                'e-waste': 'Limbah Elektronik (E-Waste)',
                'medical': 'Limbah Medis & B3'
            };

            if (src.includes('gemini')) {
                srcLabel = '✨ AI Gemini: Analisis Multimodal Cerdas';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-purple-100 text-purple-800 border border-purple-200 shadow-2xs';
            } else if (src.includes('ai_vision')) {
                const rawKey = src.replace('ai_vision_', '').replace(/_/g, ' ').trim();
                let defaultIndo = 'Material Daur Ulang';
                if (res.kategori === 'organik') defaultIndo = 'Sisa Organik & Dedaunan';
                else if (res.kategori === 'residu') defaultIndo = 'Sampah Residu & Kemasan';

                const indoTerm = res.object_name_id || kamusIndo[rawKey] || kamusIndo[rawKey.replace(/ /g, '_')] || defaultIndo;
                srcLabel = '🧠 Deep Vision: ' + indoTerm;

                if (res.kategori === 'organik') {
                    badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 shadow-2xs';
                } else if (res.kategori === 'residu') {
                    badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-rose-100 text-rose-900 border border-rose-200 shadow-2xs';
                } else {
                    badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-sky-100 text-sky-900 border border-sky-200 shadow-2xs';
                }
            } else if (src.includes('green') || src.includes('foliage')) {
                srcLabel = '🍃 AI Sensor: Dedaunan Hijau Alami';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 shadow-2xs';
            } else if (src.includes('sachet') || src.includes('multilayer')) {
                srcLabel = '🧼 AI Sensor: Kemasan Sachet Multilayer';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-rose-100 text-rose-900 border border-rose-200 shadow-2xs';
            } else if (src.includes('bottle')) {
                srcLabel = '🧴 AI Sensor: Botol Plastik PET';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-sky-100 text-sky-900 border border-sky-200 shadow-2xs';
            } else if (src.includes('yolo')) {
                const yoloKey = src.replace('yolo_waste_', '').replace('yolo_', '').trim();
                const yoloIndo = kamusIndo[yoloKey] || yoloKey.toUpperCase();
                srcLabel = '🎯 YOLO AI: ' + yoloIndo;
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-indigo-100 text-indigo-900 border border-indigo-200 shadow-2xs';
            } else if (src.includes('semantic_analysis')) {
                if (src.includes('organik')) srcLabel = '🌿 Sampel Organik Terverifikasi';
                else if (src.includes('residu')) srcLabel = '⚠️ Sampel Residu / B3 Terverifikasi';
                else srcLabel = '♻️ Sampel Daur Ulang Terverifikasi';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-slate-200 text-slate-800 border border-slate-300 shadow-2xs';
            } else if (src.includes('filename')) {
                srcLabel = '🏷️ Label Sampel Terverifikasi';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-slate-200 text-slate-800 border border-slate-300 shadow-2xs';
            } else {
                srcLabel = '🔍 Deteksi Cerdas 3 Kompartemen';
                badgeEl.className = 'text-xs px-3 py-1 rounded-full font-bold bg-slate-200 text-slate-800 border border-slate-300 shadow-2xs';
            }
            badgeEl.innerText = srcLabel;

            lucide.createIcons();
        }
    </script>
    @stack('scripts')
</body>
</html>

