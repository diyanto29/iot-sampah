# Smart Waste IoT Ecosystem
### Sistem Informasi Pemilah Sampah Cerdas & Monitoring Kapasitas Berbasis IoT, AI (YOLOv8), dan Web Dashboard

[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-777bb4?logo=php)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2B-ff2d20?logo=laravel)](https://laravel.com)
[![FastAPI Version](https://img.shields.io/badge/FastAPI-0.110%2B-009688?logo=fastapi)](https://fastapi.tiangolo.com)
[![Hardware](https://img.shields.io/badge/Hardware-ESP32--CAM-blue)](https://www.espressif.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📌 Ringkasan Eksekutif Proyek

Proyek ini adalah implementasi lengkap sistem **Smart Waste Sorting & Capacity Monitoring** yang dirancang khusus untuk memenuhi standar keilmuan dan kelulusan program studi **Sistem Informasi (SI)**:
* **Edge IoT Device (< Rp 200.000):** Modul **ESP32-CAM (OV2640)** bertindak sebagai perangkat akuisisi citra otomatis saat sampah masuk ke nampan atas (Sensor IR) dan menggerakkan sekat pemilah (Motor Servo SG90) serta mengukur ketinggian sampah (2x Sensor Ultrasonik HC-SR04P).
* **AI Worker (Python FastAPI):** Menjalankan inferensi model **YOLOv8** secara *in-memory* di RAM (**Zero-Disk Storage**, 0 file foto tersimpan ke disk hard drive) untuk memilah kategori **Organik** vs **Anorganik**.
* **Core Information System (Laravel 11 & PHP 8.5):** Mengelola aturan bisnis, pencatatan log ke database relasional, Decision Support System (DSS) alert ke **Telegram Bot API** saat tong $\ge 90\%$, ekspor rekapitulasi data ke format CSV, dan menyajikan Web Dashboard interaktif.
* **Anti-AI-Slop & Micro-Animation UI:** Antarmuka web dibangun mengadopsi standar desain *GovTech / Enterprise SaaS* dari `simutasi-asn` (Light theme berwibawa, liquid progress bar, hover physics, table row nudge, chart distribusi, dan modal simulasi cepat).

---

## 🏗️ Diagram Arsitektur Sistem

```mermaid
flowchart LR
    ESP["ESP32-CAM<br/>(IoT Edge Device)"] -->|1. POST /api/predict (Foto VGA)| Laravel["Laravel 11 Core<br/>(Port 8000)"]
    
    subgraph Microservice ["Localhost AI Service"]
        Laravel -->|2. In-Memory Image Stream| FastAPI["FastAPI Worker<br/>(Port 5000 / YOLOv8)"]
        FastAPI -->|3. JSON {kategori, confidence}| Laravel
    end
    
    Laravel -->|4. Simpan Log (Eloquent)| DB[("Database Relasional<br/>SQLite / MySQL")]
    Laravel -->|5. Alert Kapasitas >= 90%| Tele["Telegram Bot API"]
    Laravel -->|6. JSON Response {servo_angle}| ESP
    ESP -->|7. Putar Sekat 45° / 135°| Servo["Motor Servo SG90"]

    Admin["Petugas / Admin"] -->|Monitoring Real-time| Dashboard["Web Dashboard (Blade + Tailwind)"]
```

---

## 📁 Struktur Direktori Repositori

```text
iot-sampah/
├── .agents/                        # Aturan rekayasa AI & anti-slop (diadopsi dari simutasi-asn)
│   ├── rules/
│   │   ├── smartwaste_engineering_rules.md
│   │   ├── simutasi_engineering_rules.md
│   │   └── crm_engineering_rules.md
│   └── skills/security-audit/
├── ai-worker/                      # Microservice Python YOLOv8
│   ├── venv/                       # Virtual environment Python
│   ├── main.py                     # Server FastAPI (POST /detect)
│   └── requirements.txt
├── backend/                        # Core Web Application (Laravel 11 + PHP 8.5)
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Api/WasteController.php # Handler /api/predict & /api/telemetry
│   │   │   ├── AuthController.php      # Autentikasi Login/Logout
│   │   │   ├── AlertController.php     # Pusat Alert & Insiden Kapasitas
│   │   │   ├── WasteLogController.php  # Riwayat Pemilahan Terinci (Filter & Search)
│   │   │   ├── WasteBinController.php  # Master Data Multi-Titik Tempat Sampah
│   │   │   ├── UserController.php      # Manajemen Akun Petugas & Admin (RBAC)
│   │   │   ├── DashboardController.php # Dashboard Monitoring
│   │   │   └── ReportController.php    # Ekspor laporan CSV
│   │   └── Models/
│   │       ├── User.php                # Model Pengguna & Role (admin/petugas)
│   │       ├── WasteBin.php            # Model Tempat Sampah
│   │       ├── WasteLog.php            # Model Riwayat Pemilahan
│   │       ├── BinCapacity.php         # Model Telemetri Ketinggian
│   │       └── CapacityAlert.php       # Model Tiket Insiden Tong Penuh
│   ├── database/migrations/        # Migrasi tabel relasional
│   ├── resources/views/
│   │   ├── layouts/app.blade.php   # Layout Enterprise GovTech (Sidebar + Header)
│   │   ├── auth/login.blade.php    # Halaman Login dengan 1-click Demo Accounts
│   │   ├── dashboard.blade.php     # Live Capacity Gauges & Dual Analytics
│   │   ├── alerts/index.blade.php  # Pusat Tiket Alert & Verifikasi Pengosongan
│   │   ├── logs/index.blade.php    # Riwayat Log Terinci (Date filter + Export)
│   │   ├── bins/index.blade.php    # Master Titik Tempat Sampah Kampus
│   │   └── users/index.blade.php   # Manajemen Akun Petugas & Hak Akses
│   └── routes/
│       ├── api.php                 # Rute REST API IoT
│       └── web.php                 # Rute Sistem Informasi Lengkap
├── firmware/                       # Source Code Mikrokontroler (Arduino C++)
│   ├── esp32_firmware.ino          # Firmware lengkap (IR + Kamera + Servo + 2 US)
│   └── esp32_sanity_check.ino      # Sketch uji coba awal hardware baru
├── simulator/                      # Skrip Pengujian Tanpa Alat Fisik
│   └── simulate_client.py          # Simulasi via webcam laptop atau dummy image
├── docs/                           # Dokumentasi Lengkap Skripsi & Teknis
│   ├── PRD.md                      # Product Requirement Document
│   ├── ARCHITECTURE_AND_FLOW.md    # Detail arsitektur & alur data
│   ├── HARDWARE_AND_WIRING.md      # Panduan pinout aman, fokus lensa, & troubleshooting
│   ├── UML_DIAGRAMS.md             # Use Case, Activity, Sequence, & Class Diagram
│   ├── THESIS_DEFENSE_GUIDE.md     # Panduan menjawab pertanyaan dosen penguji SI
│   └── THESIS_DRAFT_CHAPTERS_1_TO_5.md # Draft Lengkap Bab 1 s.d. Bab 5 Skripsi
├── AGENTS.md                       # Standar aturan permanen rekayasa proyek
├── start_system.sh                 # Skrip satu-perintah untuk menjalankan Laravel & FastAPI
└── README.md                       # Dokumentasi utama ini
```

---

## 🚀 Panduan Memulai Cepat (Quickstart)

### 1. Menjalankan Seluruh Sistem (1 Perintah)
Cukup jalankan skrip berikut di terminal root:
```bash
./start_system.sh
```
Skrip akan otomatis menyalakan:
* **FastAPI AI Worker** di `http://127.0.0.1:5000`
* **Laravel Web Application** di `http://127.0.0.1:8000`

Buka browser di: **`http://localhost:8000`** untuk melihat Web Dashboard interaktif.

---

### 2. Mencoba Simulasi Tanpa Alat Fisik (Webcam / Laptop)
Di jendela terminal baru, jalankan:
```bash
# Uji coba otomatis dengan dummy image:
source ai-worker/venv/bin/activate && python simulator/simulate_client.py

# Atau uji coba interaktif menggunakan webcam laptop:
source ai-worker/venv/bin/activate && python simulator/simulate_client.py --webcam
```
*(Tekan **SPASI** untuk memotret objek di depan webcam laptop, atau gunakan tombol **"Simulasi Cepat"** di Web Dashboard).*

---

### 3. Menghubungkan ke Perangkat Fisik ESP32-CAM (Cloudflare Tunnel)
Untuk mengekspos server lokal kamu ke internet dengan domain publik HTTPS gratis agar bisa diakses oleh ESP32-CAM di jaringan WiFi mana pun:
```bash
cloudflared tunnel --url http://localhost:8000
```
Salin URL HTTPS yang dihasilkan (misal `https://random-name.trycloudflare.com`) ke baris konfigurasi `predictApiUrl` pada file `firmware/esp32_firmware.ino`.

---

## 🔌 Spesifikasi Pinout Hardware ESP32-CAM

| Komponen | Pin Modul | Terhubung ke ESP32-CAM |
| :--- | :--- | :--- |
| **Catu Daya Bersama** | 5V & GND | Pin **5V** & **GND** |
| **Sensor IR Obstacle** | OUT Digital | **GPIO 13** |
| **Motor Servo (SG90)** | Signal PWM | **GPIO 12** |
| **Sensor Ultrasonik 1 (Organik)** | Trig / Echo | **GPIO 14** (Shared Trig) / **GPIO 2** (Echo) |
| **Sensor Ultrasonik 2 (Anorganik)** | Trig / Echo | **GPIO 14** (Shared Trig) / **GPIO 15** (Echo) |
| **Lampu Flash LED** | Internal | **GPIO 4** |

---

## 📚 Dokumen Rujukan Lengkap

* [Product Requirement Document (PRD)](file:///Users/diyanto/iot-sampah/docs/PRD.md)
* [Arsitektur Sistem & Alur Aliran Data](file:///Users/diyanto/iot-sampah/docs/ARCHITECTURE_AND_FLOW.md)
* [Panduan Pengkabelan & Troubleshooting Hardware](file:///Users/diyanto/iot-sampah/docs/HARDWARE_AND_WIRING.md)
* [Diagram UML Format Skripsi SI](file:///Users/diyanto/iot-sampah/docs/UML_DIAGRAMS.md)
* [Panduan Strategi Sidang Skripsi](file:///Users/diyanto/iot-sampah/docs/THESIS_DEFENSE_GUIDE.md)
* [Aturan Rekayasa & Anti-AI-Slop Rules](file:///Users/diyanto/iot-sampah/AGENTS.md)

