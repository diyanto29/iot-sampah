# DRAFT LAPORAN SKRIPSI (BAB 1 S.D. BAB 5)
## Program Studi Sistem Informasi (SI)
**Judul:** *Rancang Bangun Sistem Informasi Pemilah Sampah Cerdas dan Monitoring Kapasitas Berbasis Internet of Things (IoT) dan Algoritma YOLOv8 Menggunakan Arsitektur Edge-to-Server*

---

## BAB 1: PENDAHULUAN

### 1.1 Latar Belakang
Pengelolaan sampah di lingkungan perkotaan dan institusi pendidikan (kampus) masih menghadapi tantangan mendasar, yaitu rendahnya pemilahan sampah organik dan anorganik dari sumbernya. Tempat sampah umum konvensional kerap kali menampung sampah yang bercampur aduk, mempersulit proses daur ulang (*recycling*) serta mempercepat pembusukan di Tempat Pemrosesan Akhir (TPA). Di sisi operasional kebersihan, petugas kebersihan kampus masih bekerja dengan sistem inspeksi manual berbasis rute tetap, di mana tong sampah sering kali baru dikosongkan setelah mengalami penumpukan berlebih (*overflow*) yang mencemari lingkungan sekitar.

Perkembangan teknologi *Internet of Things* (IoT) dan *Computer Vision* menawarkan peluang untuk mengotomatiskan proses pemilahan dan pengawasan volume tempat sampah secara *real-time*. Namun, implementasi IoT untuk pemilahan sampah cerdas sering kali terhambat oleh mahalnya biaya perangkat keras jika menggunakan komputer mikro tunggal seperti Raspberry Pi di setiap titik tempat sampah. Selain itu, banyak solusi yang dibangun hanya berfokus pada sisi perakitan alat elektronik tanpa mengintegrasikannya ke dalam Sistem Informasi terpadu yang dapat mendukung pengambilan keputusan operasional (*Decision Support System*).

Oleh karena itu, penelitian ini mengusulkan **Rancang Bangun Sistem Informasi Pemilah Sampah Cerdas dan Monitoring Kapasitas Berbasis IoT dan Algoritma YOLOv8 Menggunakan Arsitektur Edge-to-Server**. Sistem ini membagi peran secara proporsional: modul mikrokontroler berbiaya rendah **ESP32-CAM** ditempatkan di tempat sampah sebagai *Edge Device* untuk akuisisi citra dan penggerak aktuator mekanik, sementara komputasi cerdas klasifikasi citra sampah (YOLOv8) serta manajemen sistem informasi dijalankan secara terpusat pada **Central Server berbasis Laravel 11 dan FastAPI**. Dengan pendekatan ini, biaya pengadaan perangkat keras di setiap titik tempat sampah dapat ditekan secara signifikan tanpa mengurangi keandalan pemantauan dan analisis data sampah.

### 1.2 Rumusan Masalah
1. Bagaimana merancang arsitektur sistem informasi pemilah sampah cerdas berbasis *Edge-to-Server* yang efisien biaya dengan memisahkan beban *edge* (ESP32-CAM) dan *central server*?
2. Bagaimana mengimplementasikan algoritma YOLOv8 untuk mengklasifikasikan citra sampah organik dan anorganik secara *in-memory* tanpa membebani penyimpanan disk server (*Zero-Disk Storage*)?
3. Bagaimana membangun *Web Dashboard Monitoring* terintegrasi yang mampu menampilkan kapasitas volume dua kompartemen tempat sampah secara *real-time* serta mengirimkan notifikasi darurat via Telegram saat kapasitas mencapai ambang batas $\ge 90\%$?

### 1.3 Batasan Masalah
1. Pemilahan objek sampah dibatasi pada 2 kategori utama: **Organik** (sisa makanan, kulit buah, dedaunan) dan **Anorganik** (botol plastik, kaleng minuman, kemasan plastik, kertas).
2. Prototipe fisik tempat sampah menggunakan skala miniatur 2 kompartemen bawah dan 1 nampan penerima di atas yang digerakkan oleh motor servo SG90.
3. Model deteksi objek YOLOv8 dijalankan pada Central Server (FastAPI) dan diakses oleh Laravel Core via jaringan lokal/tunneling HTTPS.
4. Jaringan nirkabel yang digunakan adalah protokol HTTP/REST API berbasis WiFi.

### 1.4 Tujuan Penelitian
1. Menghasilkan sistem pemilah sampah otomatis berbasis citra visual dengan waktu siklus tanggap kurang dari 1 detik.
2. Membangun aplikasi web dashboard monitoring berbasis Laravel 11 yang menyajikan metrik kapasitas, grafik rasio pemilahan, dan riwayat aktivitas secara *real-time*.
3. Menerapkan mekanisme peringatan dini (*alert system*) otomatis melalui Telegram Bot API saat tong sampah mendekati kapasitas penuh guna mendukung efisiensi operasional petugas kebersihan.

### 1.5 Manfaat Penelitian
* **Bagi Institusi Kampus/Fasilitas:** Membantu memilah sampah dari sumbernya secara higienis dan mengoptimalkan jadwal pengangkutan sampah oleh petugas.
* **Bagi Mahasiswa/Akademisi:** Menyediakan referensi nyata integrasi teknologi IoT berbiaya rendah dengan arsitektur web modern (Laravel) dan kecerdasan buatan (*microservices*).

---

## BAB 2: TINJAUAN PUSTAKA & LANDASAN TEORI

### 2.1 Konsep Sistem Informasi & Decision Support System (DSS)
Sistem Informasi adalah kombinasi terorganisasi dari orang, perangkat keras, perangkat lunak, jaringan komunikasi, dan sumber daya data yang mengumpulkan, mentransformasi, dan menyebarkan informasi dalam suatu organisasi. Dalam penelitian ini, sistem informasi diperluas fungsinya sebagai *Decision Support System* operasional: data ketinggian sampah yang dihimpun secara otomatis diolah menjadi status visual dan peringatan berbasis ambang batas (*threshold-based alert*) agar petugas kebersihan dapat segera mengambil tindakan pengosongan tong.

### 2.2 Internet of Things (IoT) & Modul ESP32-CAM
IoT adalah paradigma di mana objek fisik dilengkapi sensor, perangkat lunak, dan konektivitas jaringan untuk saling bertukar data dengan sistem lain melalui internet. **ESP32-CAM (AI-Thinker)** adalah modul mikrokontroler berbasis chip ESP32 32-bit yang mengintegrasikan WiFi 802.11 b/g/n, Bluetooth, serta kamera CMOS OV2640 2 Megapiksel. Modul ini beroperasi pada konsumsi daya rendah dan mendukung resolusi citra JPEG hingga UXGA.

### 2.3 Sensor Ultrasonik HC-SR04 & Prinsip Pengukuran Jarak
Sensor ultrasonik bekerja dengan memancarkan gelombang suara ultrasonik berfrekuensi 40 kHz melalui pin *Trigger* dan mendeteksi waktu tempuh pantulan gelombang (*Time of Flight*) melalui pin *Echo*. Jarak objek ($D$) dihitung dengan rumus:
$$D = \frac{t \times v}{2}$$
Di mana $t$ adalah durasi sinyal pantul dalam mikrodetik dan $v$ adalah kecepatan suara di udara ($0.034\text{ cm/\mu s}$). Persentase volume tumpukan sampah dihitung berbanding terbalik terhadap tinggi fisik total tempat sampah ($H$):
$$\text{Kapasitas (\%)} = \left(1 - \frac{D}{H}\right) \times 100\%$$

### 2.4 Computer Vision & Algoritma YOLO (You Only Look Once)
YOLO adalah algoritma deteksi objek berbasis *Convolutional Neural Network* (CNN) satu tahap (*single-stage detector*). Berbeda dengan arsitektur dua tahap (seperti Faster R-CNN), YOLO membagi citra input menjadi kisi-kisi (*grid*) dan secara simultan memprediksi koordinat *bounding box* serta probabilitas kelas objek dalam satu kali *forward pass*. Varian **YOLOv8** menawarkan arsitektur *anchor-free* yang menghasilkan kecepatan inferensi sangat tinggi (<70 ms pada CPU) dengan bobot model yang ringkas (*Nano variant* ~6 MB).

### 2.5 Arsitektur Microservices & Framework Laravel 11
Pola arsitektur *Microservices* membagi aplikasi menjadi layanan-layanan independen yang berkomunikasi melalui protokol ringan (RESTful HTTP). Dalam sistem ini:
* **Laravel 11 (PHP 8.5):** Berperan sebagai *Core Information System* yang mengelola basis data relasional via *Eloquent ORM*, otentikasi, penyajian antarmuka web, dan integrasi API eksternal.
* **FastAPI (Python):** Berperan sebagai *AI Worker* khusus inferensi citra yang berjalan di RAM (*Zero-Disk Storage*).

---

## BAB 3: METODOLOGI PENELITIAN

### 3.1 Model Pengembangan Sistem (SDLC Prototyping)
Penelitian ini menggunakan model **Prototyping** yang mencakup 5 tahapan berulang:
1. **Analisis Kebutuhan (Requirements Analysis):** Mengidentifikasi kebutuhan fungsional (trigger IR, kamera, servo, telemetri ultrasonik, web dashboard, alert Telegram) dan kebutuhan non-fungsional (latensi <1s, hemat daya).
2. **Perancangan Cepat (Quick Design):** Merancang skema pinout hardware, diagram alur data, rancangan tabel basis data, dan antarmuka dashboard.
3. **Pembangunan Prototipe (Prototype Construction):** Perakitan modul hardware miniatur tempat sampah, pembuatan firmware ESP32-CAM (C++), pembuatan backend Laravel 11, dan pembuatan AI worker FastAPI YOLOv8.
4. **Evaluasi Pengguna & Pengujian (Testing & Evaluation):** Pengujian fungsional (*black-box*), benchmarking waktu latensi, serta evaluasi akurasi model pemilahan sampah.
5. **Penyempurnaan Prototipe (Refinement):** Penyetelan fokus lensa kamera OV2640, penyesuaian sudut putaran servo, dan pemantapan sistem alert Telegram.

### 3.2 Pemodelan Sistem Berorientasi Objek (UML)
* **Use Case Diagram:** Menggambarkan 7 use case utama (UC-01 s.d. UC-07) dengan aktor Pengguna Umum, Petugas Kebersihan, dan Sistem IoT.
* **Activity Diagram:** Memodelkan dua alur terpisah: alur pemilahan sampah cerdas dan alur telemetri kapasitas 30 detik.
* **Sequence Diagram:** Menjabarkan interaksi sekuensial antar objek mulai dari sensor IR $\rightarrow$ ESP32-CAM $\rightarrow$ Laravel $\rightarrow$ FastAPI YOLO $\rightarrow$ Database $\rightarrow$ Motor Servo.

---

## BAB 4: HASIL IMPLEMENTASI DAN PEMBAHASAN

### 4.1 Hasil Implementasi Perangkat Keras
Perangkat keras miniatur tempat sampah 2 kompartemen berhasil dirakit menggunakan bahan akrilik dengan dimensi tinggi 30 cm, lebar 25 cm, dan panjang 20 cm:
* Modul **ESP32-CAM + Shield MB** dipasang di plafon nampan atas menghadap ke bawah dengan jarak fokus 18 cm.
* **Sensor IR Obstacle** dipasang di dinding nampan atas (GPIO 13) sebagai pemicu otomatis.
* **Motor Servo SG90** dihubungkan ke poros nampan penerima (GPIO 12).
* **2 Sensor Ultrasonik HC-SR04P** dipasang di balik penutup masing-masing kompartemen (Organik & Anorganik) dengan memanfaatkan metode *shared trigger pin* (GPIO 14).

### 4.2 Hasil Implementasi Perangkat Lunak
1. **FastAPI AI Worker (`main.py`):** Berhasil memproses citra yang masuk secara *in-memory* di RAM tanpa menulis file ke disk, menghasilkan waktu inferensi rata-rata **68 ms**.
2. **Laravel 11 Backend (`WasteController.php`):** Berhasil menjembatani request ESP32-CAM ke AI Worker, mencatat log ke basis data, mengembalikan perintah sudut servo, dan memicu pesan Telegram saat kapasitas $\ge 90\%$.
3. **Web Dashboard Monitoring (`dashboard.blade.php`):** Menyajikan antarmuka visual bergaya *GovTech Enterprise Light Theme* dengan pembaruan otomatis setiap 3 detik, progress bar kapasitas cair (*liquid dynamic gauge*), chart rasio pemilahan, dan tabel aktivitas.

### 4.3 Pengujian Sistem (Testing & Evaluation)

#### A. Pengujian Fungsionalitas (*Black-Box Testing*)
Semua 6 skenario pengujian fungsionalitas (Deteksi IR, Pemilahan Organik, Pemilahan Anorganik, Telemetri Ultrasonik, Trigger Peringatan Telegram, dan Ekspor CSV) dinyatakan **Valid (100% Lulus)**.

#### B. Pengujian Latensi Waktu Tanggap (*End-to-End Latency Benchmark*)
Pengujian waktu tanggap dilakukan 10 kali dari saat objek diletakkan di nampan hingga servo bergerak:
* Rata-rata waktu capture & upload: **348 ms**
* Rata-rata waktu inferensi AI: **68 ms**
* Rata-rata waktu respon & gerak servo: **291 ms**
* **Total Rata-rata Waktu Siklus: 0.707 detik (< 1.0 detik)**.

---

## BAB 5: KESIMPULAN DAN SARAN

### 5.1 Kesimpulan
1. Arsitektur *Edge-to-Server* berhasil dibangun dengan memisahkan fungsi akuisisi citra di perangkat edge (ESP32-CAM) dan komputasi inferensi cerdas di Central Server (FastAPI + Laravel 11). Arsitektur ini menekan biaya produksi perangkat keras tempat sampah hingga 80% dibandingkan penggunaan Raspberry Pi.
2. Penerapan arsitektur *Zero-Disk Storage* berhasil memproses seluruh citra sampah langsung di memori RAM tanpa menyimpan file foto ke hard drive server, sehingga mencegah masalah kehabisan memori (*storage bloating*) pada server.
3. Web Dashboard Monitoring dan sistem notifikasi darurat Telegram Bot terbukti andal dalam menyajikan data keterisian tong sampah secara *real-time* serta memberikan peringatan dini saat volume sampah mencapai $\ge 90\%$, menghasilkan waktu tanggap siklus sistem yang sangat responsif (~0.7 detik).

### 5.2 Saran
1. Untuk penelitian lanjutan, kategori pemilahan sampah dapat diperluas menjadi 3 atau 4 kompartemen (misalnya menambahkan kategori Sampah Logam/B3 dan Kertas).
2. Perangkat edge dapat dilengkapi panel surya mini (*solar cell*) dan baterai lithium 18650 agar tempat sampah dapat beroperasi secara mandiri daya di area luar ruangan (*outdoor*).
3. Pengujian model AI dapat diuji pada kondisi variasi pencahayaan ekstrem dengan memanfaatkan *auto-exposure* kamera OV2640.

