<!DOCTYPE html>
<html lang="hi">
<head><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>एडमिन डैशबोर्ड | छःन्यात राजस्थानी विप्र मंडल</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
<style>
/* ============================================================
   🏰 ROOT TOKENS — Royal Rajasthan
   ============================================================ */
:root {
    --saffron:        #FF6B00;
    --saffron-deep:   #D94F00;
    --saffron-light:  #FF9A3C;
    --saffron-pale:   #FFF3E6;
    --maroon:         #7B1A1A;
    --maroon-deep:    #550F0F;
    --maroon-soft:    #9B2424;
    --gold:           #C9941A;
    --gold-light:     #F0C060;
    --gold-pale:      #FDF5E0;
    --cream:          #FEF9F0;
    --white:          #FFFFFF;

    --orange:         #FF6B00;
    --orange-light:   #FF8C42;
    --red:            #D32F2F;
    --red-dark:       #B71C1C;

    --text-dark:      #1A0A00;
    --text-mid:       #5C3010;
    --text-soft:      #8B5A2B;
    --text-secondary: #9E7B5A;
    --bg-light:       #FFF8F3;
    --bg-card:        #FFFFFF;

    --border:         rgba(201,148,26,0.22);
    --border-strong:  rgba(201,148,26,0.45);
    --radius:         14px;
    --radius-sm:      8px;
    --radius-md:      12px;
    --radius-lg:      20px;
    --radius-xl:      28px;
    --transition:     0.25s cubic-bezier(0.4,0,0.2,1);
    --transition-global: all 0.3s ease;

    --shadow-sm:   0 2px 8px rgba(123,26,26,0.06);
    --shadow-md:   0 4px 16px rgba(123,26,26,0.10);
    --shadow-lg:   0 10px 40px rgba(123,26,26,0.14);
    --shadow-card: 0 2px 16px rgba(123,26,26,0.08);
    --shadow-hover:0 12px 40px rgba(123,26,26,0.16);

    --spacing-xs:  8px;
    --spacing-sm:  12px;
    --spacing-md:  18px;
    --spacing-lg:  28px;
    --spacing-xl:  40px;

    --success: #2e8b57;
    --danger:  var(--red);
    --warning: #f39c12;
    --info:    #3498db;
    --gallery: var(--gold);
}

/* ============================================================
   🧱 BASE
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: var(--bg-light);
    font-family: 'Segoe UI', system-ui, sans-serif;
    color: var(--text-dark);
    line-height: 1.65;
}

/* ============================================================
   📊 DASHBOARD HEADER
   ============================================================ */
.dash-header {
    position: relative;
    background: linear-gradient(135deg, var(--maroon-deep) 0%, var(--maroon) 55%, #9B2424 100%);
    color: var(--white);
    padding: 0;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.dash-header::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 340px; height: 340px;
    border-radius: 50%;
    border: 48px solid rgba(201,148,26,0.13);
    pointer-events: none;
}
.dash-header::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    border: 36px solid rgba(255,107,0,0.10);
    pointer-events: none;
}

.dash-header-inner {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 44px 28px 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.dash-header-brand {
    display: flex;
    align-items: center;
    gap: 18px;
}

.dash-header-icon {
    width: 62px; height: 62px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.7rem;
    box-shadow: 0 4px 18px rgba(201,148,26,0.40);
    flex-shrink: 0;
}

.dash-header-text h1 {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.3px;
    line-height: 1.25;
    color: var(--white);
}

.dash-header-text .org-name {
    font-size: 0.85rem;
    opacity: 0.78;
    margin-top: 3px;
    letter-spacing: 0.3px;
}

.dash-header-text .welcome-line {
    margin-top: 6px;
    font-size: 0.9rem;
    opacity: 0.88;
    display: flex;
    align-items: center;
    gap: 7px;
}

.dash-header-text .welcome-line i { color: var(--gold-light); }

.dash-header-text h1::after {
    content: '';
    display: block;
    width: 44px; height: 3px;
    background: linear-gradient(90deg, var(--gold-light), transparent);
    border-radius: 2px;
    margin-top: 6px;
}

#logout-btn {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 11px 22px;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.12);
    color: var(--white);
    border: 1.5px solid rgba(255,255,255,0.28);
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition-global);
    backdrop-filter: blur(8px);
    white-space: nowrap;
}
#logout-btn:hover {
    background: rgba(255,255,255,0.22);
    border-color: rgba(255,255,255,0.55);
    transform: translateY(-1px);
}

.dash-header-strip {
    height: 4px;
    background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, var(--gold-light) 60%, transparent 100%);
}

/* ============================================================
   🎛️ ADMIN GRID
   ============================================================ */
.admin-section {
    max-width: 1200px;
    margin: 36px auto 60px;
    padding: 0 20px;
}

.section-eyebrow {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.section-eyebrow .eyebrow-line {
    flex: 1;
    height: 1px;
    background: var(--border);
}

.section-eyebrow span {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--text-soft);
}

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 20px;
}

/* ============================================================
   🃏 ADMIN CARD
   ============================================================ */
.admin-card {
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 0;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-card);
    border: 1px solid var(--border);
    transition: transform var(--transition), box-shadow var(--transition);
    display: flex;
    flex-direction: column;
}

.admin-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.card-top-bar {
    height: 5px;
    width: 100%;
    background: linear-gradient(90deg, var(--gold), var(--gold-light));
    border-radius: var(--radius) var(--radius) 0 0;
    flex-shrink: 0;
}
.admin-card.edit-card .card-top-bar         { background: linear-gradient(90deg, var(--info), #5dade2); }
.admin-card.delete-card .card-top-bar       { background: linear-gradient(90deg, var(--red), #e57373); }

.card-body {
    padding: 24px 22px 22px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}

.admin-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.45rem;
    margin-bottom: 4px;
}

.admin-card.edit-card .admin-icon-wrap         { background: #e8f4fd; color: var(--info); }
.admin-card.delete-card .admin-icon-wrap       { background: #ffebee; color: var(--red); }

.admin-card:not([class*="-card"]) .admin-icon-wrap,
.admin-card.family-card .admin-icon-wrap       { background: var(--saffron-pale); color: var(--saffron); }

.admin-card h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--maroon);
    line-height: 1.3;
}

.admin-card p {
    font-size: 0.875rem;
    color: var(--text-soft);
    line-height: 1.5;
    flex: 1;
}

.card-arrow {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-top: 6px;
    transition: gap var(--transition), color var(--transition);
}
.admin-card:hover .card-arrow {
    color: var(--saffron);
    gap: 9px;
}

.card-badge {
    position: absolute;
    top: 14px; right: 14px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}
.card-badge.badge-add       { background: #e8f5e9; color: #2e7d32; }
.card-badge.badge-manage    { background: #ffebee; color: var(--red-dark); }
.card-badge.badge-committee { background: #FDF5E0; color: #7B1A1A; border: 1px solid rgba(123, 26, 26, 0.15); }
.card-badge.badge-notice    { background: #FFF3E6; color: #D94F00; border: 1px solid rgba(255, 107, 0, 0.15); }
.card-badge.badge-gallery   { background: var(--gold-pale); color: #8a6200; }
.card-badge.badge-ads       { background: #FFF3E6; color: #FF6B00; border: 1px solid rgba(255, 107, 0, 0.2); }
.card-badge.badge-calendar  { background: var(--gold-pale); color: #8a6200; }
.card-badge.badge-security  { background: #F3E5F5; color: #7B1FA2; }

/* ============================================================
   📐 MODAL BASE
   ============================================================ */
.modal {
    display: none;
    position: fixed; inset: 0;
    background: rgba(26,10,0,0.60);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.modal.active {
    display: flex;
    animation: modalFadeIn 0.22s ease;
}
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.97); }
    to   { opacity: 1; transform: scale(1); }
}

.modal-content {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 80px rgba(85,15,15,0.22);
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
}

/* ============================================================
   ⚠️ CUSTOM CONFIRM DIALOG
   ============================================================ */
.confirm-overlay {
    position: fixed; inset: 0;
    background: rgba(26,10,0,0.60);
    z-index: 9999;
    display: none;
    backdrop-filter: blur(3px);
}
.confirm-overlay.active { display: block; }

.confirm-delete-dialog {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(0.95);
    background: var(--white);
    padding: 32px 28px 26px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    z-index: 10000;
    max-width: 380px;
    width: 90%;
    text-align: center;
    display: none;
    border-top: 4px solid var(--red);
}
.confirm-delete-dialog.active {
    display: block;
    animation: confirmPop 0.22s cubic-bezier(0.34,1.56,0.64,1) forwards;
}
@keyframes confirmPop {
    from { opacity: 0; transform: translate(-50%, -50%) scale(0.88); }
    to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}

.confirm-dialog-icon {
    width: 62px; height: 62px;
    background: #ffebee;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    color: var(--red);
    margin: 0 auto 18px;
    border: 3px solid #ffcdd2;
}

.confirm-delete-dialog h4 {
    color: var(--maroon);
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 10px;
}
.confirm-delete-dialog p {
    color: var(--text-soft);
    font-size: 0.88rem;
    margin-bottom: 24px;
    line-height: 1.55;
}

.confirm-delete-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}
.btn-cancel {
    flex: 1;
    padding: 11px 16px;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--border-strong);
    background: transparent;
    color: var(--text-mid);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--transition);
}
.btn-cancel:hover { background: var(--cream); }

.btn-confirm-delete {
    flex: 1;
    padding: 11px 16px;
    border-radius: var(--radius-sm);
    border: none;
    background: var(--red);
    color: var(--white);
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--transition);
}
.btn-confirm-delete:hover {
    background: var(--red-dark);
    box-shadow: 0 4px 16px rgba(211,47,47,0.32);
}

/* ============================================================
   🔔 TOAST
   ============================================================ */
.toast {
    position: fixed;
    top: 20px; right: 20px;
    background: var(--white);
    padding: 14px 18px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    display: flex; align-items: center; gap: 12px;
    min-width: 280px; max-width: 380px;
    z-index: 9999;
    transform: translateX(420px);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    border-left: 4px solid var(--success);
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-dark);
}
.toast.show    { transform: translateX(0); }
.toast.error   { border-left-color: var(--red); }
.toast.warning { border-left-color: var(--warning); }

/* ============================================================
   🔐 AUTH BOX
   ============================================================ */
.auth-box {
    max-width: 420px;
    margin: 4rem auto;
    padding: 2.5rem;
    background: var(--bg-card);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    text-align: center;
    border-top: 4px solid var(--gold);
}
.auth-btn {
    width: 100%;
    padding: 13px;
    background: var(--saffron);
    color: var(--white);
    border: none;
    border-radius: var(--radius-sm);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1rem;
    transition: var(--transition);
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.auth-btn:hover { background: var(--saffron-deep); }

/* ============================================================
   🛠️ UTILS
   ============================================================ */
.hidden { display: none !important; }

/* ============================================================
   📱 RESPONSIVE
   ============================================================ */
@media (max-width: 768px) {
    .dash-header-inner {
        padding: 24px 20px 20px;
    }
    .admin-section {
        margin: 24px auto 40px;
        padding: 0 15px;
    }
    .admin-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
    }
}

@media (max-width: 640px) {
    .dash-header-inner {
        padding: 20px 16px 16px;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .dash-header-brand {
        flex: 1 1 auto;
        gap: 12px;
    }
    .dash-header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.4rem;
        border-radius: 12px;
    }
    .dash-header-text h1 { font-size: 1.25rem; }
    .dash-header-text .org-name { font-size: 0.8rem; }
    .dash-header-text .welcome-line { font-size: 0.82rem; }

    #logout-btn { 
        width: auto; 
        margin-left: auto;
        align-self: center;
        justify-content: center;
        padding: 8px 16px;
        font-size: 0.84rem;
    }

    .admin-grid { 
        grid-template-columns: 1fr; 
        gap: 14px; 
    }

    .admin-card {
        border-radius: var(--radius-sm);
    }
    .card-body {
        padding: 18px 16px;
        gap: 8px;
    }
    .card-badge {
        top: 10px;
        right: 10px;
        padding: 2px 8px;
        font-size: 0.62rem;
    }

    /* ⚠️ Confirm and Delete Dialog responsiveness */
    .confirm-delete-dialog {
        width: calc(100% - 30px);
        max-width: 360px;
        padding: 24px 20px 20px;
        border-radius: 14px;
    }
    .confirm-dialog-icon {
        width: 52px;
        height: 52px;
        font-size: 1.35rem;
        margin-bottom: 14px;
    }
    .confirm-delete-dialog h4 {
        font-size: 1.05rem;
    }
    .confirm-delete-dialog p {
        font-size: 0.84rem;
        margin-bottom: 18px;
    }
    .confirm-delete-buttons { 
        flex-direction: column; 
        gap: 8px;
    }
    .btn-cancel, .btn-confirm-delete { 
        width: 100%; 
        padding: 11px 14px;
        font-size: 0.88rem;
    }

    /* Modal dialog */
    .modal {
        padding: 12px;
    }
    .modal-content {
        max-width: 100%;
        max-height: 92vh;
        border-radius: 14px;
    }

    .delete-modal-header { padding: 18px; }
    .delete-modal-body   { padding: 14px 16px 18px; }

    .button-group { flex-direction: column; }
    .button-group button { width: 100%; justify-content: center; }

    .file-info { flex-direction: column; }
    .file-info-cell:not(:last-child) { border-right: none; border-bottom: 1px solid var(--border); }

    /* Toast */
    .toast {
        top: 12px;
        right: 12px;
        left: 12px;
        min-width: unset;
        max-width: calc(100% - 24px);
        transform: translateY(-80px);
        border-radius: 8px;
        padding: 12px 14px;
    }
    .toast.show {
        transform: translateY(0);
    }
}

@media (max-width: 380px) {
    .dash-header-inner {
        padding: 16px 12px 12px;
    }
    .dash-header-icon { 
        width: 44px; 
        height: 44px; 
        font-size: 1.2rem; 
    }
    .dash-header-text h1 {
        font-size: 1.15rem;
    }
    #logout-btn {
        margin-left: auto;
        padding: 7px 12px;
        font-size: 0.78rem;
    }
    .confirm-delete-dialog {
        padding: 20px 15px 16px;
    }
}
</style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <!-- ✅ Dashboard Content -->
    <div id="dash-content" class="hidden">

        <!-- ═══════════════════════════════════════════
             📊 HEADER BANNER
        ═══════════════════════════════════════════ -->
        <header class="dash-header">
            <div class="dash-header-inner">
                <div class="dash-header-brand">
                    <div class="dash-header-text">
                        <h1>एडमिन डैशबोर्ड</h1>
                        <div class="org-name">छःन्यात राजस्थानी विप्र मंडल, लातूर</div>
                        <div class="welcome-line">
                            <i class="fas fa-user-circle"></i>
                            स्वागत है, <strong id="user-uid">Admin</strong>
                        </div>
                    </div>
                </div>
                <button type="button" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    लॉगआउट
                </button>
            </div>
            <div class="dash-header-strip"></div>
        </header>

        <!-- ═══════════════════════════════════════════
             🎛️ CARDS GRID
        ═══════════════════════════════════════════ -->
        <section class="admin-section">
            <div class="section-eyebrow">
                <div class="eyebrow-line"></div>
                <span>त्वरित कार्य</span>
                <div class="eyebrow-line"></div>
            </div>

            <div class="admin-grid">
                <!-- 👨‍👩‍👧‍👦 Add Family -->
                <div class="admin-card" onclick="location.href='family-management.php'">
                    <div class="card-top-bar"></div>
                    <span class="card-badge badge-add">ADD</span>
                    <div class="card-body">
                        <h3>नया परिवार जोड़ें</h3>
                        <p>फॉर्म भरकर डेटाबेस में नया परिवार सेव करें।</p>
                        <span class="card-arrow">आगे बढ़ें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- ✏️ Manage Families -->
                <div class="admin-card edit-card" onclick="location.href='manage-families.php'">
                    <div class="card-top-bar"></div>
                    <span class="card-badge badge-manage">MANAGE</span>
                    <div class="card-body">
                        <h3>परिवार प्रबंधित करें</h3>
                        <p>मौजूदा परिवारों को देखें, एडिट करें या डिलीट करें।</p>
                        <span class="card-arrow">आगे बढ़ें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 👥 Committee Management -->
                <div class="admin-card" onclick="location.href='committee-management.php'">
                    <div class="card-top-bar" style="background: linear-gradient(90deg, var(--maroon), var(--maroon-soft));"></div>
                    <span class="card-badge badge-committee">COMMITTEE</span>
                    <div class="card-body">
                        <h3>कार्यकारिणी सदस्य</h3>
                        <p>समिति के नए सदस्य जोड़ें और कार्यकारिणी प्रबंधित करें।</p>
                        <span class="card-arrow">आगे बढ़ें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 📢 Announcement Management -->
                <div class="admin-card" onclick="location.href='announcement-management.php'">
                    <div class="card-top-bar" style="background: linear-gradient(90deg, var(--maroon), var(--maroon-soft));"></div>
                    <span class="card-badge badge-notice">NOTICES</span>
                    <div class="card-body">
                        <h3>घोषणा प्रबंधन</h3>
                        <p>नई घोषणाएं जोड़ें या पुरानी घोषणाओं को देखें/हटाएं।</p>
                        <span class="card-arrow">प्रबंधित करें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 📸 Gallery -->
                <div class="admin-card" onclick="location.href='gallery-admin.php'">
                    <div class="card-top-bar"></div>
                    <span class="card-badge badge-gallery">GALLERY</span>
                    <div class="card-body">
                        <h3>गैलरी अपलोड</h3>
                        <p>नई फोटो और albums गैलरी में जोड़ें।</p>
                        <span class="card-arrow">अपलोड करें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 📢 Local Ads Management -->
                <div class="admin-card" onclick="location.href='ad-management.php'">
                    <div class="card-top-bar" style="background: linear-gradient(90deg, #FF6B00, #C9941A);"></div>
                    <span class="card-badge badge-ads">ADS</span>
                    <div class="card-body">
                        <h3>स्थानीय विज्ञापन प्रबंधन</h3>
                        <p>होमपेज के प्रायोजक एवं व्यापारिक विज्ञापन जोड़ें व प्रबंधित करें।</p>
                        <span class="card-arrow">प्रबंधित करें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 📅 Calendar Management -->
                <div class="admin-card" onclick="location.href='calendar-management.php'">
                    <div class="card-top-bar" style="background: linear-gradient(90deg, #FF6B00, #7B1A1A);"></div>
                    <span class="card-badge badge-calendar">CALENDAR</span>
                    <div class="card-body">
                        <h3>सनातन दिनदर्शिका</h3>
                        <p>१२ माह पृष्ठ, मुखपृष्ठ, वार्षिक PDF एवं पंचांग पर्व/जयंतियां प्रबंधित करें।</p>
                        <span class="card-arrow">प्रबंधित करें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 🔐 Add Admin Management -->
                <div class="admin-card" onclick="location.href='add-admin.php'">
                    <div class="card-top-bar" style="background: linear-gradient(90deg, #8E24AA, #BA68C8);"></div>
                    <span class="card-badge badge-security">SECURITY</span>
                    <div class="card-body">
                        <h3>एडमिन यूजर जोड़ें</h3>
                        <p>नए एडमिन यूजर बनाएं, पासवर्ड व क्रेडेंशियल्स प्रबंधित करें।</p>
                        <span class="card-arrow">प्रबंधित करें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- 📜 Audit Trail / Activity Log -->
                <div class="admin-card" onclick="location.href='audit-logs.php'">
                    <div class="card-top-bar" style="background: linear-gradient(90deg, #0288D1, #26C6DA);"></div>
                    <span class="card-badge" style="background: #e1f5fe; color: #0277bd; border: 1px solid #b3e5fc;">LOGS</span>
                    <div class="card-body">
                        <h3>गतिविधि लॉग (Audit Trail)</h3>
                        <p>एडमिन लॉगिन, परिवार व डेटा बदलावों का सम्पूर्ण टाइमलाइन रिकॉर्ड देखें।</p>
                        <span class="card-arrow">लॉग देखें <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ⚠️ Custom Confirm Dialog -->
    <div class="confirm-overlay" id="custom-confirm-overlay"></div>
    <div class="confirm-delete-dialog" id="custom-confirm-dialog">
        <div class="confirm-dialog-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h4>घोषणा हटाएं?</h4>
        <p>क्या आप वाकई इस घोषणा को हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती।</p>
        <div class="confirm-delete-buttons">
            <button class="btn-cancel" id="confirm-cancel-btn">
                <i class="fas fa-times"></i> रद्द करें
            </button>
            <button class="btn-confirm-delete" id="confirm-delete-btn">
                <i class="fas fa-trash-alt"></i> हाँ, हटाएं
            </button>
        </div>
    </div>

    <div data-include="components/footer.php"></div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- ✅ Scripts -->
    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>"></script>
    <script type="module" src="js/admin-dashboard.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/admin-dashboard.js')?>"></script>
</body>
</html>