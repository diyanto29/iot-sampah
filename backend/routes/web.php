<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\WasteLogController;
use App\Http\Controllers\WasteBinController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Operasional Utama
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Pusat Peringatan & Insiden Kapasitas
Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
Route::post('/alerts/{id}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

// Riwayat Pemilahan Terinci
Route::get('/logs', [WasteLogController::class, 'index'])->name('logs.index');

// Master Tempat Sampah Multi-Titik
Route::get('/bins', [WasteBinController::class, 'index'])->name('bins.index');
Route::post('/bins', [WasteBinController::class, 'store'])->name('bins.store');
Route::put('/bins/{id}', [WasteBinController::class, 'update'])->name('bins.update');
Route::delete('/bins/{id}', [WasteBinController::class, 'destroy'])->name('bins.destroy');

// Manajemen Akun Petugas & Admin
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

// Laporan & Rekapitulasi CSV
Route::get('/reports/waste/export', [ReportController::class, 'exportCsv'])->name('reports.waste.export');
