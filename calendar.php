<?php
session_start();
$calendarPdfPath = 'uploads/calendar/calendar.pdf';
$hasPdf = file_exists(__DIR__ . '/' . $calendarPdfPath);
$targetMonth = isset($_GET['month']) ? trim((string)$_GET['month']) : '';

$months = [
    1  => ['hi' => 'जानेवारी', 'en' => '', 'hi_num' => '१'],
    2  => ['hi' => 'फेब्रुवारी', 'en' => '', 'hi_num' => '२'],
    3  => ['hi' => 'मार्च', 'en' => '', 'hi_num' => '३'],
    4  => ['hi' => 'एप्रिल', 'en' => '', 'hi_num' => '४'],
    5  => ['hi' => 'मे', 'en' => '', 'hi_num' => '५'],
    6  => ['hi' => 'जून', 'en' => '', 'hi_num' => '६'],
    7  => ['hi' => 'जुलै', 'en' => '', 'hi_num' => '७'],
    8  => ['hi' => 'ऑगस्ट', 'en' => '', 'hi_num' => '८'],
    9  => ['hi' => 'सप्टेंबर', 'en' => '', 'hi_num' => '९'],
    10 => ['hi' => 'ऑक्टोबर', 'en' => '', 'hi_num' => '१०'],
    11 => ['hi' => 'नोव्हेंबर', 'en' => '', 'hi_num' => '११'],
    12 => ['hi' => 'डिसेंबर', 'en' => '', 'hi_num' => '१२'],
];

// Pre-build all 13 calendar pages for grid & interactive lightbox viewer
$coverSrc = 'uploads/calendar/cover.webp';
if (!file_exists(__DIR__ . '/' . $coverSrc)) $coverSrc = 'uploads/calendar/cover.jpg';
if (!file_exists(__DIR__ . '/' . $coverSrc)) $coverSrc = 'uploads/calendar/cover.png';

$calendarPages = [
    [
        'key'      => 'cover',
        'title'    => 'सनातन दिनदर्शिका (मुखपृष्ठ)',
        'sub'      => 'छःन्यात राजस्थानी विप्र मंडल, लातूर',
        'short'    => 'मुखपृष्ठ',
        'src'      => $coverSrc,
        'download' => 'सनातन_दिनदर्शिका_मुखपृष्ठ.webp',
    ]
];

foreach ($months as $num => $m) {
    $pSrc = "uploads/calendar/pages/{$num}.webp";
    if (!file_exists(__DIR__ . '/' . $pSrc)) $pSrc = "uploads/calendar/pages/{$num}.jpg";
    if (!file_exists(__DIR__ . '/' . $pSrc)) $pSrc = "uploads/calendar/pages/{$num}.png";

    $calendarPages[] = [
        'key'      => (string)$num,
        'title'    => "{$num}. {$m['hi']}",
        'sub'      => "{$m['en']}",
        'short'    => "{$num}. {$m['hi']}",
        'src'      => $pSrc,
        'download' => "सनातन_दिनदर्शिका_{$m['hi']}.webp",
    ];
}
?>
<!DOCTYPE html>
<html lang="hi" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#7B1A1A">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">

    <title>सनातन दिनदर्शिका (समस्त १२ माह) | छःन्यात राजस्थानी विप्र मंडल, लातूर</title>
    <meta name="description" content="छःन्यात राजस्थानी विप्र मंडल लातूर — सनातन दिनदर्शिका (समस्त १२ माह के पृष्ठ)">

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
            --cal-saffron: #FF6B00;
            --cal-saffron-dark: #e65100;
            --cal-bg: #f7f4ed;
            --sticky-offset: 140px;  /* height of sticky bar + breathing room */
        }

        body {
            background-color: var(--cal-bg);
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans Devanagari', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        .cal-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 14px 60px;
        }

        /* Both site-header and month bar scroll naturally with the page */
        .site-header {
            position: relative !important;
            top: auto !important;
        }

        .month-bar-sticky-wrap {
            position: relative;
            z-index: 10;
            background: rgba(247, 244, 237, 0.98);
            padding: 8px 0 10px;
            margin-bottom: 20px;
            border-bottom: 1.5px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        }

        .month-bar-row {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            gap: 8px;
            padding: 4px 2px 6px;
            scrollbar-width: none;
        }
        .month-bar-row::-webkit-scrollbar {
            display: none;
        }

        .month-btn {
            background: #fff;
            border: 1.5px solid var(--cal-gold);
            color: var(--cal-maroon);
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            user-select: none;
            flex-shrink: 0;
            scroll-snap-align: center;
        }

        .month-btn:hover {
            background: #fff3e6;
            color: var(--cal-saffron);
            border-color: var(--cal-saffron);
            transform: translateY(-1px);
        }

        .month-btn.active {
            background: var(--cal-maroon);
            color: #fff;
            border-color: var(--cal-maroon);
            box-shadow: 0 3px 10px rgba(123, 26, 26, 0.3);
        }

        @media (min-width: 1200px) {
            .month-bar-row {
                display: grid;
                grid-template-columns: repeat(13, 1fr);
                gap: 5px;
                overflow-x: visible;
                padding: 0;
            }
            .month-btn {
                padding: 8px 2px;
                border-radius: 8px;
                font-size: 0.85rem;
                flex-shrink: 1;
            }
        }

        /* Control / Header Bar */
    .cal-header-bar {
    width: 100%;
    max-width: none;
    box-sizing: border-box;

    background: #fff;
    padding: 12px 18px;
    border-radius: 12px;
    margin: 0 0 22px;

    border: 1px solid rgba(201, 148, 26, 0.3);

    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;

    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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

        .btn-pdf {
            background: var(--cal-maroon);
            color: #fff;
            border: 1px solid var(--cal-maroon);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-pdf:hover {
            background: var(--cal-maroon-dark);
            box-shadow: 0 3px 10px rgba(123, 26, 26, 0.25);
        }

        /* All 12 Months Grid */
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
            margin: 6px 4px 18px;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
        }

        @keyframes cardPulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 0, 0.75); border-color: var(--cal-saffron); }
            50% { box-shadow: 0 0 26px 8px rgba(255, 107, 0, 0.5); transform: scale(1.015); border-color: var(--cal-saffron); }
            100% { box-shadow: 0 4px 15px rgba(0,0,0,0.06); border-color: var(--cal-gold); transform: scale(1); }
        }
        .month-row-card.highlight-pulse {
            animation: cardPulseGlow 1.8s ease-in-out;
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
            opacity: 0.85;
        }

        .month-card-img-wrap {
            padding: 6px;
            background: #fafafa;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .month-card-img-wrap a {
            display: block;
            width: 100%;
        }

        .month-card-img-wrap img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 6px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }

        .month-card-img-wrap:hover img {
            transform: scale(1.015);
        }

        .month-card-actions {
            padding: 9px 12px;
            background: #fff;
            border-top: 1px solid #f0f0f0;
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .card-action-btn {
            flex: 1;
            padding: 8px 10px;
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
            background: #fdf6ec;
            color: var(--cal-maroon);
            transition: all 0.2s;
            min-height: 38px;
        }

        .card-action-btn:hover {
            background: var(--cal-maroon);
            color: #fff;
        }

        .card-action-btn.btn-dl {
            background: var(--cal-maroon);
            color: #fff;
            border-color: var(--cal-saffron);
        }
        .card-action-btn.btn-dl:hover {
            background: var(--cal-saffron-dark);
        }

        /* Floating Scroll-to-Top Button */
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
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        .scroll-top-btn:active {
            transform: scale(0.92);
        }

        /* Mobile Screens */
        @media (max-width: 768px) {
            .cal-container {
                padding: 10px 12px 60px;
            }

            .month-bar-sticky-wrap {
                padding: 6px 0 8px;
                margin-bottom: 14px;
            }

            .month-btn {
                padding: 7px 14px;
                font-size: 0.85rem;
            }

        .cal-header-bar {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 14px;

    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    gap: 10px;

    margin-bottom: 14px;
}

            .cal-main-title {
                font-size: 1.15rem;
                justify-content: center;
            }

            .all-months-grid {
                grid-template-columns: 1fr;
            
            }

            .month-row-card {
                margin: 8px 12px 24px;
            }

            .month-card-header h3 {
                font-size: 1rem;
            }

            .month-card-img-wrap {
                padding: 4px;
            }

            .scroll-top-btn {
                bottom: 20px;
                right: 15px;
            }
        }

        /* ══════════════════════════════════════════════════════════════
           MINIMAL FULLSCREEN CALENDAR LIGHTBOX (ONLY ARROWS & IMAGE)
        ══════════════════════════════════════════════════════════════ */
        .cal-lightbox-overlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(8, 6, 6, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.22s ease;
            user-select: none;
        }
        .cal-lightbox-overlay.active {
            display: flex;
            opacity: 1;
        }

        /* Floating Close Button */
        .cal-lb-close-btn {
            position: absolute;
            top: 20px;
            right: 22px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 25;
            transition: all 0.2s ease;
            backdrop-filter: blur(8px);
        }
        .cal-lb-close-btn:hover {
            background: #d32f2f;
            border-color: #ef5350;
            transform: scale(1.1);
        }

        /* Floating Zoom Controls Bar */
        .cal-lb-zoom-bar {
            position: absolute;
            top: 20px;
            left: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 25;
        }
        .cal-lb-zoom-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(8px);
        }
        .cal-lb-zoom-btn:hover {
            background: var(--cal-maroon);
            border-color: var(--cal-gold);
            transform: scale(1.1);
            color: #fff;
        }
        .cal-lb-zoom-btn:active {
            transform: scale(0.95);
        }

        /* Image Stage */
        .cal-lightbox-stage {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow: hidden;
            touch-action: none;
        }

        .cal-lightbox-img {
            max-width: 95vw;
            max-height: 95vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.85);
            transition: transform 0.15s ease-out, opacity 0.18s ease;
            transform-origin: center center;
            cursor: zoom-in;
            user-select: none;
            -webkit-user-drag: none;
        }
        .cal-lightbox-img.zoomed {
            cursor: grab;
        }
        .cal-lightbox-img.dragging {
            cursor: grabbing;
            transition: none !important;
        }
        .cal-lightbox-img.switching {
            opacity: 0.15;
            transform: scale(0.97);
        }

        /* Floating Prev / Next Navigation Arrows */
        .cal-lb-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: rgba(32, 18, 18, 0.8);
            border: 2px solid var(--cal-gold);
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 20;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.6);
        }
        .cal-lb-nav:hover {
            background: var(--cal-maroon);
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.75);
        }
        .cal-lb-nav:active {
            transform: translateY(-50%) scale(0.95);
        }
        .cal-lb-nav.prev { left: 24px; }
        .cal-lb-nav.next { right: 24px; }

        @media (max-width: 768px) {
            .cal-lb-close-btn {
                top: 12px;
                right: 12px;
                width: 38px;
                height: 38px;
                font-size: 1.2rem;
            }
            .cal-lb-zoom-bar {
                top: 12px;
                left: 12px;
                gap: 6px;
            }
            .cal-lb-zoom-btn {
                width: 38px;
                height: 38px;
                font-size: 1rem;
            }
            .cal-lb-nav {
                width: 46px;
                height: 46px;
                font-size: 1.25rem;
                background: rgba(25, 12, 12, 0.7);
            }
            .cal-lb-nav.prev { left: 10px; }
            .cal-lb-nav.next { right: 10px; }
            .cal-lightbox-stage { padding: 8px; }
            .cal-lightbox-img {
                max-width: 96vw;
                max-height: 94vh;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/header.php'; ?>
<div class="container" style="padding-top: 15px;">
<div class="gallery-hero">
                <div class="gallery-hero-content">
                                <h2>सनातन दिनदर्शिका</h2>
                    <p class="gallery-hero-subtitle" id="gallery-total-info"></p>
                </div>
            </div>
    </div>
        <!-- Header Bar -->
        <div class="cal-header-bar">
            <div>
                
            </div>

           <?php if ($hasPdf): ?>
    <a href="<?= htmlspecialchars($calendarPdfPath, ENT_QUOTES, 'UTF-8') ?>"
       download="सनातन_दिनदर्शिका.pdf"
       class="btn-pdf">
        <i class="fas fa-file-pdf"></i>
        सम्पूर्ण PDF डाउनलोड
    </a>
<?php endif; ?>
        </div>

        <!-- Sticky Month Bar: Desktop (All 12 in 1 Row) & Mobile (Swipeable Pills) -->
        <div class="month-bar-sticky-wrap" id="month-bar-sticky-wrap">
            <div class="month-bar-row" id="month-bar-row">
                <a href="?month=cover" class="month-btn" data-month="cover" id="tab-month-cover">
                    मुखपृष्ठ
                </a>
                <?php foreach ($months as $num => $m): ?>
                    <a href="?month=<?= $num ?>" class="month-btn" data-month="<?= $num ?>" id="tab-month-<?= $num ?>">
                        <?= $m['hi_num'] ?>. <?= $m['hi'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- All 12 Months In Rows -->
        <div class="all-months-grid" id="all-months-grid">
            <!-- Official Cover Page Card -->
            <?php $coverPage = $calendarPages[0]; ?>
            <div class="month-row-card" id="month-card-cover">
                <div class="month-card-header">
                    <h3>मुखपृष्ठ (Cover Page)</h3>
                    <span>सनातन दिनदर्शिका</span>
                </div>
                <div class="month-card-img-wrap">
                    <a href="<?= $coverPage['src'] ?>" class="cal-lb-trigger" data-lb-index="0" title="क्लिक कर पूरा देखें">
                        <img src="<?= $coverPage['src'] ?>" alt="सनातन दिनदर्शिका मुखपृष्ठ"
                             <?= ($targetMonth === 'cover') ? '' : 'loading="lazy"' ?>>
                    </a>
                </div>
                <div class="month-card-actions">
                    <a href="<?= $coverPage['src'] ?>" class="card-action-btn cal-lb-trigger" data-lb-index="0">
                        <i class="fas fa-expand"></i> पूरा देखें
                    </a>
                    <a href="<?= $coverPage['src'] ?>" download="<?= $coverPage['download'] ?>" class="card-action-btn btn-dl">
                        <i class="fas fa-download"></i> डाउनलोड
                    </a>
                </div>
            </div>

            <?php foreach ($months as $num => $m): 
                $page = $calendarPages[$num];
            ?>
                <div class="month-row-card" id="month-card-<?= $num ?>">
                    <div class="month-card-header">
                        <h3><?= $page['title'] ?></h3>
                        <?php if (!empty($m['en'])): ?><span><?= htmlspecialchars($m['en']) ?></span><?php endif; ?>
                    </div>
                    <div class="month-card-img-wrap">
                        <a href="<?= $page['src'] ?>" class="cal-lb-trigger" data-lb-index="<?= $num ?>" title="क्लिक कर पूरा देखें">
                            <img src="<?= $page['src'] ?>" alt="सनातन दिनदर्शिका - <?= $m['hi'] ?> 2026"
                                 <?= ((string)$num === (string)$targetMonth) ? '' : 'loading="lazy"' ?>>
                        </a>
                    </div>
                    <div class="month-card-actions">
                        <a href="<?= $page['src'] ?>" class="card-action-btn cal-lb-trigger" data-lb-index="<?= $num ?>">
                            <i class="fas fa-expand"></i> पूरा देखें
                        </a>
                        <a href="<?= $page['src'] ?>" download="<?= $page['download'] ?>" class="card-action-btn btn-dl">
                            <i class="fas fa-download"></i> डाउनलोड
                        </a>
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
         MINIMAL FULLSCREEN LIGHTBOX (ONLY ARROWS & IMAGE)
    ═══════════════════════════════════════════════════════════ -->
    <div class="cal-lightbox-overlay" id="cal-lightbox" role="dialog" aria-modal="true" aria-label="दिनदर्शिका दर्शक">
        <!-- Floating Close Button -->
        <button type="button" id="cal-lightbox-close" class="cal-lb-close-btn" title="बंद करें (Esc)" aria-label="बंद करें">
            <i class="fas fa-times"></i>
        </button>

        <!-- Floating Zoom Controls -->
        <div class="cal-lb-zoom-bar">
            <button type="button" id="cal-lb-zoom-in" class="cal-lb-zoom-btn" title="ज़ूम इन (+)" aria-label="ज़ूम इन">
                <i class="fas fa-search-plus"></i>
            </button>
            <button type="button" id="cal-lb-zoom-out" class="cal-lb-zoom-btn" title="ज़ूम आउट (-)" aria-label="ज़ूम आउट">
                <i class="fas fa-search-minus"></i>
            </button>
            <button type="button" id="cal-lb-zoom-reset" class="cal-lb-zoom-btn" title="रीसेट (1:1)" aria-label="रीसेट">
                <i class="fas fa-compress-arrows-alt"></i>
            </button>
        </div>

        <!-- Previous Arrow Button -->
        <button type="button" class="cal-lb-nav prev" id="cal-lightbox-prev" title="पिछला महीना (←)" aria-label="पिछला महीना">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Fullscreen Image Stage -->
        <div class="cal-lightbox-stage" id="cal-lightbox-stage">
            <img id="cal-lightbox-img" class="cal-lightbox-img" src="" alt="दिनदर्शिका पृष्ठ">
        </div>

        <!-- Next Arrow Button -->
        <button type="button" class="cal-lb-nav next" id="cal-lightbox-next" title="अगला महीना (→)" aria-label="अगला महीना">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Embedding Calendar Pages Data for JS Controller -->
    <script>
        window.CALENDAR_PAGES = <?= json_encode($calendarPages, JSON_UNESCAPED_UNICODE) ?>;
    </script>

    <?php include __DIR__ . '/components/footer.php'; ?>

    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/calendar.js?v=<?= @filemtime(__DIR__.'/js/calendar.js') ?: time()?>"></script>
</body>
</html>
