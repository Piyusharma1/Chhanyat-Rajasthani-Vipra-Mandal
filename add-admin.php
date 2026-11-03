<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>एडमिन जोड़ें - Admin Management | छःन्यात राजस्थानी विप्र मंडल</title>
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
        
        .page-container { max-width: 1200px; margin: 30px auto 60px; padding: 0 20px; }

        .top-nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn-secondary {
            background: rgba(123, 26, 26, 0.1);
            color: var(--maroon, #7B1A1A);
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
            color: var(--maroon-deep, #550F0F);
        }
        .system-badge {
            font-size: 0.9rem;
            color: var(--maroon, #7B1A1A);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        .system-badge i {
            color: var(--saffron, #FF6B00);
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
            background: linear-gradient(90deg, transparent, var(--gold, #C9941A), transparent);
        }
        .section-divider h2 {
            font-family: 'Tiro Devanagari Hindi', serif;
            color: var(--maroon, #7B1A1A);
            font-size: 1.4rem;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .section-divider h2 i {
            color: var(--saffron, #FF6B00);
        }

        /* 📦 Card Grid Layout */
        .layout-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .layout-grid { grid-template-columns: 1fr; }
        }

        .card { 
            background: var(--bg-card, #FFFFFF); 
            padding: 24px; 
            border-radius: var(--radius, 14px); 
            box-shadow: var(--shadow-card, 0 2px 16px rgba(123,26,26,0.08)); 
            border: 1px solid var(--border, rgba(201,148,26,0.22));
            position: relative;
        }
        .card h3 { 
            font-family: 'Tiro Devanagari Hindi', serif; 
            color: var(--maroon, #7B1A1A); 
            margin-top: 0; 
            margin-bottom: 16px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid var(--saffron-pale, #FFF3E6);
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card h3 i { color: var(--saffron, #FF6B00); }

        /* 📝 Form Controls */
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-mid, #5C3010);
            margin-bottom: 6px;
        }
        .form-group label span.req { color: var(--saffron-deep, #D94F00); }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap input, .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border, rgba(201,148,26,0.3));
            border-radius: var(--radius-sm, 8px);
            font-size: 0.95rem;
            background: var(--white, #FFFFFF);
            color: var(--text-dark, #1A0A00);
            transition: all 0.25s ease;
            font-family: inherit;
        }
        .input-wrap input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--saffron, #FF6B00);
            box-shadow: 0 0 0 3.5px rgba(255, 107, 0, 0.12);
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--text-soft, #8B5A2B);
            cursor: pointer;
            padding: 4px;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .password-toggle-btn:hover { color: var(--saffron, #FF6B00); }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--saffron, #FF6B00), var(--saffron-deep, #D94F00));
            color: var(--white, #fff);
            border: none;
            border-radius: var(--radius-sm, 8px);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(255, 107, 0, 0.25);
            margin-top: 8px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 0, 0.35);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* 📋 Admin Table */
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-sm, 8px);
            border: 1px solid var(--border, rgba(201,148,26,0.22));
        }
        table.admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: left;
        }
        table.admin-table th {
            background: var(--maroon-deep, #550F0F);
            color: var(--gold-light, #F0C060);
            padding: 12px 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        table.admin-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border, rgba(201,148,26,0.15));
            vertical-align: middle;
            color: var(--text-dark, #1A0A00);
        }
        table.admin-table tr:hover {
            background: var(--saffron-pale, #FFF3E6);
        }

        .badge-status {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-active { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-inactive { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .badge-you { background: var(--gold-pale, #FDF5E0); color: #8a6200; border: 1px solid var(--gold, #C9941A); margin-left: 4px; }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-mid);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .btn-action-icon:hover {
            background: var(--saffron-pale);
            color: var(--saffron-deep);
        }
        .btn-action-icon.btn-delete:hover {
            background: #ffebee;
            color: #d32f2f;
            border-color: #ffcdd2;
        }
        .btn-action-icon:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* 🔔 Toast & Dialog */
        .toast {
            position: fixed;
            top: 20px; right: 20px;
            background: var(--white);
            padding: 14px 20px;
            border-radius: var(--radius-md, 12px);
            box-shadow: 0 10px 40px rgba(123,26,26,0.18);
            display: flex; align-items: center; gap: 12px;
            min-width: 280px; max-width: 400px;
            z-index: 9999;
            transform: translateX(450px);
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
            border-left: 4px solid #2e7d32;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: #d32f2f; }

        .confirm-overlay {
            position: fixed; inset: 0; background: rgba(26,10,0,0.6);
            z-index: 9998; display: none; backdrop-filter: blur(3px);
        }
        .confirm-overlay.active { display: block; }

        .confirm-dialog {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            background: var(--white); padding: 28px 24px;
            border-radius: var(--radius-lg, 20px); box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            z-index: 9999; max-width: 380px; width: 90%; text-align: center; display: none;
            border-top: 4px solid #d32f2f;
        }
        .confirm-dialog.active {
            display: block; animation: confirmPop 0.22s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }
        @keyframes confirmPop {
            from { opacity: 0; transform: translate(-50%, -50%) scale(0.88); }
            to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }
        .confirm-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn-cancel-dlg {
            flex: 1; padding: 10px; border-radius: 8px; border: 1.5px solid var(--border);
            background: transparent; color: var(--text-mid); font-weight: 600; cursor: pointer;
        }
        .btn-delete-dlg {
            flex: 1; padding: 10px; border-radius: 8px; border: none;
            background: #d32f2f; color: white; font-weight: 700; cursor: pointer;
        }
        .btn-delete-dlg:hover { background: #b71c1c; }

        .loading-state { text-align: center; padding: 30px; color: var(--text-soft); }

        /* 📱 RESPONSIVE BREAKPOINTS */
        @media (max-width: 900px) {
            .page-container { margin: 16px auto 40px; padding: 0 16px; }
            .layout-grid { grid-template-columns: 1fr; gap: 20px; }
        }

        @media (max-width: 640px) {
            .page-container { padding: 0 12px; margin: 12px auto 30px; }
            .page-header { padding: 18px 16px; flex-direction: column; align-items: stretch; text-align: center; gap: 14px; }
            .header-title-group h2 { font-size: 1.35rem; }
            .header-title-group p { font-size: 0.82rem; }
            .btn-back { width: 100%; justify-content: center; padding: 12px 16px; font-size: 0.9rem; }
            .card { padding: 16px 12px; border-radius: 12px; }
            .card h3 { font-size: 1.15rem; }
            .input-wrap input, .form-group select { font-size: 16px; padding: 12px 14px; min-height: 48px; } /* Prevents iOS auto-zoom */
            .btn-submit { font-size: 0.95rem; min-height: 48px; }
            .btn-action-icon { width: 38px; height: 38px; font-size: 1rem; }
            
            /* 📱 Mobile Table Card Transformation */
            .table-responsive { border: none; overflow-x: visible; }
            table.admin-table, table.admin-table thead, table.admin-table tbody, table.admin-table tr, table.admin-table td {
                display: block;
                width: 100%;
            }
            table.admin-table thead { display: none; }
            table.admin-table tr {
                background: var(--white, #fff);
                border: 1px solid var(--border, rgba(201,148,26,0.3));
                border-radius: 10px;
                margin-bottom: 12px;
                padding: 12px 14px;
                box-shadow: 0 2px 8px rgba(123,26,26,0.06);
            }
            table.admin-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px dashed rgba(201, 148, 26, 0.2);
                font-size: 0.88rem;
                text-align: right;
            }
            table.admin-table td:last-child {
                border-bottom: none;
                padding-top: 10px;
                justify-content: flex-end;
                gap: 8px;
            }
            table.admin-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-mid, #5C3010);
                font-size: 0.82rem;
                float: left;
            }

            .toast { top: 12px; right: 12px; left: 12px; max-width: none; width: auto; transform: translateY(-120%); }
            .toast.show { transform: translateY(0); }
        }

        @media (max-width: 400px) {
            .page-container { padding: 0 8px; }
            .card { padding: 12px 8px; }
            .btn-back, .btn-submit { font-size: 0.88rem; }
        }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <div class="page-container" id="admin-page-content" style="display: none;">
        <!-- Top Navigation -->
        <div class="top-nav-bar">
            <a href="admin-dashboard.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> डैशबोर्ड पर वापस जाएं
            </a>
            <div class="system-badge">
                <i class="fas fa-shield-alt"></i> एडमिन सुरक्षा एवं उपयोगकर्ता प्रबंधन
            </div>
        </div>

        <!-- Section Divider -->
        <div class="section-divider">
            <h2><i class="fas fa-user-shield"></i> एडमिन प्रबंधन</h2>
        </div>

        <div class="layout-grid">
            <!-- ➕ Form Card: Add Admin -->
            <div class="card">
                <h3><i class="fas fa-user-plus"></i> नया एडमिन जोड़ें</h3>
                
                <form id="add-admin-form" autocomplete="off">
                    <div class="form-group">
                        <label for="full_name">पूरा नाम (Full Name) <span class="req">*</span></label>
                        <div class="input-wrap">
                            <input type="text" id="full_name" name="full_name" placeholder="उदा. राम शर्मा" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">यूज़रनेम (Username) <span class="req">*</span></label>
                        <div class="input-wrap">
                            <input type="text" id="username" name="username" placeholder="उदा. ram_sharma" required autocomplete="new-username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">पासवर्ड (Password) <span class="req">*</span></label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password" placeholder="कम से कम 6 अक्षर" required autocomplete="new-password">
                            <button type="button" class="password-toggle-btn" id="toggle-password" title="पासवर्ड देखें / छुपाएं">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">पासवर्ड की पुष्टि (Confirm Password) <span class="req">*</span></label>
                        <div class="input-wrap">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="पुनः वही पासवर्ड दर्ज करें" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin_branch">अनुमति शाखा (Assigned Branch) <span class="req">*</span></label>
                        <select id="admin_branch" name="admin_branch">
                            <option value="ALL" selected>🌟 सभी शाखाएं (Super Admin / ALL)</option>
                            <!-- Dynamically loaded branches from DB -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_active">खाता स्टेटस (Account Status)</label>
                        <select id="is_active" name="is_active">
                            <option value="1" selected>सक्रिय (Active)</option>
                            <option value="0">निष्क्रिय (Inactive)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit" id="submit-admin-btn">
                        <i class="fas fa-user-check"></i> एडमिन सेव करें
                    </button>
                </form>
            </div>

            <!-- 📋 Table Card: Admin List -->
            <div class="card">
                <h3><i class="fas fa-users-cog"></i> पंजीकृत एडमिन (Registered Admins)</h3>
                
                <div id="admins-loading" class="loading-state">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--saffron);"></i>
                    <p style="margin-top: 10px;">एडमिन सूची लोड हो रही है...</p>
                </div>

                <div class="table-responsive" id="admins-table-wrap" style="display: none;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>नाम (Name)</th>
                                <th>यूज़रनेम (Username)</th>
                                <th>शाखा (Branch)</th>
                                <th>स्टेटस</th>
                                <th>अंतिम लॉगिन</th>
                                <th>कार्रवाई</th>
                            </tr>
                        </thead>
                        <tbody id="admins-tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔑 Change Password Modal Dialog -->
    <div class="confirm-overlay" id="pwd-overlay"></div>
    <div class="confirm-dialog" id="pwd-dialog" style="border-top: 4px solid var(--saffron);">
        <div style="width: 50px; height: 50px; background: var(--saffron-pale); border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: var(--saffron); font-size: 1.4rem;">
            <i class="fas fa-key"></i>
        </div>
        <h4 style="color: var(--maroon); margin-bottom: 4px;">पासवर्ड बदलें (Change Password)</h4>
        <p style="font-size: 0.85rem; color: var(--text-soft); margin-bottom: 16px;" id="pwd-dialog-target">एडमिन यूज़र का नया पासवर्ड दर्ज करें</p>
        
        <form id="change-pwd-form" style="text-align: left;" autocomplete="off">
            <input type="hidden" id="pwd-target-id">
            <div class="form-group" style="margin-bottom: 12px;">
                <label for="pwd_new_password" style="font-size: 0.82rem;">नया पासवर्ड (New Password) <span class="req">*</span></label>
                <div class="input-wrap">
                    <input type="password" id="pwd_new_password" placeholder="कम से कम 6 अक्षर" required autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" id="toggle-pwd-new" title="पासवर्ड देखें / छुपाएं">
                        <i class="fas fa-eye" id="pwd-new-icon"></i>
                    </button>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="pwd_confirm_password" style="font-size: 0.82rem;">पासवर्ड की पुष्टि (Confirm Password) <span class="req">*</span></label>
                <div class="input-wrap">
                    <input type="password" id="pwd_confirm_password" placeholder="पुनः वही पासवर्ड दर्ज करें" required autocomplete="new-password">
                </div>
            </div>
            <div class="confirm-buttons" style="margin-top: 14px;">
                <button type="button" class="btn-cancel-dlg" id="cancel-pwd-btn">रद्द करें</button>
                <button type="submit" class="btn-submit" id="confirm-pwd-btn" style="flex:1; margin:0; padding:10px;">पासवर्ड अपडेट करें</button>
            </div>
        </form>
    </div>

    <!-- ⚠️ Confirm Delete Dialog -->
    <div class="confirm-overlay" id="confirm-overlay"></div>
    <div class="confirm-dialog" id="confirm-dialog">
        <div style="width: 50px; height: 50px; background: #ffebee; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: #d32f2f; font-size: 1.4rem;">
            <i class="fas fa-user-times"></i>
        </div>
        <h4 style="color: var(--maroon); margin-bottom: 8px;">एडमिन हटाएं?</h4>
        <p style="font-size: 0.88rem; color: var(--text-soft);" id="confirm-dialog-msg">क्या आप वाकई इस एडमिन यूजर को हटाना चाहते हैं?</p>
        <div class="confirm-buttons">
            <button class="btn-cancel-dlg" id="cancel-delete-btn">रद्द करें</button>
            <button class="btn-delete-dlg" id="confirm-delete-btn">हाँ, हटाएं</button>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast" class="toast">
        <i id="toast-icon" class="fas fa-check-circle" style="color: #2e7d32; font-size: 1.2rem;"></i>
        <span id="toast-msg">संदेश</span>
    </div>

    <div data-include="components/footer.php"></div>

    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>"></script>
    <script type="module" src="js/add-admin.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/add-admin.js')?>"></script>
</body>
</html>
