<!DOCTYPE html>
<html lang="hi" dir="ltr">
<head>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "छःन्यात राजस्थानी विप्र मंडल, लातूर",
      "url": "https://chhanyatvipralatur.com/",
      "logo": "https://chhanyatvipralatur.com/favicon.ico"
    }
    </script>
    

<script async src="https://www.googletagmanager.com/gtag/js?id=G-DE5KD051LL"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-DE5KD051LL');
</script>
    
    
    
    <script src="https://cdn.counter.dev/script.js" data-id="1f92108b-8857-410e-b5a8-c1ab33e859f8" data-utcoffset="6"></script>
    <link rel="canonical" href="https://chhanyatvipralatur.com/" />
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#7B1A1A">
    <title>Chhanyat Vipra Samaj Latur | छःन्यात विप्र समाज, लातूर</title>
    <meta name="description" content="Chhanyat Vipra Samaj (छःन्यात विप्र समाज) - Official website of Chhanyat Rajasthani Vipra Mandal Latur. Dedicated to the Vipra Samaj community.">
    <meta name="keywords" content="chhanyat, vipra samaj, छःन्यात, विप्र समाज, chhanyat vipra samaj, chhanyat vipra mandal, rajasthani vipra samaj latur, brahmin samaj latur">
    <meta property="og:title" content="छःन्यात राजस्थानी विप्र मंडल, लातूर | Chhanyat Rajasthani Vipra Mandal Latur">
    <meta property="og:description" content="छःन्यात राजस्थानी विप्र मंडल, लातूर - आधिकारिक वेबसाइट | Official website of Chhanyat Rajasthani Vipra Mandal Latur">
    <meta property="og:url" content="https://chhanyatvipralatur.com/">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://chhanyatvipralatur.com/favicon.ico">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="छःन्यात राजस्थानी विप्र मंडल, लातूर">
    <meta name="twitter:description" content="आधिकारिक वेबसाइट | Official website of Chhanyat Rajasthani Vipra Mandal Latur">
  
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
</head>
<body>
    <a href="#main-content" class="skip-link">मुख्य सामग्री पर जाएं</a>
    
    <div data-include="components/header.php"></div>

    <main id="main-content">
        <!-- ═══ PROFESSIONAL HERO SLIDER ═══ -->

<!-- ═══ PROFESSIONAL HERO SLIDER ═══ -->
<section class="hero-slider" aria-label="इमेज स्लाइडर" aria-roledescription="carousel">
    <div class="slider-wrapper">
        <!-- Loading & Error States -->
        <div class="slider-state slider-loading" id="slider-loading">
            <div class="spinner"></div>
            <span>छवियाँ लोड हो रही हैं...</span>
        </div>
        
        <div class="slider-state slider-error" id="slider-error" style="display: none;">
            <h3>⚠️ छवियाँ लोड नहीं हो सकीं</h3>
            <p id="error-message">कृपया बाद में पुनः प्रयास करें</p>
            <button onclick="loadGalleryImages()">पुनः प्रयास करें</button>
        </div>
        
        <!-- Slider Track (Slides are injected here) -->
        <div class="slider-track" id="slider-track"></div>
        
        <!-- Navigation Arrows -->
        <button class="slider-nav slider-nav-prev" aria-label="पिछली स्लाइड" onclick="moveSlide(-1)">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <button class="slider-nav slider-nav-next" aria-label="अगली स्लाइड" onclick="moveSlide(1)">
            <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
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
        <div class="branches-marquee" aria-label="परिवारओं की सूची">
            <div class="marquee-fade marquee-fade-left"></div>
            <div class="marquee-fade marquee-fade-right"></div>

            <div class="marquee-label">
                <span>परिवार चुने</span>
            </div>

            <div class="marquee-track" id="branches-marquee-track">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- ═══ STATISTICS ═══ -->
        <section class="stats-bg" aria-labelledby="stats-title">
            <h2 id="stats-title" class="sr-only">सांख्यिकी</h2>
            <div class="container">
                <div class="stats-grid" role="list">
                    <div class="stat-card" role="listitem">
                        <h3 id="total-branches" aria-live="polite">—</h3>
                        <p>कुल परिवार</p>
                        <div class="stat-sublabel">Total Branches</div>
                    </div>
                    <div class="stat-card" role="listitem">
                        <h3 id="total-families" aria-live="polite">—</h3>
                        <p>कुल परिवार</p>
                        <div class="stat-sublabel">Total Families</div>
                    </div>
                    <div class="stat-card" role="listitem">
                        <h3 id="total-members" aria-live="polite">—</h3>
                        <p>कुल सदस्य</p>
                        <div class="stat-sublabel">Total Members</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ 1. TOP SPONSOR BANNER SLOT ═══ -->
        <div id="ad-slot-top" class="sponsor-banner-container" aria-label="स्थानिक प्रायोजक जाहिरात" style="display: none;"></div>


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
                        <a href="calendar.php" title="संपूर्ण सनातन दिनदर्शिका देखें" class="home-cal-img-link">
                            <img id="home-cal-preview-img" src="uploads/calendar/cover.webp" alt="सनातन दिनदर्शिका" loading="lazy">
                            <span class="home-cal-img-zoom-hint">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h6v6"></path><path d="M10 14L21 3"></path><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path></svg>
                                बड़ा देखें
                            </span>
                        </a>
                    </div>
                    <div class="home-cal-info">
                        <div class="home-cal-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span>सनातन दिनदर्शिका (समस्त १२ माह)</span>
                        </div>
                        <h3 class="home-cal-title" id="home-cal-month-title">2026-27</h3>
                        <p class="home-cal-text">
                            छःन्यात राजस्थानी विप्र मंडल, लातूर की आधिकारिक वार्षिक सनातन दिनदर्शिका। समस्त १२ माह के पृष्ठ, पावन पर्व, व्रत, एकादशी एवं कुलप्रवर्तक जयंतियां अब डिजिटल रूप में उपलब्ध हैं।
                        </p>
                        
                        <!-- 12 Month Quick Jump Links -->
                        <div class="home-cal-months-label">
                            <span>माहवार पृष्ठ (सीधे देखें)</span>
                        </div>
                        <div class="home-cal-months-strip" id="home-cal-months-strip">
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
                            <a href="calendar.php?month=11" class="home-month-pill" data-month="11">११. नोव्हेंबर</a>
                            <a href="calendar.php?month=12" class="home-month-pill" data-month="12">१२. डिसेंबर</a>
                        </div>

                        <div class="home-cal-action-btns">
                            <a href="calendar.php" class="home-cal-btn primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                <span>संपूर्ण १२ माह दिनदर्शिका देखें</span>
                            </a>
                            <a href="uploads/calendar/calendar.pdf" download="सनातन_दिनदर्शिका.pdf" class="home-cal-btn secondary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                <span>सम्पूर्ण PDF डाउनलोड</span>
                            </a>
                        </div>
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
            <div class="committee-grid" id="home-committee-grid" role="list" aria-busy="true">
                <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
                <div class="committee-card" aria-hidden="true"><div class="skeleton skeleton-circle"></div><div class="skeleton" style="height: 18px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; width: 60%; margin: 0 auto;"></div></div>
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
                <div id="announcements-list" role="feed" aria-busy="true">
                    <div class="ann-card" aria-hidden="true"><div class="skeleton" style="height: 22px; width: 70px; margin-bottom: 10px;"></div><div class="skeleton" style="height: 20px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; margin-bottom: 6px;"></div><div class="skeleton" style="height: 14px; width: 90%;"></div></div>
                    <div class="ann-card" aria-hidden="true"><div class="skeleton" style="height: 22px; width: 70px; margin-bottom: 10px;"></div><div class="skeleton" style="height: 20px; margin-bottom: 8px;"></div><div class="skeleton" style="height: 14px; margin-bottom: 6px;"></div><div class="skeleton" style="height: 14px; width: 90%;"></div></div>
                </div>
                <button id="load-more-announcements" class="load-more-btn" style="display: none;">और घोषणाएँ लोड करें ↓</button>
            </div>
        </section>

        <!-- ═══ 3. BOTTOM SPONSOR BANNER SLOT ═══ -->
        <div id="ad-slot-bottom" class="sponsor-banner-container" aria-label="स्थानिक प्रायोजक जाहिरात" style="display: none;"></div>
    </main>

    <div data-include="components/footer.php"></div>

    <!-- ══ IMAGE MODAL / LIGHTBOX ═══ -->
    <div id="image-modal" class="image-modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-backdrop" onclick="closeModal()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()" aria-label="बंद करें">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <button class="modal-prev" onclick="changeImage(-1)" aria-label="पिछली छवि">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <button class="modal-next" onclick="changeImage(1)" aria-label="अगली छवि">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
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
    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>" defer></script>
    <script type="module" src="js/home.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/home.js')?>"></script>
</body>
</html>