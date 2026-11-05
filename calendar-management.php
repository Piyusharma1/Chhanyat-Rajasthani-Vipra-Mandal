<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$adminBranch = $_SESSION['admin_branch'] ?? 'ALL';
$isSuperAdmin = ($adminBranch === 'ALL' || empty($adminBranch));
if (!$isSuperAdmin) {
    header('Location: admin-dashboard.php');
    exit;
}

$calendarPdfPath = 'uploads/calendar/calendar.pdf';
$hasPdf = file_exists(__DIR__ . '/' . $calendarPdfPath);
$pdfSize = $hasPdf ? round(filesize(__DIR__ . '/' . $calendarPdfPath) / (1024 * 1024), 2) . ' MB' : 'अनुपलब्ध';

$months = [
    1  => ['hi' => 'जानेवारी', 'en' => 'January'],
    2  => ['hi' => 'फेब्रुवारी', 'en' => 'February'],
    3  => ['hi' => 'मार्च', 'en' => 'March'],
    4  => ['hi' => 'एप्रिल', 'en' => 'April'],
    5  => ['hi' => 'मे', 'en' => 'May'],
    6  => ['hi' => 'जून', 'en' => 'June'],
    7  => ['hi' => 'जुलै', 'en' => 'July'],
    8  => ['hi' => 'ऑगस्ट', 'en' => 'August'],
    9  => ['hi' => 'सप्टेंबर', 'en' => 'September'],
    10 => ['hi' => 'ऑक्टोबर', 'en' => 'October'],
    11 => ['hi' => 'नोव्हेंबर', 'en' => 'November'],
    12 => ['hi' => 'डिसेंबर', 'en' => 'December'],
];
?>
<!DOCTYPE html>
<html lang="hi" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>दिनदर्शिका प्रबंधन - Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Main CSS (containing :root variables) -->
    <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__.'/css/style.css') ?: time()?>">

    <style>
        /* ========================================= */
        /* 🛠️ BASE & RESET (Ref: committee-management) */
        /* ========================================= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1240px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .section-divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 25px 0 25px;
        }
        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold, #C9941A), transparent);
        }
        .section-divider h2 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon, #7B1A1A);
            font-size: 1.4rem;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .section-divider h2 i {
            color: var(--saffron, #FF6B00);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-xl);
            flex-wrap: wrap;
            gap: 16px;
        }
        .page-header h2 {
            color: var(--maroon);
            margin: 0;
            font-size: clamp(1.3rem, 2.5vw, 1.8rem);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            background: var(--bg-card);
            padding: var(--spacing-lg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            margin-bottom: var(--spacing-xl);
            border: 1px solid var(--border);
        }
        .card h3 {
            color: var(--maroon-deep);
            margin-top: 0;
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--saffron-pale);
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card h4 {
            color: var(--text-mid);
            margin-top: var(--spacing-lg);
            margin-bottom: var(--spacing-sm);
        }

        /* Standard Buttons (Ref: committee-management) */
        .btn {
            padding: 12px 16px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: var(--maroon);
            color: white;
        }
        .btn-primary:hover {
            background: var(--saffron-deep);
            color: white;
        }
        .btn-success {
            background: var(--maroon);
            color: white;
        }
        .btn-success:hover {
            background: var(--maroon-deep);
            color: white;
        }

        .btn-secondary {
            background: transparent;
            color: var(--maroon);
            border: 2px solid var(--maroon);
            width: auto;
            padding: 10px 24px;
            white-space: nowrap;
        }
        .btn-secondary:hover {
            background: var(--maroon);
            color: white;
        }

        /* Sticky Month Navigation Bar */
        .month-bar-sticky-wrap {
            position: sticky;
            top: 75px;
            z-index: 90;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 10px;
            margin-bottom: 22px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .month-bar-row {
            display: grid;
            grid-template-columns: repeat(13, 1fr);
            gap: 6px;
            width: 100%;
        }
        .month-btn {
            background: var(--white);
            border: 1.5px solid var(--gold);
            color: var(--maroon);
            padding: 8px 4px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: var(--transition);
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            user-select: none;
        }
        .month-btn:hover {
            background: var(--maroon);
            color: white;
            border-color: var(--maroon);
            transform: translateY(-1px);
        }

        /* 12 Months & Cover Grid */
        .all-months-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .month-row-card {
            background: var(--bg-card);
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
            scroll-margin-top: 155px;
        }
        .month-row-card:hover {
            transform: translateY(-3px);
            border-color: var(--saffron);
            box-shadow: 0 8px 24px rgba(255, 107, 0, 0.16);
        }
        .month-card-header {
            background: var(--maroon);
            color: #fff;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .month-card-header h3 {
            margin: 0;
            font-size: 1rem;
            color: #fff;
            border: none;
            padding: 0;
        }
        .month-card-header span {
            font-size: 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 12px;
            color: var(--gold-pale);
        }
        .month-card-img-wrap {
            padding: 8px;
            background: var(--cream);
            position: relative;
            cursor: pointer;
            overflow: hidden;
        }
        .month-card-img-wrap img {
            width: 100%;
            height: 240px;
            object-fit: contain;
            display: block;
            border-radius: 6px;
            background: #fff;
            transition: transform 0.25s ease;
        }
        .month-card-img-wrap:hover img {
            transform: scale(1.02);
        }
        .zoom-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0, 0, 0, 0.75);
            color: #fff;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            backdrop-filter: blur(2px);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .month-card-img-wrap:hover .zoom-badge {
            opacity: 1;
        }
        .month-card-actions {
            padding: 10px 12px;
            display: flex;
            gap: 8px;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
        }
        .card-action-btn {
            flex: 1;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-dark);
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .card-action-btn:hover {
            border-color: var(--maroon);
            color: var(--maroon);
            background: var(--cream);
        }
        .card-action-btn.btn-replace {
            background: var(--maroon);
            color: #fff;
            border-color: var(--maroon);
        }
        .card-action-btn.btn-replace:hover {
            background: var(--saffron-deep);
            border-color: var(--saffron-deep);
            color: #fff;
        }

        /* Modal Overlays (Themed) */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-overlay.show {
            display: flex;
        }
        .modal-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            width: 100%;
            max-width: 540px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 16px 40px rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            overflow: hidden;
            animation: modalFadeIn 0.25s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 2px solid var(--saffron-pale);
            background: var(--cream);
        }
        .modal-header h3 {
            margin: 0;
            color: var(--maroon);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 28px;
            color: var(--text-mid);
            cursor: pointer;
            line-height: 1;
            padding: 0 8px;
            border-radius: 4px;
            transition: var(--transition);
        }
        .modal-close-btn:hover {
            color: var(--red);
            background: rgba(220, 38, 38, 0.1);
        }
        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }
        .modal-footer {
            padding: 14px 20px;
            background: var(--cream);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-cancel {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-mid);
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .btn-cancel:hover {
            background: var(--white);
            color: var(--text-dark);
            border-color: var(--text-mid);
        }
        .btn-submit-action {
            background: var(--maroon);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-submit-action:hover {
            background: var(--saffron-deep);
        }

        /* Lightbox modal */
        .lightbox-modal {
            max-width: 900px;
            background: #111;
            border-color: #333;
        }
        .lightbox-modal .modal-header {
            background: #1a1a1a;
            border-bottom: 1px solid #333;
        }
        .lightbox-modal .modal-header h3 {
            color: var(--gold-light);
        }
        .lightbox-modal .modal-close-btn {
            color: #bbb;
        }
        .lightbox-img-wrap {
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000;
            max-height: 80vh;
            overflow: auto;
        }
        .lightbox-img-wrap img {
            max-width: 100%;
            max-height: 75vh;
            object-fit: contain;
            border-radius: 4px;
        }

        /* Floating Scroll to Top Button */
        .scroll-top-btn {
            position: fixed;
            bottom: 25px;
            right: 20px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--maroon);
            color: #fff;
            border: 2px solid var(--gold);
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

        /* Toast notifications container (matching committee-management) */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
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
        .admin-toast.success { background: linear-gradient(135deg, var(--maroon), var(--maroon-deep)); }
        .admin-toast.error   { background: linear-gradient(135deg, var(--red-dark, #8B0000), var(--red, #D32F2F)); }
        .admin-toast.info    { background: linear-gradient(135deg, var(--maroon-deep), var(--maroon)); }

        /* Mobile Screens */
        @media (max-width: 768px) {
            .page-container { padding: 0 14px; margin: 16px auto; }
            .page-header { flex-direction: column; align-items: stretch; text-align: center; gap: 12px; }
            .page-header .btn { width: 100%; justify-content: center; }
            .card { padding: 16px 14px; }
            .month-bar-sticky-wrap { top: 60px; padding: 6px 0; }
            .month-bar-row {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                gap: 6px;
                padding: 4px;
                scrollbar-width: none;
            }
            .month-bar-row::-webkit-scrollbar { display: none; }
            .month-btn {
                flex-shrink: 0;
                padding: 6px 14px;
                border-radius: 20px;
                font-size: 0.82rem;
                scroll-snap-align: center;
            }
            .all-months-grid { grid-template-columns: 1fr; gap: 16px; }
            #toast-container { right: 10px; left: 10px; top: 10px; }
        }
    </style>
</head>
<body>

    <!-- Standard Header Component -->
    <div data-include="components/header.php"></div>

    <!-- Toast Notifications Container -->
    <div id="toast-container"></div>

    <main class="page-container">

        <!-- Top Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="admin-dashboard.php" class="btn btn-secondary" style="width: auto;">
                    <i class="fas fa-arrow-left"></i> डैशबोर्ड पर वापस जाएं
                </a>
            </div>
            <div style="font-size: 0.9rem; color: var(--maroon);">
                <i class="fas fa-shield-alt"></i> दिनदर्शिका प्रबंधन प्रणाली
            </div>
        </div>

        <!-- Section Divider -->
        <div class="section-divider">
            <h2><i class="fas fa-calendar-alt"></i> सनातन दिनदर्शिका प्रबंधन</h2>
        </div>

        <!-- 1. Page Info & Quick Action Card -->
        <section class="card">
            <h3><i class="fas fa-om"></i> सनातन दिनदर्शिका पृष्ठ प्रबंधन</h3>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 16px;">
                <p style="color: var(--text-mid); margin: 0; font-size: 0.95rem;">
                    यहाँ से आप मुखपृष्ठ एवं समस्त १२ माह के पृष्ठों की इमेज सीधे बदल सकते हैं। प्रत्येक बदलाव तुरंत वेबसाइट पर लाइव हो जाता है।
                </p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-primary" id="btn-open-pdf-modal" style="width: auto; padding: 10px 18px; min-height: 42px; font-size: 0.9rem;">
                        <i class="fas fa-cloud-upload-alt"></i> नई PDF अपलोड करें
                    </button>
                    <?php if ($hasPdf): ?>
                    <a href="<?= $calendarPdfPath ?>" download="सनातन_दिनदर्शिका.pdf" class="btn btn-secondary" style="width: auto; padding: 10px 18px; min-height: 42px; font-size: 0.9rem;">
                        <i class="fas fa-download"></i> PDF डाउनलोड (<?= $pdfSize ?>)
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sticky Month Navigation Bar -->
            <div class="month-bar-sticky-wrap">
                <div class="month-bar-row">
                    <a href="#admin-month-card-cover" class="month-btn">मुखपृष्ठ</a>
                    <?php foreach ($months as $num => $m): ?>
                        <a href="#admin-month-card-<?= $num ?>" class="month-btn">
                            <?= $num ?>. <?= $m['hi'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- All 12 Months & Cover Grid -->
            <div class="all-months-grid">

                <!-- 1. COVER PAGE CARD -->
                <?php 
                    $coverWebp = 'uploads/calendar/cover.webp';
                    $coverSrc = file_exists(__DIR__ . '/' . $coverWebp) ? $coverWebp : 'uploads/calendar/cover.png';
                ?>
                <div class="month-row-card" id="admin-month-card-cover">
                    <div class="month-card-header">
                        <h3>मुखपृष्ठ (Cover Page)</h3>
                        <span>सनातन दिनदर्शिका</span>
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
                            <span><?= $m['en'] ?></span>
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
        </section>

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
                    <p style="font-size:0.9rem; color:var(--text-mid); margin-bottom:14px;">
                        <strong id="replace-target-label" style="color:var(--maroon);"></strong> के लिए नई इमेज चुनें। फॉर्मेट: JPG, PNG, WEBP.
                    </p>

                    <!-- Preview of currently selected image -->
                    <div style="text-align:center; margin-bottom:16px;">
                        <div style="max-height:240px; overflow:hidden; border-radius:8px; border:1px solid var(--border); background:var(--cream); display:inline-block; padding:6px;">
                            <img id="replace-preview-img" src="" alt="पूर्वावलोकन" style="max-height:220px; max-width:100%; object-fit:contain; display:block;">
                        </div>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label for="replace-file-input" style="display:block; font-size:0.9rem; font-weight:600; color:var(--maroon); margin-bottom:6px;">
                            <i class="fas fa-upload" style="color:var(--saffron);"></i> नई इमेज फ़ाइल चुनें:
                        </label>
                        <input type="file" id="replace-file-input" name="page_image" accept="image/jpeg,image/png,image/webp" required style="width:100%; padding:10px; border:1.5px solid var(--border); border-radius:6px; background:#fff;">
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
                <div style="display:flex; align-items:center; gap:16px; padding:12px; background:var(--gold-pale); border-radius:8px; border:1px solid var(--border); margin-bottom:18px;">
                    <i class="fas fa-file-pdf" style="font-size:2.4rem; color:var(--red, #D32F2F);"></i>
                    <div>
                        <strong style="color:var(--maroon); font-size:1rem;">uploads/calendar/calendar.pdf</strong>
                        <div style="font-size:0.85rem; color:var(--text-mid); margin-top:2px;">
                            स्थिति: <strong><?= $hasPdf ? 'सक्रिय फ़ाइल मौजूद (' . $pdfSize . ')' : 'फ़ाइल उपलब्ध नहीं है' ?></strong>
                        </div>
                    </div>
                </div>

                <!-- PDF Upload Form -->
                <form id="pdf-upload-form" enctype="multipart/form-data">
                    <label style="display:block; font-size:0.9rem; font-weight:600; color:var(--maroon); margin-bottom:6px;">
                        <i class="fas fa-upload" style="color:var(--saffron);"></i> नई PDF फ़ाइल अपलोड करें (.pdf):
                    </label>
                    <input type="file" id="pdf-file-input" name="calendar_pdf" accept="application/pdf" required style="width:100%; padding:10px; border:1.5px solid var(--border); border-radius:6px; background:#fff; margin-bottom:12px;">
                    <button type="submit" class="btn-submit-action" id="btn-submit-pdf-upload" style="width:100%; justify-content:center; padding:12px;">
                        <i class="fas fa-cloud-upload-alt"></i> नई PDF अपलोड करें एवं स्वतः १२ पृष्ठ निकालें
                    </button>
                </form>

                <hr style="border:none; border-top:1px solid var(--border); margin:20px 0;">

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btn-cancel" id="btn-reextract-pdf" style="flex:1; min-width:200px; border-color:var(--maroon); color:var(--maroon);">
                        <i class="fas fa-sync-alt"></i> वर्तमान PDF से १२ माह पुनः निकालें
                    </button>
                    <?php if ($hasPdf): ?>
                    <a href="<?= $calendarPdfPath ?>" download="सनातन_दिनदर्शिका.pdf" class="btn-cancel" style="flex:1; min-width:140px; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
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

    <!-- Standard Footer Component -->
    <div data-include="components/footer.php"></div>

    <!-- Scripts -->
    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/calendar-management.js?v=<?= @filemtime(__DIR__.'/js/calendar-management.js') ?: time()?>"></script>
</body>
</html>
