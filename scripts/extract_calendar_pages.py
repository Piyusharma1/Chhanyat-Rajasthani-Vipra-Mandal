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
out_dir  = os.path.join(base_dir, 'uploads', 'calendar', 'pages')

if not os.path.exists(pdf_path):
    print(f"Error: PDF not found at {pdf_path}")
    sys.exit(1)

os.makedirs(out_dir, exist_ok=True)

# ─────────────────────────────────────────────────────────────
# Quality / resolution settings
# ─────────────────────────────────────────────────────────────
# Full-page images: rendered at 300 DPI (matrix scale = 300/72 ≈ 4.17)
# This gives ~2480×3508 px for an A4 page — print-quality sharp.
RENDER_SCALE   = 300 / 72        # ≈ 4.167  (72 pt = 1 inch in PDF)
WEBP_QUALITY   = 93              # 93 = very high; lossless detail
WEBP_METHOD    = 6               # slowest / best compression method

# Thumbnails: rendered at 150 DPI — fast load in gallery/admin
THUMB_SCALE    = 150 / 72       # ≈ 2.083
THUMB_QUALITY  = 85
THUMB_METHOD   = 4

thumb_dir = os.path.join(base_dir, 'uploads', 'calendar', 'thumbs')
os.makedirs(thumb_dir, exist_ok=True)


def render_page(page, scale):
    """Render a PDF page to a Pixmap at the given scale."""
    mat = fitz.Matrix(scale, scale)
    return page.get_pixmap(matrix=mat, alpha=False)


def save_webp(pix, path, quality=WEBP_QUALITY, method=WEBP_METHOD):
    """Save a Pixmap as WebP, using Pillow if available (better encoder)."""
    if has_pil:
        img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
        img.save(path, "WEBP", quality=quality, method=method)
    else:
        # PyMuPDF's built-in saver (quality not tunable, but still good)
        pix.save(path)


def save_page(doc, idx, out_path, thumb_path):
    """Render full-res + thumbnail for one PDF page index."""
    page = doc[idx]

    # Full-resolution image
    pix_full = render_page(page, RENDER_SCALE)
    save_webp(pix_full, out_path, WEBP_QUALITY, WEBP_METHOD)
    print(f"  Saved full ({pix_full.width}×{pix_full.height}px) → {os.path.basename(out_path)}")

    # Thumbnail
    pix_thumb = render_page(page, THUMB_SCALE)
    save_webp(pix_thumb, thumb_path, THUMB_QUALITY, THUMB_METHOD)
    print(f"  Saved thumb ({pix_thumb.width}×{pix_thumb.height}px) → thumbs/{os.path.basename(thumb_path)}")


try:
    doc = fitz.open(pdf_path)
    num_pages = len(doc)
    print(f"Total PDF pages: {num_pages}")
    print(f"Render DPI: {round(RENDER_SCALE * 72)} | WebP quality: {WEBP_QUALITY}")

    # ── Page-map detection ──────────────────────────────────────
    cover_idx  = None
    samiti_idx = None
    month_page_map = {}

    if num_pages == 14:
        # 14-page Sanatan Panchang format:
        # 0=Cover, 1=Samiti, 2=Nov, 3=Dec, 4=Jan … 13=Oct
        cover_idx  = 0
        samiti_idx = 1
        month_page_map = {
            1: 4, 2: 5, 3: 6, 4: 7, 5: 8, 6: 9,
            7: 10, 8: 11, 9: 12, 10: 13, 11: 2, 12: 3
        }
    elif num_pages == 28:
        cover_idx = 0
        month_page_map = {
            1: 8,  2: 10, 3: 12, 4: 14, 5: 16, 6: 18,
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

    # ── Cover ──────────────────────────────────────────────────
    if cover_idx is not None and cover_idx < num_pages:
        print("Processing cover …")
        save_page(
            doc, cover_idx,
            out_path   = os.path.join(base_dir, 'uploads', 'calendar', 'cover.webp'),
            thumb_path = os.path.join(thumb_dir, 'cover.webp'),
        )

    # ── Samiti / Committee ─────────────────────────────────────
    if samiti_idx is not None and samiti_idx < num_pages:
        print("Processing samiti page …")
        save_page(
            doc, samiti_idx,
            out_path   = os.path.join(base_dir, 'uploads', 'calendar', 'samiti.webp'),
            thumb_path = os.path.join(thumb_dir, 'samiti.webp'),
        )

    # ── 12 month pages ─────────────────────────────────────────
    for m in range(1, 13):
        if m not in month_page_map:
            continue
        idx = month_page_map[m]
        if idx >= num_pages:
            print(f"  Warning: month {m} mapped to page {idx+1} but PDF only has {num_pages} pages.")
            continue

        print(f"Processing month {m} (PDF page {idx + 1}) …")
        webp_file  = os.path.join(out_dir,   f"{m}.webp")
        thumb_file = os.path.join(thumb_dir, f"{m}.webp")
        save_page(doc, idx, webp_file, thumb_file)

        # Remove stale legacy formats
        for old_ext in ['.jpg', '.png']:
            old_file = os.path.join(out_dir, f"{m}{old_ext}")
            if os.path.exists(old_file):
                try:
                    os.remove(old_file)
                except Exception:
                    pass

    print("SUCCESS: Extraction complete — 300 DPI WebP images generated.")
    sys.exit(0)

except Exception as e:
    print(f"Error during extraction: {e}")
    sys.exit(1)
