<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <meta name="robots" content="noindex, nofollow">
      <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>परिवार प्रबंधन | छःन्यात राजस्थानी विप्र मंडल</title>
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
 
        .search-container {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 0 1rem;
            display: flex;
            gap: 1rem;
        }
        /* Add to your CSS for better photo hover effect */
.family-card img {
    transition: transform 0.3s;
}
.family-card:hover img {
    transform: scale(1.05);
}
        .search-input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid #eaeaea;
            border-radius: 12px;
            font-size: 1.05rem;
            background: #ffffff;
            color: var(--text-dark);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .search-input:hover { border-color: #d1d1d1; }
        .search-input:focus { 
            outline: none; 
            border-color: var(--orange); 
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.15); 
        }

        .families-grid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 3rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .family-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            border-top: 4px solid var(--orange);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .family-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 12px 30px rgba(0,0,0,0.08); 
        }
        .family-card h3 { color: var(--maroon-deep); margin: 0 0 0.5rem; font-size: 1.25rem; font-weight: 700; }
        .family-meta { color: #555; font-size: 0.95rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px; }
        body.dark-mode .family-meta { color: #aaa; }
        
        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: 1.5rem;
            padding-top: 1.2rem;
            border-top: 1px dashed #eaeaea;
        }
        .action-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-edit {
            background: rgba(52, 152, 219, 0.1);
            color: #2980b9;
            border: 1px solid rgba(52, 152, 219, 0.2);
        }
        .btn-edit:hover { background: #3498db; color: white; transform: translateY(-2px); }
        .btn-delete {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        .btn-delete:hover { background: #e74c3c; color: white; transform: translateY(-2px); }

        .loading-state, .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem;
            color: #666;
            font-size: 1.2rem;
            background: rgba(255,255,255,0.5);
            border-radius: 16px;
            border: 2px dashed #eaeaea;
        }
        .empty-state i { font-size: 3.5rem; color: var(--orange); margin-bottom: 1.2rem; display: block; opacity: 0.8; }

        /* ✏️ Edit Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(5px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; animation: modalFadeIn 0.3s ease-out forwards; }
        @keyframes modalFadeIn { 
            from { opacity: 0; backdrop-filter: blur(0px); } 
            to { opacity: 1; backdrop-filter: blur(5px); } 
        }
        
        .modal-content {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            width: 95%;
            max-width: 950px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            border: 1px solid rgba(0,0,0,0.05);
            animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .modal-close {
            position: absolute;
            top: 20px; right: 20px;
            background: rgba(231, 76, 60, 0.1); 
            border: none;
            border-radius: 50%;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: var(--red);
            cursor: pointer;
            transition: all 0.2s;
        }
        .modal-close:hover { background: var(--red); color: white; transform: rotate(90deg); }
        
        .modal-content h3 { 
            color: var(--maroon-deep); 
            margin-bottom: 2rem; 
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 2px dashed rgba(255, 107, 0, 0.3);
            font-size: 1.8rem;
        }
        
        #edit-form {
            width: 100%;
            box-sizing: border-box;
        }
        .edit-section {
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border-left: 5px solid var(--orange);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .edit-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        }
        .edit-section h4 {
            color: var(--maroon);
            margin: 0 0 1.5rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .edit-section h4 i {
            color: var(--orange);
            background: rgba(255,107,0,0.1);
            padding: 8px;
            border-radius: 8px;
        }
        
        .form-grid { 
            width: 100%;
            box-sizing: border-box;
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 1.5rem; 
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
            margin-bottom: 6px; 
            font-weight: 600; 
            font-size: 0.9rem; 
            color: #444;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; 
            padding: 12px 14px; 
            border: 2px solid #eaeaea;
            border-radius: 8px; 
            font-size: 1rem; 
            background: #fdfdfd; 
            color: var(--text-dark);
            transition: all 0.2s ease;
        }
        .form-group input:hover, .form-group select:hover {
            border-color: #d1d1d1;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
            outline: none; 
            border-color: var(--orange); 
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.15);
        }
        .form-group input[readonly] {
            background: #f5f5f5;
            color: #888;
            cursor: not-allowed;
            border-color: #eee;
        }
        
        .members-container {
            margin-top: 1.5rem;
        }
        .member-edit-item {
            background: #fafafa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #eee;
            border-top: 4px solid var(--saffron);
            position: relative;
            transition: all 0.2s;
        }
        .member-edit-item:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .member-edit-item h5 {
            margin: 0 0 1.2rem;
            color: #2c3e50;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .member-edit-item .form-grid {
            margin-bottom: 0;
        }
        .remove-member-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #fff;
            color: var(--danger);
            border: 1px solid #ffcccc;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .remove-member-btn:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
            transform: scale(1.1);
        }
        .add-member-btn {
            width: 100%;
            padding: 14px;
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            border: 2px dashed #27ae60;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .add-member-btn:hover {
            background: #27ae60;
            color: white;
        }
        
        .btn-save {
            width: 100%; 
            padding: 16px; 
            background: linear-gradient(135deg, var(--red), var(--orange));
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 1.2rem; 
            font-weight: 600;
            cursor: pointer; 
            margin-top: 2rem; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }
        .btn-save:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 25px rgba(255, 107, 0, 0.4); 
        }
        .btn-save:disabled { 
            background: #95a5a6; 
            cursor: not-allowed; 
            transform: none; 
            box-shadow: none;
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
            <h2><i class="fas fa-users-cog"></i> परिवार डेटा प्रबंधन</h2>
            <p style="opacity: 0.9; margin-top: 8px;">मौजूदा परिवारों को देखें, संपादित करें या हटाएं</p>
        </div>

        <div class="search-container">
            <input type="text" id="search-input" class="search-input" placeholder="🔍 नाम, ID, गांव या समाज से खोजें...">
        </div>

        <div id="families-grid" class="families-grid">
            <div class="loading-state"><i class="fas fa-spinner fa-spin"></i> परिवार लोड हो रहे हैं...</div>
        </div>
    </div>

    <!-- ✏️ Edit Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" id="modal-close-btn">&times;</button>
            <h3><i class="fas fa-edit"></i> परिवार डेटा संपादित करें</h3>
            <form id="edit-form">
                <input type="hidden" id="edit-doc-id">
                
                <!-- 📋 Basic Information -->
                <div class="edit-section">
                    <h4><i class="fas fa-info-circle"></i> सामान्य जानकारी</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>परिवार ID</label>
                            <input type="text" id="edit-family-id" readonly>
                        </div>
                        <div class="form-group">
                            <label>समाज *</label>
                            <select id="edit-branch" required>
                                <option value="">लोड हो रहा है...</option>
                                <option value="__OTHER__">➕ अन्य (नया समाज जोड़ें)</option>
                            </select>
                        </div>
                        <div class="form-group" id="edit-new-branch-container" style="display: none;">
                            <label>नया समाज का नाम *</label>
                            <input type="text" id="edit-family-new-branch" data-hindi-input="true" placeholder="नया समाज का नाम दर्ज करें">
                        </div>
                        <div class="form-group">
                            <label>कुटुंब प्रमुख नाव *</label>
                            <input type="text" id="edit-head" data-hindi-input="true" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="edit-family-photo">कुटुंब प्रमुख की फोटो:</label>
                            <input type="file" id="edit-family-photo" accept="image/jpeg, image/png, image/webp">
                            <small style="color: #666; display: block; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> नई फोटो चुनें (खाली छोड़ने पर पुरानी फोटो रहेगी) | अधिकतम 2MB
                            </small>
                            
                            <div id="edit-current-photo-container" style="margin-top:15px;">
                                <label style="font-weight:500; color:#555;">वर्तमान फोटो:</label>
                                <img id="edit-family-photo-preview" src="#" alt="Current Photo" 
                                     style="display:none; max-width:200px; max-height:200px; margin-top:10px; border-radius:8px; border:2px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                            
                            <div id="edit-new-photo-container" style="margin-top:15px; display:none;">
                                <label style="font-weight:500; color:#555;">नई फोटो Preview:</label>
                                <img id="edit-family-new-preview" src="#" alt="New Preview" 
                                     style="max-width:200px; max-height:200px; margin-top:10px; border-radius:8px; border:2px solid #4CAF50; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>पत्ता *</label>
                            <input type="text" id="edit-address" data-hindi-input="true" required>
                        </div>
                        <div class="form-group">
                            <label>गाव/शहर</label>
                            <input type="text" id="edit-village" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>जिल्हा *</label>
                            <input type="text" id="edit-district" data-hindi-input="true" required>
                        </div>
                        <div class="form-group">
                            <label>पिनकोड</label>
                            <input type="text" id="edit-pincode" pattern="[0-9]{6}">
                        </div>
                        <div class="form-group">
                            <label>मोबाईल *</label>
                            <input type="tel" id="edit-mobile" required pattern="[0-9]{10}">
                        </div>
                        <div class="form-group">
                            <label>ईमेल</label>
                            <input type="email" id="edit-email">
                        </div>
                        <div class="form-group">
                            <label>फोन STD कोड</label>
                            <input type="text" id="edit-std-code">
                        </div>
                        <div class="form-group">
                            <label>फोन</label>
                            <input type="tel" id="edit-phone">
                        </div>
                    </div>
                </div>

                <!-- 🏡 Native Place -->
                <div class="edit-section">
                    <h4><i class="fas fa-home"></i> राजस्थान मूल माहिती</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>मूळ गाव</label>
                            <input type="text" id="edit-native" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>तालुका</label>
                            <input type="text" id="edit-native-taluka" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>जिल्हा</label>
                            <input type="text" id="edit-native-district" data-hindi-input="true">
                        </div>
                    </div>
                </div>

                <!-- 🙏 कुलदेवता माहिती -->
                <div class="edit-section">
                    <h4><i class="fas fa-om"></i> कुलदेवता माहिती</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>कुलदेवता/कुलदेवी</label>
                            <input type="text" id="edit-kuldaivat" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>स्थान</label>
                            <input type="text" id="edit-kuldaivat-place" data-hindi-input="true">
                        </div>
                    </div>
                </div>

                <!-- 💼 Business -->
                <div class="edit-section">
                    <h4><i class="fas fa-briefcase"></i> व्यवसाय जानकारी</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>प्रतिष्ठान नाम</label>
                            <input type="text" id="edit-biz-name" data-hindi-input="true">
                        </div>
                        <div class="form-group full-width">
                            <label>व्यवसाय पता</label>
                            <input type="text" id="edit-biz-address" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>व्यवसाय मोबाइल</label>
                            <input type="tel" id="edit-biz-mobile" pattern="[0-9]{10}">
                        </div>
                        <div class="form-group">
                            <label>व्यवसाय प्रकार</label>
                            <input type="text" id="edit-biz-type" data-hindi-input="true">
                        </div>
                        <div class="form-group">
                            <label>वर्षों से</label>
                            <input type="text" id="edit-biz-years" min="0">
                        </div>
                    </div>
                </div>

                <!-- 👥 Members -->
                <div class="edit-section">
                    <h4><i class="fas fa-users"></i> पारिवारिक सदस्य</h4>
                    <div id="edit-members-container" class="members-container"></div>
                    
                    <button type="button" class="add-member-btn" id="edit-add-member-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>नया सदस्य जोड़ें</span>
                    </button>
                </div>

                <button type="submit" class="btn-save" id="save-btn">
                    <i class="fas fa-save"></i> बदलाव सेव करें
                </button>
            </form>
        </div>
    </div>

    <div data-include="components/footer.php"></div>
    <div id="toast-container"></div>

    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>"></script>
    <script src="js/hindi-transliteration.js?v=<?= @filemtime(__DIR__.'/js/hindi-transliteration.js') ?: time()?>"></script>
    <script type="module" src="js/family-edit.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/family-edit.js')?>"></script>
</body>
</html>