<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ऑडिट लॉग्स (Audit Trail) | छःन्यात राजस्थानी विप्र मंडल</title>
    <meta name="robots" content="noindex, nofollow">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            background-color: var(--bg-light, #FFF8F3); 
            font-family: 'Noto Sans Devanagari', 'Poppins', sans-serif; 
            color: var(--text-dark, #1A0A00);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        
        .page-container { max-width: 1240px; margin: 30px auto 60px; padding: 0 20px; }

        .top-nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

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
            background: linear-gradient(90deg, transparent, var(--border-strong, rgba(201,148,26,0.35)), transparent);
        }
        .section-divider h2 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon, #7B1A1A);
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        /* 🔍 Filter & Search Bar */
        .filter-card {
            background: var(--white, #fff);
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 4px 18px rgba(123,26,26,0.06);
            border: 1px solid var(--border, rgba(201,148,26,0.22));
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            flex: 1 1 650px;
        }

        .filter-input, .filter-select {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid var(--border, rgba(201,148,26,0.3));
            font-size: 0.9rem;
            outline: none;
            background: #fff;
            color: var(--text-dark, #1A0A00);
            transition: all 0.2s ease;
        }
        .filter-input:focus, .filter-select:focus {
            border-color: var(--saffron, #FF6B00);
            box-shadow: 0 0 0 3px var(--saffron-pale, #FFF3E6);
        }
        .filter-input { flex: 1 1 220px; }

        .btn-refresh {
            padding: 10px 18px;
            border-radius: 8px;
            background: var(--maroon, #7B1A1A);
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-refresh:hover {
            background: var(--maroon-deep, #550F0F);
            transform: translateY(-1px);
        }

        /* 📋 Table Styles */
        .card-table {
            background: var(--white, #fff);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(123,26,26,0.08);
            border: 1px solid var(--border, rgba(201,148,26,0.22));
            overflow: hidden;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table.audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: left;
        }
        table.audit-table th {
            background: var(--maroon-deep, #550F0F);
            color: var(--gold-light, #F0C060);
            padding: 14px 16px;
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        table.audit-table td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(201,148,26,0.12);
            vertical-align: middle;
            color: var(--text-dark, #1A0A00);
        }
        table.audit-table tr:hover {
            background: var(--saffron-pale, #FFF3E6);
        }

        /* 🏷️ Action & Category Badges */
        .badge-action {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-create { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-update { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        .badge-delete { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .badge-login  { background: #ede7f6; color: #512da8; border: 1px solid #d1c4e9; }
        .badge-logout { background: #fafafa; color: #616161; border: 1px solid #e0e0e0; }
        .badge-upload { background: #fff3e0; color: #ef6c00; border: 1px solid #ffe0b2; }

        .cat-tag {
            font-size: 0.75rem;
            background: #f5f5f5;
            color: #555;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .log-admin-user {
            font-weight: 700;
            color: var(--maroon, #7B1A1A);
        }
        .log-target {
            font-weight: 600;
            color: var(--text-dark, #1A0A00);
        }
        .log-time {
            font-size: 0.78rem;
            color: #777;
            white-space: nowrap;
        }
        .log-details {
            font-size: 0.78rem;
            color: #555;
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 📄 Pagination */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-top: 1px solid rgba(201,148,26,0.15);
            background: #fff;
            flex-wrap: wrap;
            gap: 12px;
        }
        .pagination-info { font-size: 0.85rem; color: #666; }
        .pagination-buttons { display: flex; gap: 8px; }
        .btn-page {
            padding: 6px 14px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--maroon);
            font-weight: 600;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-page:hover:not(:disabled) {
            background: var(--maroon);
            color: #fff;
        }
        .btn-page:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* 📱 Responsive Mobile View */
        @media (max-width: 768px) {
            .page-container { padding: 0 12px; margin: 16px auto 40px; }
            .filter-card { padding: 14px; }
            .filter-group { flex-direction: column; align-items: stretch; }
            .filter-input, .filter-select { width: 100%; }

            /* Card list on mobile */
            table.audit-table thead { display: none; }
            table.audit-table, table.audit-table tbody, table.audit-table tr, table.audit-table td {
                display: block;
                width: 100%;
            }
            table.audit-table tr {
                background: #fff;
                border-bottom: 2px solid rgba(201,148,26,0.2);
                padding: 14px 12px;
            }
            table.audit-table td {
                padding: 5px 0;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            table.audit-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #888;
                font-size: 0.76rem;
                text-transform: uppercase;
                margin-right: 10px;
            }
            .log-details { max-width: 100%; white-space: normal; text-align: right; }
        }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <div class="page-container" id="admin-page-content" style="display: none;">
        <!-- Top Navigation -->
        <div class="top-nav-bar">
            <a href="admin-dashboard.php" class="btn-back-dashboard">
                <i class="fas fa-arrow-left"></i> <span>डैशबोर्ड पर वापस जाएं</span>
            </a>
            <div class="system-badge">
                <i class="fas fa-shield-alt"></i> सुरक्षा व ऑडिट सिस्टम
            </div>
        </div>

        <!-- Section Divider -->
        <div class="section-divider">
            <h2><i class="fas fa-history"></i> गतिविधि लॉग (Audit Trail)</h2>
        </div>

        <!-- 🔍 Filters -->
        <div class="filter-card">
            <div class="filter-group">
                <input type="text" id="filter-search" class="filter-input" placeholder="🔍 नाम, ID या विवरण खोजें...">
                
                <select id="filter-category" class="filter-select">
                    <option value="">सभी श्रेणियां (All)</option>
                    <option value="family">परिवार (Family)</option>
                    <option value="admin">एडमिन (Admin)</option>
                    <option value="committee">कार्यकारिणी (Committee)</option>
                    <option value="announcement">घोषणा (Announcement)</option>
                    <option value="gallery">गैलरी (Gallery)</option>
                    <option value="ad">विज्ञापन (Ads)</option>
                    <option value="calendar">कैलेंडर (Calendar)</option>
                    <option value="auth">लॉगिन/लॉगआउट (Auth)</option>
                </select>

                <select id="filter-action" class="filter-select">
                    <option value="">सभी कार्य (All Actions)</option>
                    <option value="CREATE">CREATE (जोड़ना)</option>
                    <option value="UPDATE">UPDATE (बदलाव)</option>
                    <option value="DELETE">DELETE (हटाना)</option>
                    <option value="LOGIN">LOGIN (लॉगिन)</option>
                    <option value="LOGOUT">LOGOUT (लॉगआउट)</option>
                    <option value="UPLOAD">UPLOAD (फ़ाइल अपलोड)</option>
                    <option value="PASSWORD_CHANGE">PASSWORD (पासवर्ड)</option>
                </select>
            </div>

            <button type="button" class="btn-refresh" id="btn-refresh">
                <i class="fas fa-sync-alt"></i> रीफ्रेश
            </button>
        </div>

        <!-- 📋 Logs Table Card -->
        <div class="card-table">
            <div class="table-responsive">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>समय / दिनांक</th>
                            <th>एडमिन</th>
                            <th>कार्य (Action)</th>
                            <th>श्रेणी</th>
                            <th>लक्षित रिकॉर्ड</th>
                            <th>अतिरिक्त विवरण</th>
                            <th>IP पता</th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #777;">
                                <i class="fas fa-spinner fa-spin"></i> लॉग लोड हो रहे हैं...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info" id="pagination-info">0 में से 0 रिकॉर्ड</div>
                <div class="pagination-buttons">
                    <button class="btn-page" id="btn-prev" disabled><i class="fas fa-chevron-left"></i> पिछला</button>
                    <button class="btn-page" id="btn-next" disabled>अगला <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auth Guard Spinner -->
    <div id="auth-guard" style="display: block; text-align: center; padding: 100px 20px;">
        <i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: var(--maroon, #7B1A1A);"></i>
        <p style="margin-top: 15px; color: #666; font-weight: 500;">प्रमाणीकरण जांच जारी है...</p>
    </div>

    <div data-include="components/footer.php"></div>
    <div id="toast-container"></div>

    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>"></script>
    <script type="module" src="js/audit-logs.js?v=<?= time()?>"></script>
</body>
</html>
