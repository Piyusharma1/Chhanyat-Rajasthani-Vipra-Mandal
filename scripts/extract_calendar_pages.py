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

    # Determine cover and month page indices
    # Scenarios:
    # 24 pages: alternate pages 0, 2, 4, ... 22 are Jan-Dec
    # 25+ pages: page 0 is cover, then 1, 3, 5, ... 23 are Jan-Dec
    # 13 pages: page 0 is cover, pages 1..12 are Jan-Dec
    # 12 pages: pages 0..11 are Jan-Dec
    cover_idx = None
    if num_pages >= 25:
        cover_idx = 0
        indices = [1 + i * 2 for i in range(12)]
    elif num_pages >= 24:
        indices = [i * 2 for i in range(12)]
    elif num_pages == 13:
        cover_idx = 0
        indices = list(range(1, 13))
    elif num_pages >= 12:
        indices = list(range(12))
    else:
        indices = list(range(num_pages))

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
    for m, idx in enumerate(indices, start=1):
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
