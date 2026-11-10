/**
 * SMART WASTE SORTING SYSTEM (3 MOTOR SERVO) - FIRMWARE ESP32
 * Sistem Pembuka Tutup Otomatis 3 Tong Sampah (Organik, Daur Ulang, Residu & B3)
 *
 * Konsep Kerja 3 Motor Servo:
 * - Servo 1 (Pin 12): Menggerakkan penutup Tong Organik (Buka 90°, Tutup 0°)
 * - Servo 2 (Pin 13): Menggerakkan penutup Tong Daur Ulang (Buka 90°, Tutup 0°)
 * - Servo 3 (Pin 15): Menggerakkan penutup Tong Residu & B3 (Buka 90°, Tutup 0°)
 * - 3x Sensor Ultrasonik HC-SR04P (Shared Trig: GPIO 14, Echo 1: GPIO 2, Echo 2: GPIO 4, Echo 3: GPIO 16)
 */

#include "esp_camera.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>

// ==========================================
// 1. KREDENSIAL WIFI & URL ENDPOINT LARAVEL
// ==========================================
const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI";

// Ganti domain ini dengan URL Cloudflare Tunnel atau IP Laptop (contoh: http://192.168.1.100:8000/api/...)
const char* predictApiUrl   = "https://domain-tunnel-anda.trycloudflare.com/api/predict";
const char* telemetryApiUrl = "https://domain-tunnel-anda.trycloudflare.com/api/telemetry/capacity";

// ==========================================
// 2. PEMETAAN PIN 3 MOTOR SERVO & SENSOR
// ==========================================
#define PIN_SERVO_ORGANIK    12   // Servo 1: Buka tutup tong organik
#define PIN_SERVO_ANORGANIK  13   // Servo 2: Buka tutup tong daur ulang
#define PIN_SERVO_RESIDU     15   // Servo 3: Buka tutup tong residu/B3

#define PIN_TRIG_SHARED      14   // Shared Trigger ke 3 sensor ultrasonik
#define PIN_ECHO_ORGANIK     2    // Echo ultrasonik tong organik
#define PIN_ECHO_ANORGANIK   4    // Echo ultrasonik tong daur ulang
#define PIN_ECHO_RESIDU      16   // Echo ultrasonik tong residu & B3

// Objek 3 Motor Servo
Servo servoOrganik;
Servo servoAnorganik;
Servo servoResidu;

// Konfigurasi pin modul kamera AI-Thinker
#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27
#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22

const float TINGGI_TONG_CM = 30.0; // Tinggi fisik tong prototipe
unsigned long lastTelemetryTime = 0;

void setupCamera() {
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;
  config.frame_size = FRAMESIZE_VGA;
  config.jpeg_quality = 12;
  config.fb_count = 1;

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("[CAMERA] Gagal: 0x%x\n", err);
  } else {
    Serial.println("[CAMERA] Berhasil inisialisasi!");
  }
}

float bacaJarakUltrasonik(int echoPin) {
  digitalWrite(PIN_TRIG_SHARED, LOW);
  delayMicroseconds(2);
  digitalWrite(PIN_TRIG_SHARED, HIGH);
  delayMicroseconds(10);
  digitalWrite(PIN_TRIG_SHARED, LOW);

  long durasi = pulseIn(echoPin, HIGH, 30000);
  if (durasi == 0) return TINGGI_TONG_CM;
  return (durasi * 0.034) / 2.0;
}

void kirimTelemetriKapasitas() {
  float distOrg = bacaJarakUltrasonik(PIN_ECHO_ORGANIK);
  delay(40);
  float distAnorg = bacaJarakUltrasonik(PIN_ECHO_ANORGANIK);
  delay(40);
  float distRes = bacaJarakUltrasonik(PIN_ECHO_RESIDU);

  float pctOrg = constrain((1.0 - (distOrg / TINGGI_TONG_CM)) * 100.0, 0.0, 100.0);
  float pctAnorg = constrain((1.0 - (distAnorg / TINGGI_TONG_CM)) * 100.0, 0.0, 100.0);
  float pctRes = constrain((1.0 - (distRes / TINGGI_TONG_CM)) * 100.0, 0.0, 100.0);

  Serial.printf("[TELEMETRI 3 TONG] Organik: %.1f%% | Daur Ulang: %.1f%% | Residu/B3: %.1f%%\n", pctOrg, pctAnorg, pctRes);

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(telemetryApiUrl);
    http.addHeader("Content-Type", "application/json");

    String jsonPayload = "{\"device_id\":\"BIN-01\",\"organik_percent\":" + String(pctOrg, 1) + 
                         ",\"anorganik_percent\":" + String(pctAnorg, 1) + 
                         ",\"residu_percent\":" + String(pctRes, 1) + "}";
    int httpCode = http.POST(jsonPayload);
    http.end();
  }
}

void bukaTutupTong(const char* kategori) {
  if (strcmp(kategori, "organik") == 0) {
    Serial.println("[AKSI SERVO] Membuka Tutup Tong 1 (Organik)...");
    servoOrganik.write(90);  // Buka tutup 90 derajat
    delay(3500);             // Beri waktu 3.5 detik bagi user membuang sampah
    servoOrganik.write(0);   // Tutup kembali
    Serial.println("[AKSI SERVO] Tong 1 (Organik) ditutup kembali.");
  } else if (strcmp(kategori, "residu") == 0) {
    Serial.println("[AKSI SERVO] Membuka Tutup Tong 3 (Residu/B3)...");
    servoResidu.write(90);
    delay(3500);
    servoResidu.write(0);
    Serial.println("[AKSI SERVO] Tong 3 (Residu/B3) ditutup kembali.");
  } else {
    Serial.println("[AKSI SERVO] Membuka Tutup Tong 2 (Daur Ulang)...");
    servoAnorganik.write(90);
    delay(3500);
    servoAnorganik.write(0);
    Serial.println("[AKSI SERVO] Tong 2 (Daur Ulang) ditutup kembali.");
  }
}

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n=== SMART WASTE BIN (3 MOTOR SERVO TUTUP TONG) START ===");

  pinMode(PIN_TRIG_SHARED, OUTPUT);
  pinMode(PIN_ECHO_ORGANIK, INPUT);
  pinMode(PIN_ECHO_ANORGANIK, INPUT);
  pinMode(PIN_ECHO_RESIDU, INPUT);

  // Inisialisasi 3 Motor Servo pada Posisi Tertutup (0°)
  servoOrganik.attach(PIN_SERVO_ORGANIK);
  servoOrganik.write(0);

  servoAnorganik.attach(PIN_SERVO_ANORGANIK);
  servoAnorganik.write(0);

  servoResidu.attach(PIN_SERVO_RESIDU);
  servoResidu.write(0);

  setupCamera();

  WiFi.begin(ssid, password);
  Serial.print("[WIFI] Menghubungkan ke ");
  Serial.println(ssid);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n[WIFI] Terhubung!");
}

void loop() {
  // Ambil frame dan kirim ke AI jika dipicu
  // Kirim data telemetri kapasitas setiap 30 detik
  if (millis() - lastTelemetryTime > 30000) {
    kirimTelemetriKapasitas();
    lastTelemetryTime = millis();
  }
}
