#!/bin/bash

# ===============================================================
# SMART WASTE SORTING IOT SYSTEM
# Runner script untuk Laravel 11 (PHP 8.5) & FastAPI Python AI Worker
# ===============================================================

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "=================================================================="
echo "🚀 MEMULAI SISTEM IOT TEMPAT SAMPAH PINTAR (LARAVEL + FASTAPI AI)"
echo "=================================================================="

# Pastikan binary python dan php ada
PYTHON_BIN="$ROOT_DIR/ai-worker/venv/bin/python3"
if [ ! -f "$PYTHON_BIN" ]; then
    echo "❌ Error: Virtual environment Python belum dibuat!"
    echo "   Jalankan: python3 -m venv ai-worker/venv && source ai-worker/venv/bin/activate && pip install -r ai-worker/requirements.txt"
    exit 1
fi

PHP_BIN="$(which php)"
if [ -z "$PHP_BIN" ]; then
    echo "❌ Error: PHP belum terinstall di sistem!"
    exit 1
fi

# Auto-download model YOLO sampah jika belum ada
if [ ! -f "$ROOT_DIR/ai-worker/waste_yolov8.pt" ]; then
    echo "⬇️  Mengunduh model YOLOv8 Khusus Sampah (waste_yolov8.pt) dari Hugging Face..."
    curl -L "https://huggingface.co/HrutikAdsare/waste-detection-yolov8/resolve/main/best.pt" -o "$ROOT_DIR/ai-worker/waste_yolov8.pt"
fi

# 1. Jalankan Python AI Worker di Background (Port 8001 agar tidak bentrok dengan AirPlay macOS)
echo "[1/2] Menyalakan FastAPI AI Microservice di port 8001 (Deep Vision + YOLOv8)..."

# Load GEMINI_API_KEY dari ai-worker/.env jika ada
if [ -f "$ROOT_DIR/ai-worker/.env" ]; then
    export $(grep -v '^#' "$ROOT_DIR/ai-worker/.env" | grep -v '^\s*$' | xargs) 2>/dev/null
fi

cd "$ROOT_DIR/ai-worker"
"$PYTHON_BIN" "$ROOT_DIR/ai-worker/main.py" &
PID_AI=$!
cd "$ROOT_DIR"

# 2. Jalankan Laravel di Background (Port 8000)
echo "[2/2] Menyalakan Laravel Web Application di port 8000..."
cd "$ROOT_DIR/backend"
"$PHP_BIN" artisan serve --port=8000 &
PID_LARAVEL=$!

# Tunggu 2 detik untuk memastikan server siap
sleep 2

echo ""
echo "=================================================================="
echo "✅ SEMUA SERVICE BERHASIL DIJALANKAN:"
echo "   - Web Dashboard Monitoring : http://localhost:8000"
echo "   - Export Laporan CSV       : http://localhost:8000/reports/waste/export"
echo "   - Laravel API Predict      : http://localhost:8000/api/predict"
echo "   - FastAPI AI Worker        : http://localhost:8001"
echo "=================================================================="
echo "💡 CARA UJI COBA:"
echo "   1. Buka browser: http://localhost:8000"
echo "   2. Klik tombol '⚡ Simulasi Cepat' di pojok kanan atas dashboard"
echo "   3. Atau jalankan simulator webcam di terminal lain:"
echo "      ai-worker/venv/bin/python3 simulator/simulate_client.py"
echo "=================================================================="
echo "Tekan [CTRL + C] untuk mematikan semua service sekaligus."

# Tangkap signal CTRL+C untuk mematikan kedua proses background
trap "echo ''; echo 'Mematikan service...'; kill $PID_AI $PID_LARAVEL 2>/dev/null; exit 0" INT TERM EXIT

# Tahan script agar tetap berjalan
wait
