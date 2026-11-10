# Arsitektur Sistem & Alur Aliran Data (Data Flow)
## Smart Waste IoT Ecosystem: Laravel 11 + FastAPI + ESP32-CAM

---

### 1. Diagram Arsitektur Tingkat Tinggi (High-Level Architecture)

```mermaid
flowchart TD
    subgraph EdgeDevice ["PERANGKAT KERAS IOT (TEMPAT SAMPAH)"]
        IR["Sensor IR Obstacle<br/>(GPIO 13)"]
        CAM["ESP32-CAM (OV2640)<br/>Akuisisi Citra VGA"]
        Servo["Motor Servo SG90<br/>(GPIO 12)"]
        US_Org["Sensor Ultrasonik 1<br/>(Trig: 14, Echo: 2)"]
        US_Anorg["Sensor Ultrasonik 2<br/>(Trig: 14, Echo: 15)"]
    end

    subgraph Tunnel ["SECURE PUBLIC GATEWAY"]
        CF["Cloudflare Tunnel / Ngrok<br/>(HTTPS Public Domain Tanpa Port Forwarding)"]
    end

    subgraph Server ["CENTRAL / HOME SERVER"]
        subgraph LaravelApp ["Laravel 11 Backend (Port 8000)"]
            Route["routes/api.php"]
            Controller["WasteController.php"]
            DB[("Database Relasional<br/>SQLite / MySQL")]
            Report["ReportController.php<br/>(Export CSV)"]
        end

        subgraph AIMicroservice ["FastAPI AI Worker (Port 5000)"]
            FastAPIApp["main.py (FastAPI)"]
            YOLO["YOLOv8 Engine<br/>(In-Memory RAM Inference)"]
        end
    end

    subgraph ExternalClients ["ANTARMUKA PENGGUNA & NOTIFIKASI"]
        Dashboard["Web Dashboard Monitoring<br/>(Tailwind CSS + Chart.js)"]
        Telegram["Telegram Bot API<br/>(Alert Darurat Kapasitas >= 90%)"]
    end

    %% Alur Pemilahan
    IR -->|1. Deteksi Objek Masuk (LOW)| CAM
    CAM -->|2. HTTP POST Image (Multipart)| CF
    CF -->|3. Forward HTTPS Request| Route
    Route --> Controller
    Controller -->|4. Internal HTTP Post (In-Memory)| FastAPIApp
    FastAPIApp -->|5. Stream Bytesio ke RAM| YOLO
    YOLO -->|6. JSON: {kategori, confidence}| FastAPIApp
    FastAPIApp -->|7. Return JSON Prediksi| Controller
    Controller -->|8. Simpan Log (Eloquent)| DB
    Controller -->|9. JSON Response: servo_angle| CF
    CF -->|10. Terima Instruksi Sudut| CAM
    CAM -->|11. Gerakkan Sudut 45° / 135°| Servo

    %% Alur Telemetri
    US_Org & US_Anorg -->|12. Telemetri Ketinggian Berkala (30s)| CF
    CF -->|13. POST /api/telemetry/capacity| Route
    Controller -->|14. Simpan Kapasitas| DB
    Controller -.->|15. Jika Kapasitas >= 90%| Telegram

    %% Antarmuka Web
    DB -.->|16. Query Data & Metrik| Dashboard
```

---

### 2. Alur Pemilahan Sampah Otomatis (Step-by-Step Flow)

1. **Tahap Standby (Hemat Daya):**
   * ESP32-CAM dalam kondisi siaga (*idle*). Kamera tidak aktif mengambil gambar secara sembarangan untuk mencegah panas berlebih dan beban jaringan.
2. **Tahap Deteksi Objek (Event Trigger):**
   * Pengguna meletakkan sampah di nampan penerima atas.
   * Sensor IR Obstacle mendeteksi pantulan objek dan mengirimkan sinyal digital `LOW` ke pin **GPIO 13** ESP32-CAM.
3. **Tahap Stabilisasi & Akuisisi Citra:**
   * Mikrokontroler memberikan jeda stabilisasi selama **400 milidetik** agar posisi sampah berhenti bergoyang.
   * Lampu flash LED (GPIO 4) dinyalakan sesaat, modul kamera OV2640 mengambil 1 frame citra resolusi VGA (640x480 format JPEG), lalu lampu dimatikan.
4. **Tahap Transmisi ke Laravel API:**
   * ESP32-CAM membungkus buffer citra ke dalam format `multipart/form-data` bersama parameter `device_id: "BIN-01"`.
   * Data dikirimkan via koneksi WiFi ke URL publik Cloudflare Tunnel (`POST /api/predict`).
5. **Tahap Forwarding ke AI Microservice:**
   * Laravel menerima request pada `WasteController::predict()`.
   * Menggunakan fasilitas HTTP Client Laravel (`Http::attach()`), bytes foto diteruskan secara lokal ke port 5000 (`POST http://127.0.0.1:5000/detect`).
6. **Tahap Inferensi YOLOv8 In-Memory:**
   * FastAPI membaca file bytes langsung ke RAM menggunakan `PIL.Image.open(io.BytesIO(image_bytes))`.
   * Model YOLOv8 mengeksekusi inferensi objek. Jika kelas objek terdeteksi masuk dalam daftar kata kunci organik (buah, sayur, dedaunan, makanan), maka dikategorikan sebagai **"organik"**. Jika selain itu (plastik, kaleng, botol, kertas), dikategorikan sebagai **"anorganik"**.
   * FastAPI mengembalikan respon JSON ke Laravel:  
     `{"kategori": "organik", "confidence": 0.94}`.
7. **Tahap Pencatatan Database & Respon Balik:**
   * Laravel menyimpan data ke tabel `waste_logs` via Eloquent ORM.
   * Laravel menentukan aturan sudut servo:
     - Organik $\rightarrow$ `servo_action: "BUKA_KIRI"`, `servo_angle: 45`.
     - Anorganik $\rightarrow$ `servo_action: "BUKA_KANAN"`, `servo_angle: 135`.
   * Respon JSON dikirim balik ke ESP32-CAM.
8. **Tahap Eksekusi Aktuator Mekanik:**
   * ESP32-CAM mengurai JSON dan memutar servo ke sudut yang ditentukan.
   * Nampan miring, sampah jatuh ke dalam tong yang sesuai.
   * Setelah jeda 2 detik, servo berputar kembali ke posisi netral 90° (posisi nampan datar kembali).

---

### 3. Alur Telemetri Kapasitas & Decision Support System (DSS Alert)

1. **Pengukuran Sensor Ultrasonik:**
   * Setiap 30 detik, ESP32-CAM memicu pin Trig (GPIO 14) untuk memancarkan gelombang suara frekuensi 40 kHz.
   * Echo sensor organik (GPIO 2) dan echo sensor anorganik (GPIO 15) menghitung durasi pantulan untuk mengukur jarak permukaan sampah ke tutup tong ($D$).
2. **Kalkulasi Persentase Volume:**
   $$\text{Persentase Keterisian (\%)} = \left(1 - \frac{D}{\text{Tinggi Tong (30 cm)}}\right) \times 100\%$$
3. **Pengiriman Data:**
   * Data dikirim via `POST /api/telemetry/capacity` berformat JSON:  
     `{"device_id": "BIN-01", "organik_percent": 42.5, "anorganik_percent": 91.0}`.
4. **Evaluasi Ambang Batas (Threshold Rule) di Laravel:**
   * Data dicatat ke tabel `bin_capacities`.
   * Jika $\text{organik\_percent} \ge 90\%$ atau $\text{anorganik\_percent} \ge 90\%$, Laravel secara otomatis memanggil API Telegram Bot untuk mengirim pesan peringatan darurat ke petugas kebersihan kampus.
5. **Visualisasi di Web Dashboard:**
   * Frontend Web Dashboard yang sedang dibuka petugas melakukan sinkronisasi otomatis (*polling*) setiap 3 detik ke endpoint `GET /api/dashboard/stats`.
   * Gauge kapasitas langsung bergerak secara visual, warna berubah menjadi merah berkedip (*animate-pulse-glow*), dan grafik donut terupdate otomatis.

---

### 4. Kenapa Arsitektur Ini Unggul untuk Skripsi Sistem Informasi?

* **Pemisahan Tanggung Jawab (Separation of Concerns):**  
  Mikrokontroler tidak dibebani komputasi AI; model AI tidak dipusingkan dengan manajemen database/tampilan web; dan Laravel memegang kendali penuh atas aturan bisnis dan penyajian data.
* **Efisiensi Biaya Signifikan:**  
  Penggunaan ESP32-CAM (< Rp 100.000) menggantikan Raspberry Pi 4 (Rp 1.500.000+), sehingga biaya produksi alat turun hingga 80%.
* **Konsep Multi-Tier Skalabel:**  
  Jika di kemudian hari kampus menambah 10 unit tempat sampah cerdas di berbagai lantai gedung, tidak perlu membeli 10 komputer tambahan. Seluruh unit cukup mengarahkan HTTP POST ke endpoint server Laravel yang sama.

