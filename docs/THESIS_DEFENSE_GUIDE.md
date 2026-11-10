# Panduan Strategi Sidang Skripsi (Thesis Defense Guide)
## Kiat Menjawab Pertanyaan Kritis Dosen Penguji Jurusan Sistem Informasi (SI)

Dokumen ini memuat daftar pertanyaan klasik dan pertanyaan menjebak yang sering diajukan oleh dosen penguji sidang skripsi Sistem Informasi, beserta rekomendasi jawaban ilmiah yang berbobot dan terstruktur.

---

### 1. Pertanyaan: Identitas & Relevansi Jurusan
> **Dosen Penguji:**  
> *"Kamu kan mahasiswa Sistem Informasi, kenapa skripsinya merakit alat mikrokontroler dan melatih model Computer Vision (YOLO)? Ini bukannya lebih cocok untuk skripsi Teknik Elektro atau Teknik Informatika/Ilmu Komputer?"*

**Jawaban Strategis:**
> *"Terima kasih atas pertanyaannya, Bapak/Ibu Penguji.  
> Titik berat (*core contribution*) dari penelitian ini bukanlah pada perancangan sirkuit perangkat keras ataupun penemuan algoritma AI baru. Kontribusi utama penelitian ini terletak pada **Integrasi Sistem Informasi (Systems Integration)** dan perancangan arsitektur **Edge-to-Server IoT Ecosystem**.  
> 
> Komponen mikrokontroler dan AI di sini bertindak sebagai **agen akuisisi data otomatis (Automated Data Acquisition Agent)**. Di dunia nyata, kelemahan terbesar sistem informasi operasional kebersihan konvensional adalah ketergantungannya pada input manual manusia yang sering terlambat dan tidak akurat.  
> 
> Dengan menggabungkan ESP32-CAM sebagai ujung tombak sensor dan Laravel 11 sebagai Core Information System, sistem ini berhasil menyajikan **Decision Support System (DSS)** berbasis data nyata: memantau kapasitas volume secara *real-time*, memicu peringatan otomatis saat tong penuh, serta menyajikan rekapitulasi data analitik berkala untuk efisiensi rute operasional petugas kebersihan."*

---

### 2. Pertanyaan: Arsitektur Perangkat Keras vs Biaya
> **Dosen Penguji:**  
> *"Kenapa kamu memisahkan ESP32-CAM dengan Server AI? Kenapa tidak pasang saja Raspberry Pi 4 atau mini PC langsung di dalam tong sampah agar alatnya bisa berpikir sendiri tanpa butuh server luar?"*

**Jawaban Strategis:**
> *"Berdasarkan analisis kelayakan biaya dan skalabilitas sistem (**Cost-Benefit & Scalability Analysis**):  
> 1. **Faktor Biaya Per Unit:** Satu unit Raspberry Pi 4 berharga Rp 1.500.000 s.d. Rp 2.000.000. Jika kampus kita ingin menerapkan sistem tempat sampah cerdas di 50 titik lokasi, biaya pengadaan perangkat keras edge saja sudah mencapai Rp 75 s.d. 100 juta. Sedangkan modul ESP32-CAM hanya berharga **Rp 85.000**.  
> 2. **Skalabilitas Multi-Tenant:** Dengan arsitektur yang saya bangun, 1 Central Server (yang sudah dimiliki kampus/disediakan cloud) sanggup melayani puluhan unit ESP32-CAM secara bersamaan.  
> 3. **Efisiensi Daya & Durabilitas:** Raspberry Pi cepat panas dan boros listrik (butuh daya 15W), sedangkan ESP32-CAM sangat hemat daya (<2W) dan tahan ditaruh di lingkungan luar (*outdoor*)."*

---

### 3. Pertanyaan: Keandalan Jaringan & Toleransi Kesalahan (Fail-Safe)
> **Dosen Penguji:**  
> *"Bagaimana jika koneksi WiFi kampus terputus atau respon server mengalami lag/timeout saat ada orang membuang sampah?"*

**Jawaban Strategis:**
> *"Firmware ESP32-CAM telah dilengkapi dengan mekanisme toleransi kegagalan (**Fail-Safe Mode**):  
> 1. Pada level transmisi HTTP, mikrokontroler memiliki batas waktu tanggap (*timeout*) maksimal 3.0 detik.  
> 2. Jika koneksi terputus atau server tidak merespon dalam 3 detik, sistem tidak akan macet (*non-blocking*). Servo akan diarahkan secara otomatis ke kompartemen *default* (Anorganik) agar nampan tidak tertumpuk sampah berikutnya.  
> 3. Pada sisi mikrokontroler, terdapat rutin koneksi ulang (*auto-reconnect*) setiap kali sinyal WiFi pulih, sehingga alat tidak perlu di-restart manual oleh petugas."*

---

### 4. Pertanyaan: Privasi & Manajemen Penyimpanan Citra
> **Dosen Penguji:**  
> *"Kalau setiap hari ada 1.000 orang membuang sampah, hard drive server kamu bukankah akan cepat penuh karena menyimpan ribuan file foto sampah?"*

**Jawaban Strategis:**
> *"Sistem ini secara khusus menerapkan prinsip **Zero-Disk Storage Architecture**.  
> Berkas citra yang dikirimkan oleh ESP32-CAM hanya dialirkan ke dalam memori RAM (*in-memory byte stream* via `io.BytesIO` di Python). Setelah model YOLO selesai mengklasifikasikan kategori sampah (dalam waktu ~65 milidetik), buffer gambar di RAM langsung dibersihkan oleh *garbage collector*.  
> 
> Server **sama sekali tidak menyimpan file foto ke hard drive**. Yang dicatat secara persisten ke database relasional (MySQL/SQLite) hanyalah metadata teks berupa: `id_alat`, `kategori`, `nilai_confidence`, dan `waktu_pemilahan`. Hal ini menjamin ruang penyimpanan server tetap hemat dan terbebas dari masalah kehabisan memori (*storage bloating*)."*

---

### 5. Pertanyaan: Metrik Pengujian & Hasil Evaluasi
> **Dosen Penguji:**  
> *"Bagaimana kamu membuktikan bahwa sistem ini bekerja secara ilmiah di Bab 4?"*

**Jawaban Strategis:**
> *"Pengujian pada Bab 4 dilakukan melalui tiga pengujian empiris kuantitatif:  
> 1. **Black-Box Functional Testing:** Memvalidasi 100% jalur fungsionalitas logika sistem (mulai dari trigger IR, aktuasi servo, hingga alert Telegram).  
> 2. **Pengujian Akurasi Model AI:** Mengukur performa klasifikasi model sampah pada dataset uji menggunakan matriks *Precision*, *Recall*, dan *F1-Score* (rata-rata akurasi mencapai $\ge 88\%$).  
> 3. **Pengujian Latensi Waktu Tanggap (Latency Benchmark):** Mengukur durasi siklus sistem secara menyeluruh dari 10 kali percobaan. Diperoleh rata-rata waktu tanggap:  
>    - Akuisisi citra & upload WiFi: **~348 ms**  
>    - Inferensi AI di server: **~68 ms**  
>    - Respon balik & pergerakan servo: **~291 ms**  
>    - **Total Latensi End-to-End: ~0.707 detik**, yang terbukti sangat responsif dan tidak menimbulkan antrean bagi pengguna."*

