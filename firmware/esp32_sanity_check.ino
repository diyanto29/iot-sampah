/**
 * SANITY CHECK TEST - ESP32-CAM
 * Gunakan sketch ini untuk uji coba awal hardware ESP32-CAM yang baru dibeli:
 * 1. Memastikan sensor kamera OV2640 terdeteksi dan tidak error.
 * 2. Memastikan radio WiFi bisa connect ke hotspot.
 * 3. Memastikan fungsi capture frame memori RAM berjalan normal.
 */

#include "esp_camera.h"
#include <WiFi.h>

const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI";

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

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n--- Memulai Tes Mandiri ESP32-CAM ---");

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
    Serial.printf("❌ Gagal Inisialisasi Kamera! Kode Error: 0x%x\n", err);
    Serial.println("Periksa apakah pita kabel kamera fleksibel sudah terpasang rapat.");
    return;
  }
  Serial.println("✅ Sensor Kamera OV2640 Terdeteksi Normal!");

  WiFi.begin(ssid, password);
  Serial.print("[WiFi] Menghubungkan ke ");
  Serial.println(ssid);
  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 20) {
    delay(500);
    Serial.print(".");
    retry++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Berhasil Terhubung!");
    Serial.print("Alamat IP Lokal: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n⚠️ WiFi belum terhubung, tapi hardware ESP32 berfungsi.");
  }

  // Uji coba pengambilan foto pertama
  Serial.println("\n[Uji Foto] Mengambil 1 frame citra...");
  camera_fb_t* fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("❌ Gagal mengambil buffer citra!");
  } else {
    Serial.printf("✅ Berhasil! Ukuran Citra: %u bytes (%dx%d)\n", fb->len, fb->width, fb->height);
    esp_camera_fb_return(fb);
  }
  Serial.println("\n--- Sanity Check Selesai: ESP32-CAM Siap Digunakan! ---");
}

void loop() {
  delay(10000);
}

