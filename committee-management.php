<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>कार्यकारिणी प्रबंधन - Admin Dashboard</title>
    
    <!-- Include your main CSS (which contains the :root variables) -->
    <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__.'/css/style.css') ?: time()?>"> 
    <style>
        /* ========================================= */
        /* 🛠️ BASE & RESET                           */
        /* ========================================= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            background-color: var(--bg-light); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        
        .page-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        
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
            color: var(--maroon);
            font-size: 1.4rem;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .section-divider h2 i {
            color: var(--saffron);
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
            color: var(--maroon-deep); margin-top: 0; margin-bottom: var(--spacing-md); 
            padding-bottom: var(--spacing-sm); border-bottom: 2px solid var(--saffron-pale);
            font-size: clamp(1.1rem, 2vw, 1.4rem);
        }
        .card h4 { color: var(--text-mid); margin-top: var(--spacing-lg); margin-bottom: var(--spacing-sm); }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md); margin-top: var(--spacing-md); }
        .form-grid input, .form-grid select { 
            padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius-sm); 
            font-size: 0.95rem; background: var(--white); color: var(--text-dark); 
            transition: var(--transition); width: 100%; min-height: 48px; /* Touch-friendly */
        }
        .form-grid input:focus, .form-grid select:focus {
            outline: none; border-color: var(--saffron); box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
        }
        .full-width { grid-column: 1 / -1; }

        .upload-wrapper { display: flex; flex-direction: column; gap: 10px; }
        .upload-wrapper input[type="file"] { padding: 8px; background: transparent; border: none; }
        .preview-img { 
            width: 50px; height: 50px; border-radius: 50%; object-fit: cover; 
            border: 2px solid var(--border); align-self: flex-start; display: none;
        }

        .btn { 
            padding: 12px 16px; border: none; border-radius: var(--radius-sm); cursor: pointer; 
            font-size: 1rem; font-weight: 600; color: white; width: 100%; 
            transition: var(--transition); text-decoration: none; 
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 48px;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .btn:active { transform: translateY(0); }
        
        .btn-primary { background: var(--maroon); } 
        .btn-primary:hover { background: var(--saffron-deep); }
        .btn-success { background: var(--maroon); } 
        .btn-success:hover { background: var(--maroon-deep); }
        
        .btn-secondary { 
            background: transparent; color: var(--maroon); border: 2px solid var(--maroon); 
            width: auto; padding: 10px 24px; white-space: nowrap;
        }
        .btn-secondary:hover { background: var(--maroon); color: white; }

        .list-container { 
            max-height: 400px; overflow-y: auto; overflow-x: hidden;
            margin-bottom: 20px; border: 1px solid var(--border); padding: var(--spacing-md); 
            border-radius: var(--radius-sm); background: var(--cream); word-wrap: break-word;
        }
        .list-container::-webkit-scrollbar { width: 8px; }
        .list-container::-webkit-scrollbar-thumb { background: var(--gold-light); border-radius: 4px; }
        .list-container::-webkit-scrollbar-track { background: transparent; }

        /* Member Card Item Styles */
        .member-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--saffron-pale);
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
            border: 1px solid var(--border);
            gap: 10px;
        }
        .member-item-row.central {
            background: var(--gold-pale);
        }
        .member-actions-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Order Reordering Buttons */
        .order-btn {
            background: var(--saffron-pale);
            color: var(--saffron-deep);
            border: 1px solid var(--saffron);
            border-radius: var(--radius-sm);
            padding: 6px 10px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .order-btn:hover:not(:disabled) {
            background: var(--saffron);
            color: white;
        }
        .order-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            border-color: #ccc;
            background: #f0f0f0;
            color: #888;
        }

        /* Toast Container */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }

        /* ========================================= */
        /* 📱 RESPONSIVE BREAKPOINTS                 */
        /* ========================================= */
        @media (max-width: 768px) {
            .page-container { padding: 0 14px; margin: 16px auto; }
            
            .page-header { flex-direction: column; align-items: stretch; text-align: center; gap: 12px; }
            .btn-secondary { width: 100%; justify-content: center; padding: 12px 16px; }
            
            .form-grid { grid-template-columns: 1fr; }
            .form-grid input, .form-grid select { font-size: 16px; padding: 12px 14px; min-height: 48px; } /* Prevents iOS auto-zoom */
            .card { padding: 16px 14px; }
            .list-container { max-height: 380px; padding: 10px; }
            #toast-container { right: 10px; left: 10px; top: 10px; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        }

        @media (max-width: 640px) {
            .member-item-row {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 12px 10px;
            }
            .member-actions-group {
                width: 100%;
                justify-content: space-between;
                border-top: 1px dashed rgba(201, 148, 26, 0.3);
                padding-top: 8px;
            }
            .member-actions-group .order-btn, 
            .member-actions-group button {
                flex: 1;
                justify-content: center;
                padding: 8px 6px;
                font-size: 0.8rem;
                min-height: 40px;
            }
        }

        @media (max-width: 480px) {
            .page-container { padding: 0 10px; margin: 10px auto; }
            .card { padding: 12px 10px; margin-bottom: 16px; }
            .card h3 { font-size: 1.1rem; }
            .btn { font-size: 0.92rem; padding: 12px; min-height: 48px; }
            .form-grid input, .form-grid select { font-size: 16px; padding: 10px 12px; }
            #toast-container { top: 8px; right: 8px; left: 8px; }
        }

        /* 📸 Photo Selection Box & Family Photo Picker */
        .photo-selection-box {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
        }
        .btn-choose-family {
            background: var(--gold-pale);
            color: var(--maroon);
            border: 1.5px solid var(--gold);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: var(--radius-sm);
            padding: 8px 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }
        .btn-choose-family:hover {
            background: var(--maroon);
            color: white;
            border-color: var(--maroon);
            transform: translateY(-1px);
        }
        .selected-photo-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 107, 0, 0.08);
            border: 1px dashed var(--saffron);
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            margin-top: 10px;
        }
        .family-photo-modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .family-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(4px);
        }
        .family-modal-dialog {
            position: relative;
            background: var(--bg-card);
            border-radius: var(--radius);
            width: 100%;
            max-width: 820px;
            max-height: 85vh;
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
        .family-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 2px solid var(--saffron-pale);
            background: var(--cream);
        }
        .family-modal-header h3 {
            margin: 0;
            color: var(--maroon);
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .family-modal-close {
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
        .family-modal-close:hover {
            color: var(--red);
            background: rgba(220, 38, 38, 0.1);
        }
        .family-modal-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }
        .family-search-row {
            display: flex;
            gap: 12px;
            align-items: center;
            background: #fff;
            padding: 8px;
            border-radius: var(--radius-sm, 10px);
            border: 1.5px solid rgba(201, 148, 26, 0.3);
            box-shadow: 0 2px 10px rgba(123, 26, 26, 0.04);
            margin-bottom: 6px;
            transition: all 0.25s ease;
        }
        .family-search-row:focus-within {
            border-color: var(--saffron);
            box-shadow: 0 4px 16px rgba(255, 107, 0, 0.15);
        }
        .family-search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
        }
        .family-search-input-wrap i {
            position: absolute;
            left: 14px;
            color: var(--saffron);
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s;
        }
        .family-search-input {
            width: 100%;
            padding: 11px 16px 11px 40px !important;
            border: 1px solid rgba(201, 148, 26, 0.2) !important;
            border-radius: 8px !important;
            font-size: 0.92rem !important;
            color: var(--maroon-deep) !important;
            background: var(--cream, #FEF9F0) !important;
            outline: none !important;
            font-family: inherit !important;
            transition: all 0.25s ease !important;
        }
        .family-search-input:focus {
            background: #fff !important;
            border-color: var(--saffron) !important;
        }
        .family-branch-select {
            padding: 11px 16px !important;
            border: 1px solid rgba(201, 148, 26, 0.25) !important;
            border-radius: 8px !important;
            font-size: 0.92rem !important;
            color: var(--maroon-deep) !important;
            background: var(--cream, #FEF9F0) !important;
            cursor: pointer !important;
            outline: none !important;
            font-family: inherit !important;
            font-weight: 500 !important;
            min-width: 150px !important;
            transition: all 0.25s ease !important;
        }
        .family-branch-select:focus {
            background: #fff !important;
            border-color: var(--saffron) !important;
        }
        @media (max-width: 600px) {
            .family-search-row {
                flex-direction: column;
                align-items: stretch;
            }
            .family-branch-select {
                width: 100% !important;
            }
        }
        .family-photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
            margin-top: 10px;
            max-height: 460px;
            overflow-y: auto;
            padding: 4px;
        }
        .family-photo-card {
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .family-photo-card:hover {
            border-color: var(--saffron);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(255, 107, 0, 0.18);
        }
        .family-photo-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
            border: 2px solid var(--saffron-pale);
            transition: transform 0.2s;
        }
        .family-photo-card:hover img {
            transform: scale(1.05);
            border-color: var(--saffron);
        }
        .family-photo-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--maroon-deep);
            line-height: 1.25;
            margin-bottom: 4px;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .family-photo-branch {
            font-size: 0.72rem;
            color: var(--text-soft);
            background: var(--gold-pale);
            padding: 2px 6px;
            border-radius: 8px;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <!-- Toast Container for Notifications -->
    <div id="toast-container"></div>

    <main class="page-container">
        <!-- Top Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <a href="admin-dashboard.php" class="btn-back-dashboard">
                <i class="fas fa-arrow-left"></i> <span>डैशबोर्ड पर वापस जाएं</span>
            </a>
            <div style="font-size: 0.9rem; color: var(--maroon);">
                <i class="fas fa-shield-alt"></i> कार्यकारिणी प्रबंधन प्रणाली
            </div>
        </div>

        <!-- Section Divider -->
        <div class="section-divider">
            <h2><i class="fas fa-users-cog"></i> कार्यकारिणी प्रबंधन</h2>
        </div>

        <!-- Hindi Transliteration Toggle Container -->
        <div id="committee-translit-toggle" style="margin-bottom: 15px;"></div>

        <!-- 1. CENTRAL COMMITTEE SECTION -->
        <section class="card">
            <h3><i class="fas fa-landmark"></i> केंद्रीय कार्यकारिणी (छःन्यात राजस्थानी विप्र मंडल) </h3>
            <div id="central-committee-list" class="list-container">
                <p style="text-align:center;color:var(--text-secondary);">लोड हो रहा है...</p>
            </div>
            
            <h4>नया केंद्रीय सदस्य जोड़ें</h4>
            <form id="central-committee-form" class="form-grid">
                <input type="text" id="central-comm-name" placeholder="नाम *" required data-hindi-input="true">
                <input type="text" id="central-comm-designation" placeholder="पदनाम *" required data-hindi-input="true">
                <input type="text" id="central-comm-mobile" placeholder="मोबाइल नंबर">
                
                <div class="photo-selection-box full-width">
                    <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid); display: block; margin-bottom: 6px;">
                        <i class="fas fa-camera"></i> सदस्य फोटो
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div class="upload-wrapper" style="flex: 1; min-width: 180px;">
                            <input type="file" id="central-comm-photo-file" accept="image/*" title="डिवाइस से नई फोटो चुनें">
                        </div>
                        <span style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600;">— अथवा —</span>
                        <button type="button" class="btn-choose-family" onclick="openFamilyPhotoPicker('central')">
                            <i class="fas fa-images"></i> परिवार फोटो से चुनें
                        </button>
                    </div>
                    
                    <div id="central-comm-photo-selected" class="selected-photo-badge" style="display: none;">
                        <img id="central-comm-photo-preview" class="preview-img" src="" alt="Preview" style="display: block; width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid var(--saffron);">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--maroon-deep);" id="central-comm-photo-label">चयनित फोटो</div>
                            <small style="color: var(--text-soft); font-size: 0.75rem; word-break: break-all;" id="central-comm-photo-source"></small>
                        </div>
                        <button type="button" onclick="clearSelectedPhoto('central')" style="background: transparent; border: none; color: var(--red); cursor: pointer; font-size: 1.1rem; padding: 4px 8px;" title="फोटो हटाएं">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <input type="hidden" id="central-comm-photo" value="">
                    <input type="hidden" id="central-comm-family-id" value="">
                </div>

                <button type="submit" id="central-committee-submit" class="btn btn-success full-width">
                    <i class="fas fa-plus"></i> केंद्रीय सदस्य जोड़ें
                </button>
            </form>
        </section>

        <!-- 2. BRANCH COMMITTEE SECTION -->
        <section class="card">
            <h3><i class="fas fa-sitemap"></i> समाज कार्यकारिणी</h3>
            
            <div style="margin-bottom: 20px;">
                <label for="committee-branch-select" style="font-weight: bold; display: block; margin-bottom: 8px; color: var(--text-mid);">समाज चुनें:</label>
                <select id="committee-branch-select" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 1rem;">
                    <option value="">लोड हो रहा है...</option>
                </select>
            </div>

            <div id="committee-list" class="list-container">
                <p style="color:var(--text-secondary);text-align:center;margin:0;">कृपया पहले एक समाज चुनें।</p>
            </div>

            <h4>नया समाज सदस्य जोड़ें</h4>
            <form id="committee-form" class="form-grid">
                <input type="text" id="comm-name" placeholder="नाम *" required data-hindi-input="true">
                <input type="text" id="comm-designation" placeholder="पदनाम *" required data-hindi-input="true">
                <input type="text" id="comm-mobile" placeholder="मोबाइल नंबर">
                
                <div class="photo-selection-box full-width">
                    <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid); display: block; margin-bottom: 6px;">
                        <i class="fas fa-camera"></i> सदस्य फोटो
                    </label>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div class="upload-wrapper" style="flex: 1; min-width: 180px;">
                            <input type="file" id="comm-photo-file" accept="image/*" title="डिवाइस से नई फोटो चुनें">
                        </div>
                        <span style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600;">— अथवा —</span>
                        <button type="button" class="btn-choose-family" onclick="openFamilyPhotoPicker('branch')">
                            <i class="fas fa-images"></i> परिवार फोटो से चुनें
                        </button>
                    </div>
                    
                    <div id="comm-photo-selected" class="selected-photo-badge" style="display: none;">
                        <img id="comm-photo-preview" class="preview-img" src="" alt="Preview" style="display: block; width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid var(--saffron);">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--maroon-deep);" id="comm-photo-label">चयनित फोटो</div>
                            <small style="color: var(--text-soft); font-size: 0.75rem; word-break: break-all;" id="comm-photo-source"></small>
                        </div>
                        <button type="button" onclick="clearSelectedPhoto('branch')" style="background: transparent; border: none; color: var(--red); cursor: pointer; font-size: 1.1rem; padding: 4px 8px;" title="फोटो हटाएं">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <input type="hidden" id="comm-photo" value="">
                    <input type="hidden" id="comm-family-id" value="">
                </div>

                <button type="submit" class="btn btn-primary full-width">
                    <i class="fas fa-plus"></i> समाज सदस्य जोड़ें
                </button>
            </form>
        </section>
    </main>

    <!-- ══ EDIT COMMITTEE MEMBER MODAL ═══ -->
    <div id="edit-committee-modal" class="family-photo-modal" role="dialog" aria-modal="true" style="display: none;">
        <div class="family-modal-backdrop" onclick="closeEditCommitteeModal()"></div>
        <div class="family-modal-dialog" style="max-width: 580px;">
            <div class="family-modal-header">
                <h3><i class="fas fa-user-edit"></i> कार्यकारिणी सदस्य संपादित करें</h3>
                <button type="button" class="family-modal-close" onclick="closeEditCommitteeModal()" aria-label="बंद करें">&times;</button>
            </div>
            <div class="family-modal-body">
                <form id="edit-committee-form" class="form-grid" style="grid-template-columns: 1fr;">
                    <input type="hidden" id="edit-comm-type" value="branch">
                    <input type="hidden" id="edit-comm-index" value="">
                    <input type="hidden" id="edit-comm-id" value="">
                    <input type="hidden" id="edit-comm-orig-photo" value="">
                    <input type="hidden" id="edit-comm-orig-family-id" value="">

                    <div>
                        <label style="font-weight: 600; font-size: 0.88rem; color: var(--text-mid); display: block; margin-bottom: 5px;">
                            पूरा नाम <span style="color: var(--red);">*</span>
                        </label>
                        <input type="text" id="edit-comm-name" placeholder="नाम *" required data-hindi-input="true" style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 0.88rem; color: var(--text-mid); display: block; margin-bottom: 5px;">
                            पदनाम <span style="color: var(--red);">*</span>
                        </label>
                        <input type="text" id="edit-comm-designation" placeholder="पदनाम *" required data-hindi-input="true" style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 0.88rem; color: var(--text-mid); display: block; margin-bottom: 5px;">
                            मोबाइल नंबर
                        </label>
                        <input type="text" id="edit-comm-mobile" placeholder="मोबाइल नंबर" style="width: 100%;">
                    </div>

                    <div class="photo-selection-box" style="margin-top: 5px;">
                        <label style="font-weight: 600; font-size: 0.88rem; color: var(--text-mid); display: block; margin-bottom: 6px;">
                            <i class="fas fa-camera"></i> सदस्य फोटो
                        </label>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <div class="upload-wrapper" style="flex: 1; min-width: 180px;">
                                <input type="file" id="edit-comm-photo-file" accept="image/*" title="डिवाइस से नई फोटो चुनें">
                            </div>
                            <span style="color: var(--text-soft); font-size: 0.85rem; font-weight: 600;">— अथवा —</span>
                            <button type="button" class="btn-choose-family" onclick="openFamilyPhotoPicker('edit')">
                                <i class="fas fa-images"></i> परिवार फोटो से चुनें
                            </button>
                        </div>
                        
                        <div id="edit-comm-photo-selected" class="selected-photo-badge" style="display: none; margin-top: 10px;">
                            <img id="edit-comm-photo-preview" class="preview-img" src="" alt="Preview" style="display: block; width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--saffron);">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--maroon-deep);" id="edit-comm-photo-label">फोटो</div>
                                <small style="color: var(--text-soft); font-size: 0.75rem; word-break: break-all;" id="edit-comm-photo-source"></small>
                            </div>
                            <button type="button" onclick="clearSelectedPhoto('edit')" style="background: transparent; border: none; color: var(--red); cursor: pointer; font-size: 1.1rem; padding: 4px 8px;" title="फोटो हटाएं">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        <input type="hidden" id="edit-comm-photo" value="">
                        <input type="hidden" id="edit-comm-family-id" value="">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="submit" id="btn-save-edit-comm" class="btn btn-primary" style="flex: 2;">
                            <i class="fas fa-save"></i> बदलाव सहेजें
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditCommitteeModal()" style="flex: 1;">
                            रद्द करें
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══ FAMILY PHOTO PICKER MODAL ═══ -->
    <div id="family-photo-modal" class="family-photo-modal" role="dialog" aria-modal="true" style="display: none;">
        <div class="family-modal-backdrop" onclick="closeFamilyPhotoPicker()"></div>
        <div class="family-modal-dialog">
            <div class="family-modal-header">
                <h3><i class="fas fa-portrait"></i> परिवार फोटो से चुनें</h3>
                <button type="button" class="family-modal-close" onclick="closeFamilyPhotoPicker()" aria-label="बंद करें">&times;</button>
            </div>
            <div class="family-modal-body">
                <div class="family-search-row">
                    <div class="family-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="family-picker-search" class="family-search-input" placeholder="मुखिया के नाम, समाज या फ़ाइल नाम से खोजें...">
                    </div>
                    <select id="family-picker-branch-filter" class="family-branch-select">
                        <option value="">सभी समाज</option>
                    </select>
                </div>
                <div id="family-photos-count" style="font-size: 0.82rem; color: var(--text-soft); margin: 8px 0 10px;"></div>
                <div id="family-photos-grid" class="family-photos-grid">
                    <p style="text-align: center; color: var(--text-soft); grid-column: 1/-1;">लोड हो रहा है...</p>
                </div>
            </div>
        </div>
    </div>

    
    <div data-include="components/footer.php"></div>

    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/hindi-transliteration.js?v=<?= @filemtime(__DIR__.'/js/hindi-transliteration.js') ?: time()?>"></script>
    <script src="js/committee-management.js?v=<?= @filemtime(__DIR__.'/js/committee-management.js') ?: time()?>"></script>
</body>
</html>