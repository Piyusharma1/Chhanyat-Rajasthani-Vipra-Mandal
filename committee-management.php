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
        
        .page-header {
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: 16px;
        }
        .page-header h2 { color: var(--maroon); margin: 0; font-size: clamp(1.3rem, 2.5vw, 1.8rem); }

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
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <!-- Toast Container for Notifications -->
    <div id="toast-container"></div>

    <main class="page-container">
        <div class="page-header">
            <h2><i class="fas fa-users-cog"></i> कार्यकारिणी प्रबंधन</h2>
            <a href="admin-dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> डैशबोर्ड पर वापस जाएं
            </a>
        </div>

        <!-- 1. CENTRAL COMMITTEE SECTION -->
        <section class="card">
            <h3><i class="fas fa-landmark"></i> केंद्रीय कार्यकारिणी (छःन्यात राजस्थानी विप्र मंडल) </h3>
            <div id="central-committee-list" class="list-container">
                <p style="text-align:center;color:var(--text-secondary);">लोड हो रहा है...</p>
            </div>
            
            <h4>नया केंद्रीय सदस्य जोड़ें</h4>
            <form id="central-committee-form" class="form-grid">
                <input type="text" id="central-comm-name" placeholder="नाम *" required>
                <input type="text" id="central-comm-designation" placeholder="पदनाम *" required>
                <input type="text" id="central-comm-mobile" placeholder="मोबाइल नंबर">
                <div class="upload-wrapper">
                    <input type="file" id="central-comm-photo-file" accept="image/*">
                    <img id="central-comm-photo-preview" class="preview-img" src="" alt="Preview">
                </div>
                <input type="text" id="central-comm-photo" placeholder="या फोटो URL (वैकल्पिक)" class="full-width">
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
                <input type="text" id="comm-name" placeholder="नाम *" required>
                <input type="text" id="comm-designation" placeholder="पदनाम *" required>
                <input type="text" id="comm-mobile" placeholder="मोबाइल नंबर">
                <div class="upload-wrapper">
                    <input type="file" id="comm-photo-file" accept="image/*">
                    <img id="comm-photo-preview" class="preview-img" src="" alt="Preview">
                </div>
                <input type="text" id="comm-photo" placeholder="या फोटो URL (वैकल्पिक)">
                <button type="submit" class="btn btn-primary full-width">
                    <i class="fas fa-plus"></i> समाज सदस्य जोड़ें
                </button>
            </form>
        </section>
    </main>
    
    <div data-include="components/footer.php"></div>

    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    <script src="js/committee-management.js?v=<?= @filemtime(__DIR__.'/js/committee-management.js') ?: time()?>"></script>
</body>
</html>