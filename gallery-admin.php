<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Upload | छःन्यात राजस्थानी विप्र मंडल</title>
    
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

        /* ===== MAIN LAYOUT ===== */
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

        /* ===== UPLOAD SECTION ===== */
        .upload-section {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(201, 148, 26, 0.15);
        }

        .album-select-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .album-select-row .field {
            flex: 1;
            min-width: 250px;
        }

        .album-select-row label {
            display: block;
            font-weight: 600;
            color: var(--maroon);
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .album-select-row select,
        .album-select-row input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid rgba(201, 148, 26, 0.3);
            border-radius: var(--radius-sm);
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 0.95rem;
            color: var(--maroon-deep);
            background: var(--cream);
            transition: border-color 0.3s;
        }

        .album-select-row select:focus,
        .album-select-row input:focus {
            outline: none;
            border-color: var(--saffron);
        }

        /* Drop Zone */
        .drop-zone {
            border: 2px dashed var(--gold);
            border-radius: var(--radius);
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--gold-pale);
            margin-bottom: 15px;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: var(--saffron);
            background: var(--saffron-pale);
            transform: scale(1.01);
        }

        .drop-zone i {
            font-size: 2.5rem;
            color: var(--saffron);
            margin-bottom: 10px;
        }

        .drop-zone p {
            color: var(--maroon);
            font-weight: 500;
        }

        .drop-zone small {
            color: #888;
            font-size: 0.8rem;
        }

        /* Preview */
        .preview-container {
            display: none;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--saffron-pale);
            border-radius: var(--radius-sm);
            max-height: 250px;
            overflow-y: auto;
        }

        .preview-container.active { display: flex; }

        .preview-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 2px solid var(--gold-light);
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-remove {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(123, 26, 26, 0.9);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-info {
            display: none;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: var(--gold-pale);
            border-radius: var(--radius-sm);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .file-info.active { display: flex; }

        .file-info span { color: var(--maroon); font-weight: 500; }

        /* Progress */
        .progress-wrapper {
            display: none;
            margin-bottom: 15px;
        }

        .progress-wrapper.active { display: block; }

        .progress-bar-track {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--saffron), var(--gold));
            border-radius: 10px;
            transition: width 0.3s;
            width: 0%;
            text-align: center;
            font-size: 0.7rem;
            color: white;
            line-height: 8px;
        }

        .progress-text {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
            text-align: center;
        }

        /* Upload Status */
        .upload-status {
            display: none;
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .upload-status.active { display: block; }
        .upload-status.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .upload-status.error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .upload-status.info { background: var(--saffron-pale); color: var(--maroon); border: 1px solid var(--saffron-light); }

        /* Buttons */
        .btn-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

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
            background: transparent;
            color: var(--maroon);
            border: 2px solid var(--maroon);
        }

        .btn-secondary:hover {
            background: var(--maroon);
            color: white;
        }

        /* ===== ALBUMS GRID ===== */
        .albums-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .album-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(201, 148, 26, 0.15);
            position: relative;
        }

        .album-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--gold);
        }

        .album-cover {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--saffron-pale), var(--gold-pale));
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .album-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .album-card:hover .album-cover img {
            transform: scale(1.05);
        }

        /* Lazy placeholder */
        .album-cover .lazy-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            gap: 8px;
        }

        .album-cover .lazy-placeholder i {
            font-size: 2.5rem;
            animation: pulse 2s infinite;
        }

        .album-cover .lazy-placeholder span {
            font-size: 0.8rem;
            font-family: 'Poppins', sans-serif;
        }

        .album-cover img.loaded + .lazy-placeholder {
            display: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .album-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(123, 26, 26, 0.85);
            color: var(--gold-light);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            backdrop-filter: blur(5px);
            font-family: 'Poppins', sans-serif;
        }

        .album-info {
            padding: 15px;
        }

        .album-info h3 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon);
            font-size: 1.1rem;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .album-info p {
            color: #888;
            font-size: 0.8rem;
            font-family: 'Poppins', sans-serif;
        }

        /* ===== ALBUM DETAIL VIEW ===== */
        .album-detail-view {
            display: none;
            margin-top: 20px;
        }

        .album-detail-view.active { display: block; }

        .album-detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .album-detail-header h2 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .album-detail-header h2 i {
            color: var(--saffron);
        }

        .back-to-albums {
            background: var(--maroon);
            color: var(--gold-light);
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-to-albums:hover {
            background: var(--maroon-deep);
        }

        .album-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .album-image-item {
            position: relative;
            border-radius: var(--radius-sm);
            overflow: hidden;
            aspect-ratio: 1;
            background: var(--gold-pale);
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .album-image-item:hover {
            transform: scale(1.03);
        }

        .album-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .album-image-item .lazy-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            pointer-events: none;
        }

        .album-image-item .lazy-placeholder i {
            font-size: 2rem;
            animation: pulse 2s infinite;
        }

        .album-image-item img.loaded + .lazy-placeholder {
            display: none;
        }

        .image-delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(198, 40, 40, 0.9);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            opacity: 0;
        }

        .album-image-item:hover .image-delete-btn {
            display: flex;
            opacity: 1;
        }

        .image-delete-btn:hover {
            background: #b71c1c;
            transform: scale(1.1);
        }

        .album-delete-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(198, 40, 40, 0.9);
            color: white;
            border: 2px solid white;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        .album-delete-btn:hover {
            background: #b71c1c;
            transform: scale(1.15);
        }

        /* Loading spinner for album open */
        .album-loading {
            text-align: center;
            padding: 60px 20px;
            color: var(--maroon);
        }

        .album-loading i {
            font-size: 2.5rem;
            color: var(--saffron);
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gold-light);
            margin-bottom: 15px;
        }

        .empty-state p {
            font-size: 1.1rem;
        }

        /* ===== LIGHTBOX ===== */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.92);
            z-index: 999999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .lightbox.active { display: flex; }

        .lightbox img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: var(--radius-sm);
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 25px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .lightbox-close:hover { background: rgba(255,255,255,0.3); }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            color: white;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .lightbox-nav:hover { background: rgba(255,255,255,0.3); }
        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.2rem; }
            .albums-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
            .album-images-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
            .upload-section { padding: 15px; }
            .album-select-row { flex-direction: column; }
        }

        @media (max-width: 480px) {
            .albums-grid { grid-template-columns: 1fr; }
            .album-images-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>
    <!-- ===== HEADER ===== -->

    <!-- ===== MAIN ===== -->
    <div class="page-container">
        <!-- Top Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <a href="admin-dashboard.php" class="btn-back-dashboard">
                <i class="fas fa-arrow-left"></i> <span>डैशबोर्ड पर वापस जाएं</span>
            </a>
            <div style="font-size: 0.9rem; color: var(--maroon);">
                <i class="fas fa-shield-alt"></i> गैलरी प्रबंधन प्रणाली
            </div>
        </div>

        <!-- ===== UPLOAD SECTION ===== -->
        <div class="section-divider">
            <h2><i class="fas fa-cloud-upload-alt"></i> नई Photos अपलोड करें</h2>
        </div>

        <!-- Hindi Transliteration Toggle Container -->
        <div id="gallery-translit-toggle" style="margin-bottom: 15px;"></div>

        <div class="upload-section">
            <form id="gallery-upload-form">
                <div class="album-select-row">
                    <div class="field">
                        <label for="existing-albums"><i class="fas fa-folder-open"></i> Existing Album चुनें</label>
                        <select id="existing-albums">
                            <option value="">-- नया Album बनाएं (नीचे नाम भरें) --</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="album-name"><i class="fas fa-plus-circle"></i> या नया Album नाम</label>
                        <input type="text" id="album-name" placeholder="उदा: वार्षिक सामुदायिक मिलन समारोह 2025" data-hindi-input="true">
                    </div>
                    <div class="field">
                        <label for="gallery-branch"><i class="fas fa-sitemap"></i> संबंधित समाज / शाखा (Branch)</label>
                        <select id="gallery-branch">
                            <option value="ALL">सामूहिक</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 18px; padding: 10px 14px; background: rgba(201, 148, 26, 0.08); border: 1px solid rgba(201, 148, 26, 0.25); border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="auto-announcement" name="auto-announcement" checked style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--maroon, #7b1a1a);">
                    <label for="auto-announcement" style="margin: 0; cursor: pointer; font-size: 0.92rem; font-weight: 600; color: var(--maroon, #7b1a1a);">
                        <i class="fas fa-bullhorn" style="color: var(--saffron, #e65100); margin-right: 4px;"></i> फोटो अपलोड होने पर वेबसाइट पर स्वतः घोषणा (Announcement) प्रकाशित करें
                    </label>
                </div>

                <div class="drop-zone" id="drop-zone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>📸 Photos यहाँ Drag & Drop करें या Click करें</p>
                    <small>JPEG, PNG, WEBP, GIF — अधिकतम 15MB प्रति फोटो</small>
                    <input type="file" id="file-input" multiple accept="image/*" hidden>
                </div>

                <div class="preview-container" id="preview-container"></div>

                <div class="file-info" id="file-info">
                    <span>📷 <strong id="file-count">0</strong> photos</span>
                    <span>💾 Total: <strong id="total-size">0 B</strong></span>
                </div>

                <div class="progress-wrapper" id="progress-wrapper">
                    <div class="progress-bar-track">
                        <div class="progress-bar" id="progress-bar">0%</div>
                    </div>
                    <div class="progress-text" id="progress-text">तैयार...</div>
                </div>

                <div class="upload-status" id="upload-status"></div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary" id="upload-btn">
                        <i class="fas fa-upload"></i> अपलोड करें
                    </button>
                    <button type="button" class="btn btn-secondary" id="clear-btn">
                        <i class="fas fa-times"></i> साफ़ करें
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== ALBUMS LIST VIEW ===== -->
        <div id="albums-list-view">
            <div class="section-divider">
                <h2><i class="fas fa-photo-video"></i> पुराने Albums</h2>
            </div>

            <div id="albums-loading" class="album-loading">
                <i class="fas fa-spinner"></i>
                <p>Albums लोड हो रहे हैं...</p>
            </div>

            <div id="albums-empty" class="empty-state" style="display:none;">
                <i class="fas fa-folder-open"></i>
                <p>अभी तक कोई Album नहीं बना।</p>
            </div>

            <div class="albums-grid" id="albums-grid"></div>
        </div>

        <!-- ===== ALBUM DETAIL VIEW (Lazy Loaded Images) ===== -->
        <div class="album-detail-view" id="album-detail-view">
            <div class="album-detail-header">
                <h2 id="detail-album-title">
                    <i class="fas fa-images"></i> <span>Album Name</span>
                </h2>
                <button class="back-to-albums" id="back-to-albums-btn">
                    <i class="fas fa-arrow-left"></i> सभी Albums
                </button>
            </div>

            <div id="detail-loading" class="album-loading" style="display:none;">
                <i class="fas fa-spinner"></i>
                <p>Photos लोड हो रही हैं...</p>
            </div>

            <div class="album-images-grid" id="album-images-grid"></div>
        </div>
    </div>

    <!-- ===== LIGHTBOX ===== -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" id="lightbox-close"><i class="fas fa-times"></i></button>
        <button class="lightbox-nav lightbox-prev" id="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
        <button class="lightbox-nav lightbox-next" id="lightbox-next"><i class="fas fa-chevron-right"></i></button>
        <img id="lightbox-img" src="" alt="">
    </div>
    <div data-include="components/footer.php"></div>


    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/hindi-transliteration.js?v=<?= @filemtime(__DIR__.'/js/hindi-transliteration.js') ?: time()?>"></script>
    <script src="js/gallery-upload.js?v=<?= @filemtime(__DIR__.'/js/gallery-upload.js') ?: time()?>"></script>
</body>
</html>


