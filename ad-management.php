<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advertisement Management | छःन्यात राजस्थानी विप्र मंडल</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__.'/css/style.css') ?: time()?>">
    
    <style>
        :root {
            --saffron: #FF6B00;
            --saffron-deep: #D94F00;
            --saffron-light: #FF9A3C;
            --saffron-pale: #FFF3E6;
            --maroon: #7B1A1A;
            --maroon-deep: #550F0F;
            --gold: #C9941A;
            --gold-light: #F0C060;
            --gold-pale: #FDF5E0;
            --cream: #FEF9F0;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 20px rgba(123, 26, 26, 0.1);
            --shadow-hover: 0 8px 30px rgba(123, 26, 26, 0.2);
            --green: #2e7d32;
            --red: #c62828;
            --red-dark: #b71c1c;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Noto Sans Devanagari', sans-serif;
            background: var(--cream);
            color: var(--maroon-deep);
            min-height: 100vh;
        }

        /* ===== MAIN CONTAINER ===== */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* ===== SECTION DIVIDER ===== */
        .section-divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0 20px;
        }

        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .section-divider h2 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon);
            font-size: 1.4rem;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-divider h2 i {
            color: var(--saffron);
        }

        /* ===== FORM SECTION ===== */
        .form-section {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(201, 148, 26, 0.15);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--maroon);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group label i {
            color: var(--saffron);
            margin-right: 5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(201, 148, 26, 0.3);
            border-radius: var(--radius-sm);
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 0.95rem;
            color: var(--maroon-deep);
            background: var(--cream);
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--saffron);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        /* Upload Area */
        .upload-dropzone {
            border: 2px dashed var(--gold);
            border-radius: var(--radius-sm);
            padding: 25px 20px;
            text-align: center;
            background: var(--saffron-pale);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-dropzone:hover {
            background: #ffe8d2;
            border-color: var(--saffron);
        }

        .upload-dropzone i {
            font-size: 2.5rem;
            color: var(--saffron);
            margin-bottom: 10px;
            display: block;
        }

        .upload-dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .image-preview-box {
            margin-top: 15px;
            display: none;
            text-align: center;
            background: #fff;
            padding: 10px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(201, 148, 26, 0.3);
        }

        .image-preview-box img {
            max-width: 100%;
            max-height: 160px;
            object-fit: contain;
            border-radius: 4px;
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 12px 30px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--saffron), var(--saffron-deep));
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 0, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: rgba(123, 26, 26, 0.1);
            color: var(--maroon);
        }
        .btn-secondary:hover {
            background: rgba(123, 26, 26, 0.2);
        }

        /* ===== ADS LIST SECTION ===== */
        .ads-list-section {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(201, 148, 26, 0.15);
        }

        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .ad-card-item {
            background: var(--cream);
            border: 1px solid rgba(201, 148, 26, 0.25);
            border-radius: var(--radius-sm);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .ad-card-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(123, 26, 26, 0.12);
        }

        .ad-card-image-wrap {
            width: 100%;
            height: 180px;
            background: #fdfaf5;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .ad-card-image-wrap img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .ad-card-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .ad-card-badge.active {
            background: #2e7d32;
            color: #fff;
        }

        .ad-card-badge.inactive {
            background: #757575;
            color: #fff;
        }

        .ad-card-order {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .ad-card-position-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            background: var(--saffron-pale);
            color: var(--saffron-deep);
            border: 1px solid rgba(255, 107, 0, 0.25);
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 600;
            margin-bottom: 8px;
            width: fit-content;
        }

        .ad-card-body {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .ad-card-title {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon);
            font-size: 1.15rem;
            margin-bottom: 5px;
        }

        .ad-card-biz {
            color: #666;
            font-size: 0.88rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .ad-card-meta {
            font-size: 0.82rem;
            color: var(--maroon-deep);
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 12px;
        }

        .ad-card-meta a {
            color: var(--maroon);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .ad-card-meta a:hover {
            color: var(--saffron);
        }

        .ad-card-actions {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(201, 148, 26, 0.2);
        }

        .btn-status-toggle {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            border: 1px solid;
            cursor: pointer;
            font-weight: 600;
            background: transparent;
            transition: all 0.2s;
        }

        .btn-status-toggle.is-active {
            color: #2e7d32;
            border-color: #2e7d32;
        }
        .btn-status-toggle.is-active:hover {
            background: #2e7d32;
            color: #fff;
        }

        .btn-status-toggle.is-inactive {
            color: #757575;
            border-color: #757575;
        }
        .btn-status-toggle.is-inactive:hover {
            background: #757575;
            color: #fff;
        }

        .action-btns-group {
            display: flex;
            gap: 6px;
        }

        .btn-icon-action {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.85rem;
            transition: transform 0.2s, background-color 0.2s;
        }

        .btn-edit-ad {
            background: rgba(201, 148, 26, 0.15);
            color: var(--maroon);
        }
        .btn-edit-ad:hover {
            background: var(--gold);
            color: #fff;
            transform: scale(1.1);
        }

        .btn-delete-ad {
            background: rgba(198, 40, 40, 0.12);
            color: var(--red);
        }
        .btn-delete-ad:hover {
            background: var(--red);
            color: #fff;
            transform: scale(1.1);
        }

        /* ===== EDIT MODAL ===== */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 15px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-dialog {
            background: #fff;
            border-radius: var(--radius);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            border: 2px solid var(--gold);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 18px 25px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-deep));
            color: var(--gold-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-family: 'Tiro Devanagari Hindi', serif;
            font-size: 1.25rem;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 25px;
        }

        /* ===== CONFIRM DIALOG ===== */
        .confirm-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.65);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1100;
        }

        .confirm-dialog {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(201, 148, 26, 0.3);
        }

        .confirm-icon {
            font-size: 3rem;
            color: var(--red);
            margin-bottom: 15px;
        }

        .confirm-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        /* ===== LOADING & EMPTY STATES ===== */
        .loading-state,
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }

        .loading-state i,
        .empty-state i {
            font-size: 2.5rem;
            color: var(--gold);
            margin-bottom: 15px;
            display: block;
        }

        .loading-state i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
            .form-section, .ads-list-section { padding: 15px; }
            .ads-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>
    <div id="toast-container"></div>

    <div class="page-container">
        <!-- Top Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <a href="admin-dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> डैशबोर्ड पर वापस जाएं
            </a>
            <div style="font-size: 0.9rem; color: var(--maroon);">
                <i class="fas fa-shield-alt"></i> स्थानीय विज्ञापन प्रबंधन प्रणाली
            </div>
        </div>

        <!-- Add Advertisement Form -->
        <div class="section-divider">
            <h2><i class="fas fa-plus-circle"></i> नया विज्ञापन जोड़ें</h2>
        </div>

        <div class="form-section">
            <form id="add-ad-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="ad-title"><i class="fas fa-heading"></i> विज्ञापन का नाम / शीर्षक *</label>
                        <input type="text" id="ad-title" name="title" placeholder="उदा: श्री बालाजी ज्वेलर्स & संस" required>
                    </div>

                    <div class="form-group">
                        <label for="ad-business"><i class="fas fa-store"></i> व्यापार / प्रायोजक का नाम</label>
                        <input type="text" id="ad-business" name="business_name" placeholder="उदा: प्रो. रमेश शर्मा">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ad-phone"><i class="fas fa-phone-alt"></i> संपर्क नंबर (Phone / WhatsApp)</label>
                        <input type="tel" id="ad-phone" name="phone" placeholder="उदा: 9822012345">
                    </div>

                    <div class="form-group">
                        <label for="ad-link"><i class="fas fa-globe"></i> वेबसाइट / वेब लिंक (वैकल्पिक)</label>
                        <input type="url" id="ad-link" name="link_url" placeholder="https://example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> विज्ञापन बैनर छवि (Banner Image) *</label>
                    <div class="upload-dropzone" id="ad-dropzone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p style="font-weight: 600; color: var(--maroon);">बैनर इमेज चुनें या यहाँ ड्रैग करें</p>
                        <small style="color: #666;">अनुशंसित आकार: 728x90, 600x200, या 400x150 (अधिकतम 5MB)</small>
                        <input type="file" id="ad-image-file" name="image" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="image-preview-box" id="ad-image-preview">
                        <img id="preview-img" src="" alt="पूर्वावलोकन">
                        <div><small id="preview-filename" style="color: #666;"></small></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ad-position"><i class="fas fa-layer-group"></i> विज्ञापन प्राथमिकता (Priority) *</label>
                        <select id="ad-position" name="position" required>
                            <option value="above_calendar">Priority 1 : सनातन दिनदर्शिका के ऊपर</option>
                            <option value="above_announcements">Priority 2 : नवीनतम घोषणाएँ के ऊपर</option>
                            <option value="above_committee">Priority 3 : कार्यकारिणी सदस्य के ऊपर</option>
                            <option value="below_committee">Priority 4 : कार्यकारिणी सदस्य के नीचे</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ad-order"><i class="fas fa-sort-numeric-down"></i> प्रदर्शन क्रम (Order)</label>
                        <input type="number" id="ad-order" name="display_order" value="0" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ad-status"><i class="fas fa-toggle-on"></i> स्थिति (Status)</label>
                        <select id="ad-status" name="status">
                            <option value="active" selected>सक्रिय (Active - वेबसाइट पर दिखेगा)</option>
                            <option value="inactive">निष्क्रिय (Inactive - छुपा रहेगा)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ad-end-date"><i class="fas fa-calendar-times"></i> समाप्ति तिथि (डिफ़ॉल्ट: 10 दिन बाद)</label>
                        <input type="date" id="ad-end-date" name="end_date">
                    </div>
                </div>

                <button type="submit" id="submit-ad-btn" class="btn btn-primary">
                    <i class="fas fa-save"></i> विज्ञापन सहेजें (Save Ad)
                </button>
            </form>
        </div>

        <!-- Existing Ads Section -->
        <div class="section-divider">
            <h2><i class="fas fa-bullhorn"></i> मौजूदा विज्ञापन सूची</h2>
        </div>

        <div class="ads-list-section">
            <div id="ads-loading" class="loading-state">
                <i class="fas fa-spinner"></i>
                <p>विज्ञापन लोड हो रहे हैं...</p>
            </div>

            <div id="ads-empty" class="empty-state" style="display:none;">
                <i class="fas fa-folder-open"></i>
                <p>वर्तमान में कोई विज्ञापन नहीं है। ऊपर दिए गए फॉर्म से नया विज्ञापन जोड़ें।</p>
            </div>

            <div id="ads-grid-container" class="ads-grid" style="display:none;"></div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="edit-ad-modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> विज्ञापन संपादित करें</h3>
                <button type="button" class="modal-close-btn" id="close-edit-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-ad-form" enctype="multipart/form-data">
                    <input type="hidden" id="edit-ad-id" name="id">

                    <div class="form-group">
                        <label for="edit-ad-title">शीर्षक *</label>
                        <input type="text" id="edit-ad-title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-ad-business">व्यापार / प्रायोजक का नाम</label>
                        <input type="text" id="edit-ad-business" name="business_name">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-ad-phone">फोन / WhatsApp</label>
                            <input type="tel" id="edit-ad-phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="edit-ad-link">लिंक</label>
                            <input type="url" id="edit-ad-link" name="link_url">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>नया बैनर चुनें (छोड़ दें यदि बदलना नहीं है)</label>
                        <input type="file" id="edit-ad-image-file" name="image" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-ad-position"><i class="fas fa-layer-group"></i> विज्ञापन प्राथमिकता (Priority) *</label>
                            <select id="edit-ad-position" name="position" required>
                                <option value="above_calendar">Priority 1 : सनातन दिनदर्शिका के ऊपर</option>
                                <option value="above_announcements">Priority 2 : नवीनतम घोषणाएँ के ऊपर</option>
                                <option value="above_committee">Priority 3 : कार्यकारिणी सदस्य के ऊपर</option>
                                <option value="below_committee">Priority 4 : कार्यकारिणी सदस्य के नीचे</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-ad-order">क्रम (Order)</label>
                            <input type="number" id="edit-ad-order" name="display_order" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-ad-status">स्थिति (Status)</label>
                            <select id="edit-ad-status" name="status">
                                <option value="active">सक्रिय (Active)</option>
                                <option value="inactive">निष्क्रिय (Inactive)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-ad-end-date">समाप्ति तिथि (वैकल्पिक)</label>
                            <input type="date" id="edit-ad-end-date" name="end_date">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                        <button type="button" class="btn btn-secondary" id="cancel-edit-btn">रद्द करें</button>
                        <button type="submit" id="save-edit-btn" class="btn btn-primary">
                            <i class="fas fa-check"></i> अपडेट करें
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="confirm-overlay" id="delete-confirm-overlay">
        <div class="confirm-dialog">
            <i class="fas fa-trash-alt confirm-icon"></i>
            <h3 style="color: var(--maroon); margin-bottom: 8px;">विज्ञापन हटाएं?</h3>
            <p style="color: #666; font-size: 0.9rem;">क्या आप वाकई इस विज्ञापन को हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती।</p>
            <div class="confirm-buttons">
                <button type="button" class="btn btn-secondary" id="cancel-delete-btn">रद्द करें</button>
                <button type="button" class="btn btn-primary" id="confirm-delete-btn" style="background: var(--red);">
                    <i class="fas fa-trash"></i> हटाएं
                </button>
            </div>
        </div>
    </div>

    <div data-include="components/footer.php"></div>

    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script type="module" src="js/ad-management.js?v=<?= @filemtime(__DIR__.'/js/ad-management.js') ?: time()?>"></script>
</body>
</html>
