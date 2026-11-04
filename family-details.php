<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>परिवार विवरण | छःन्यात राजस्थानी विप्र मंडल</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
    <style>
        /* ── Page wrapper ── */
        .fd-page { padding-bottom: var(--spacing-2xl); }

        /* ── Profile hero card ── */
        .fd-hero {
            background: linear-gradient(135deg, var(--maroon-deep) 0%, var(--maroon) 100%);
            border-radius: var(--radius);
            padding: 28px 20px 24px;
            margin-bottom: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-hover);
        }
        .fd-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 40%, rgba(201,148,26,.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 60%, rgba(255,107,0,.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .fd-photo-wrap { position: relative; z-index: 1; }
        .fd-photo, .fd-photo-skeleton {
            width: 110px; height: 110px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            object-fit: cover;
            box-shadow: 0 6px 20px rgba(0,0,0,.35);
        }
        .fd-photo-skeleton {
            background: linear-gradient(90deg, rgba(255,255,255,.1) 25%, rgba(255,255,255,.2) 50%, rgba(255,255,255,.1) 75%);
            background-size: 200% 100%;
            animation: fd-shimmer 1.5s infinite;
        }
        @keyframes fd-shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .fd-hero-info { position: relative; z-index: 1; }
        .fd-hero-name {
            font-family: var(--font-heading, 'Yatra One', cursive);
            font-size: 1.5rem;
            color: var(--gold-light);
            margin: 0 0 6px;
            text-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .fd-branch-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(201,148,26,.25);
            border: 1px solid rgba(201,148,26,.5);
            color: var(--gold-light);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: .78rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        .fd-branch-badge i { font-size: .72rem; }

        /* ── Error box ── */
        .fd-error {
            background: #FFF0F0;
            border: 1px solid #FFCDD2;
            color: #C62828;
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            text-align: center;
            margin-bottom: var(--spacing-lg);
            display: none;
        }
        .fd-error small { color: #666; display: block; margin-top: 6px; }

        /* ── Section header ── */
        .fd-section-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 24px 0 12px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid var(--border);
        }
        .fd-section-icon {
            width: 32px; height: 32px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--maroon), var(--maroon-deep));
            display: flex; align-items: center; justify-content: center;
            color: var(--gold-light);
            font-size: .85rem;
            flex-shrink: 0;
            position:relative;
        
        }

        /* Golden dot accent */
        .fd-section-icon::after {
            content: '';
            position: center;
            top: 4px;
            right: 4px;
            width: 6px;
            height: 6px;
            background: var(--gold-light);
            border-radius: 50%;
            box-shadow: 0 0 4px rgba(240, 192, 96, 0.6);
        }
        .fd-section-title {
            font-family: var(--font-heading, 'Yatra One', cursive);
            font-size: 1.05rem;
            color: var(--maroon);
            margin: 0;
        }

        /* ── Info rows ── */
        .fd-info-grid { display: grid; grid-template-columns: 1fr; gap: 6px; }
        .fd-row {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px 12px;
            background: var(--bg-card);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            transition: border-color var(--transition);
        }
        .fd-row:hover { border-color: var(--gold); }
        .fd-label {
            font-size: .75rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--text-soft);
            min-width: 110px; flex-shrink: 0;
            padding-top: 1px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .fd-value {
            font-size: .9rem;
            color: var(--text-dark);
            word-break: break-word;
            flex: 1;
            display: flex;
            align-items: center;
        }
        .fd-value a { color: var(--saffron); text-decoration: none; font-weight: 600; }
        .fd-value a:hover { text-decoration: underline; }

        /* Wrapper so number + WhatsApp button align on the same baseline and wrap gracefully */
        .fd-mobile-wrap {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
            line-height: 1;
        }

        /* ── WhatsApp Button (page-scoped, independent of style.css) ── */
        .fd-page .fd-wa-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            background: #25D366 !important;
            color: #ffffff !important;
            padding: 3px 12px !important;
            border-radius: 14px !important;
            font-size: .75rem !important;
            line-height: 1.9 !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            border: none !important;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .fd-page .fd-wa-btn:hover {
            background: #128C7E !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
            text-decoration: none !important;
            color: #ffffff !important;
        }
        .fd-page .fd-wa-btn i {
            font-size: .85rem;
            line-height: 1;
            color: #ffffff !important;
        }
        .fd-page .fd-wa-icon {
            width: 14px !important;
            height: 14px !important;
            color: #ffffff !important;
            flex-shrink: 0;
        }
        .fd-page .fd-wa-btn.hidden {
            display: none !important;
        }

        /* ── Members table ── */
        .fd-table-wrap {
            overflow-x: auto;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            margin-top: 4px;
        }
        .fd-table {
            width: 100%; border-collapse: collapse;
            font-size: .82rem;
            font-family: 'Poppins', sans-serif;
            min-width: 560px;
        }
        .fd-table thead tr { background: linear-gradient(90deg, var(--maroon), var(--maroon-deep)); }
        .fd-table thead th {
            color: var(--gold-light);
            padding: 10px 12px; text-align: left;
            font-weight: 600; font-size: .75rem;
            letter-spacing: .04em; text-transform: uppercase;
            white-space: nowrap;
        }
        .fd-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background var(--transition);
        }
        .fd-table tbody tr:last-child { border-bottom: none; }
        .fd-table tbody tr:nth-child(even) { background: var(--saffron-pale); }
        .fd-table tbody tr:hover { background: var(--gold-pale); }
        .fd-table tbody td { padding: 10px 12px; color: var(--text-dark); vertical-align: middle; }

        .member-name-cell { display: flex; align-items: center; gap: 8px; }
        .member-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--saffron), var(--maroon));
            color: var(--white);
            font-size: .68rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .gender-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: .68rem;
            font-weight: 600;
        }
        .gender-m { background: #E3F0FF; color: #1565C0; }
        .gender-f { background: #FCE4EC; color: #880E4F; }
        .gender-o { background: var(--saffron-pale); color: var(--saffron-deep); }

        /* ── Business section card ── */
        #business-section {
            background: var(--gold-pale);
            border: 1px solid rgba(201,148,26,.3);
            border-radius: var(--radius);
            padding: 16px;
        }
        #business-section .fd-section-head { border-bottom-color: rgba(201,148,26,.3); margin-top: 0; }

        /* ── Print ── */
        @media print {
            .fd-hero { background: white !important; border: 1px solid #ccc; }
            .fd-hero-name { color: var(--maroon) !important; }
            .fd-table thead tr { background: #ddd !important; }
            .fd-table thead th { color: #000 !important; }
            .no-print { display: none !important; }
        }

        /* ── Tablet+ ── */
        @media (min-width: 600px) {
            .fd-hero { flex-direction: row; text-align: left; padding: 32px 28px; }
            .fd-photo, .fd-photo-skeleton { width: 120px; height: 120px; }
            .fd-hero-name { font-size: 1.75rem; }
            .fd-info-grid { grid-template-columns: repeat(2, 1fr); }
            .fd-label { min-width: 120px; }
        }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <div class="container fd-page">
        

        <!-- ✅ Error box (shown only on failure) -->
        <div class="fd-error" id="fd-error"></div>
<br>
        <!-- ── Hero Profile Card ── -->
        <div class="fd-hero" id="fd-hero">
            <div class="fd-photo-wrap">
                <div class="fd-photo-skeleton" id="fd-photo-skeleton"></div>
                <img class="fd-photo" id="fd-photo" src="" alt="परिवार फोटो" style="display:none">
            </div>
            <div class="fd-hero-info">
                <h1 class="fd-hero-name" id="fd-name">—</h1>
                <span class="fd-branch-badge">
                    <i class="fas fa-landmark"></i>
                    <span id="fd-branch-label">—</span>
                </span>
            </div>
        </div>

        <!-- ── सर्वसाधारण जानकारी ── -->
        <div class="fd-section-head">
            <div class="fd-section-icon"><i class="fas fa-home"></i></div>
            <h2 class="fd-section-title">सर्वसाधारण जानकारी</h2>
        </div>
        <div class="fd-info-grid">
            <div class="fd-row"><span class="fd-label">कुटुंब प्रमुख</span><span class="fd-value" id="family-head">—</span></div>
            <div class="fd-row"><span class="fd-label">परिवार</span><span class="fd-value" id="family-branch">—</span></div>
            <div class="fd-row"><span class="fd-label">पता</span><span class="fd-value" id="family-address">—</span></div>
            <div class="fd-row"><span class="fd-label">गांव/शहर</span><span class="fd-value" id="family-village">—</span></div>
            <div class="fd-row"><span class="fd-label">जिल्हा</span><span class="fd-value" id="family-district">—</span></div>
            <div class="fd-row"><span class="fd-label">पिनकोड</span><span class="fd-value" id="family-pincode">—</span></div>
            <div class="fd-row">
                <span class="fd-label">मोबाईल</span>
                <span class="fd-value">
                    <span class="fd-mobile-wrap">
                        <span id="family-mobile">—</span>
                        <a href="#" id="whatsapp-btn" class="fd-wa-btn hidden" target="_blank" rel="noopener noreferrer">
                            <svg class="fd-wa-icon" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path fill="currentColor" d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.696 4.61 1.898 6.484L4 29l7.72-1.858A11.93 11.93 0 0 0 16.001 27C22.628 27 28 21.627 28 15S22.628 3 16.001 3zm6.994 16.845c-.297.836-1.47 1.53-2.41 1.73-.64.136-1.475.244-4.286-.92-3.598-1.49-5.914-5.14-6.096-5.378-.175-.237-1.457-1.94-1.457-3.7s.914-2.626 1.238-2.984c.324-.359.708-.449.944-.449.236 0 .473.002.679.013.218.011.51-.083.798.608.297.71.997 2.45 1.084 2.63.088.18.147.39.03.628-.118.237-.177.383-.352.588-.176.204-.371.457-.53.614-.176.174-.36.363-.155.712.206.35.914 1.51 1.964 2.446 1.35 1.203 2.489 1.576 2.837 1.75.35.175.554.15.759-.09.206-.24.881-1.028 1.117-1.38.235-.354.47-.294.793-.176.323.117 2.058.97 2.41 1.147.352.176.586.264.673.412.088.147.088.851-.209 1.687z"/>
                            </svg>
                            <span>WhatsApp</span>
                        </a>
                    </span>
                </span>
            </div>
            <div class="fd-row"><span class="fd-label">ईमेल</span><span class="fd-value" id="family-email">—</span></div>
            <div class="fd-row"><span class="fd-label">STD कोड</span><span class="fd-value" id="family-std-code">—</span></div>
            <div class="fd-row"><span class="fd-label">फोन</span><span class="fd-value" id="family-phone">—</span></div>
        </div>

        <!-- ── राजस्थान मूल ── -->
        <div class="fd-section-head">
            <div class="fd-section-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h2 class="fd-section-title">राजस्थान मूल माहिती</h2>
        </div>
        <div class="fd-info-grid">
            <div class="fd-row"><span class="fd-label">मूळ गाव</span><span class="fd-value" id="family-native">—</span></div>
            <div class="fd-row"><span class="fd-label">तालुका</span><span class="fd-value" id="family-native-taluka">—</span></div>
            <div class="fd-row"><span class="fd-label">जिल्हा</span><span class="fd-value" id="family-native-district">—</span></div>
        </div>

        <!-- ── कुलदेवता ── -->
        <div class="fd-section-head">
            <div class="fd-section-icon"><i class="fas fa-om"></i></div>
            <h2 class="fd-section-title">कुलदेवता माहिती</h2>
        </div>
        <div class="fd-info-grid">
            <div class="fd-row"><span class="fd-label">कुलदेवता/कुलदेवी</span><span class="fd-value" id="family-kuldaivat">—</span></div>
            <div class="fd-row"><span class="fd-label">स्थान</span><span class="fd-value" id="family-kuldaivat-place">—</span></div>
        </div>

        <!-- ── Business (hidden until populated) ── -->
        <div id="business-section" style="display:none; margin-top: var(--spacing-lg);">
            <div class="fd-section-head">
                <div class="fd-section-icon"><i class="fas fa-briefcase"></i></div>
                <h2 class="fd-section-title">व्यवसाय जानकारी</h2>
            </div>
            <div class="fd-info-grid">
                <div class="fd-row"><span class="fd-label">प्रतिष्ठान</span><span class="fd-value" id="biz-name">—</span></div>
                <div class="fd-row"><span class="fd-label">पता</span><span class="fd-value" id="biz-address">—</span></div>
                <div class="fd-row">
                    <span class="fd-label">मोबाइल</span>
                    <span class="fd-value">
                        <span class="fd-mobile-wrap">
                            <span id="biz-mobile">—</span>
                            <a href="#" id="whatsapp-biz-btn" class="fd-wa-btn hidden" target="_blank" rel="noopener noreferrer">
                                <svg class="fd-wa-icon" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path fill="currentColor" d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.696 4.61 1.898 6.484L4 29l7.72-1.858A11.93 11.93 0 0 0 16.001 27C22.628 27 28 21.627 28 15S22.628 3 16.001 3zm6.994 16.845c-.297.836-1.47 1.53-2.41 1.73-.64.136-1.475.244-4.286-.92-3.598-1.49-5.914-5.14-6.096-5.378-.175-.237-1.457-1.94-1.457-3.7s.914-2.626 1.238-2.984c.324-.359.708-.449.944-.449.236 0 .473.002.679.013.218.011.51-.083.798.608.297.71.997 2.45 1.084 2.63.088.18.147.39.03.628-.118.237-.177.383-.352.588-.176.204-.371.457-.53.614-.176.174-.36.363-.155.712.206.35.914 1.51 1.964 2.446 1.35 1.203 2.489 1.576 2.837 1.75.35.175.554.15.759-.09.206-.24.881-1.028 1.117-1.38.235-.354.47-.294.793-.176.323.117 2.058.97 2.41 1.147.352.176.586.264.673.412.088.147.088.851-.209 1.687z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                        </span>
                    </span>
                </div>
                <div class="fd-row"><span class="fd-label">प्रकार</span><span class="fd-value" id="biz-type">—</span></div>
                <div class="fd-row"><span class="fd-label">वर्षों से</span><span class="fd-value" id="biz-years">—</span></div>
            </div>
        </div>

        <!-- ── Members table ── -->
        <div class="fd-section-head" style="margin-top: var(--spacing-lg);">
            <div class="fd-section-icon"><i class="fas fa-users"></i></div>
            <h2 class="fd-section-title">पारिवारिक जानकारी</h2>
        </div>
        <div class="fd-table-wrap">
            <table class="fd-table">
                <thead>
                    <tr>
                        <th>नाम</th>
                        <th>नाता</th>
                        <th>आयु</th>
                        <th>लिंग</th>
                        <th>वैवाहिक स्थिति</th>
                        <th>शिक्षा</th>
                        <th>व्यवसाय</th>
                        <th>मोबाइल</th>
                    </tr>
                </thead>
                <tbody id="members-tbody"></tbody>
            </table>
        </div>

        <!-- hidden container kept for JS photo compatibility -->
        <div id="family-photo-container" style="display:none"></div>
    </div>

    <div data-include="components/footer.php"></div>

    <!-- ⚠️ NOTE: If your file is named "Home.js" (capital H), change import in family-details.js
         to "./Home.js" OR rename the file to "home.js" to avoid case-sensitivity issues on Linux servers -->
<script src="js/include.js?v=<?= @filemtime(__DIR__ . '/js/include.js') ?: time()?>" defer></script>
<script type="module" src="js/family-details.js?v=<?= @filemtime(__DIR__ . '/js/family-details.js') ?: time()?>"></script>

    <!-- UI enhancement: upgrade members-tbody rows with avatar initials + gender badges -->

</body>
</html>