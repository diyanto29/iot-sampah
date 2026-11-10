#!/usr/bin/env python3
"""
SIMULATOR TEMPAT SAMPAH CERDAS (3 KOMPARTEMEN)
Skrip ini mensimulasikan perilaku ESP32-CAM mengirim frame foto ke Laravel API
dan telemetri ultrasonik 3 kompartemen (Organik, Daur Ulang, Residu/B3).
"""

import sys
import time
import requests
import random
import io
from PIL import Image, ImageDraw

LARAVEL_API_URL = "http://localhost:8000/api/predict"
TELEMETRY_API_URL = "http://localhost:8000/api/telemetry/capacity"

def test_telemetry(org_pct=42.0, anorg_pct=55.0, res_pct=91.5):
    print(f"\n[1] Mengirim telemetri 3 kompartemen: Organik={org_pct}%, Daur Ulang={anorg_pct}%, Residu/B3={res_pct}%...")
    try:
        res = requests.post(TELEMETRY_API_URL, json={
            "device_id": "BIN-01",
            "organik_percent": org_pct,
            "anorganik_percent": anorg_pct,
            "residu_percent": res_pct
        }, timeout=5)
        print("-> Respon Telemetri:", res.status_code, res.json())
    except Exception as e:
        print("-> Gagal mengirim telemetri:", e)

def test_prediction(category_type="organik"):
    colors = {
        "organik": ((34, 139, 34), "DUMMY ORGANIC (LEAF/FOOD)"),
        "anorganik": ((2, 132, 199), "DUMMY RECYCLABLE (PLASTIC BOTTLE)"),
        "residu": ((220, 38, 38), "DUMMY RESIDUAL / HAZARDOUS (BATTERY)")
    }

    color, label = colors.get(category_type, colors["anorganik"])
    print(f"\n[2] Membuat gambar tiruan ({label})...")
    img = Image.new("RGB", (320, 240), color=color)
    d = ImageDraw.Draw(img)
    d.text((30, 100), label, fill=(255, 255, 255))

    buf = io.BytesIO()
    img.save(buf, format="JPEG")
    buf.seek(0)

    print(f"[3] Mengirim ke Laravel API /api/predict (Target: {category_type})...")
    try:
        files = {"file": (f"sample_{category_type}.jpg", buf.getvalue(), "image/jpeg")}
        data = {"device_id": "BIN-01"}
        res = requests.post(LARAVEL_API_URL, files=files, data=data, timeout=5)
        print("-> Respon Prediksi:", res.status_code, res.json())
    except Exception as e:
        print("-> Gagal mengirim prediksi:", e)

def run_webcam():
    try:
        import cv2
    except ImportError:
        print("\nOpenCV belum terinstall. Install dengan: pip install opencv-python")
        print("Menjalankan uji 3 jenis sampah secara berurutan...")
        for cat in ["organik", "anorganik", "residu"]:
            test_prediction(cat)
            time.sleep(1)
        return

    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("Webcam tidak dapat dibuka. Menjalankan mode dummy 3 jenis sampah...")
        for cat in ["organik", "anorganik", "residu"]:
            test_prediction(cat)
            time.sleep(1)
        return

    print("\n=== SIMULATOR WEBCAM 3 KOMPARTEMEN AKTIF ===")
    print("Tekan 'SPASI' untuk ambil foto & kirim ke Laravel.")
    print("Tekan 'T' untuk kirim telemetri 3 kompartemen acak.")
    print("Tekan 'Q' untuk keluar.")

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        cv2.putText(frame, "SPASI: FOTO | T: TELEMETRI 3 KOMPARTEMEN | Q: KELUAR", (20, 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.55, (0, 255, 0), 2)
        cv2.imshow("Simulator Kamera Tempat Sampah Cerdas", frame)

        key = cv2.waitKey(1) & 0xFF
        if key == ord(' '):
            _, encoded = cv2.imencode('.jpg', frame)
            files = {"file": ("webcam_capture.jpg", encoded.tobytes(), "image/jpeg")}
            data = {"device_id": "BIN-01"}
            print("\n[Mengirim Foto Webcam ke Laravel...]")
            try:
                res = requests.post(LARAVEL_API_URL, files=files, data=data, timeout=5)
                print("Hasil:", res.json())
            except Exception as e:
                print("Error:", e)
        elif key == ord('t') or key == ord('T'):
            test_telemetry(random.randint(10, 95), random.randint(10, 95), random.randint(10, 95))
        elif key == ord('q') or key == ord('Q'):
            break

    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "--webcam":
        run_webcam()
    else:
        print("Menjalankan Sanity Test Sistem 3 Kompartemen (Organik, Daur Ulang, Residu/B3)...")
        test_telemetry(38.0, 45.0, 92.5)
        test_prediction("organik")
        test_prediction("anorganik")
        test_prediction("residu")
        print("\nUntuk menjalankan simulator interaktif dengan webcam:")
        print("ai-worker/venv/bin/python3 simulator/simulate_client.py --webcam")
