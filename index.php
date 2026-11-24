<?php
// 1. Hero slide image: sort by modified date DESC to match api/get_gallery.php
$galleryDir = __DIR__ . '/gallerys';
$firstHeroImage = 'gallerys/' . rawurlencode('गायत्री माता.webp');
$firstHeroTitle = 'गायत्री माता';
if (is_dir($galleryDir)) {
    $images = [];
    $files = scandir($galleryDir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..' && preg_match('/\.(webp|jpg|jpeg|png)$/i', $f)) {
            $images[] = [
                'file' => $f,
                'mtime' => filemtime($galleryDir . '/' . $f),
            ];
        }
    }
    if (!empty($images)) {
        usort($images, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });
        $firstHeroImage = 'gallerys/' . rawurlencode($images[0]['file']);
        $firstHeroTitle = htmlspecialchars(pathinfo($images[0]['file'], PATHINFO_FILENAME), ENT_QUOTES, 'UTF-8');
    }
}

// 2. Calendar PDF file size
$calendarPdfPath = __DIR__ . '/uploads/calendar/calendar.pdf';
$calendarPdfSize = '';
if (file_exists($calendarPdfPath)) {
    $bytes = filesize($calendarPdfPath);
    if ($bytes >= 1048576) {
        $calendarPdfSize = round($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        $calendarPdfSize = round($bytes / 1024, 0) . ' KB';
    }
}

// 3. Database queries for Server-Side Rendering (SSR)
$ssrBranches = [];
$ssrTotalFamilies = '—';
$ssrTotalMembers = '—';
$ssrAnnouncements = [];
$ssrCommittee = [];

$suppress_db_die = true;
@require_once __DIR__ . '/api/db.php';

if (!empty($pdo)) {
    try {
        $branchStmt = $pdo->query("SELECT * FROM branches ORDER BY id ASC");
        $ssrBranches = $branchStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    try {
        $famCountStmt = $pdo->query("SELECT COUNT(*) FROM families");
        $famCount = (int)$famCountStmt->fetchColumn();
        if ($famCount > 0) {
            $ssrTotalFamilies = number_format($famCount, 0, '.', ',');
        }

        $memStmt = $pdo->query("SELECT members FROM families");
        $totalMems = 0;
        while ($row = $memStmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['members'])) {
                $mList = is_string($row['members']) ? json_decode($row['members'], true) : $row['members'];
                if (is_array($mList)) {
                    $totalMems += count($mList);
                }
            }
        }
        if ($totalMems > 0) {
            $ssrTotalMembers = number_format($totalMems, 0, '.', ',');
        }
    } catch (Exception $e) {}

    try {
        $annStmt = $pdo->query("SELECT * FROM announcements ORDER BY createdAt DESC, id DESC LIMIT 4");
        $ssrAnnouncements = $annStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    try {
        $commStmt = $pdo->query("SELECT * FROM committee ORDER BY display_order ASC, id ASC LIMIT 12");
        $ssrCommittee = $commStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

$cssPath = __DIR__ . '/css/style.css';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : '1.0';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#7B1A1A">
    <title>छःन्यात राजस्थानी विप्र मंडल, लातूर | Chhanyat Rajasthani Vipra Mandal Latur</title>
    <meta name="description" content="छःन्यात (छन्यात) राजस्थानी विप्र मंडल, लातूर का आधिकारिक वेब पोर्टल। लातूर विप्र समाज के समस्त परिवारों, सनातन दिनदर्शिका, समिति व आयोजनों की सम्पूर्ण जानकारी।">

    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/images/logo.webp">
    <link rel="canonical" href="https://chhanyatvipralatur.com/">

    <!-- Preconnect to Google Fonts and external origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="image" href="<?= $firstHeroImage ?>" type="image/webp" fetchpriority="high">

    <!-- Google Fonts loaded asynchronously to eliminate render blocking -->
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap" rel="stylesheet"></noscript>

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "https://chhanyatvipralatur.com/#organization",
          "name": "छःन्यात राजस्थानी विप्र मंडल, लातूर",
          "alternateName": ["Chhanyat Rajasthani Vipra Mandal Latur", "छन्यात विप्र समाज लातूर", "Chhanyat Vipra Samaj Latur"],
          "url": "https://chhanyatvipralatur.com/",
          "logo": {
            "@type": "ImageObject",
            "url": "https://chhanyatvipralatur.com/images/logo.webp",
            "width": "200",
            "height": "200"
          },
          "address": {
            "@type": "PostalAddress",
            "addressLocality": "Latur",
            "addressRegion": "Maharashtra",
            "addressCountry": "IN"
          },
          "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "General Inquiries",
            "availableLanguage": ["Hindi", "Marathi", "English"]
          }
        },
        {
          "@type": "WebSite",
          "@id": "https://chhanyatvipralatur.com/#website",
          "url": "https://chhanyatvipralatur.com/",
          "name": "छःन्यात राजस्थानी विप्र मंडल, लातूर",
          "publisher": {
            "@id": "https://chhanyatvipralatur.com/#organization"
          },
          "inLanguage": "hi-IN"
        }
      ]
    }
    </script>
    
    <!-- Open Graph Metadata -->
    <meta property="og:locale" content="hi_IN">
    <meta property="og:site_name" content="छःन्यात राजस्थानी विप्र मंडल, लातूर">
    <meta property="og:title" content="छःन्यात राजस्थानी विप्र मंडल, लातूर | Chhanyat Rajasthani Vipra Mandal Latur">
    <meta property="og:description" content="छःन्यात (छन्यात) राजस्थानी विप्र मंडल, लातूर का आधिकारिक वेब पोर्टल। लातूर विप्र समाज के समस्त परिवारों, सनातन दिनदर्शिका, समिति व आयोजनों की सम्पूर्ण जानकारी।">
    <meta property="og:url" content="https://chhanyatvipralatur.com/">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://chhanyatvipralatur.com/<?= $firstHeroImage ?>">
    <meta property="og:image:alt" content="छःन्यात राजस्थानी विप्र मंडल, लातूर">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="छःन्यात राजस्थानी विप्र मंडल, लातूर">
    <meta name="twitter:description" content="छःन्यात (छन्यात) राजस्थानी विप्र मंडल, लातूर का आधिकारिक वेब पोर्टल।">
    <meta name="twitter:image" content="https://chhanyatvipralatur.com/<?= $firstHeroImage ?>">

    <!-- Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-DE5KD051LL"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-DE5KD051LL');
    </script>
    <script src="https://cdn.counter.dev/script.js" data-id="1f92108b-8857-410e-b5a8-c1ab33e859f8" data-utcoffset="5.5" defer></script>
  
    <link rel="stylesheet" href="css/style.css?v=<?= $cssVersion ?>">
</head>
<body>
    <a href="#main-content" class="skip-link">मुख्य सामग्री पर जाएं</a>
    
    <?php include __DIR__ . '/components/header.php'; ?>

    <main id="main-content">
        <!-- ═══ SCREEN-READER PRIMARY HEADING (H1) ═══ -->
        <h1 class="sr-only">छःन्यात राजस्थानी विप्र मंडल, लातूर</h1>

        <!-- ═══ PROFESSIONAL HERO SLIDER ═══ -->
        <section class="hero-slider" aria-label="इमेज स्लाइडर" aria-roledescription="carousel">
            <div class="slider-wrapper">
                <!-- Loading & Error States -->
                <div class="slider-state slider-loading" id="slider-loading" role="status" style="display: none;">
                    <div class="spinner"></div>
                    <span>छवियाँ लोड हो रही हैं...</span>
                </div>
                
                <div class="slider-state slider-error" id="slider-error" role="alert" style="display: none;">
                    <h3>⚠️ छवियाँ लोड नहीं हो सकीं</h3>
                    <p id="error-message">कृपया बाद में पुनः प्रयास करें</p>
                    <button id="slider-retry-btn">पुनः प्रयास करें</button>
                </div>
                
                <!-- Slider Track (Pre-rendered first hero slide for instant LCP) -->
                <div class="slider-track" id="slider-track">
                    <div class="slide active" role="group" aria-roledescription="slide" aria-label="स्लाइड 1">
                        <div class="slide-picture">
                            <img src="<?= $firstHeroImage ?>" alt="<?= $firstHeroTitle ?>" width="768" height="428" loading="eager" fetchpriority="high" decoding="async">
                        </div>
                        <div class="slide-gradient"></div>
                        <div class="slide-caption">
                            <div class="hero-caption-title"><?= $firstHeroTitle ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Arrows & Controls -->
                <button class="slider-nav slider-nav-prev" id="slider-prev-btn" aria-label="पिछली स्लाइड">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
                <button class="slider-nav slider-nav-pause" id="slider-pause-btn" aria-label="स्लाइडर रोकें" title="स्लाइडर रोकें/चलाएं">
                    <svg id="slider-pause-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" width="20" height="20" fill="currentColor">
                        <rect x="6" y="4" width="4" height="16" rx="1"></rect>
                        <rect x="14" y="4" width="4" height="16" rx="1"></rect>
                    </svg>
                    <svg id="slider-play-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" width="20" height="20" fill="currentColor" style="display:none;">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                </button>
                <button class="slider-nav slider-nav-next" id="slider-next-btn" aria-label="अगली स्लाइड">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
                
                <!-- Progress Bar directly on the image edge -->
                <div class="slider-progress-bar">
                    <div class="slider-progress" id="slider-progress"></div>
                </div>
            </div>
        </section>

        <section class="samaj-instruction-section">
            <p class="samaj-description">
                अपने परिवार की जानकारी देखने के लिए नीचे दिए गए परिवारों में से किसी एक परिवार का चयन करें।
            </p>
        </section>

        <!-- ═══ BRANCHES MARQUEE ═══ -->
        <section class="branches-marquee" role="region" aria-label="परिवारों की सूची">
            <div class="marquee-fade marquee-fade-left"></div>
            <div class="marquee-fade marquee-fade-right"></div>

            <div class="marquee-label">
                <span>परिवार चुने</span>
            </div>

            <div class="marquee-track" id="branches-marquee-track">
                <?php if (!empty($ssrBranches)): ?>
                    <?php 
                    // Duplicate for continuous seamless marquee display
                    $loopBranches = array_merge($ssrBranches, $ssrBranches);
                    foreach ($loopBranches as $branch): 
                        $bName = htmlspecialchars($branch['branchName'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bUrl = urlencode($branch['branchName'] ?? '');
                    ?>
                        <a href="branch.php?branch=<?= $bUrl ?>" 
                           class="marquee-item" 
                           title="<?= $bName ?> परिवार देखें"
                           data-branch="<?= $bName ?>">
                            <span class="branch-name"><?= $bName ?></span>
                        </a>
                        <span class="marquee-separator" aria-hidden="true">✦</span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- ═══ STATISTICS ═══ -->
        <section class="stats-bg" aria-labelledby="stats-title">
            <h2 id="stats-title" class="sr-only">सांख्यिकी</h2>
            <div class="container">
                <div class="stats-grid" role="list">
                    <div class="stat-card" role="listitem">
                        <h3 id="total-families" aria-live="polite"><?= $ssrTotalFamilies ?></h3>
                        <p>कुल परिवार</p>
                        <div class="stat-sublabel">Total Families</div>
                    </div>
                    <div class="stat-card" role="listitem">
                        <h3 id="total-members" aria-live="polite"><?= $ssrTotalMembers ?></h3>
                        <p>कुल सदस्य</p>
                        <div class="stat-sublabel">Total Members</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ AD POSITION 1: ABOVE सनातन दिनदर्शिका ═══ -->
        <section class="promotions-outer-section ad-outer-section" id="ads-pos-above-calendar" aria-label="स्थानीय विज्ञापन - दिनदर्शिका" style="display: none;">
            <div class="container">
                <div class="promotions-section ad-section">
                    <div class="promotions-header-row ad-header-row">
                        <div class="promotions-badge ad-badge">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--saffron);"><path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path></svg>
                            <span>प्रायोजक एवं विज्ञापन</span>
                        </div>
                        <span class="promotions-disclaimer ad-disclaimer">विज्ञापन</span>
                    </div>
                    <div class="promotions-grid ad-grid" id="ads-grid-above-calendar" role="list">
                        <!-- Populated dynamically via home.js -->
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ SANATAN DINDARSHIKA SHOWCASE ═══ -->
        <section class="container" style="padding: 30px 16px 20px;" aria-labelledby="events-title">
            <div class="section-header">
                <div class="section-eyebrow">वार्षिक दिनदर्शिका</div>
                <h2 class="section-title" id="events-title">सनातन दिनदर्शिका</h2>
                <div class="ornament" aria-hidden="true"><span class="ornament-icon">❧</span></div>
                <p class="section-desc" style="margin-top:10px;">छःन्यात राजस्थानी विप्र मंडल, लातूर — समस्त १२ माह के पृष्ठ ऑनलाइन देखें व डाउनलोड करें</p>
            </div>

            <div class="home-cal-showcase">
                <div class="home-cal-preview-card">
                    <div class="home-cal-img-wrap">
                        <a href="calendar.php?month=cover" title="सनातन दिनदर्शिका मुखपृष्ठ देखें" class="home-cal-img-link">
                            <img id="home-cal-preview-img" src="uploads/calendar/thumbs/cover.webp?v=<?= @filemtime(__DIR__ . '/uploads/calendar/thumbs/cover.webp') ?: time() ?>" alt="सनातन दिनदर्शिका - मुखपृष्ठ" width="340" height="511" loading="lazy">
                            <span class="home-cal-img-zoom-hint">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="M15 3h6v6"></path><path d="M10 14L21 3"></path><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path></svg>
                                बड़ा देखें
                            </span>
                        </a>
                    </div>
                    <div class="home-cal-info">
                        <div class="home-cal-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span>सनातन दिनदर्शिका २०२६-२७</span>
                        </div>
                        <h3 class="home-cal-title" id="home-cal-month-title">सनातन दिनदर्शिका (मुखपृष्ठ)</h3>
                        <p class="home-cal-text">
                            छःन्यात राजस्थानी विप्र मंडल, लातूर की आधिकारिक वार्षिक सनातन दिनदर्शिका। समस्त १२ माह के पृष्ठ, पावन पर्व, व्रत, एकादशी एवं कुलप्रवर्तक जयंतियां अब डिजिटल रूप में उपलब्ध हैं।
                        </p>
                        
                        <!-- 12 Month Quick Jump Links -->
                        <div class="home-cal-months-label">
                            <span>माहवार पृष्ठ (सीधे देखें)</span>
                        </div>
                        <div class="home-cal-months-strip" id="home-cal-months-strip">
                            <a href="calendar.php?month=cover" class="home-month-pill" data-month="cover">मुखपृष्ठ</a>
                            <a href="calendar.php?month=samiti" class="home-month-pill" data-month="samiti">समिति</a>
                            <a href="calendar.php?month=11" class="home-month-pill" data-month="11">११. नोव्हेंबर</a>
                            <a href="calendar.php?month=12" class="home-month-pill" data-month="12">१२. डिसेंबर</a>
                            <a href="calendar.php?month=1" class="home-month-pill" data-month="1">१. जानेवारी</a>
                            <a href="calendar.php?month=2" class="home-month-pill" data-month="2">२. फेब्रुवारी</a>
                            <a href="calendar.php?month=3" class="home-month-pill" data-month="3">३. मार्च</a>
                            <a href="calendar.php?month=4" class="home-month-pill" data-month="4">४. एप्रिल</a>
                            <a href="calendar.php?month=5" class="home-month-pill" data-month="5">५. मे</a>
                            <a href="calendar.php?month=6" class="home-month-pill" data-month="6">६. जून</a>
                            <a href="calendar.php?month=7" class="home-month-pill" data-month="7">७. जुलै</a>
                            <a href="calendar.php?month=8" class="home-month-pill" data-month="8">८. ऑगस्ट</a>
                            <a href="calendar.php?month=9" class="home-month-pill" data-month="9">९. सप्टेंबर</a>
                            <a href="calendar.php?month=10" class="home-month-pill" data-month="10">१०. ऑक्टोबर</a>
                        </div>

                        <div class="home-cal-action-btns">
                            <a href="calendar.php" class="home-cal-btn primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                <span>संपूर्ण १२ माह दिनदर्शिका देखें</span>
                            </a>
                            <a href="uploads/calendar/calendar.pdf" download="सनातन_दिनदर्शिका.pdf" class="home-cal-btn secondary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <span>सम्पूर्ण PDF डाउनलोड<?= $calendarPdfSize ? ' (' . $calendarPdfSize . ')' : '' ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ AD POSITION 2: ABOVE नवीनतम घोषणाएँ ═══ -->
        <section class="promotions-outer-section ad-outer-section" id="ads-pos-above-announcements" aria-label="स्थानीय विज्ञापन - सूचनाएँ" style="display: none;">
            <div class="container">
                <div class="promotions-section ad-section">
                    <div class="promotions-header-row ad-header-row">
                        <div class="promotions-badge ad-badge">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--saffron);"><path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path></svg>
                            <span>प्रायोजक एवं विज्ञापन</span>
                        </div>
                        <span class="promotions-disclaimer ad-disclaimer">विज्ञापन</span>
                    </div>
                    <div class="promotions-grid ad-grid" id="ads-grid-above-announcements" role="list">
                        <!-- Populated dynamically via home.js -->
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ ANNOUNCEMENTS ═══ -->
        <section class="announcements-section" aria-labelledby="announcements-title">
            <div class="container">
                <div class="section-header">
                    <div class="section-eyebrow">सूचनाएँ</div>
                    <h2 class="section-title" id="announcements-title">नवीनतम घोषणाएँ</h2>
                    <div class="ornament" aria-hidden="true"><span class="ornament-icon">❧</span></div>
                </div>
                <div id="announcements-list" aria-live="polite">
                    <?php if (!empty($ssrAnnouncements)): ?>
                        <?php foreach ($ssrAnnouncements as $a): 
                            $tagType = htmlspecialchars($a['type'] ?? 'notice', ENT_QUOTES, 'UTF-8');
                            $tagClass = "tag-" . $tagType;
                            $tagLabel = $tagType === 'event' ? 'कार्यक्रम' : ($tagType === 'update' ? 'अपडेट' : 'सूचना');
                            $branchName = htmlspecialchars($a['branch'] ?? 'ALL', ENT_QUOTES, 'UTF-8');
                            $dateStr = !empty($a['date']) ? htmlspecialchars($a['date'], ENT_QUOTES, 'UTF-8') : (!empty($a['createdAt']) ? date('d/m/Y', strtotime($a['createdAt'])) : '');
                            $title = htmlspecialchars($a['title'] ?? 'शीर्षक नहीं', ENT_QUOTES, 'UTF-8');
                            $desc = htmlspecialchars($a['description'] ?? 'विवरण नहीं', ENT_QUOTES, 'UTF-8');
                            $author = !empty($a['author']) ? htmlspecialchars($a['author'], ENT_QUOTES, 'UTF-8') : '';
                            $link = !empty($a['link']) ? htmlspecialchars($a['link'], ENT_QUOTES, 'UTF-8') : '';
                        ?>
                            <div class="ann-card">
                                <div class="ann-meta" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
                                    <span class="ann-tag <?= $tagClass ?>"><?= $tagLabel ?></span>
                                    <?php if ($branchName === 'ALL'): ?>
                                        <span class="ann-tag" style="background:var(--maroon, #7B1A1A); color:white;">केंद्रीय</span>
                                    <?php else: ?>
                                        <span class="ann-tag" style="background:var(--saffron, #FF6B00); color:white;">📍 <?= $branchName ?></span>
                                    <?php endif; ?>
                                    <?php if ($dateStr): ?>
                                        <span class="ann-date">📅 <?= $dateStr ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="ann-title"><?= $title ?></h3>
                                <p class="ann-body"><?= $desc ?></p>
                                <div class="ann-footer">
                                    <?php if ($author): ?>
                                        <span class="ann-author">— <?= $author ?></span>
                                    <?php else: ?>
                                        <span></span>
                                    <?php endif; ?>
                                    <?php if ($link): ?>
                                        <a href="<?= $link ?>" target="_blank" rel="noopener" class="ann-link">और पढ़ें →</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="ann-card" aria-hidden="true"><div class="skeleton" style="height: 22px; width: 70px; margin-bottom: 10px;"></div><div class="skeleton" style="height: 20px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; margin-bottom: 6px;"></div><div class="skeleton" style="height: 14px; width: 90%;"></div></div>
                        <div class="ann-card" aria-hidden="true"><div class="skeleton" style="height: 22px; width: 70px; margin-bottom: 10px;"></div><div class="skeleton" style="height: 20px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; margin-bottom: 6px;"></div><div class="skeleton" style="height: 14px; width: 90%;"></div></div>
                    <?php endif; ?>
                </div>
                <button id="load-more-announcements" class="load-more-btn" style="display: none;">और घोषणाएँ लोड करें ↓</button>
            </div>
        </section>

        <!-- ═══ AD POSITION 3: ABOVE कार्यकारिणी सदस्य ═══ -->
        <section class="promotions-outer-section ad-outer-section" id="ads-pos-above-committee" aria-label="स्थानीय विज्ञापन - कार्यकारिणी" style="display: none;">
            <div class="container">
                <div class="promotions-section ad-section">
                    <div class="promotions-header-row ad-header-row">
                        <div class="promotions-badge ad-badge">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--saffron);"><path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path></svg>
                            <span>प्रायोजक एवं विज्ञापन</span>
                        </div>
                        <span class="promotions-disclaimer ad-disclaimer">विज्ञापन</span>
                    </div>
                    <div class="promotions-grid ad-grid" id="ads-grid-above-committee" role="list">
                        <!-- Populated dynamically via home.js -->
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ COMMITTEE ═══ -->
        <section class="container" style="padding: 30px 16px 48px;" aria-labelledby="committee-title">
            <div class="section-header">
                <h2 class="section-title" id="committee-title">कार्यकारिणी सदस्य</h2>
                <div class="ornament" aria-hidden="true"><span class="ornament-icon">❧</span></div>
                <p class="section-desc" style="margin-top:10px;">मार्गदर्शक और प्रमुख सदस्य</p>
            </div>
            <div class="committee-grid" id="home-committee-grid" role="list">
                <?php if (!empty($ssrCommittee)): ?>
                    <?php foreach ($ssrCommittee as $m): 
                        $mName = htmlspecialchars($m['name'] ?? 'अज्ञात', ENT_QUOTES, 'UTF-8');
                        $mDesig = htmlspecialchars($m['designation'] ?? '', ENT_QUOTES, 'UTF-8');
                        $mPhoto = !empty($m['photo']) ? htmlspecialchars($m['photo'], ENT_QUOTES, 'UTF-8') : 'images/user.webp';
                        $mBranch = htmlspecialchars($m['branch'] ?? '', ENT_QUOTES, 'UTF-8');
                        $mFamilyId = !empty($m['family_id']) ? htmlspecialchars($m['family_id'], ENT_QUOTES, 'UTF-8') : '';
                    ?>
                        <div class="committee-card<?= $mFamilyId ? ' committee-card-clickable' : '' ?>"<?= $mFamilyId ? ' onclick="window.location.href=\'family-details.php?id=' . rawurlencode($mFamilyId) . '\'" role="link" tabindex="0" title="परिवार विवरण देखें"' : '' ?>>
                            <div class="avatar">
                                <img src="<?= $mPhoto ?>" alt="<?= $mName ?>" width="300" height="300" loading="lazy" decoding="async" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" onerror="this.onerror=null; this.src='images/user.webp';">
                            </div>
                            <div class="member-name"><?= $mName ?></div>
                            <?php if ($mDesig): ?><div class="member-role"><?= $mDesig ?></div><?php endif; ?>
                            <?php if ($mBranch): ?><div class="member-branch"><?= $mBranch ?></div><?php endif; ?>
                            <?php if ($mFamilyId): ?>
                                <div class="committee-family-link" style="margin-top: 8px; font-size: 0.76rem; color: var(--maroon); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; background: rgba(201, 148, 26, 0.15); padding: 3px 8px; border-radius: 10px;">
                                    <span>🏠 परिवार विवरण देखें →</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                    <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                    <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                    <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ═══ AD POSITION 4: BELOW कार्यकारिणी सदस्य ═══ -->
        <section class="promotions-outer-section ad-outer-section" id="ads-pos-below-committee" aria-label="स्थानीय विज्ञापन - समापन" style="display: none;">
            <div class="container">
                <div class="promotions-section ad-section">
                    <div class="promotions-header-row ad-header-row">
                        <div class="promotions-badge ad-badge">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--saffron);"><path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path></svg>
                            <span>प्रायोजक एवं विज्ञापन</span>
                        </div>
                        <span class="promotions-disclaimer ad-disclaimer">विज्ञापन</span>
                    </div>
                    <div class="promotions-grid ad-grid" id="ads-grid-below-committee" role="list">
                        <!-- Populated dynamically via home.js -->
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/components/footer.php'; ?>

    <!-- ══ IMAGE MODAL / LIGHTBOX ═══ -->
    <div id="image-modal" class="image-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modal-title">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal-content">
            <button class="modal-close" id="modal-close-btn" aria-label="बंद करें">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <button class="modal-prev" id="modal-prev-btn" aria-label="पिछली छवि">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <button class="modal-next" id="modal-next-btn" aria-label="अगली छवि">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
            <div class="modal-image-container">
                <img id="modal-image" src="" alt="" class="modal-image">
                <div class="modal-loader"><div class="spinner"></div></div>
            </div>
            <div class="modal-caption">
                <h3 id="modal-title"></h3>
                <p id="modal-description"></p>
                <div class="modal-counter"><span id="current-image">1</span> / <span id="total-images">0</span></div>
            </div>
            <div class="modal-thumbnails" id="modal-thumbnails"></div>
        </div>
    </div>

    <!-- ═══ SCRIPTS ═══ -->
    <script src="js/include.js?v=<?= @filemtime(__DIR__ . '/js/include.js') ?: time()?>" defer></script>
    <script type="module" src="js/home.js?v=<?= @filemtime(__DIR__ . '/js/home.js') ?: time()?>"></script>
</body>
</html>