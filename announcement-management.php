<?php
session_start();
// Auth check will be done via JS/API, but we can add PHP check too
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
    <title>Announcement Management | छःन्यात राजस्थानी विप्र मंडल</title>
    
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

        /* ===== HEADER ===== */
        .page-header {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-deep) 100%);
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 15px rgba(0,0,0,0.2);
        }

        .page-header h1 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--gold-light);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h1 i {
            color: var(--saffron-light);
        }

        .back-btn {
            background: rgba(255,255,255,0.1);
            color: var(--gold-light);
            border: 1px solid rgba(240, 192, 96, 0.3);
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.2);
            border-color: var(--gold-light);
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

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--saffron);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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

        .btn-secondary {
            background: rgba(123, 26, 26, 0.1);
            color: var(--maroon);
            padding: 10px 22px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
        }
        .btn-secondary:hover {
            background: rgba(123, 26, 26, 0.2);
            color: var(--maroon-deep);
        }

        /* ===== ANNOUNCEMENTS LIST ===== */
        .announcements-section {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(201, 148, 26, 0.15);
        }

        .announcement-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px;
            background: var(--saffron-pale);
            border-radius: var(--radius-sm);
            margin-bottom: 12px;
            border: 1px solid rgba(201, 148, 26, 0.2);
            gap: 15px;
            flex-wrap: wrap;
        }

        .announcement-info {
            flex: 1;
            min-width: 0;
        }

        .announcement-info h4 {
            color: var(--maroon);
            margin-bottom: 8px;
            font-size: 1.05rem;
            font-family: 'Tiro Devanagari Hindi', serif;
        }

        .announcement-info p {
            color: var(--maroon-deep);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .announcement-info small {
            color: #666;
            font-size: 0.8rem;
            display: block;
        }

        .announcement-info small i {
            color: var(--saffron);
            margin-right: 5px;
        }

        .announcement-info a {
            color: var(--maroon);
            text-decoration: none;
            margin-left: 10px;
            transition: color 0.3s;
        }

        .announcement-info a:hover {
            color: var(--saffron);
        }

        .announcement-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .edit-announcement-btn {
            background: #1976d2;
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .edit-announcement-btn:hover {
            background: #1565c0;
            transform: scale(1.05);
        }

        .delete-announcement-btn {
            background: var(--red);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 10px 15px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .delete-announcement-btn:hover {
            background: var(--red-dark);
            transform: scale(1.05);
        }

        .delete-announcement-btn:disabled,
        .edit-announcement-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ===== EDIT MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            padding: 20px;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            border: 2px solid var(--gold);
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-deep));
            color: white;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-family: 'Tiro Devanagari Hindi', serif;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gold-light);
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            color: var(--saffron-light);
            transform: scale(1.1);
        }

        .modal-body {
            padding: 25px;
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

        /* ===== SUCCESS/ERROR MESSAGES ===== */
        .success-message,
        .error-message {
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 15px;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== TOAST CONTAINER ===== */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.2rem; }
            .form-row { grid-template-columns: 1fr; }
            .form-section, .announcements-section { padding: 15px; }
            .announcement-item { flex-direction: column; }
            .delete-announcement-btn { width: 100%; }
        }
    </style>
</head>
<body>
        <div data-include="components/header.php"></div>
    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Header -->
    
    <!-- Main Container -->
    <div class="page-container">
        <!-- Top Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <a href="admin-dashboard.php" class="btn-back-dashboard">
                <i class="fas fa-arrow-left"></i> <span>डैशबोर्ड पर वापस जाएं</span>
            </a>
            <div style="font-size: 0.9rem; color: var(--maroon);">
                <i class="fas fa-shield-alt"></i> घोषणा प्रबंधन प्रणाली
            </div>
        </div>
        
        <!-- Add Announcement Form -->
        <div class="section-divider">
            <h2><i class="fas fa-plus-circle"></i> नई घोषणा जोड़ें</h2>
        </div>

        <div class="form-section">
            <form id="announcement-form">
                <div class="form-group">
                    <label for="ann-title"><i class="fas fa-heading"></i> शीर्षक (Title) *</label>
                    <input type="text" id="ann-title" data-hindi-input="true" placeholder="उदा: वार्षिक सामुदायिक मिलन समारोह" required>
                </div>

                <div class="form-group">
                    <label for="ann-desc"><i class="fas fa-align-left"></i> विवरण (Description) *</label>
                    <textarea id="ann-desc" data-hindi-input="true" placeholder="घोषणा का पूरा विवरण यहाँ लिखें..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ann-date"><i class="fas fa-calendar-alt"></i> तिथि (Date)</label>
                        <input type="date" id="ann-date">
                    </div>

                    <div class="form-group">
                        <label for="ann-link"><i class="fas fa-link"></i> लिंक (Link)</label>
                        <input type="url" id="ann-link" placeholder="https://example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label for="ann-branch"><i class="fas fa-sitemap"></i> संबंधित समाज / शाखा (Branch)</label>
                    <select id="ann-branch" style="width: 100%; padding: 12px 15px; border: 2px solid rgba(201, 148, 26, 0.3); border-radius: var(--radius-sm); font-size: 0.95rem; background: var(--cream); color: var(--maroon-deep);">
                        <option value="ALL">सभी समाज (ALL Central)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> घोषणा सेव करें
                </button>
            </form>
        </div>

        <!-- Existing Announcements List -->
        <div class="section-divider">
            <h2><i class="fas fa-list"></i> मौजूदा घोषणाएं</h2>
        </div>

        <div class="announcements-section">
            <div id="announcements-loading" class="loading-state">
                <i class="fas fa-spinner"></i>
                <p>घोषणाएं लोड हो रही हैं...</p>
            </div>

            <div id="announcements-empty" class="empty-state" style="display:none;">
                <i class="fas fa-inbox"></i>
                <p>अभी तक कोई घोषणा नहीं है।</p>
            </div>

            <div id="announcements-list" style="display:none;"></div>
        </div>
    </div>

    <!-- Edit Announcement Modal -->
    <div id="edit-announcement-modal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> घोषणा संपादित करें</h3>
                <button type="button" class="modal-close-btn" id="close-edit-modal-btn" aria-label="बंद करें">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-announcement-form">
                    <input type="hidden" id="edit-ann-id">

                    <div class="form-group">
                        <label for="edit-ann-title"><i class="fas fa-heading"></i> शीर्षक (Title) *</label>
                        <input type="text" id="edit-ann-title" data-hindi-input="true" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-ann-desc"><i class="fas fa-align-left"></i> विवरण (Description) *</label>
                        <textarea id="edit-ann-desc" data-hindi-input="true" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-ann-date"><i class="fas fa-calendar-alt"></i> तिथि (Date)</label>
                            <input type="date" id="edit-ann-date">
                        </div>

                        <div class="form-group">
                            <label for="edit-ann-link"><i class="fas fa-link"></i> लिंक (Link)</label>
                            <input type="url" id="edit-ann-link" placeholder="https://example.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit-ann-branch"><i class="fas fa-sitemap"></i> संबंधित समाज / शाखा (Branch)</label>
                        <select id="edit-ann-branch" style="width: 100%; padding: 12px 15px; border: 2px solid rgba(201, 148, 26, 0.3); border-radius: var(--radius-sm); font-size: 0.95rem; background: var(--cream); color: var(--maroon-deep);">
                            <option value="ALL">सभी समाज (ALL Central)</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" id="cancel-edit-btn">
                            रद्द करें
                        </button>
                        <button type="submit" class="btn btn-primary" id="save-edit-btn">
                            <i class="fas fa-save"></i> अपडेट करें
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div data-include="components/footer.php"></div>

    <!-- ✅ Scripts -->
    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/hindi-transliteration.js?v=<?= @filemtime(__DIR__.'/js/hindi-transliteration.js') ?: time()?>"></script>
    <script src="js/announcement-management.js?v=<?= @filemtime(__DIR__.'/js/announcement-management.js') ?: time()?>"></script>
</body>
</html>