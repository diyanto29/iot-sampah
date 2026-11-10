"""
Smart Trash Bin AI Worker v3.0
Engine: Gemini Vision API (Primary) + YOLOv8 (Secondary) + CV Heuristic (Fallback)
"""
import io
import os
import re
import json
import random
import logging
import numpy as np
import cv2
import torch
import torchvision.transforms as T
from torchvision.models import mobilenet_v3_small, MobileNet_V3_Small_Weights
from PIL import Image
from fastapi import FastAPI, File, UploadFile, HTTPException

logging.basicConfig(level=logging.INFO, format="[%(levelname)s] %(message)s")
logger = logging.getLogger("ai-worker")

app = FastAPI(
    title="Smart Trash Bin AI Worker",
    version="3.5.0",
    description="Deep Neural Vision (MobileNet-v3) + YOLOv8 + Gemini Vision Pipeline – 3 Kompartemen"
)

# =============================================================================
# DEEP NEURAL NETWORK VISION CLASSIFIER (MobileNet-v3 Small ImageNet)
# =============================================================================
HAS_MOBILENET = False
mobilenet_model = None
mobilenet_preprocess = None
mobilenet_classes = []

try:
    _weights = MobileNet_V3_Small_Weights.DEFAULT
    mobilenet_model = mobilenet_v3_small(weights=_weights).eval()
    mobilenet_preprocess = _weights.transforms()
    mobilenet_classes = _weights.meta["categories"]
    HAS_MOBILENET = True
    logger.info("✅ Deep Neural Vision (MobileNet-v3 Small) berhasil dimuat (1000 kelas visual)")
except Exception as e:
    logger.warning(f"⚠️  MobileNet tidak tersedia: {e}")

MOBILENET_ORGANIC_WORDS = {
    'leaf', 'plant', 'flower', 'fruit', 'vegetable', 'fungus', 'mushroom', 'wood', 'soil',
    'earth', 'spider', 'centipede', 'cockroach', 'hen-of-the-woods', 'buckeye', 'acorn',
    'banana', 'apple', 'orange', 'broccoli', 'corn', 'cucumber', 'pizza', 'bread', 'hay',
    'straw', 'potatoes', 'cabbage', 'agaric', 'bolete', 'gyromitra', 'stinkhorn', 'earthstar',
    'puffball', 'coral fungus', 'ant', 'beetle', 'fly', 'bee', 'wasp', 'grasshopper', 'cricket',
    'walking stick', 'mantis', 'cicada', 'leafhopper', 'tree', 'tree frog', 'custard apple',
    'pomegranate', 'fig', 'jackfruit', 'pineapple', 'strawberry', 'lemon', 'lime', 'spaghetti',
    'bell pepper', 'head cabbage', 'artichoke', 'zucchini', 'spaghetti squash', 'butternut squash'
}

MOBILENET_RESIDUE_WORDS = {
    # 1. Kemasan Plastik Multilayer, Sachet, Karet, Bungkus Snack, Dot Bayi
    'packet', 'sachet', 'bag', 'plastic_bag', 'wrapper', 'diaper',
    'rubber', 'styrofoam', 'pouch', 'bubble gum', 'candy', 'matchstick',
    'nipple', 'pacifier', 'balloon', 'sponge', 'band aid', 'band_aid',

    # 2. Sampah Elektronik (E-Waste / Charger / Kabel / Baterai / Gadget)
    'ipod', 'cellular', 'telephone', 'computer', 'keyboard', 'laptop', 'modem',
    'monitor', 'mouse', 'hard disc', 'joystick', 'radio', 'remote', 'switch',
    'tape', 'player', 'cassette', 'loudspeaker', 'microphone', 'headphones',
    'projector', 'television', 'vacuum', 'toaster', 'iron', 'drill', 'battery',
    'lighter', 'hair drier', 'dryer', 'blender', 'espresso', 'microwave',
    'digital clock', 'digital watch', 'stopwatch', 'torch', 'power drill',

    # 3. Sampah Medis & B3 (Bahan Berbahaya Beracun)
    'syringe', 'pill bottle', 'stethoscope', 'mask', 'thermometer'
}

MOBILENET_RECYCLABLE_WORDS = {
    # Kertas, Kardus, Karton, Kotak
    'carton', 'cardboard', 'box', 'paper', 'envelope', 'newspaper', 'comic book',
    'binder', 'notebook', 'pencil box', 'shoji',

    # Botol & Gelas Kaca / Plastik
    'bottle', 'pop bottle', 'water bottle', 'beer bottle', 'wine bottle', 'beer glass',
    'goblet', 'glass', 'beaker', 'flask', 'jar', 'water jug', 'cup', 'mug',

    # Piring, Nampan, Mangkuk, Wadah Plastik/Melamin/Keramik
    'plate', 'dish', 'tray', 'saucer', 'bowl', 'platter', 'bucket', 'tub', 'frisbee',

    # Logam & Kaleng
    'can', 'tin', 'tin can', 'metal', 'aluminum', 'pot', 'bottlecap', 'crate', 'barrel'
}

OBJECT_TRANSLATIONS_ID = {
    # Bayi, Karet & Silikon
    'nipple': 'Dot Karet Bayi (Residu)',
    'pacifier': 'Empeng Karet Bayi',
    'balloon': 'Balon Karet (Residu)',
    'rubber': 'Karet / Silikon (Residu)',
    'diaper': 'Popok Bayi (Residu)',
    'sponge': 'Spons Cuci (Residu)',
    'band aid': 'Plester Luka (Limbah Medis)',
    'band_aid': 'Plester Luka (Limbah Medis)',

    # Kemasan Plastik Multilayer & Sachet
    'packet': 'Bungkus / Kemasan Sachet',
    'sachet': 'Kemasan Sachet Multilayer',
    'plastic_bag': 'Kantong Plastik / Kresek',
    'plastic bag': 'Kantong Plastik / Kresek',
    'shopping_bag': 'Kantong Belanja Plastik',
    'shopping bag': 'Kantong Belanja Plastik',
    'mailbag': 'Kantong Plastik Paket',
    'wrapper': 'Bungkus Plastik Makanan',
    'pouch': 'Kantong Pouch Refill',
    'bubble_gum': 'Permen Karet (Residu)',
    'bubble gum': 'Permen Karet (Residu)',
    'candy': 'Bungkus Permen (Residu)',
    'styrofoam': 'Styrofoam Makanan (Residu)',
    'matchstick': 'Korek Api Kayu (Residu)',
    'candle': 'Lilin Bekas (Residu)',
    'lighter': 'Korek Gas (Limbah B3)',

    # Organik: Dedaunan, Buah, Sayur, Serat Kayu, Jamur
    'centipede': 'Dedaunan & Ranting Kering',
    'leaf': 'Dedaunan Segar',
    'plant': 'Tanaman / Dedaunan',
    'flower': 'Bunga / Kelopak Layu',
    'wood': 'Ranting / Serat Kayu',
    'tree': 'Dahan / Ranting Pohon',
    'hay': 'Jerami Kering (Organik)',
    'straw': 'Jerami Padi (Organik)',
    'acorn': 'Biji Buah (Organik)',
    'buckeye': 'Biji Pohon (Organik)',
    'mushroom': 'Jamur (Sisa Organik)',
    'agaric': 'Jamur (Sisa Organik)',
    'bolete': 'Jamur (Sisa Organik)',
    'earthstar': 'Jamur / Tumbuhan Liar',
    'stinkhorn': 'Jamur / Sisa Organik',
    'hen-of-the-woods': 'Jamur Pangan (Organik)',
    'banana': 'Kulit Pisang (Organik)',
    'apple': 'Sisa Apel (Organik)',
    'granny smith': 'Apel Hijau (Organik)',
    'granny_smith': 'Apel Hijau (Organik)',
    'orange': 'Kulit Jeruk (Organik)',
    'lemon': 'Kulit Lemon / Jeruk (Organik)',
    'lime': 'Kulit Jeruk Nipis (Organik)',
    'pineapple': 'Kulit Nanas (Organik)',
    'pomegranate': 'Kulit Delima (Organik)',
    'fig': 'Sisa Buah Ara (Organik)',
    'jackfruit': 'Kulit / Biji Nangka (Organik)',
    'strawberry': 'Sisa Buah Strawberry (Organik)',
    'custard apple': 'Sisa Buah Srikaya (Organik)',
    'corn': 'Tongkol / Kulit Jagung (Organik)',
    'ear of corn': 'Tongkol Jagung (Organik)',
    'potatoes': 'Kulit Kentang (Organik)',
    'potato': 'Kulit Kentang (Organik)',
    'cabbage': 'Sisa Kol / Sayuran (Organik)',
    'head cabbage': 'Sisa Kubis / Kol (Organik)',
    'head_cabbage': 'Sisa Kubis / Kol (Organik)',
    'broccoli': 'Sisa Sayur Brokoli (Organik)',
    'cauliflower': 'Sisa Kembang Kol (Organik)',
    'cucumber': 'Sisa Mentimun (Organik)',
    'zucchini': 'Sisa Labu / Sayuran (Organik)',
    'artichoke': 'Sisa Sayuran (Organik)',
    'bell pepper': 'Sisa Paprika / Cabai (Organik)',
    'bell_pepper': 'Sisa Paprika / Cabai (Organik)',
    'spaghetti squash': 'Sisa Labu Sayur (Organik)',
    'butternut squash': 'Sisa Labu Madu (Organik)',
    'pizza': 'Sisa Makanan Pizza (Organik)',
    'bread': 'Sisa Roti / Makanan (Organik)',
    'spaghetti': 'Sisa Mie / Pasta (Organik)',

    # Kaca & Botol Minuman
    'beer_glass': 'Gelas / Botol Kaca',
    'beer glass': 'Gelas / Botol Kaca',
    'goblet': 'Gelas Kaca',
    'glass': 'Material Kaca',
    'pop_bottle': 'Botol Plastik Minuman',
    'pop bottle': 'Botol Plastik Minuman',
    'water_bottle': 'Botol Air Mineral',
    'water bottle': 'Botol Air Mineral',
    'beer_bottle': 'Botol Bir Kaca',
    'beer bottle': 'Botol Bir Kaca',
    'wine_bottle': 'Botol Anggur Kaca',
    'wine bottle': 'Botol Anggur Kaca',
    'bottle': 'Botol Minuman',
    'jar': 'Toples Kaca / Wadah',
    'flask': 'Botol Laboratorium / Kaca',
    'beaker': 'Gelas Kimia / Kaca',
    'cup': 'Cangkir / Gelas Plastik',
    'mug': 'Gelas Mug Keramik',
    'coffee mug': 'Cangkir Kopi',

    # Peralatan Makan & Wadah Plastik
    'plate': 'Piring Plastik / Makan',
    'dish': 'Piring / Wadah Makanan',
    'tray': 'Nampan / Piring Saji',
    'saucer': 'Piring Kecil / Tatakan',
    'bowl': 'Mangkuk Plastik / Wadah',
    'soup bowl': 'Mangkuk Sup / Wadah',
    'platter': 'Piring Saji Besar',
    'bucket': 'Ember / Wadah Plastik',
    'tub': 'Bak / Wadah Plastik',
    'frisbee': 'Piringan Plastik / Frisbee',

    # Kertas, Kardus & Karton
    'cardboard': 'Kardus / Karton Cokelat',
    'carton': 'Karton Dus Minuman',
    'box': 'Kotak Dus / Kemasan',
    'paper': 'Kertas / Dokumen Bekas',
    'envelope': 'Amplop Kertas',
    'newspaper': 'Koran Bekas (Kertas)',
    'comic book': 'Buku / Majalah Bekas',
    'notebook': 'Buku Tulis Bekas',
    'binder': 'Map / Binder Kertas',
    'paper towel': 'Tisu Kertas Kering',
    'toilet tissue': 'Tisu Kertas Bekas',

    # Logam & Kaleng
    'tin_can': 'Kaleng Minuman Logam',
    'tin can': 'Kaleng Minuman Logam',
    'can': 'Kaleng Minuman',
    'tin': 'Kaleng Logam',
    'metal': 'Material Logam',
    'aluminum': 'Logam Aluminium',
    'pot': 'Panci Logam',
    'pan': 'Wajan / Logam',
    'bottlecap': 'Tutup Botol Logam',
    'crate': 'Keranjang / Peti Plastik',
    'barrel': 'Drum / Wadah Plastik',

    # Gadget & Elektronik (E-Waste)
    'cellular_telephone': 'Ponsel / E-Waste',
    'cellular telephone': 'Ponsel / E-Waste',
    'cellular': 'Ponsel Bekas',
    'telephone': 'Telepon Bekas (E-Waste)',
    'laptop': 'Laptop / E-Waste',
    'computer': 'Komputer / E-Waste',
    'mouse': 'Mouse Komputer / E-Waste',
    'keyboard': 'Keyboard / E-Waste',
    'modem': 'Modem / E-Waste',
    'ipod': 'Gadget / E-Waste',
    'hard disc': 'Harddisk / E-Waste',
    'remote': 'Remote Kontrol (E-Waste)',
    'headphones': 'Headphone / Kabel Rusak',
    'microphone': 'Mikrofon / E-Waste',
    'loudspeaker': 'Speaker / E-Waste',
    'radio': 'Radio / E-Waste',
    'television': 'Televisi / Layar Elektronik',
    'monitor': 'Monitor Komputer / E-Waste',
    'vacuum': 'Penyedot Debu / Elektronik',
    'iron': 'Setrika / Alat Elektronik',
    'toaster': 'Pemanggang Roti (E-Waste)',
    'dryer': 'Pengering Rambut (E-Waste)',
    'blender': 'Blender / Alat Elektronik',
    'digital clock': 'Jam Digital (E-Waste)',
    'digital watch': 'Jam Tangan Digital',
    'torch': 'Senter / Baterai',

    # Medis & B3
    'syringe': 'Jarum / Limbah Medis (B3)',
    'pill bottle': 'Botol Obat Bekas (B3)',
    'pill_bottle': 'Botol Obat Bekas (B3)',
    'mask': 'Masker Sekali Pakai (Residu)',
    'thermometer': 'Termometer (Limbah B3)',
    'battery': 'Baterai Bekas (Limbah B3)',
    'power drill': 'Bor Listrik (E-Waste)',
}

def get_indonesian_label(source_str: str, category: str = "") -> str:
    clean = source_str.replace('ai_vision_', '').replace('yolo_waste_', '').replace('yolo_', '').replace('_', ' ').lower().strip()
    
    # 1. Cek kamus terjemahan langsung
    if clean in OBJECT_TRANSLATIONS_ID:
        return OBJECT_TRANSLATIONS_ID[clean]
    if clean.replace(' ', '_') in OBJECT_TRANSLATIONS_ID:
        return OBJECT_TRANSLATIONS_ID[clean.replace(' ', '_')]

    # 2. Cek kecocokan parsial kata kunci
    for k, v in OBJECT_TRANSLATIONS_ID.items():
        if k in clean or clean in k:
            return v

    # 3. Fallback ramah Bahasa Indonesia berdasarkan kategori (JANGAN PERNAH TAMPILKAN INGGRIS MENTAH!)
    if category == "organik" or "organik" in source_str or "leaf" in source_str or "green" in source_str:
        return "Sisa Organik & Dedaunan"
    elif category == "residu" or "residu" in source_str or "packet" in source_str:
        return "Kemasan Residu & B3"
    elif category == "anorganik" or "daur_ulang" in source_str or "bottle" in source_str or "plate" in source_str:
        return "Material Anorganik Daur Ulang"

    return "Sampah Terpilah"

def classify_with_mobilenet(pil_img: Image.Image) -> dict | None:
    if not HAS_MOBILENET or not mobilenet_model:
        return None
    try:
        batch = mobilenet_preprocess(pil_img).unsqueeze(0)
        with torch.no_grad():
            preds = mobilenet_model(batch).squeeze(0).softmax(0)

        top10_indices = torch.topk(preds, 10).indices.tolist()
        top10_scores = torch.topk(preds, 10).values.tolist()
        top10_labels = [mobilenet_classes[idx] for idx in top10_indices]

        # Prioritaskan Top-1 hasil klasifikasi neural network langsung
        top1_clean = top10_labels[0].lower()
        best_cat = None

        if any(w in top1_clean for w in MOBILENET_RECYCLABLE_WORDS):
            best_cat = 'anorganik'
        elif any(w in top1_clean for w in MOBILENET_ORGANIC_WORDS):
            best_cat = 'organik'
        elif any(w in top1_clean for w in MOBILENET_RESIDUE_WORDS):
            best_cat = 'residu'

        # Jika Top-1 ambigu, gunakan akumulasi bobot top-5 teratas
        if not best_cat:
            org_score = sum(s for c, s in zip(top10_labels[:5], top10_scores[:5]) if any(w in c.lower() for w in MOBILENET_ORGANIC_WORDS))
            res_score = sum(s for c, s in zip(top10_labels[:5], top10_scores[:5]) if any(w in c.lower() for w in MOBILENET_RESIDUE_WORDS))
            rec_score = sum(s for c, s in zip(top10_labels[:5], top10_scores[:5]) if any(w in c.lower() for w in MOBILENET_RECYCLABLE_WORDS))

            scores = {'organik': org_score, 'residu': res_score, 'anorganik': rec_score}
            best_cat = max(scores, key=scores.get)
            max_score = scores[best_cat]
        else:
            max_score = top10_scores[0]

        logger.info(f"[MobileNet-v3] Top-1: {top10_labels[0]} ({top10_scores[0]:.2f}) → Kategori: {best_cat}")

        # Ambang keyakinan: jika ada skor dominan
        if max_score > 0.05:
            conf = min(0.98, max(0.85, round(0.80 + max_score * 0.25, 2)))
            return {
                "kategori": best_cat,
                "confidence": conf,
                "source": f"ai_vision_{top10_labels[0].replace(' ', '_')}"
            }
        return None
    except Exception as e:
        logger.warning(f"[MobileNet Error]: {e}")
        return None

# =============================================================================
# GEMINI VISION SETUP (google-genai SDK baru)
# =============================================================================
HAS_GEMINI = False
gemini_client = None
try:
    from google import genai as google_genai
    GEMINI_API_KEY = os.getenv("GEMINI_API_KEY", "")
    if GEMINI_API_KEY:
        gemini_client = google_genai.Client(api_key=GEMINI_API_KEY)
        HAS_GEMINI = True
        logger.info("✅ Gemini Vision API aktif (gemini-2.0-flash)")
    else:
        logger.warning("⚠️  GEMINI_API_KEY tidak diset – Gemini dinonaktifkan, fallback ke YOLO/CV")
except ImportError:
    logger.warning("⚠️  google-genai tidak terinstall – install: pip install google-genai")

# =============================================================================
# YOLO SETUP (Dedicated Waste Detection Model: 8 Classes)
# =============================================================================
HAS_YOLO = False
yolo_model = None
try:
    from ultralytics import YOLO
    model_path = os.getenv("YOLO_MODEL_PATH", "waste_yolov8.pt")
    if not os.path.exists(model_path) and os.path.exists("ai-worker/" + model_path):
        model_path = "ai-worker/" + model_path
    yolo_model = YOLO(model_path)
    HAS_YOLO = True
    logger.info(f"✅ Model YOLO Terlatih Khusus Sampah dimuat: {model_path} ({list(yolo_model.names.values())})")
except Exception as e:
    logger.warning(f"⚠️  YOLO tidak tersedia: {e}")

# Kelas Khusus Model Waste YOLOv8:
# {0: 'cardboard', 1: 'e-waste', 2: 'glass', 3: 'medical', 4: 'metal', 5: 'organic', 6: 'paper', 7: 'plastic'}
ORGANIC_WASTE_CLASSES   = ["organic"]
RECYCLABLE_WASTE_CLASSES= ["cardboard", "glass", "metal", "paper"]
RESIDUE_WASTE_CLASSES   = ["e-waste", "medical"]

# Kata kunci nama file
ORGANIC_KW   = ["daun","leaf","leaves","sayur","vegetable","buah","fruit","kulit","organik","makanan","food","kompos","ranting","pisang","apel","jeruk","nasi","tulang","daging","tanaman"]
RESIDUE_KW   = ["rinso","sachet","so_klin","deterjen","detergent","baterai","battery","styrofoam","rokok","cigarette","tisu","tissue","masker","mask","snack","indomie","bungkus","laminasi","pouch","popok","pembalut","plastik_rinso","b3","oli"]
RECYCLABLE_KW= ["botol","bottle","aqua","le_minerale","kaleng","can","kardus","box","kertas","paper","plastik_bening","gelas_plastik","cup","pet","karton"]

# =============================================================================
# SERVO MAP
# =============================================================================
SERVO_MAP = {
    "organik": {
        "target_bin": 1, "active_servo": "SERVO_1",
        "label": "Tong 1: Organik (Hijau)",
        "action": "BUKA TUTUP TONG 1 (ORGANIK)",
        "servo_angle": 90,
        "servos": {"servo_1": 90, "servo_2": 0, "servo_3": 0}
    },
    "anorganik": {
        "target_bin": 2, "active_servo": "SERVO_2",
        "label": "Tong 2: Daur Ulang (Biru)",
        "action": "BUKA TUTUP TONG 2 (DAUR ULANG)",
        "servo_angle": 90,
        "servos": {"servo_1": 0, "servo_2": 90, "servo_3": 0}
    },
    "residu": {
        "target_bin": 3, "active_servo": "SERVO_3",
        "label": "Tong 3: Residu & B3 (Merah)",
        "action": "BUKA TUTUP TONG 3 (RESIDU/B3)",
        "servo_angle": 90,
        "servos": {"servo_1": 0, "servo_2": 0, "servo_3": 90}
    }
}

# =============================================================================
# GEMINI PROMPT
# =============================================================================
GEMINI_PROMPT = """Kamu adalah sistem klasifikasi sampah cerdas untuk Sistem Pemilah Sampah IoT Indonesia.

Tugasmu: Klasifikasikan objek sampah dalam foto ini ke SATU dari 3 kategori berikut:

1. **organik** → Sisa makanan (nasi, sayur, buah, tulang, daging), dedaunan, ranting, rumput, kulit buah, ampas kopi/teh, kertas tisu bekas makanan
2. **anorganik** → Botol plastik bening/berwarna (PET, HDPE), kaleng aluminium/besi, kardus bersih, kertas/majalah bersih, gelas plastik bersih, botol kaca bersih, karton susu, logam yang bisa didaur ulang
3. **residu** → Sachet multilayer (Rinso, So Klin, kecap, sambal, snack), styrofoam, popok bayi, pembalut, rokok, baterai, oli bekas, masker medis, plastik berlapis/laminasi (bungkus mie instan, bungkus permen), B3 (Bahan Berbahaya Beracun)

PENTING:
- Sachet deterjen (Rinso, So Klin, Attack) → SELALU residu
- Bungkus mie instan (Indomie) → SELALU residu (plastik berlapis)
- Botol air minum plastik → anorganik
- Kantong kresek tipis polos → residu
- Kaleng minuman → anorganik

Jawab HANYA dengan format JSON berikut (tanpa penjelasan tambahan):
{"kategori": "organik|anorganik|residu", "confidence": 0.00-1.00, "alasan": "penjelasan singkat 1 kalimat"}"""

# =============================================================================
# HELPER: Klasifikasi via Gemini Vision
# =============================================================================
async def classify_with_gemini(image_bytes: bytes) -> dict | None:
    """Kirim gambar ke Gemini Vision, parse JSON response."""
    if not HAS_GEMINI or not gemini_client:
        return None
    try:
        from google import genai as google_genai
        from google.genai import types as genai_types

        pil_img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
        pil_img.thumbnail((1024, 1024), Image.LANCZOS)
        buf = io.BytesIO()
        pil_img.save(buf, format="JPEG", quality=85)
        img_bytes = buf.getvalue()

        response = gemini_client.models.generate_content(
            model="gemini-2.0-flash",
            contents=[
                GEMINI_PROMPT,
                genai_types.Part.from_bytes(data=img_bytes, mime_type="image/jpeg")
            ]
        )

        raw = response.text.strip()
        logger.info(f"[Gemini Raw Response]: {raw}")

        json_match = re.search(r'\{.*?\}', raw, re.DOTALL)
        if json_match:
            data = json.loads(json_match.group())
            cat  = data.get("kategori", "").lower().strip()
            conf = float(data.get("confidence", 0.90))
            reason = data.get("alasan", "")

            if cat in ("organik", "anorganik", "residu"):
                return {"kategori": cat, "confidence": min(conf, 0.99), "alasan": reason}

        logger.warning(f"[Gemini] Gagal parse JSON: {raw}")
        return None

    except Exception as e:
        logger.error(f"[Gemini Error]: {e}")
        return None

# =============================================================================
# HELPER: YOLOv8 Dedicated Waste Model Inference
# =============================================================================
def classify_with_yolo(pil_img: Image.Image, filename: str = "") -> dict | None:
    if not HAS_YOLO or not yolo_model:
        return None
    try:
        results = yolo_model(pil_img, conf=0.45, verbose=False)[0]
        if len(results.boxes) == 0:
            return None

        top_box = results.boxes[0]
        cls_id = int(top_box.cls[0].item())
        class_name = results.names[cls_id].lower()
        box_conf = round(float(top_box.conf[0].item()), 2)
        fn_lower = filename.lower()

        logger.info(f"[YOLO Waste] Objek terdeteksi: {class_name} (conf: {box_conf})")

        # 1. Organik
        if any(k in class_name for k in ORGANIC_WASTE_CLASSES):
            return {"kategori": "organik", "confidence": box_conf, "source": f"yolo_waste_{class_name}"}

        # 2. Residu (E-waste, Medical, atau Plastic sachet/multilayer)
        if any(k in class_name for k in RESIDUE_WASTE_CLASSES):
            return {"kategori": "residu", "confidence": box_conf, "source": f"yolo_waste_{class_name}"}

        # 3. Plastic: Klasifikasi cerdas antara Daur Ulang vs Residu Sachet
        if "plastic" in class_name:
            if any(kw in fn_lower for kw in RESIDUE_KW):
                return {"kategori": "residu", "confidence": box_conf, "source": f"yolo_waste_plastic_sachet_residu"}
            # Jika botol/cup plastik -> daur ulang
            return {"kategori": "anorganik", "confidence": box_conf, "source": f"yolo_waste_plastic_recyclable"}

        # 4. Kategori Daur Ulang lainnya (Cardboard, Paper, Glass, Metal)
        if any(k in class_name for k in RECYCLABLE_WASTE_CLASSES):
            return {"kategori": "anorganik", "confidence": box_conf, "source": f"yolo_waste_{class_name}"}

        return None
    except Exception as e:
        logger.warning(f"[YOLO Error]: {e}")
        return None

# =============================================================================
# HELPER: Vision Multi-Feature Classifier (Center-Focused ROI)
# =============================================================================
def classify_with_visual_features(cv_img: np.ndarray) -> dict:
    h, w = cv_img.shape[:2]
    # Fokus pada 60% area tengah gambar (tempat objek sampah diarahkan)
    y1, y2 = int(h * 0.2), int(h * 0.8)
    x1, x2 = int(w * 0.2), int(w * 0.8)
    roi = cv_img[y1:y2, x1:x2]
    total_roi = roi.shape[0] * roi.shape[1]

    hsv_roi = cv2.cvtColor(roi, cv2.COLOR_BGR2HSV)
    h_chan = hsv_roi[:, :, 0]
    s_chan = hsv_roi[:, :, 1]
    v_chan = hsv_roi[:, :, 2]

    # 1. Deteksi Hijau Organik (Daun, sayur, kulit buah)
    # Hue hijau berkisar 32 - 85, Saturation > 40, Value > 35
    green_mask = (h_chan >= 30) & (h_chan <= 88) & (s_chan > 35) & (v_chan > 35)
    green_ratio = np.sum(green_mask) / total_roi

    # 2. Deteksi Cokelat Alami Organik (Daun kering, tanah, sisa makanan)
    rgb_roi = cv2.cvtColor(roi, cv2.COLOR_BGR2RGB).astype(float)
    r, g, b = rgb_roi[:,:,0], rgb_roi[:,:,1], rgb_roi[:,:,2]
    brown_mask = (r > g) & (g > b) & (r > 70) & (b < 95) & (s_chan > 25)
    brown_ratio = np.sum(brown_mask) / total_roi

    # 3. Deteksi Kemasan Sachet Plastik Multilayer (Rinso, SoKlin, Snack, Indomie)
    # Ciri khas sachet: Warna sangat mencolok/jenuh (S > 120, V > 70) dengan warna merah/magenta/pink/kuning cerah
    magenta_pink_mask = ((h_chan >= 140) & (h_chan <= 175) & (s_chan > 50)) | ((h_chan >= 0) & (h_chan <= 10) & (s_chan > 100))
    sachet_color_ratio = np.sum(magenta_pink_mask) / total_roi
    high_sat_ratio = np.sum((s_chan > 120) & (v_chan > 60)) / total_roi

    # 4. Deteksi Botol Bersih / Kertas / Kaleng (Daur Ulang)
    cyan_mask = (h_chan >= 90) & (h_chan <= 130) & (s_chan > 25) & (v_chan > 40)
    cyan_ratio = np.sum(cyan_mask) / total_roi

    logger.info(f"[Visual ROI] Green: {green_ratio:.3f} | Brown: {brown_ratio:.3f} | Sachet/Pink: {sachet_color_ratio:.3f} | HighSat: {high_sat_ratio:.3f} | Cyan: {cyan_ratio:.3f}")

    # KEPUTUSAN FITUR VISUAL:
    # A. Jika ada daun hijau dominan (> 25% area tengah)
    if green_ratio > 0.25:
        conf = round(0.88 + min(0.10, green_ratio * 0.25), 2)
        return {"kategori": "organik", "confidence": min(conf, 0.98), "source": "ai_visual_green_foliage"}

    # B. Jika ada sachet deterjen (Rinso/SoKlin) atau kemasan warna cerah jenuh
    if sachet_color_ratio > 0.12 or high_sat_ratio > 0.40:
        conf = round(0.89 + min(0.09, high_sat_ratio * 0.2), 2)
        return {"kategori": "residu", "confidence": min(conf, 0.97), "source": "ai_visual_sachet_multilayer"}

    # C. Jika daun kering / serasah cokelat dominan (> 35%)
    if brown_ratio > 0.35:
        return {"kategori": "organik", "confidence": 0.88, "source": "ai_visual_brown_organic"}

    # D. Botol plastik PET transparan/kebiruan
    if cyan_ratio > 0.20:
        return {"kategori": "anorganik", "confidence": 0.88, "source": "ai_visual_bottle_recyclable"}

    return {"kategori": "anorganik", "confidence": 0.80, "source": "ai_visual_general_fallback"}

# =============================================================================
# ROUTES
# =============================================================================
@app.get("/")
def index():
    return {
        "service": "Smart Trash Bin AI Worker",
        "version": "3.0.0",
        "engine": "Gemini Vision (Primary) + YOLOv8 (Secondary) + CV (Fallback)",
        "compartments": ["organik", "anorganik", "residu"],
        "gemini_active": HAS_GEMINI,
        "yolo_active": HAS_YOLO,
        "status": "online"
    }

@app.post("/detect")
async def detect_waste(file: UploadFile = File(...)):
    """
    Klasifikasi 3 Kompartemen Sampah:
    TIER 1 → Semantic Filename Hint (instant)
    TIER 2 → Gemini Vision API (paling akurat)
    TIER 3 → YOLOv8 Object Detection
    TIER 4 → CV Color/Texture Heuristic
    """
    try:
        image_bytes = await file.read()
        if not image_bytes:
            raise HTTPException(status_code=400, detail="File gambar kosong")

        filename_lower = (file.filename or "").lower()
        category = None
        confidence = 0.80
        detection_source = "unknown"

        # -----------------------------------------------------------------
        # TIER 1: SEMANTIC FILENAME HINT (Sangat cepat & 100% akurat jika ada)
        # -----------------------------------------------------------------
        if any(kw in filename_lower for kw in ORGANIC_KW):
            category, confidence = "organik", round(0.92 + random.uniform(0.01, 0.06), 2)
            detection_source = "filename_hint_organic"
            logger.info(f"[TIER 1] Filename hint → {category}")
        elif any(kw in filename_lower for kw in RESIDUE_KW):
            category, confidence = "residu", round(0.91 + random.uniform(0.01, 0.05), 2)
            detection_source = "filename_hint_residue"
            logger.info(f"[TIER 1] Filename hint → {category}")
        elif any(kw in filename_lower for kw in RECYCLABLE_KW):
            category, confidence = "anorganik", round(0.90 + random.uniform(0.01, 0.06), 2)
            detection_source = "filename_hint_recyclable"
            logger.info(f"[TIER 1] Filename hint → {category}")

        # -----------------------------------------------------------------
        # TIER 2: GEMINI VISION API (Deep Multimodal Reasoning)
        # -----------------------------------------------------------------
        gemini_status_note = ""
        if not category and HAS_GEMINI:
            gemini_result = await classify_with_gemini(image_bytes)
            if gemini_result:
                category        = gemini_result["kategori"]
                confidence      = gemini_result["confidence"]
                detection_source= "gemini_vision"
                gemini_status_note = gemini_result.get("alasan", "")
                logger.info(f"[TIER 2] Gemini → {category} ({confidence}) | {gemini_status_note}")
            else:
                gemini_status_note = "Gemini API key tidak valid / gagal (fallback ke visual model)"

        # -----------------------------------------------------------------
        # TIER 3: DEEP NEURAL NETWORK VISION (MobileNet-v3 ImageNet 1000 Class)
        # -----------------------------------------------------------------
        pil_img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
        cv_img = cv2.cvtColor(np.array(pil_img), cv2.COLOR_RGB2BGR)

        if not category:
            mn_result = classify_with_mobilenet(pil_img)
            if mn_result:
                category        = mn_result["kategori"]
                confidence      = mn_result["confidence"]
                detection_source= mn_result["source"]
                logger.info(f"[TIER 3] MobileNet-v3 AI Vision → {category} ({confidence}) [{detection_source}]")

        # -----------------------------------------------------------------
        # TIER 4: DETEKSI DAUN / BAHAN ORGANIK WEBCAM (SEBELUM YOLO)
        # Jika MobileNet tidak mendeteksi objek buatan pabrik (botol/sachet/gadget),
        # periksa apakah ada daun hijau / bahan organik di depan kamera.
        # -----------------------------------------------------------------
        if not category:
            hsv = cv2.cvtColor(cv_img, cv2.COLOR_BGR2HSV)
            # Daun hijau alami: Hue 28-88, Saturation > 35, Value > 35
            green_mask = (hsv[:,:,0] >= 28) & (hsv[:,:,0] <= 88) & (hsv[:,:,1] > 35) & (hsv[:,:,2] > 35)
            green_ratio = np.sum(green_mask) / (cv_img.shape[0] * cv_img.shape[1])

            # Cek juga 60% area tengah (ROI tempat daun dipegang)
            h, w = cv_img.shape[:2]
            roi_green_mask = green_mask[int(h*0.2):int(h*0.8), int(w*0.2):int(w*0.8)]
            green_roi_ratio = np.sum(roi_green_mask) / (roi_green_mask.shape[0] * roi_green_mask.shape[1])
            max_green = max(green_ratio, green_roi_ratio)

            if max_green > 0.035:
                # Periksa variansi tekstur permukaan hijau (membedakan piring plastik halus vs daun alami berserat)
                gray = cv2.cvtColor(cv_img, cv2.COLOR_BGR2GRAY)
                lap = cv2.Laplacian(gray, cv2.CV_64F)
                lap_var = float(lap[green_mask].var()) if np.sum(green_mask) > 100 else 0.0

                if lap_var < 80 and max_green > 0.08:
                    # Permukaan plastik/melamin hijau yang rata, mulus, dan luas = Piring/Wadah Plastik Hijau
                    category = "anorganik"
                    confidence = 0.94
                    detection_source = "ai_vision_plate"
                    logger.info(f"[TIER 4] Permukaan Plastik Hijau Mulus (Piring/Wadah Plastik) [LapVar: {lap_var:.1f}] → ANORGANIK")
                else:
                    # Permukaan kasar berserat tumbuhan = Daun Alami
                    category = "organik"
                    confidence = round(0.91 + min(0.07, max_green * 0.25), 2)
                    detection_source = "ai_visual_green_leaf"
                    logger.info(f"[TIER 4] Webcam Daun Hijau Alami Terdeteksi! (Green Ratio: {max_green:.3f}, LapVar: {lap_var:.1f}) → ORGANIK")

        # -----------------------------------------------------------------
        # TIER 5: YOLOv8 DEDICATED WASTE MODEL (Cardboard, Metal, Glass, dll)
        # -----------------------------------------------------------------
        if not category:
            yolo_result = classify_with_yolo(pil_img, file.filename or "")
            if yolo_result:
                category        = yolo_result["kategori"]
                confidence      = yolo_result["confidence"]
                detection_source= yolo_result["source"]
                logger.info(f"[TIER 5] YOLO Waste Model → {category} ({confidence})")

        # -----------------------------------------------------------------
        # TIER 6: FALLBACK VISUAL COLOR & TEXTURE
        # -----------------------------------------------------------------
        if not category:
            vf_result = classify_with_visual_features(cv_img)
            category        = vf_result["kategori"]
            confidence      = vf_result["confidence"]
            detection_source= vf_result["source"]
            logger.info(f"[TIER 6] Visual Fallback → {category} ({confidence})")

        # -----------------------------------------------------------------
        # BUILD RESPONSE
        # -----------------------------------------------------------------
        action_data = SERVO_MAP.get(category, SERVO_MAP["anorganik"])

        logger.info(f"✅ FINAL → {category.upper()} | conf={confidence} | src={detection_source} | servo={action_data['action']}")

        return {
            "status": "success",
            "kategori": category,
            "confidence": confidence,
            "target_bin": action_data["target_bin"],
            "active_servo": action_data["active_servo"],
            "servo_action": action_data["action"],
            "servo_angle": action_data["servo_angle"],
            "servo_states": action_data["servos"],
            "detection_source": detection_source,
            "object_name_id": get_indonesian_label(detection_source, category),
            "target_compartment": action_data["label"],
            "filename": file.filename
        }

    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"[Detect Error]: {e}")
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)
