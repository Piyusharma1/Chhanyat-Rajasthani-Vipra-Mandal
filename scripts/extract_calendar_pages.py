import os
import sys

# Try importing PyMuPDF
try:
    import pymupdf as fitz
except ImportError:
    try:
        import fitz
    except ImportError:
        print("PyMuPDF not installed in this Python environment.")
        sys.exit(1)

# Try importing Pillow for WebP encoding
try:
    from PIL import Image
    has_pil = True
except ImportError:
    has_pil = False

base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
pdf_path = os.path.join(base_dir, 'uploads', 'calendar', 'calendar.pdf')
out_dir = os.path.join(base_dir, 'uploads', 'calendar', 'pages')

if not os.path.exists(pdf_path):
    print(f"Error: PDF not found at {pdf_path}")
    sys.exit(1)

os.makedirs(out_dir, exist_ok=True)

try:
    doc = fitz.open(pdf_path)
    num_pages = len(doc)
    print(f"Total PDF pages: {num_pages}")

    # Determine cover and month page indices (0-based)
    cover_idx = None
    month_page_map = {}

    if num_pages == 14:
        # 🕉️ Sanatan Panchang 14-page format:
        # Page 1 (idx 0): Cover (सनातन दिनदर्शिका)
        # Page 2 (idx 1): Committee & Advisory
        # Page 3 (idx 2): November (11)
        # Page 4 (idx 3): December (12)
        # Page 5 (idx 4): January (1)
        # Page 6 (idx 5): February (2)
        # Page 7 (idx 6): March (3)
        # Page 8 (idx 7): April (4)
        # Page 9 (idx 8): May (5)
        # Page 10 (idx 9): June (6)
        # Page 11 (idx 10): July (7)
        # Page 12 (idx 11): August (8)
        # Page 13 (idx 12): September (9)
        # Page 14 (idx 13): October (10)
        cover_idx = 0
        month_page_map = {
            1: 4, 2: 5, 3: 6, 4: 7, 5: 8, 6: 9,
            7: 10, 8: 11, 9: 12, 10: 13, 11: 2, 12: 3
        }
    elif num_pages == 28:
        cover_idx = 0
        month_page_map = {
            1: 8, 2: 10, 3: 12, 4: 14, 5: 16, 6: 18,
            7: 20, 8: 22, 9: 24, 10: 26, 11: 4, 12: 6
        }
    elif num_pages >= 25:
        cover_idx = 0
        for m in range(1, 13):
            month_page_map[m] = 1 + (m - 1) * 2
    elif num_pages >= 24:
        for m in range(1, 13):
            month_page_map[m] = (m - 1) * 2
    elif num_pages == 13:
        cover_idx = 0
        for m in range(1, 13):
            month_page_map[m] = m
    else:
        for m in range(1, min(13, num_pages + 1)):
            month_page_map[m] = m - 1

    # Extract Cover if applicable
    if cover_idx is not None and cover_idx < num_pages:
        c_page = doc[cover_idx]
        c_pix = c_page.get_pixmap(matrix=fitz.Matrix(2.0, 2.0))
        c_file = os.path.join(base_dir, 'uploads', 'calendar', 'cover.webp')
        if has_pil:
            c_img = Image.frombytes("RGB", [c_pix.width, c_pix.height], c_pix.samples)
            c_img.save(c_file, "WEBP", quality=88, method=6)
        else:
            c_pix.save(c_file)
        print(f"Saved cover -> {c_file}")

    # Extract each of the 12 month pages
    for m in range(1, 13):
        if m in month_page_map:
            idx = month_page_map[m]
            if idx < num_pages:
                page = doc[idx]
                pix = page.get_pixmap(matrix=fitz.Matrix(2.0, 2.0))
                
                webp_file = os.path.join(out_dir, f"{m}.webp")
                if has_pil:
                    img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
                    img.save(webp_file, "WEBP", quality=88, method=6)
                else:
                    # Fallback to JPG if PIL is unavailable
                    webp_file = os.path.join(out_dir, f"{m}.jpg")
                    pix.save(webp_file)

                # Remove old legacy formats if exists to prevent stale cache
                for old_ext in ['.jpg', '.png']:
                    old_file = os.path.join(out_dir, f"{m}{old_ext}")
                    if os.path.exists(old_file) and old_file != webp_file:
                        try:
                            os.remove(old_file)
                        except Exception:
                            pass

                print(f"Saved month {m} (page {idx+1}) -> {webp_file}")

    print("SUCCESS: Extraction complete in WEBP format.")
    sys.exit(0)

except Exception as e:
    print(f"Error during extraction: {e}")
    sys.exit(1)
