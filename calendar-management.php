<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$calendarPdfPath = 'uploads/calendar/calendar.pdf';
$hasPdf = file_exists(__DIR__ . '/' . $calendarPdfPath);
$pdfSize = $hasPdf ? round(filesize(__DIR__ . '/' . $calendarPdfPath) / (1024 * 1024), 2) . ' MB' : 'अनुपलब्ध';

$months = [
    1  => ['hi' => 'जनवरी', 'en' => 'January'],
    2  => ['hi' => 'फ़रवरी', 'en' => 'February'],
    3  => ['hi' => 'मार्च', 'en' => 'March'],
    4  => ['hi' => 'अप्रैल', 'en' => 'April'],
    5  => ['hi' => 'मई', 'en' => 'May'],
    6  => ['hi' => 'जून', 'en' => 'June'],
    7  => ['hi' => 'जुलाई', 'en' => 'July'],
    8  => ['hi' => 'अगस्त', 'en' => 'August'],
    9  => ['hi' => 'सितंबर', 'en' => 'September'],
    10 => ['hi' => 'अक्टूबर', 'en' => 'October'],
    11 => ['hi' => 'नवंबर', 'en' => 'November'],
    12 => ['hi' => 'दिसंबर', 'en' => 'December'],
];
?>
<!DOCTYPE html>
<html lang="hi" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>दिनदर्शिका प्रबंधन (Calendar Admin) | छःन्यात राजस्थानी विप्र मंडल, लातूर</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__.'/css/style.css') ?: time()?>">

    <style>
        :root {
            --cal-maroon: #7B1A1A;
            --cal-maroon-dark: #581212;
            --cal-gold: #d4af37;
            --cal-gold-light: #F0C060;
            --cal-gold-pale: #FDF5E0;
            --cal-saffron: #FF6B00;
            --cal-saffron-dark: #e65100;
            --cal-saffron-pale: #FFF3E6;
            --cal-bg: #f7f4ed;
            --border-color: rgba(201, 148, 26, 0.3);
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 2px 8px rgba(123, 26, 26, 0.08);
            --shadow-md: 0 6px 20px rgba(123, 26, 26, 0.14);
            --transition: all 0.25s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--cal-bg);
            font-family: 'Noto Sans Devanagari', sans-serif;
            color: #2C1810;
            min-height: 100vh;
        }

        /* ===== TOP ADMIN HEADER ===== */
        .admin-topbar {
            background: linear-gradient(135deg, var(--cal-maroon-dark) 0%, var(--cal-maroon) 100%);
            color: #fff;
            padding: 16px 24px;
            box-shadow: 0 4px 18px rgba(88, 18, 18, 0.25);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-topbar-inner {
            max-width: 1240px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .admin-topbar-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-topbar-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--cal-gold) 0%, var(--cal-gold-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cal-maroon-dark);
            font-size: 1.3rem;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        .admin-topbar-title h1 {
            font-family: 'Tiro Devanagari Hindi', serif;
            font-size: 1.35rem;
            color: var(--cal-gold-light);
            line-height: 1.2;
            margin: 0;
        }
        .admin-topbar-title p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 2px 0 0;
        }
        .admin-topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-topbar {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border: 1px solid rgba(240, 192, 96, 0.35);
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: var(--transition);
            cursor: pointer;
        }
        .btn-topbar:hover {
            background: rgba(255, 255, 255, 0.22);
            border-color: var(--cal-gold-light);
            color: var(--cal-gold-light);
            transform: translateY(-1px);
        }
        .btn-topbar.btn-live {
            background: linear-gradient(135deg, var(--cal-saffron) 0%, var(--cal-saffron-dark) 100%);
            border: none;
            box-shadow: 0 3px 10px rgba(255, 107, 0, 0.3);
        }
        .btn-topbar.btn-live:hover {
            box-shadow: 0 5px 14px rgba(255, 107, 0, 0.45);
            color: #fff;
        }

        /* ===== CONTAINER ===== */
        .cal-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 16px 16px 60px;
        }

        /* ===== STICKY MONTH PILLS (Matching calendar.php) ===== */
        .month-bar-sticky-wrap {
            position: sticky;
            top: 76px;
            z-index: 90;
            background: rgba(247, 244, 237, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 10px 0 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.25);
        }
        .month-bar-row {
            display: grid;
            grid-template-columns: repeat(13, 1fr);
            gap: 5px;
            width: 100%;
        }
        .month-btn {
            background: #fff;
            border: 1.5px solid var(--cal-gold);
            color: var(--cal-maroon);
            padding: 8px 2px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            user-select: none;
        }
        .month-btn:hover {
            background: var(--cal-saffron-pale);
            color: var(--cal-saffron);
            border-color: var(--cal-saffron);
            transform: translateY(-1px);
        }

        /* ===== CONTROL / BANNER BAR ===== */
        .cal-header-bar {
            background: #fff;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 22px;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            box-shadow: var(--shadow-sm);
        }
        .cal-main-title {
            font-family: 'Tiro Devanagari Hindi', serif;
            font-size: 1.35rem;
            color: var(--cal-maroon);
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pdf-quick-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-pdf-mgr {
            background: var(--cal-maroon);
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 3px 8px rgba(123, 26, 26, 0.25);
        }
        .btn-pdf-mgr:hover {
            background: var(--cal-maroon-dark);
            transform: translateY(-1px);
        }

        /* ===== 12 MONTHS & COVER GRID ===== */
        .all-months-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));

            margin-bottom: 30px;
        }
        .month-row-card {
            background: #fff;
            border-radius: 12px;
            border: 1.5px solid var(--cal-gold);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
            scroll-margin-top: 145px;
        }
        .month-row-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(123, 26, 26, 0.15);
        }
        .month-card-header {
            background: var(--cal-maroon);
            color: #fff;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .month-card-header h3 {
            margin: 0;
            font-size: 1.05rem;
            font-family: 'Tiro Devanagari Hindi', serif;
            color: #fff;
        }
        .month-card-header span {
            font-size: 0.8rem;
            opacity: 0.9;
            background: rgba(255, 255, 255, 0.18);
            padding: 2px 8px;
            border-radius: 12px;
        }
        .month-card-img-wrap {
            padding: 6px;
            background: #fafafa;
            text-align: center;
            position: relative;
            cursor: pointer;
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .month-card-img-wrap img {
            width: 100%;
            height: auto;
            max-height: 380px;
            object-fit: contain;
            display: block;
            border-radius: 6px;
            transition: transform 0.2s ease;
        }
        .month-card-img-wrap:hover img {
            transform: scale(1.02);
        }
        .month-card-img-wrap .zoom-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0,0,0,0.65);
            color: #fff;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            opacity: 0;
            transition: var(--transition);
            pointer-events: none;
        }
        .month-card-img-wrap:hover .zoom-badge {
            opacity: 1;
        }

        .month-card-actions {
            padding: 10px 12px;
            background: #fff;
            border-top: 1px solid #f0f0f0;
            display: flex;
            gap: 8px;
            margin-top: auto;
        }
        .card-action-btn {
            flex: 1;
            padding: 9px 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid rgba(123, 26, 26, 0.25);
            background: var(--cal-gold-pale);
            color: var(--cal-maroon);
            transition: var(--transition);
            min-height: 38px;
        }
        .card-action-btn:hover {
            background: var(--cal-maroon);
            color: #fff;
        }
        .card-action-btn.btn-replace {
            background: linear-gradient(135deg, var(--cal-saffron) 0%, var(--cal-saffron-dark) 100%);
            color: #fff;
            border: none;
            box-shadow: 0 2px 8px rgba(255, 107, 0, 0.3);
        }
        .card-action-btn.btn-replace:hover {
            box-shadow: 0 4px 12px rgba(255, 107, 0, 0.45);
        }

        /* ===== MODAL OVERLAYS ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(44, 24, 16, 0.75);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }
        .modal-overlay.show { display: flex; }
        .modal-card {
            background: #fff;
            border-radius: var(--radius-md);
            width: 100%;
            max-width: 540px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-md);
            border: 1.5px solid var(--cal-gold);
            animation: modalPop 0.25s ease;
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.94) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-header {
            background: linear-gradient(135deg, var(--cal-maroon-dark) 0%, var(--cal-maroon) 100%);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 {
            font-family: 'Tiro Devanagari Hindi', serif;
            font-size: 1.2rem;
            color: var(--cal-gold-light);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .modal-close-btn {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.3rem;
            cursor: pointer;
            padding: 4px;
            transition: var(--transition);
        }
        .modal-close-btn:hover { color: #fff; transform: rotate(90deg); }
        .modal-body {
            padding: 22px;
        }
        .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #eee;
            background: #fdfaf5;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-cancel {
            background: #fff;
            border: 1.5px solid #ccc;
            color: #666;
            padding: 8px 18px;
            border-radius: 6px;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-submit-action {
            background: linear-gradient(135deg, var(--cal-saffron) 0%, var(--cal-saffron-dark) 100%);
            border: none;
            color: #fff;
            padding: 8px 22px;
            border-radius: 6px;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(255, 107, 0, 0.3);
        }
        .btn-submit-action:hover {
            box-shadow: 0 5px 14px rgba(255, 107, 0, 0.45);
        }

        /* ===== LIGHTBOX MODAL ===== */
        .lightbox-modal {
            max-width: 900px;
            background: #111;
            border: 1px solid #333;
            overflow: hidden;
        }
        .lightbox-modal .modal-header {
            background: #222;
            border-bottom: 1px solid #333;
        }
        .lightbox-img-wrap {
            max-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            background: #000;
        }
        .lightbox-img-wrap img {
            max-width: 100%;
            max-height: 75vh;
            object-fit: contain;
            border-radius: 4px;
        }

        /* ===== TOAST NOTIFICATIONS ===== */
        #toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .admin-toast {
            min-width: 280px;
            max-width: 400px;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            font-size: 0.9rem;
            animation: toastIn 0.3s ease;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .admin-toast.success { background: linear-gradient(135deg, #1B5E20, #2E7D32); }
        .admin-toast.error   { background: linear-gradient(135deg, #B71C1C, #C62828); }
        .admin-toast.info    { background: linear-gradient(135deg, var(--cal-maroon-dark), var(--cal-maroon)); }

        /* Floating Scroll to Top */
        .scroll-top-btn {
            position: fixed;
            bottom: 25px;
            right: 20px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--cal-maroon);
            color: #fff;
            border: 2px solid var(--cal-gold);
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 80;
            transition: transform 0.2s ease;
        }
        .scroll-top-btn:active { transform: scale(0.92); }

        /* Mobile Screens */
        @media (max-width: 768px) {
            .admin-topbar-inner { flex-direction: column; align-items: flex-start; }
            .admin-topbar-actions { width: 100%; justify-content: space-between; }
            .month-bar-sticky-wrap { top: 110px; padding: 8px 0; }
            .month-bar-row {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                gap: 8px;
                padding: 4px 4px 6px;
                scrollbar-width: none;
            }
            .month-bar-row::-webkit-scrollbar { display: none; }
            .month-btn {
                flex-shrink: 0;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 0.88rem;
                min-width: 82px;
                scroll-snap-align: center;
            }
            .all-months-grid { grid-template-columns: 1fr; gap: 16px; }
            .cal-header-bar { justify-content: center; text-align: center; }
            .pdf-quick-actions { justify-content: center; width: 100%; }
        }
    </style>
</head>
<body>

    <!-- ═══════════════════════════════════════════════════════════
         ADMIN TOPBAR
    ═══════════════════════════════════════════════════════════ -->
    <header class="admin-topbar">
        <div class="admin-topbar-inner">
            <div class="admin-topbar-title">
                <div class="admin-topbar-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1>सनातन दिनदर्शिका प्रबंधन (Calendar Admin)</h1>
                    <p>छःन्यात राजस्थानी विप्र मंडल, लातूर</p>
                </div>
            </div>
            <div class="admin-topbar-actions">
                <a href="admin-dashboard.php" class="btn-topbar">
                    <i class="fas fa-arrow-left"></i> डैशबोर्ड
                </a>
                <button type="button" class="btn-topbar" id="btn-open-pdf-modal">
                    <i class="fas fa-file-pdf"></i> संपूर्ण PDF प्रबंधन
                </button>
                <a href="calendar.php" target="_blank" class="btn-topbar btn-live">
                    <i class="fas fa-external-link-alt"></i> लाइव दिनदर्शिका देखें
                </a>
                <a href="api/logout.php" class="btn-topbar">
                    <i class="fas fa-sign-out-alt"></i> लॉगआउट
                </a>
            </div>
        </div>
    </header>

    <!-- ═══════════════════════════════════════════════════════════
         MAIN CONTAINER
    ═══════════════════════════════════════════════════════════ -->
    <main class="cal-container">

        <!-- Sticky Month Navigation Bar (Identical to calendar.php) -->
        <div class="month-bar-sticky-wrap">
            <div class="month-bar-row">
                <a href="#admin-month-card-cover" class="month-btn">
                    मुखपृष्ठ
                </a>
                <?php foreach ($months as $num => $m): ?>
                    <a href="#admin-month-card-<?= $num ?>" class="month-btn">
                        <?= $num ?>. <?= $m['hi'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Header / Status Bar -->
        <div class="cal-header-bar">
            <div>
                <h2 class="cal-main-title">
                    <i class="fas fa-om" style="color:var(--cal-saffron);"></i> सनातन दिनदर्शिका पृष्ठ प्रबंधन
                </h2>
                <p style="margin: 4px 0 0; font-size: 0.88rem; color: #666;">
                    यहाँ से आप मुखपृष्ठ एवं समस्त १२ माह के पृष्ठों की इमेज सीधे बदल सकते हैं। प्रत्येक बदलाव तुरंत वेबसाइट पर लाइव हो जाता है।
                </p>
            </div>
            <div class="pdf-quick-actions">
                <button type="button" class="btn-pdf-mgr" onclick="document.getElementById('btn-open-pdf-modal').click()">
                    <i class="fas fa-cloud-upload-alt"></i> नई PDF अपलोड करें
                </button>
                <?php if ($hasPdf): ?>
                <a href="<?= $calendarPdfPath ?>" download="सनातन_दिनदर्शिका.pdf" class="btn-pdf-mgr" style="background:#555;">
                    <i class="fas fa-download"></i> PDF (<?= $pdfSize ?>)
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- All 12 Months & Cover Grid (Identical Layout to calendar.php) -->
        <div class="all-months-grid">

            <!-- 1. OFFICIAL COVER PAGE CARD -->
            <?php 
                $coverWebp = 'uploads/calendar/cover.webp';
                $coverSrc = file_exists(__DIR__ . '/' . $coverWebp) ? $coverWebp : 'uploads/calendar/cover.png';
            ?>
            <div class="month-row-card" id="admin-month-card-cover">
                <div class="month-card-header">
                    <h3>मुखपृष्ठ (Cover Page)</h3>
                    <span>सनातन दिनदर्शिका (WEBP)</span>
                </div>
                <div class="month-card-img-wrap" onclick="openLightbox('<?= $coverSrc ?>', 'सनातन दिनदर्शिका मुखपृष्ठ')">
                    <img src="<?= $coverSrc ?>?v=<?= time() ?>" id="img-preview-cover" alt="सनातन दिनदर्शिका मुखपृष्ठ">
                    <span class="zoom-badge"><i class="fas fa-search-plus"></i> बड़ा देखें</span>
                </div>
                <div class="month-card-actions">
                    <button type="button" class="card-action-btn btn-replace" onclick="openReplaceModal('cover', 'मुखपृष्ठ (Cover Page)', '<?= $coverSrc ?>')">
                        <i class="fas fa-camera"></i> फ़ोटो बदलें
                    </button>
                    <button type="button" class="card-action-btn" onclick="openLightbox('<?= $coverSrc ?>', 'सनातन दिनदर्शिका मुखपृष्ठ')">
                        <i class="fas fa-expand"></i> पूरा देखें
                    </button>
                </div>
            </div>

            <!-- 2. MONTHS 1 TO 12 CARDS -->
            <?php foreach ($months as $num => $m): 
                $pageWebp = "uploads/calendar/pages/{$num}.webp";
                $pageSrc = file_exists(__DIR__ . '/' . $pageWebp) ? $pageWebp : "uploads/calendar/pages/{$num}.jpg";
            ?>
                <div class="month-row-card" id="admin-month-card-<?= $num ?>">
                    <div class="month-card-header">
                        <h3><?= $num ?>. <?= $m['hi'] ?></h3>
                        <span><?= $m['en'] ?> (WEBP)</span>
                    </div>
                    <div class="month-card-img-wrap" onclick="openLightbox('<?= $pageSrc ?>', '<?= $m['hi'] ?> - सनातन दिनदर्शिका')">
                        <img src="<?= $pageSrc ?>?v=<?= time() ?>" id="img-preview-<?= $num ?>" alt="सनातन दिनदर्शिका - <?= $m['hi'] ?>">
                        <span class="zoom-badge"><i class="fas fa-search-plus"></i> बड़ा देखें</span>
                    </div>
                    <div class="month-card-actions">
                        <button type="button" class="card-action-btn btn-replace" onclick="openReplaceModal('<?= $num ?>', '<?= $num ?>. <?= $m['hi'] ?>', '<?= $pageSrc ?>')">
                            <i class="fas fa-camera"></i> फ़ोटो बदलें
                        </button>
                        <button type="button" class="card-action-btn" onclick="openLightbox('<?= $pageSrc ?>', '<?= $m['hi'] ?> - सनातन दिनदर्शिका')">
                            <i class="fas fa-expand"></i> पूरा देखें
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Floating Scroll to Top Button -->
        <button type="button" class="scroll-top-btn" id="scroll-top-btn" title="ऊपर जाएं" aria-label="ऊपर जाएं">
            <i class="fas fa-arrow-up"></i>
        </button>
    </main>

    <!-- ═══════════════════════════════════════════════════════════
         MODAL 1: REPLACE PAGE / COVER IMAGE
    ═══════════════════════════════════════════════════════════ -->
    <div class="modal-overlay" id="replace-modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="replace-modal-title"><i class="fas fa-image"></i> फ़ोटो बदलें</h3>
                <button type="button" class="modal-close-btn" id="btn-close-replace-modal">&times;</button>
            </div>
            <form id="replace-form" enctype="multipart/form-data">
                <input type="hidden" id="replace-page-target" name="page_target" value="">
                <div class="modal-body">
                    <p style="font-size:0.9rem; color:#666; margin-bottom:14px;">
                        <strong id="replace-target-label" style="color:var(--cal-maroon);"></strong> के लिए नई इमेज चुनें। फॉर्मेट: JPG, PNG, WEBP.
                    </p>

                    <!-- Preview of currently selected image -->
                    <div style="text-align:center; margin-bottom:16px;">
                        <div style="max-height:240px; overflow:hidden; border-radius:8px; border:1px solid #ddd; background:#fafafa; display:inline-block; padding:4px;">
                            <img id="replace-preview-img" src="" alt="पूर्वावलोकन" style="max-height:230px; max-width:100%; object-fit:contain; display:block;">
                        </div>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label for="replace-file-input" style="display:block; font-size:0.9rem; font-weight:600; color:var(--cal-maroon); margin-bottom:6px;">
                            <i class="fas fa-upload" style="color:var(--cal-saffron);"></i> नई इमेज फ़ाइल चुनें:
                        </label>
                        <input type="file" id="replace-file-input" name="page_image" accept="image/jpeg,image/png,image/webp" required style="width:100%; padding:10px; border:1.5px solid var(--border-color); border-radius:6px; background:#fff;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="btn-cancel-replace-modal">रद्द करें</button>
                    <button type="submit" class="btn-submit-action" id="btn-submit-replace">
                        <i class="fas fa-cloud-upload-alt"></i> अपलोड व सहेजें
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         MODAL 2: PDF MANAGEMENT & EXTRACTION
    ═══════════════════════════════════════════════════════════ -->
    <div class="modal-overlay" id="pdf-modal-overlay">
        <div class="modal-card" style="max-width:580px;">
            <div class="modal-header">
                <h3><i class="fas fa-file-pdf"></i> संपूर्ण वार्षिक PDF प्रबंधन</h3>
                <button type="button" class="modal-close-btn" id="btn-close-pdf-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display:flex; align-items:center; gap:16px; padding:12px; background:#FDF5E0; border-radius:8px; border:1px solid var(--border-color); margin-bottom:18px;">
                    <i class="fas fa-file-pdf" style="font-size:2.4rem; color:#D32F2F;"></i>
                    <div>
                        <strong style="color:var(--cal-maroon); font-size:1rem;">uploads/calendar/calendar.pdf</strong>
                        <div style="font-size:0.85rem; color:#666; margin-top:2px;">
                            स्थिति: <strong><?= $hasPdf ? 'सक्रिय फ़ाइल मौजूद (' . $pdfSize . ')' : 'फ़ाइल उपलब्ध नहीं है' ?></strong>
                        </div>
                    </div>
                </div>

                <!-- PDF Upload Form -->
                <form id="pdf-upload-form" enctype="multipart/form-data">
                    <label style="display:block; font-size:0.9rem; font-weight:600; color:var(--cal-maroon); margin-bottom:6px;">
                        <i class="fas fa-upload" style="color:var(--cal-saffron);"></i> नई PDF फ़ाइल अपलोड करें (.pdf):
                    </label>
                    <input type="file" id="pdf-file-input" name="calendar_pdf" accept="application/pdf" required style="width:100%; padding:10px; border:1.5px solid var(--border-color); border-radius:6px; background:#fff; margin-bottom:12px;">
                    <button type="submit" class="btn-submit-action" id="btn-submit-pdf-upload" style="width:100%; justify-content:center; padding:11px;">
                        <i class="fas fa-cloud-upload-alt"></i> नई PDF अपलोड करें एवं स्वतः १२ पृष्ठ निकालें
                    </button>
                </form>

                <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn-cancel" id="btn-reextract-pdf" style="flex:1; border-color:var(--cal-maroon); color:var(--cal-maroon);">
                        <i class="fas fa-sync-alt"></i> वर्तमान PDF से १२ माह पुनः निकालें
                    </button>
                    <?php if ($hasPdf): ?>
                    <a href="<?= $calendarPdfPath ?>" download="सनातन_दिनदर्शिका.pdf" class="btn-cancel" style="flex:1; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                        <i class="fas fa-download"></i> PDF डाउनलोड
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         MODAL 3: LIGHTBOX (FULL VIEW)
    ═══════════════════════════════════════════════════════════ -->
    <div class="modal-overlay" id="lightbox-overlay">
        <div class="modal-card lightbox-modal">
            <div class="modal-header">
                <h3 id="lightbox-title"><i class="fas fa-eye"></i> पृष्ठ पूर्वावलोकन</h3>
                <button type="button" class="modal-close-btn" id="btn-close-lightbox">&times;</button>
            </div>
            <div class="lightbox-img-wrap">
                <img id="lightbox-img" src="" alt="Calendar Page">
            </div>
        </div>
    </div>

    <!-- 🔔 Toast Container -->
    <div id="toast-container"></div>

    <!-- 📜 Scripts -->
    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/calendar-management.js?v=<?= @filemtime(__DIR__.'/js/calendar-management.js') ?: time()?>"></script>
</body>
</html>
