<!DOCTYPE html>
<html lang="hi">
<head><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta name="robots" content="noindex, nofollow">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>नया परिवार जोड़ें | छःन्यात राजस्थानी विप्र मंडल</title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--maroon-deep) 0%, var(--maroon) 50%, var(--saffron) 100%);
            color: white;
            padding: 2rem 1rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .page-header h2 { color: #FFD180; margin: 0; display: flex; align-items: center; justify-content: center; gap: 10px; }
        
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem 3rem;
        }
        
        .edit-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(255, 107, 0, 0.05);
            border-radius: 8px;
            border-left: 4px solid var(--orange);
        }
        .edit-section h4 {
            color: var(--red);
            margin: 0 0 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 1rem; 
            margin-bottom: 1rem; 
        }
        .form-group { 
            display: flex; 
            flex-direction: column; 
        }
        .form-group.full-width { 
            grid-column: 1 / -1; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 4px; 
            font-weight: 500; 
            font-size: 0.85rem; 
            color: var(--text-dark);
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; 
            padding: 10px; 
            border: 2px solid #ddd;
            border-radius: 6px; 
            font-size: 0.95rem; 
            background: var(--bg-card); 
            color: var(--text-dark);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
            outline: none; 
            border-color: var(--orange); 
        }
        .form-group input[readonly] {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        
        .members-container {
            margin-top: 1rem;
        }
        .member-edit-item {
            background: white;
            padding: 1.25rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 2px solid #ddd;
            border-left: 4px solid var(--orange);
            position: relative;
        }
        .member-edit-item h5 {
            margin: 0 0 1rem;
            color: var(--red);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .member-edit-item .form-grid {
            margin-bottom: 0;
        }
        .remove-member-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .remove-member-btn:hover {
            background: #c0392b;
            transform: scale(1.1);
        }
        .add-member-btn {
            width: 100%;
            padding: 12px;
            background: var(--success, #27ae60);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .add-member-btn:hover {
            background: #219a52;
            transform: translateY(-2px);
        }
        
        .btn-save {
            width: 100%; 
            padding: 14px; 
            background: linear-gradient(135deg, var(--red), var(--orange));
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 1.1rem; 
            font-weight: 600;
            cursor: pointer; 
            margin-top: 1rem; 
            transition: var(--transition);
        }
        .btn-save:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3); 
        }
        .btn-save:disabled { 
            background: #95a5a6; 
            cursor: not-allowed; 
            transform: none; 
        }

        .toast {
            position: fixed; top: 20px; right: 20px; background: var(--bg-card);
            padding: 16px 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 12px; z-index: 9999;
            transform: translateX(450px); transition: transform 0.3s; border-left: 4px solid var(--orange);
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: var(--red); }
        .toast-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; }
        .toast.success .toast-icon { background: var(--orange); }
        .toast.error .toast-icon { background: var(--red); }
    </style>
</head>
<body>
    <div data-include="components/header.php"></div>

    <div id="auth-guard" style="display:none; text-align:center; padding: 5rem 1rem;">
        <i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: var(--orange);"></i>
        <p style="margin-top: 1rem; color: #666;">सत्यापन हो रहा है...</p>
    </div>

    <div id="main-content" style="display: none;">
        <div class="page-header">
            <h2><i class="fas fa-user-plus"></i> नया परिवार जोड़ें</h2>
            <p style="opacity: 0.9; margin-top: 8px;">नए परिवार की जानकारी भरें</p>
        </div>

        <div class="form-container">
            <form id="family-form">
                <!-- 📋 Basic Information -->
                <div class="edit-section">
                    <h4><i class="fas fa-info-circle"></i> सामान्य जानकारी</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>परिवार ID</label>
                            <input type="text" id="family-id" readonly style="background: #f5f5f5;">
                        </div>
                        <div class="form-group">
    <label>समाज *</label>
    <select id="family-branch" required>
        <option value="">-- समाज चुनें --</option>
        <option value="__OTHER__">➕ अन्य (नया समाज जोड़ें)</option>
    </select>
</div>

<!-- New Branch Input (Hidden by default) -->
<div class="form-group" id="new-branch-container" style="display: none;">
    <label>नया समाज का नाम *</label>
    <input type="text" id="family-new-branch" data-hindi-input="true" placeholder="नया समाज का नाम दर्ज करें">
</div>
                        <div class="form-group">
                            <label>कुटुंब प्रमुख नाव *</label>
                            <input type="text" id="family-head" data-hindi-input="true" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="family-photo">कुटुंब प्रमुख की फोटो:</label>
                            <input type="file" id="family-photo" accept="image/jpeg, image/png, image/webp">
                            <small style="color: #666; display: block; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> केवल JPG, PNG या WebP | अधिकतम 2MB
                            </small>
                            <img id="family-photo-preview" src="#" alt="Preview" 
                                 style="display:none; max-width:200px; max-height:200px; margin-top:15px; border-radius:8px; border:2px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>पत्ता *</label>
                            <input type="text" id="family-address" data-hindi-input="true" required>
                        </div>
                        <div class="form-group">
                            <label>गाव/शहर</label>
                            <input type="text" id="family-village" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>जिल्हा *</label>
                            <input type="text" id="family-district" data-hindi-input="true" required>
                        </div>
                        <div class="form-group">
                            <label>पिनकोड</label>
                            <input type="text" id="family-pincode" pattern="[0-9]{6}">
                        </div>
                        <div class="form-group">
                            <label>मोबाईल *</label>
                            <input type="tel" id="family-mobile" required pattern="[0-9]{10}">
                        </div>
                        <div class="form-group">
                            <label>ईमेल</label>
                            <input type="email" id="family-email">
                        </div>
                        <div class="form-group">
                            <label>फोन STD कोड</label>
                            <input type="text" id="family-std-code">
                        </div>
                        <div class="form-group">
                            <label>फोन</label>
                            <input type="tel" id="family-phone">
                        </div>
                    </div>
                </div>

                <!-- 🏡 Native Place -->
                <div class="edit-section">
                    <h4><i class="fas fa-home"></i> राजस्थान मूल माहिती</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>मूळ गाव</label>
                            <input type="text" id="family-native" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>तालुका</label>
                            <input type="text" id="family-native-taluka" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>जिल्हा</label>
                            <input type="text" id="family-native-district" data-hindi-input="true">
                        </div>
                    </div>
                </div>

                <!-- 🙏 कुलदेवता माहिती -->
                <div class="edit-section">
                    <h4><i class="fas fa-om"></i> कुलदेवता माहिती</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>कुलदेवता/कुलदेवी</label>
                            <input type="text" id="family-kuldaivat" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>स्थान</label>
                            <input type="text" id="family-kuldaivat-place" data-hindi-input="true">
                        </div>
                    </div>
                </div>

                <!-- 💼 Business -->
                <div class="edit-section">
                    <h4>
                        <i class="fas fa-briefcase"></i> व्यवसाय जानकारी
                        <label style="margin-left: 15px; font-weight: normal;">
                            <input type="checkbox" id="has-business"> व्यवसाय है?
                        </label>
                    </h4>
                    <div id="business-fields" class="form-grid" style="display: none;">
                        <div class="form-group">
                            <label>प्रतिष्ठान नाम</label>
                            <input type="text" id="biz-name" data-hindi-input="true">
                        </div>
                        <div class="form-group full-width">
                            <label>व्यवसाय पता</label>
                            <input type="text" id="biz-address" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>व्यवसाय मोबाइल</label>
                            <input type="tel" id="biz-mobile" pattern="[0-9]{10}">
                        </div>
                        <div class="form-group">
                            <label>व्यवसाय प्रकार</label>
                            <input type="text" id="biz-type" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>वर्षों से</label>
                            <input type="text" id="biz-years" min="0">
                        </div>
                    </div>
                </div>

                <!-- 👥 Members -->
                <div class="edit-section">
                    <h4><i class="fas fa-users"></i> पारिवारिक सदस्य</h4>
                    <div id="members-container" class="members-container"></div>
                    <button type="button" class="add-member-btn" id="add-member-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>नया सदस्य जोड़ें</span>
                    </button>
                </div>

                <button type="submit" class="btn-save" id="submit-btn">
                    <i class="fas fa-save"></i> परिवार सेव करें
                </button>
            </form>
        </div>
    </div>

    <div data-include="components/footer.php"></div>
    <div id="toast-container"></div>

    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>"></script>
    <script src="js/hindi-transliteration.js?v=<?= @filemtime(__DIR__.'/js/hindi-transliteration.js') ?: time()?>"></script>
    <script type="module" src="js/family-add.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/main.js')?>"></script>
</body>
</html>