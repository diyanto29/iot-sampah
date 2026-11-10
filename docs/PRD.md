# Product Requirement Document (PRD)
## Smart Waste Sorting & Monitoring System
**Sistem Pemilah Sampah Cerdas & Monitoring Kapasitas Berbasis IoT, AI, dan Web Dashboard**

---

### 1. Informasi Dokumen
* **Nama Proyek:** Smart Waste IoT Ecosystem
* **Institusi / Bidang:** Sistem Informasi (SI) / Enterprise IoT Solution
* **Versi Dokumen:** 1.0.0
* **Status:** Ready for Implementation & Thesis Defense

---

### 2. Latar Belakang & Pernyataan Masalah (Problem Statement)
* **Krisis Pemilahan Sampah:** Kesadaran masyarakat dan civitas akademika dalam memilah sampah organik dan anorganik dari sumbernya masih sangat rendah, menyebabkan proses daur ulang terhambat dan timbunan sampah tercampur di tempat pemrosesan akhir.
* **Pengawasan Manual yang Tidak Efisien:** Pengawasan tempat sampah konvensional mengandalkan inspeksi manual petugas kebersihan. Sering kali tempat sampah sudah membludak (*overflow*) sebelum sempat dikosongkan.
* **Tantangan Akademik Skripsi SI:** Mahasiswa jurusan Sistem Informasi sering terjebak dalam dua kubu: terlalu berat di perakitan perangkat keras rumit (seperti memakai komputer mikro mahal Raspberry Pi) atau sekadar membuat model AI tanpa arsitektur sistem informasi yang bermanfaat.
* **Solusi yang Diajukan:** Membangun ekosistem **Edge-to-Server** berbiaya rendah (< Rp 250.000 untuk komponen IoT di tempat sampah) yang memisahkan tugas akuisisi data lapangan (ESP32-CAM) dengan pusat pemrosesan cerdas (Laravel 11 Core + FastAPI YOLOv8 Microservice) serta Decision Support System (DSS) berbasis Web Dashboard dan Telegram Alert.

---

### 3. Tujuan Produk (Product Objectives)
1. **Otomasi Pemilahan 3 Kategori:** Mengidentifikasi jenis sampah (**Organik**, **Daur Ulang / Anorganik**, dan **Residu / B3**) secara visual menggunakan Computer Vision (YOLOv8) dan memilahnya via motor servo 3 arah sudut (30°, 90°, 150°).
2. **Arsitektur Efisien & Skalabel:** Memisahkan beban *Edge Device* (ESP32-CAM hanya memotret dan menggerakkan servo) dan *Central Server* (komputasi AI & orkestrasi data), sehingga 1 server dapat melayani banyak titik tempat sampah cerdas.
3. **Optimasi Penyimpanan (*Zero-Disk Storage*):** Memproses foto secara *in-memory* di RAM tanpa menyimpan file ke hard drive server guna mencegah pemborosan ruang disk.
4. **Monitoring Real-time & DSS Alert:** Menyajikan Web Dashboard analitik kapasitas volume ketiga kompartemen serta mengirimkan notifikasi instan Telegram dan pembuatan tiket insiden saat kapasitas mencapai $\ge 90\%$.
5. **Dukungan Pelaporan:** Menyediakan fitur ekspor rekapitulasi data pemilahan harian dan bulanan ke format CSV untuk analisis pimpinan fasilitas.

---

### 4. Analisis Pengguna & Stakeholder (User Personas)
* **Pengguna Umum (Mahasiswa / Karyawan / Pengunjung):**
  - Meletakkan sampah pada nampan penampung atas.
  - Menginginkan proses pemilahan yang cepat (< 1 detik) tanpa tombol manual yang rumit.
* **Petugas Kebersihan / Koordinator Fasilitas:**
  - Memantau volume keterisian tong sampah organik dan anorganik secara *real-time* lewat web.
  - Menerima pesan darurat otomatis di ponsel (Telegram) saat tong sudah hampir penuh ($\ge 90\%$).
* **Dosen Penguji Sidang Skripsi (Aspek Akademik):**
  - Menilai kekuatan arsitektur software, relasi database, integrasi sistem, dan relevansi implementasi Sistem Informasi.

---

### 5. Kebutuhan Fungsional (Functional Requirements)

| ID | Modul | Deskripsi Kebutuhan |
| :--- | :--- | :--- |
| **FR-01** | **Otomasi Trigger IR** | Sensor Infrared Obstacle pada nampan atas mendeteksi keberadaan objek sampah secara otomatis dengan jeda stabilisasi 400 ms sebelum memicu kamera memotret. |
| **FR-02** | **Akuisisi Citra Edge** | ESP32-CAM mengambil frame gambar JPEG (resolusi VGA 640x480) dan menyalakan lampu flash LED sebentar jika kondisi ruangan redup. |
| **FR-03** | **Transmisi Multipart HTTP** | ESP32-CAM mengunggah foto ke endpoint Laravel `POST /api/predict` dalam format `multipart/form-data` dengan menyertakan `device_id`. |
| **FR-04** | **In-Memory AI Inference** | FastAPI AI Worker menerima stream gambar dari Laravel, memproses inferensi YOLOv8 langsung di memori RAM, dan mengembalikan kategori (`organik`/`anorganik`) serta confidence score. |
| **FR-05** | **Pencatatan Log Relasional** | Laravel menyimpan setiap data pemilahan ke tabel `waste_logs` menggunakan Eloquent ORM secara persisten. |
| **FR-06** | **Eksekusi Aktuator Servo** | ESP32-CAM menerima respon JSON dari Laravel dan memutar motor servo: 45° (ke kiri untuk organik) atau 135° (ke kanan untuk anorganik) selama 2 detik, lalu kembali ke posisi netral 90°. |
| **FR-07** | **Telemetri Kapasitas Tong** | Dua sensor ultrasonik membaca jarak tumpukan sampah di tong organik dan anorganik, mengkalkulasi persentase keterisian, dan mengirimkannya ke `POST /api/telemetry/capacity` setiap 30 detik. |
| **FR-08** | **Peringatan Penuh (Telegram)** | Laravel otomatis mengirim pesan peringatan ke Telegram Bot jika salah satu kompartemen mencapai kapasitas $\ge 90\%$. |
| **FR-09** | **Web Dashboard Monitoring** | Menampilkan 4 metrik eksekutif, live gauge kapasitas kedua tong dengan warna dinamis, chart rasio pemilahan, dan tabel aktivitas dengan auto-refresh setiap 3 detik. |
| **FR-10** | **Ekspor Laporan CSV** | Menyediakan fitur unduh rekapitulasi data pemilahan ke format `.csv` dengan filter rentang tanggal. |
| **FR-11** | **Simulasi Client & Modal** | Mendukung pengujian menggunakan skrip Python webcam maupun tombol modal simulasi pada web dashboard tanpa memerlukan perangkat keras fisik. |

---

### 6. Kebutuhan Non-Fungsional (Non-Functional Requirements)
1. **Waktu Respons (Latency):**
   * Total siklus dari saat objek terdeteksi di nampan $\rightarrow$ upload $\rightarrow$ inferensi $\rightarrow$ servo membuka harus selesai dalam waktu **< 1.0 detik** (rata-rata benchmark: ~0.7 detik).
2. **Efisiensi Sumber Daya Server:**
   * Nol file gambar yang disimpan ke disk penyimpanan permanen (*0 disk storage*). Seluruh manipulasi citra bersifat *ephemeral* di RAM.
3. **Skalabilitas Multi-Device:**
   * Backend dirancang modular menggunakan arsitektur microservices sehingga 1 server mampu melayani puluhan titik tempat sampah cerdas yang berbeda secara bersamaan.
4. **Keamanan & Kompatibilitas:**
   * Komunikasi data melalui jalur aman HTTPS via Cloudflare Tunnel.
   * Backend berjalan di lingkungan modern PHP 8.5 dan Python 3.10+.

---

### 7. Bill of Materials (BOM) & Spesifikasi Perangkat Keras

| Komponen | Spesifikasi Teknis | Fungsi dalam Sistem | Estimasi Biaya |
| :--- | :--- | :--- | :--- |
| **ESP32-CAM + Shield MB** | AI-Thinker OV2640 + Micro USB Downloader | Mikrokontroler utama, capture citra, kontrol servo & WiFi | Rp 80.000 – Rp 95.000 |
| **Sensor IR Obstacle** | Sinyal Digital Output (GPIO 13) | Trigger otomatis pendeteksi sampah di nampan | Rp 5.000 – Rp 8.000 |
| **Sensor Ultrasonik (2 unit)** | HC-SR04P (3.3V compatible) | Mengukur ketinggian tumpukan sampah di 2 tong | Rp 26.000 (2x @ Rp 13.000) |
| **Motor Servo** | TowerPro SG90 (atau MG996R) | Memutar sekat pemilah nampan ke kiri/kanan | Rp 16.000 – Rp 35.000 |
| **Kabel Jumper** | Female-to-Female 20cm (40 pcs) | Pengkabelan antar modul elektronik | Rp 8.000 |
| **Breadboard Mini** | Mini SYB-170 point | Distribusi jalur daya 5V dan Ground (GND) | Rp 5.000 |
| **Catu Daya** | Adaptor 5V 2A + Kabel Data Micro USB | Suplai arus listrik stabil untuk ESP32 & kamera | Rp 25.000 – Rp 35.000 |
| **Bahan Rangka Fisik** | Impraboard / Akrilik / Karton 2 sekat | Miniatur fisik tempat sampah 2 kompartemen | Rp 25.000 – Rp 40.000 |
| **Total Estimasi** | | | **Rp 190.000 – Rp 250.000** |

