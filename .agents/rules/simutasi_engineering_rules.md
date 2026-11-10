# SIMUTASI-GIS Engineering Rules & Standards
> Diadopsi dari best-practices `crm_worrie` (anti-slop layout, database-designer, security-guard, high-fidelity UI).

Aturan ini bersifat **MANDATORI** untuk proyek **SIMUTASI-GIS (Sistem Pendukung Keputusan Pemetaan Spasial & Rekomendasi Mutasi ASN BKPSDM)** di `/Users/diyanto/simutasi-asn`.

---

## 1. Identitas Brand & Design System (UI/UX)
* **Tema Visual:** **LIGHT THEME ONLY** (Pemerintahan modern berstandar enterprise/GovTech, bersih, terstruktur, dan berwibawa).
* **Palet Warna Resmi BKPSDM / ASN:**
  - **Primary Navy Blue:** `#0f172a` / `#1e3a8a` (Navbar, heading utama, sidebar aktif, tombol primer).
  - **Accent Teal / Emerald:** `#0d9488` / `#059669` (Status zona dekat, formasi tersedia, indikator positif).
  - **Accent Warning Amber:** `#d97706` / `#f59e0b` (Status zona sedang, peringatan kapasitas).
  - **Accent Danger Rose:** `#e11d48` / `#dc2626` (Status zona jauh / beban tempuh tinggi).
  - **Background Canvas:** Putih bersih (`#ffffff`) dengan soft slate surface (`#f8fafc` / `#f1f5f9`) dan border halus (`#e2e8f0`).
* **Anti-AI-Slop Layout:**
  - Dilarang membuat layout "kartu melayang kecil di tengah layar kosong" (ciri khas AI slop).
  - Gunakan komposisi UI proporsional: *Full-width Split View* (Peta GIS di sisi kiri/kanan dan Data Filter/Ranking di sisi lain), *Stat Cards Grid* dengan metrik konkret, dan *Dense Data Table* dengan paginasi serta pencarian.
  - Peta Leaflet/OSM harus memiliki kontainer yang tegas, kontrol zoom terdefinisi, layer switcher, dan legenda zona jarak (Hijau, Kuning, Merah).

---

## 2. Aturan Database & Migrasi SQL (MANDATORI)
* **Setiap penambahan atau perubahan skema database WAJIB dibuatkan file migration resmi:**
  - Format lokasi: `database/migrations/YYYY_MM_DD_HHMMSS_<nama_perubahan>.php`
* **Sifat Script Migrasi:** Wajib terstruktur, memiliki rollback (`down()`), dan menyertakan indeks relasional:
  - Indeks pada kolom pencarian: `nip`, `id_unit_kerja`, `jarak_km`.
* **Dokumentasi Changelog:** Wajib mencatat ringkasan perubahan di file `MIGRATIONS.md`.

---

## 3. Keamanan Server & Otorisasi Pengguna (RBAC)
* **Pencegahan URL Injection:** Proteksi rute internal dashboard harus divalidasi pada middleware level server.
* **Pemisahan Peran Admin vs Pegawai (ASN):**
  - Rute admin (`/admin/formasi`, `/admin/kriteria`, `/admin/topsis`, `/admin/mutasi`) **HANYA boleh diakses oleh akun dengan `role = 'admin'`**.
  - Jika akun `asn` mencoba mengetik URL admin, sistem langsung mengalihkan dengan pesan error `unauthorized`.
  - Menu admin otomatis disembunyikan (*hidden*) dari sidebar untuk pengguna non-admin.
* **Keamanan Integrasi SSO:**
  - Pertukaran token One-Time SSO dari CodeIgniter diverifikasi dan langsung dihapus setelah 1x pemakaian.
  - Token hangus jika melebihi batas waktu 2 menit.

---

## 4. Standar Logika Bisnis & Algoritma (Spasial & SPK)
* **Kalkulasi Spasial (Haversine Formula):**
  - Mengukur jarak garis lurus unit kerja vs domisili dalam kilometer ($R = 6.371\text{ km}$).
  - Klasifikasi zona wajib seragam:
    - **Dekat:** $< 5\text{ km}$
    - **Sedang:** $5 - 20\text{ km}$
    - **Jauh (Prioritas):** $> 20\text{ km}$
* **Mesin TOPSIS & Dynamic Weighting:**
  - Bobot kriteria ($C_1 \dots C_n$) disimpan di database dan dapat diatur fleksibel oleh Admin via dashboard.
  - Unit tujuan wajib memiliki kuota formasi kosong ($> 0$).
  - Perhitungan matriks ternormalisasi, solusi ideal positif/negatif, dan nilai preferensi ($V_i$) harus transparan dan dapat diaudit.

---

## 5. Standar Kualitas Kode (Clean Code)
* Gunakan Service Layer (`app/Services/HaversineService.php`, `app/Services/TopsisService.php`).
* Form Request Validation wajib diterapkan untuk semua data masuk.
* Jalankan `php artisan test` atau validasi sintaks sebelum menyerahkan hasil ke pengguna.

