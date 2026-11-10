# Smart Waste IoT Engineering Rules & Standards
> Diadopsi dari best-practices `simutasi-asn` & `crm_worrie` (anti-slop layout, dense enterprise layout, high-fidelity UI, micro-animations & physics, clean architecture).

Aturan ini bersifat **MANDATORI** untuk proyek **Smart Waste IoT (Sistem Pemilah Sampah Cerdas & Monitoring Kapasitas Berbasis IoT, AI, dan Web Dashboard)** di `/Users/diyanto/iot-sampah`.

---

## 1. Identitas Visual & Standar UI Anti-Slop (MANDATORI)
* **Tema Visual:** **LIGHT ENTERPRISE THEME** (Bersih, berwibawa, modern, standar SaaS / GovTech, bukan dark mode murahan atau AI slop).
* **Palet Warna Resmi Smart Waste:**
  - **Primary Navy / Slate:** `#0f172a` / `#1e3a8a` (Navbar, sidebar, heading utama, teks kontras tinggi).
  - **Organik Emerald:** `#059669` / `#10b981` (Status tong organik, indikator positif, grafik organik).
  - **Anorganik Sky / Blue:** `#0284c7` / `#0ea5e9` (Status tong anorganik, grafik anorganik).
  - **Alert Warning Amber:** `#d97706` / `#f59e0b` (Kapasitas sedang 60–89%).
  - **Alert Danger Rose / Red:** `#dc2626` / `#e11d48` (Kapasitas penuh $\ge 90\%$, status darurat).
  - **Canvas Surface:** Putih bersih (`#ffffff`) dengan soft slate background (`#f8fafc` / `#f1f5f9`) dan border halus rapi (`#e2e8f0`).

* **Prinsip Anti-AI-Slop Layout:**
  - ❌ **Dilarang keras:** Membuat layout "kartu melayang kecil sendirian di tengah layar kosong".
  - ❌ **Dilarang keras:** Membuat UI tanpa struktur navigasi yang jelas (harus memiliki Header/Navbar dan Sidebar atau Full-width Workspace).
  - ✅ **Wajib:** Menggunakan komposisi layout yang proporsional:
    - **Header & Metric Strip:** Menampilkan status server, koneksi IoT real-time, tombol quick action (Export CSV, Test Trigger).
    - **Dual Bin Live Capacity Gauges:** Kartu kapasitas interaktif dengan progress bar cair (*liquid gauge*), persentase besar yang tegas, dan status dinamis (Aman / Waspada / Penuh).
    - **Visual Analytics Split View:** Donut Chart rasio sampah + Bar Chart tren harian.
    - **Dense Data Activity Table:** Tabel log pemilahan real-time dengan paginasi, badge kategori (Organik/Anorganik), status confidence, dan filter pencarian.

---

## 2. Sistem Animasi & Fisika Transisi (Interactive Micro-Animations)
Setiap elemen interaktif wajib mengadopsi sistem animasi yang halus dan berbobot (*physics-based cubic-bezier*):
* **Card Entry:** `@keyframes cardSlideIn` (`0.35s cubic-bezier(0.16, 1, 0.3, 1)`).
* **Modal / Dialog:** `@keyframes modalPopIn` (`0.35s cubic-bezier(0.16, 1, 0.3, 1)`).
* **Pulse Glow Alert:** `@keyframes pulseGlow` untuk indikator status kritis (saat tong $\ge 90\%$).
* **Interactive Hover Lift:** `.interactive-card:hover` (`translateY(-3px) scale(1.008)` dengan bayangan lembut berdimensi).
* **Table Row Hover Nudge:** `.table-row-standard:hover` (`translateX(4px)` dengan highlight latar belakang halus).
* **Real-time Live Indicator:** Titik hijau berdenyut (*pulsing dot*) untuk menandakan telemetri websocket/polling aktif.

---

## 3. Arsitektur Data & Standar Kode
* **Laravel Backend (Core Information System):**
  - Mengelola database relasional (Eloquent models: `WasteBin`, `WasteLog`, `BinCapacity`).
  - Mengatur bisnis logic, DSS alerting ke Telegram Bot, dan ekspor laporan CSV.
* **FastAPI AI Worker (Microservice):**
  - Pemrosesan citra in-memory RAM (Zero-Disk Storage).
  - Respon prediksi cepat (<100ms) untuk aktuator ESP32.
* **Firmware ESP32-CAM:**
  - Non-blocking execution, debounced IR sensor trigger, pin-sharing ultrasonik.

