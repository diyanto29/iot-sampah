# Diagram Pemodelan UML (Unified Modeling Language)
## Format Standar Skripsi / Capstone Project Sistem Informasi

Dokumentasi diagram UML ini dirancang sesuai format baku laporan skripsi program studi **Sistem Informasi**, memodelkan fungsionalitas, alur aktivitas bisnis, dan interaksi komponen sistem secara terstruktur.

---

### 1. Use Case Diagram

Diagram Use Case memetakan interaksi antara para aktor dengan layanan yang disediakan oleh sistem informasi.

```mermaid
flowchart LR
    subgraph Aktor ["Aktor Pengguna"]
        User["Pengguna Sampah<br/>(Mahasiswa / Umum)"]
        Petugas["Petugas Kebersihan /<br/>Admin Fasilitas"]
        IoT["Perangkat Keras IoT<br/>(ESP32-CAM)"]
    end

    subgraph SystemBoundary ["Sistem Informasi Smart Waste IoT"]
        UC1(["UC-01: Membuang Sampah"])
        UC2(["UC-02: Deteksi & Klasifikasi Sampah"])
        UC3(["UC-03: Eksekusi Pemilahan Mekanik"])
        UC4(["UC-04: Monitoring Kapasitas Real-time"])
        UC5(["UC-05: Menerima Peringatan Darurat"])
        UC6(["UC-06: Mengunduh Laporan Rekapitulasi (CSV)"])
        UC7(["UC-07: Mengirim Data Telemetri"])
    end

    User --> UC1
    UC1 -.->|triggers| UC2
    IoT --> UC2
    UC2 -.->|includes| UC3
    IoT --> UC3
    IoT --> UC7

    Petugas --> UC4
    Petugas --> UC5
    Petugas --> UC6
    UC7 -.->|updates| UC4
    UC7 -.->|triggers alert| UC5
```

#### Deskripsi Spesifikasi Use Case:
1. **UC-01 (Membuang Sampah):** Pengguna meletakkan sampah pada nampan penampung atas tempat sampah.
2. **UC-02 (Deteksi & Klasifikasi Sampah):** Sistem secara otomatis mengambil citra dan mengidentifikasi kategori sampah (Organik/Anorganik) menggunakan model YOLOv8.
3. **UC-03 (Eksekusi Pemilahan Mekanik):** Sistem menggerakkan motor servo untuk membuang sampah ke wadah kompartemen yang sesuai.
4. **UC-04 (Monitoring Kapasitas Real-time):** Petugas kebersihan memantau persentase volume keterisian kedua tong melalui Web Dashboard.
5. **UC-05 (Menerima Peringatan Darurat):** Petugas kebersihan menerima notifikasi pesan instan via Telegram saat kapasitas tong mencapai $\ge 90\%$.
6. **UC-06 (Mengunduh Laporan Rekapitulasi):** Admin mengunduh berkas laporan pemilahan sampah berkala dalam format `.csv`.
7. **UC-07 (Mengirim Data Telemetri):** Perangkat IoT mengirimkan data ketinggian sampah secara otomatis setiap 30 detik ke server.

---

### 2. Activity Diagram: Alur Pemilahan Sampah Cerdas

Diagram aktivitas menggambarkan aliran kerja bisnis (*workflow*) mulai dari objek sampah diletakkan hingga data terekam di database.

```mermaid
stateDiagram-v2
    [*] --> Standby: Tempat Sampah Siaga
    Standby --> DeteksiObjek: Pengguna Meletakkan Sampah
    DeteksiObjek --> AkuisisiCitra: Sensor IR Aktif (GPIO 13 LOW)
    AkuisisiCitra --> TransmisiFoto: ESP32 Ambil Frame JPEG (VGA)
    TransmisiFoto --> ForwardAI: POST /api/predict ke Laravel Core
    ForwardAI --> InferensiRAM: Laravel Forward ke FastAPI Port 5000
    InferensiRAM --> KlasifikasiHasil: YOLOv8 Olah Citra di RAM
    KlasifikasiHasil --> SimpanLog: Return JSON {kategori, confidence}
    SimpanLog --> ResponESP32: Laravel Simpan ke Tabel waste_logs
    ResponESP32 --> GerakServo: Kirim JSON {servo_angle: 45 / 135}
    GerakServo --> JedaJatuh: Servo Buka Sekat Nampan
    JedaJatuh --> PosisiNetral: Delay 2 Detik Sampah Jatuh
    PosisiNetral --> Standby: Servo Kembali ke Sudut 90° (Datar)
```

---

### 3. Activity Diagram: Alur Telemetri Kapasitas & Notifikasi Penuh

```mermaid
stateDiagram-v2
    [*] --> TriggerTimer: Interval 30 Detik Tercapai
    TriggerTimer --> UkurJarak: ESP32 Kirim Pulsa Trig GPIO 14
    UkurJarak --> HitungPersen: Baca Echo GPIO 2 (Org) & GPIO 15 (Anorg)
    HitungPersen --> KirimTelemetri: Kalkulasi Persen Keterisian ((1 - D/30) * 100%)
    KirimTelemetri --> SimpanKapasitas: POST /api/telemetry/capacity
    SimpanKapasitas --> CekBatas: Laravel Simpan ke Tabel bin_capacities
    
    state CekBatas <<choice>>
    CekBatas --> KirimTelegram: Kapasitas >= 90%
    CekBatas --> Selesai: Kapasitas < 90%
    
    KirimTelegram --> UpdateWeb: Kirim Pesan Alert ke Telegram Bot
    Selesai --> UpdateWeb: Data Ditampilkan di Web Dashboard
    UpdateWeb --> [*]
```

---

### 4. Sequence Diagram: Interaksi Komponen Sistem

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna
    participant IR as Sensor IR (GPIO 13)
    participant ESP as ESP32-CAM
    participant Servo as Motor Servo (GPIO 12)
    participant Laravel as Laravel Backend (:8000)
    participant FastAPI as Python AI Worker (:5000)
    participant DB as Database (SQLite/MySQL)
    actor Admin as Petugas Kebersihan

    User->>IR: Letakkan Sampah di Nampan Atas
    IR->>ESP: Sinyal Digital LOW
    ESP->>ESP: Delay 400ms (Stabilisasi)
    ESP->>ESP: Capture Frame Citra JPEG (VGA)
    ESP->>Laravel: POST /api/predict (multipart/form-data)
    Note over Laravel,FastAPI: Microservices HTTP Bridge
    Laravel->>FastAPI: POST /detect (In-Memory Image Stream)
    FastAPI->>FastAPI: Inferensi YOLOv8 pada Buffer RAM
    FastAPI-->>Laravel: JSON {"kategori": "organik", "confidence": 0.94}
    Laravel->>DB: INSERT INTO waste_logs
    Laravel-->>ESP: JSON {"servo_action": "BUKA_KIRI", "servo_angle": 45}
    ESP->>Servo: Putar Sudut 45°
    Note over Servo: Sekat Terbuka (2 detik)
    ESP->>Servo: Putar Kembali ke Sudut 90° (Netral)

    opt Interval 30 Detik Telemetri
        ESP->>Laravel: POST /api/telemetry/capacity {org: 40%, anorg: 92%}
        Laravel->>DB: INSERT INTO bin_capacities
        alt Kapasitas >= 90%
            Laravel->>Admin: Kirim Alert Telegram ("⚠️ Tong Anorganik PENUH!")
        end
    end

    Admin->>Laravel: Akses Web Dashboard (GET /)
    Laravel-->>Admin: Render Live Capacity Gauges & Charts
```

---

### 5. Class Diagram (Entity-Relationship Model)

```mermaid
classDiagram
    class WasteBin {
        +String id [PK]
        +String location_name
        +Decimal bin_height_cm
        +Boolean is_active
        +Timestamp last_active_at
        +timestamps()
        +logs() HasMany
        +capacities() HasMany
    }

    class WasteLog {
        +BigInteger id [PK]
        +String waste_bin_id [FK]
        +Enum category ['organik', 'anorganik']
        +Decimal confidence
        +Timestamp sorted_at
        +bin() BelongsTo
    }

    class BinCapacity {
        +BigInteger id [PK]
        +String waste_bin_id [FK]
        +Decimal organik_percent
        +Decimal anorganik_percent
        +Timestamp recorded_at
        +bin() BelongsTo
    }

    WasteBin "1" -- "0..*" WasteLog : mencatat
    WasteBin "1" -- "0..*" BinCapacity : memantau
```

