# Panduan Perangkat Keras, Pinout & Pengkabelan (Hardware & Wiring)
## AI-Thinker ESP32-CAM (3 Kompartemen: Organik, Daur Ulang, Residu & B3)

---

### 1. Pemilihan Varian Modul ESP32-CAM (Rekomendasi Marketplace)

Modul **ESP32-CAM** di marketplace dijual dalam beberapa varian. Pastikan memilih varian yang tepat:

* **Varian "ESP32-CAM Saja" (Kurang Disarankan):**
  - Hanya modul kamera polosan tanpa port USB. Memerlukan konverter FTDI USB-TTL eksternal dan jumper IO0-GND manual setiap flash.
* **Varian "ESP32-CAM + MB Shield" (SANGAT DIREKOMENDASIKAN):**
  - Dilengkapi papan dudukan hitam (*Shield MB*) dengan port Micro-USB, chip USB-Serial CH340, tombol Reset (RST), dan tombol Boot (IO0).
  - Bisa langsung di-upload program via kabel Micro-USB biasa seperti Arduino Uno.

---

### 2. Tantangan Pin GPIO pada ESP32-CAM untuk 3 Kompartemen

Modul ESP32-CAM memiliki alokasi pin kamera OV2640 internal yang padat. Namun, dengan teknik **Shared Trigger** dan pemanfaatan pin komunikasi sekunder, kita dapat mengendalikan **1 Sensor IR**, **1 Motor Servo (3 Arah Sudut)**, dan **3 Sensor Ultrasonik**:

| Nama Komponen | Pin pada Sensor/Modul | Terhubung ke Pin ESP32-CAM | Penjelasan & Teknik Penghematan |
| :--- | :--- | :--- | :--- |
| **Catu Daya Bersama** | VCC / 5V<br>GND | **5V** ESP32-CAM<br>**GND** ESP32-CAM | Sambungkan ke rel breadboard untuk menyuplai semua sensor dan servo. |
| **Sensor IR Obstacle** | VCC & GND<br>OUT (Digital) | 5V & GND<br>**GPIO 13** | Menghasilkan sinyal digital `LOW` saat ada objek sampah di nampan atas. |
| **Motor Servo (MG90S/SG90)** | VCC (+5V)<br>GND<br>Signal PWM | 5V<br>GND<br>**GPIO 12** | Mengendalikan nampan 3 arah: **30° (Kiri - Organik)**, **90° (Tengah - Daur Ulang)**, **150° (Kanan - Residu/B3)**. |
| **Ultrasonik 1 (Organik)** | VCC & GND<br>Trig<br>Echo | 5V & GND<br>**GPIO 14**<br>**GPIO 2** | Pin `Trig` digabung secara paralel ke GPIO 14.<br>Pin `Echo` khusus membaca level tong Organik. |
| **Ultrasonik 2 (Daur Ulang)** | VCC & GND<br>Trig<br>Echo | 5V & GND<br>**GPIO 14**<br>**GPIO 15** | Pin `Trig` digabung ke GPIO 14.<br>Pin `Echo` membaca level tong Daur Ulang. |
| **Ultrasonik 3 (Residu & B3)** | VCC & GND<br>Trig<br>Echo | 5V & GND<br>**GPIO 14**<br>**GPIO 16** (U2RXD) | Pin `Trig` digabung ke GPIO 14.<br>Pin `Echo` membaca level tong Residu & B3. |

> **Teknik Pin-Sharing Sensor Ultrasonik:**  
> Kaki `Trig` ketiga sensor ultrasonik dihubungkan secara paralel ke satu pin mikrokontroler (**GPIO 14**). Ketika pulsa Trig dikirimkan, ketiga sensor memancarkan gelombang sonar bersamaan, namun sinyal pantulan (*Echo*) dibaca secara bergantian dengan jeda 40 ms via GPIO 2, GPIO 15, dan GPIO 16. Teknik ini menghemat 2 pin GPIO penting pada ESP32-CAM.

---

### 3. Diagram Mekanik 3 Arah Motor Servo

```
                          [ POSISI NETRAL SIAGA ]
                                    90°
                                     |
                                     v
                        +-------------------------+
                        | NAMPAN PENAMPUNG SAMPAH |
                        +-------------------------+
                               /     |     \
                              /      |      \
                             /       |       \
                            v        v        v
                         30°        90°       150°
                       (KIRI)     (TENGAH)   (KANAN)
                          |          |          |
                          v          v          v
                     +---------+ +---------+ +---------+
                     | 1. TONG | | 2. TONG | | 3. TONG |
                     | ORGANIK | |  DAUR   | | RESIDU  |
                     | (HIJAU) | |  ULANG  | |   / B3  |
                     |         | | (BIRU)  | | (MERAH) |
                     +---------+ +---------+ +---------+
```

---

### 4. Diagram Rangkaian Pengkabelan (Wiring Schematic 3 Tong)

```
        +---------------------------------------------------------------+
        |                          ESP32-CAM                            |
        |                                                               |
        | [5V] [GND] [GPIO 13] [GPIO 12] [GPIO 14] [GPIO 2] [GPIO 15] [GPIO 16]
        +---|----|-------|---------|---------|--------|--------|--------|---+
            |    |       |         |         |        |        |        |
    +-------+    +----+  |         |         |        |        |        |
    | (Rel 5V) (Rel GND)|         |         |        |        |        |
    |                 |  |         |         |        |        |        |
    +-- VCC           |  |         |         |        |        |        |
    +-- GND ----------+  |         |         |        |        |        |
    |  Sensor IR OUT ----+         |         |        |        |        |
    |                              |         |        |        |        |
    +-- VCC (Servo Merah)          |         |        |        |        |
    +-- GND (Servo Cokelat)        |         |        |        |        |
    |   Signal (Servo Oranye) -----+         |        |        |        |
    |                                        |        |        |        |
    +-- VCC (US1, US2, US3)                  |        |        |        |
    +-- GND (US1, US2, US3)                  |        |        |        |
    |   Trig Bersama (US1, US2, US3) --------+        |        |        |
    |   Echo US1 (Organik) ---------------------------+        |        |
    |   Echo US2 (Daur Ulang) ---------------------------------+        |
    |   Echo US3 (Residu/B3) -------------------------------------------+
```
