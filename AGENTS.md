# Smart Waste IoT Engineering Rules & Guidelines
> Diadopsi dari arsitektur & anti-slop rules `simutasi-asn` & `crm_worrie` (clean architecture, anti-AI-slop layout, dense enterprise layout, high-fidelity UI, micro-animations & physics).

Aturan permanen dan standar rekayasa perangkat lunak untuk sistem **Smart Waste IoT** (Sistem Pemilah Sampah Cerdas & Monitoring Kapasitas Berbasis IoT, AI, dan Web Dashboard):

---

## 1. Identitas Visual & Standar UI Anti-Slop (MANDATORI)
* **Tema Visual:** **LIGHT THEME ENTERPRISE** (Standar aplikasi operasional modern/GovTech, bersih, terstruktur, padat data, dan berwibawa).
* **Palet Warna:**
  - **Primary Navy Blue:** `#0f172a` / `#1e3a8a` (Navbar, heading utama, sidebar aktif, tombol primer).
  - **Organik Emerald:** `#059669` / `#10b981` (Status tong organik, indikator positif, grafik organik).
  - **Anorganik Sky / Blue:** `#0284c7` / `#0ea5e9` (Status tong anorganik, grafik anorganik).
  - **Warning Amber:** `#d97706` / `#f59e0b` (Kapasitas sedang 60–89%).
  - **Danger Rose / Red:** `#dc2626` / `#e11d48` (Kapasitas penuh $\ge 90\%$, alert darurat).
  - **Canvas Background:** Putih bersih (`#ffffff`) dengan soft slate surface (`#f8fafc` / `#f1f5f9`) dan border halus (`#e2e8f0`).

* **Anti-AI-Slop Layout:**
  - Dilarang membuat layout "kartu melayang kecil di tengah layar kosong" (ciri khas AI slop).
  - Gunakan komposisi UI proporsional:
    - *Full-width Executive Header* dengan indikator status server, status IoT, dan aksi cepat (*Export CSV, Simulate Input*).
    - *Dual Bin Live Capacity Cards* dengan persentase besar, progress bar cair dinamis, dan badge status visual.
    - *Split View Analytics*: Donut Chart Rasio Sampah + Bar Chart Tren Harian.
    - *Dense Data Table* dengan paginasi, sorting, badge kategori (Organik/Anorganik), dan filter pencarian.

---

## 2. Sistem Animasi & Fisika Transisi (Interactive Micro-Animations)
* **Card Slide In:** `@keyframes cardSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;`
* **Interactive Hover Lift:** `.interactive-card:hover { transform: translateY(-3px) scale(1.008); box-shadow: ...; }`
* **Table Row Hover Nudge:** `.table-row-standard:hover { transform: translateX(4px); background-color: rgba(239, 246, 255, 0.65); }`
* **Pulsing Alert Glow:** `@keyframes pulseGlow` berkedip merah saat kapasitas tong mencapai $\ge 90\%$.

---

## 3. Aturan Database & Kode
* Semua perubahan skema database wajib melalui migration Laravel resmi (`backend/database/migrations/`).
* Microservice Python YOLO berjalan di RAM (Zero-Disk Storage).
* Firmware ESP32-CAM menggunakan pin GPIO yang teruji aman dan kompatibel.

