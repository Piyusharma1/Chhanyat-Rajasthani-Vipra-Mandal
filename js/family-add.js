// ✅ Import API loader from home.js
import { loadAllFromAPI } from "./home.js";

let allFamilies = [];

// ✅ Image Upload Configuration
const UPLOAD_API_URL = window.location.origin + '/api/upload-image.php';

// =========================================
let currentAdminAuth = null;

// =========================================
// 🔐 ADMIN AUTH CHECK
// =========================================
async function checkAdminAuth() {
    try {
        const response = await fetch('api/check_auth.php');
        const result = await response.json();
        
        const guard = document.getElementById('auth-guard');
        const content = document.getElementById('main-content');
        
        if (guard && content) {
            if (result.isAdmin) {
                currentAdminAuth = result;
                guard.style.display = 'none';
                content.style.display = 'block';
                initAddFamilyForm();
            } else {
                window.location.href = 'admin-login.php';
            }
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'admin-login.php';
    }
}
// =========================================
// 📦 LOAD BRANCHES INTO DROPDOWN
// =========================================
async function loadBranchesDropdown() {
    const select = document.getElementById('family-branch');
    const newBranchContainer = document.getElementById('new-branch-container');
    
    if (!select) return;
    
    select.innerHTML = '<option value="">लोड हो रहा है...</option>';
    select.disabled = true;
    
    try {
        const branches = await loadAllFromAPI('branches');
        
        select.innerHTML = '<option value="">-- समाज चुनें --</option>';
        if (!currentAdminAuth || currentAdminAuth.isSuperAdmin) {
            select.innerHTML += '<option value="__OTHER__">➕ अन्य (नया समाज जोड़ें)</option>';
        }
        
        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.branchName;
            option.textContent = branch.branchName;
            select.appendChild(option);
        });

        // If admin is restricted to a branch, auto-select and lock dropdown
        if (currentAdminAuth && !currentAdminAuth.isSuperAdmin && currentAdminAuth.branch) {
            select.value = currentAdminAuth.branch;
            select.disabled = true;
            select.style.backgroundColor = '#f0f0f0';
            select.title = `आप केवल ${currentAdminAuth.branch} शाखा में ही परिवार जोड़ सकते हैं।`;
        }
        
    } catch (error) {
        console.error("❌ Branch load error:", error);
        select.innerHTML = '<option value="">त्रुटि: समाज लोड नहीं हुई</option>';
    } finally {
        if (!currentAdminAuth || currentAdminAuth.isSuperAdmin) {
            select.disabled = false;
        }
    }
    
    // Handle "Other" option
    select.addEventListener('change', () => {
        if (select.value === '__OTHER__') {
            if (newBranchContainer) newBranchContainer.style.display = 'block';
            const newBranchInput = document.getElementById('family-new-branch');
            if (newBranchInput) newBranchInput.required = true;
        } else {
            if (newBranchContainer) newBranchContainer.style.display = 'none';
            const newBranchInput = document.getElementById('family-new-branch');
            if (newBranchInput) newBranchInput.required = false;
        }
    });
}
// =========================================
// 📸 IMAGE UPLOAD TO HOSTINGER
// =========================================
async function uploadImageToHostinger(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) throw new Error('केवल JPG, PNG या WebP allowed हैं।');
    if (file.size > 2 * 1024 * 1024) throw new Error('Image 2MB से कम होनी चाहिए।');
    
    const formData = new FormData();
    formData.append('image', file);
    
    const response = await fetch(UPLOAD_API_URL, { method: 'POST', body: formData });
    const responseText = await response.text();
    let result;
    try { result = JSON.parse(responseText); } catch { throw new Error('Server ने invalid response भेजा।'); }
    
    if (!result.success) throw new Error(result.error || 'Upload failed');
    if (!result.url) throw new Error('Server ने URL नहीं भेजा।');
    
    return result.url;
}

// =========================================
// 🔔 TOAST NOTIFICATION
// =========================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check' : 'fa-times';
    toast.innerHTML = `
        <div class="toast-icon"><i class="fas ${icon}"></i></div>
        <div><strong>${type === 'success' ? 'सफल' : 'त्रुटि'}</strong><br><small>${message}</small></div>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
}

// =========================================
// ➕ ADD FAMILY FORM INITIALIZATION
// =========================================
function initAddFamilyForm() {
    // Generate family ID
    const familyIdInput = document.getElementById('family-id');
    if (familyIdInput) {
        familyIdInput.value = 'FAM-' + Date.now().toString().slice(-6);
    }
    
    // 🔥 Load branches into dropdown
    loadBranchesDropdown();
    
    // Business checkbox toggle
    const hasBusiness = document.getElementById('has-business');
    const businessFields = document.getElementById('business-fields');
    if (hasBusiness && businessFields) {
        hasBusiness.addEventListener('change', (e) => {
            businessFields.style.display = e.target.checked ? 'grid' : 'none';
        });
    }
    
    // Photo preview
    const photoInput = document.getElementById('family-photo');
    const preview = document.getElementById('family-photo-preview');
    if (photoInput && preview) {
        photoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showToast('Image 2MB से कम होनी चाहिए।', 'error');
                    photoInput.value = '';
                    preview.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    }
    
    // Add member button
    const addMemberBtn = document.getElementById('add-member-btn');
    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', () => {
            addMemberRow();
        });
    }
    
    // Add initial member row
    addMemberRow();
    
    // Form submit handler
    const form = document.getElementById('family-form');
    if (form) {
        form.addEventListener('submit', handleAddFamily);
    }

    // ⌨️ Initialize Hindi transliteration on all static form fields
    if (typeof window.initHindiTransliteration === 'function') {
        window.initHindiTransliteration('[data-hindi-input="true"]', document.querySelector('.form-container'));
    }

    // ⏎ Enter key → move to next field instead of submitting
    if (form) {
        form.addEventListener('keydown', (e) => {
            // Only intercept Enter on input/select (not textarea, not submit button)
            if (e.key !== 'Enter') return;
            const el = e.target;
            if (el.tagName === 'TEXTAREA' || el.type === 'submit' || el.type === 'button') return;
            // Don't intercept if Hindi suggestion box is open (it handles Enter itself)
            if (document.querySelector('.hindi-translit-suggestions')) return;

            e.preventDefault();

            // Get all visible, enabled focusable fields in form order
            const fields = Array.from(form.querySelectorAll(
                'input:not([type="hidden"]):not([type="file"]):not([type="checkbox"]):not([readonly]), select, textarea'
            )).filter(f => {
                // Must be visible and enabled
                if (f.disabled || f.offsetParent === null) return false;
                // Check parent display (for hidden business fields etc.)
                let parent = f.closest('[style*="display: none"], [style*="display:none"]');
                return !parent;
            });

            const currentIdx = fields.indexOf(el);
            if (currentIdx >= 0 && currentIdx < fields.length - 1) {
                fields[currentIdx + 1].focus();
            }
        });
    }
}

// =========================================
// 👥 ADD MEMBER ROW
// =========================================
function addMemberRow() {
    const container = document.getElementById('members-container');
    if (!container) return;
    
    const div = document.createElement('div');
    div.className = 'member-edit-item';
    
    div.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <h5 style="margin:0;"><i class="fas fa-user"></i> नया सदस्य</h5>
            <button type="button" class="remove-member-btn" style="background:#e74c3c;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="form-grid">
            <div class="form-group"><label>नाम *</label><input type="text" class="m-name" data-hindi-input="true" required></div>
            <div class="form-group"><label>नाता *</label>
                <select class="m-relation" required>
                    <option value="">चुनें</option>
                    <option value="पति">पति</option>
                    <option value="पत्नी">पत्नी</option>
                    <option value="पिता">पिता</option>
                    <option value="माता">माता</option>
                    <option value="पुत्र">पुत्र</option>
                    <option value="पुत्री">पुत्री</option>
                    <option value="पुत्रवधू">पुत्रवधू</option>
                    <option value="पौत्र">पौत्र</option>
                    <option value="पौत्री">पौत्री</option>
                    <option value="भाई">भाई</option>
                    <option value="भाभी">भाभी</option>
                    <option value="बहन">बहन</option>
                    <option value="भतीजा">भतीजा</option>
                    <option value="भतीजी">भतीजी</option>
                    <option value="अन्य">अन्य</option>
                </select>
            </div>
            <div class="form-group"><label>आयु *</label><input type="text" class="m-age" min="0" max="120" required></div>
            <div class="form-group"><label>लिंग *</label>
                <select class="m-gender" required>
                    <option value="">चुनें</option>
                    <option value="पुरुष">पुरुष</option>
                    <option value="महिला">महिला</option>
                </select>
            </div>
            <div class="form-group"><label>वैवाहिक स्थिति</label>
                <select class="m-status">
                    <option value="अविवाहित">अविवाहित</option>
                    <option value="विवाहित">विवाहित</option>
                    <option value="विधवा">विधवा/विधुर</option>
                </select>
            </div>
            <div class="form-group"><label>शिक्षा</label><input type="text" class="m-education" data-hindi-input="true"></div>
            <div class="form-group"><label>व्यवसाय</label><input type="text" class="m-occupation" data-hindi-input="true"></div>
            <div class="form-group"><label>मोबाइल</label><input type="tel" class="m-mobile" pattern="[0-9]{10}"></div>
        </div>
    `;
    
    div.querySelector('.remove-member-btn').addEventListener('click', () => {
        const items = container.querySelectorAll('.member-edit-item');
        if (items.length > 1) {
            div.remove();
        } else {
            showToast('कम से कम एक सदस्य आवश्यक है।', 'warning');
        }
    });
    
    container.appendChild(div);

    // ⌨️ Bind Hindi transliteration on dynamically added member fields
    if (typeof window.initHindiTransliteration === 'function') {
        window.initHindiTransliteration('[data-hindi-input="true"]', null);
    }
}

// =========================================
// 💾 HANDLE ADD FAMILY SUBMIT
// =========================================
async function handleAddFamily(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('submit-btn');
    const getVal = (id) => document.getElementById(id)?.value.trim() || '';
    
    // 🔥 Get branch value (from select or new input)
    const branchSelect = document.getElementById('family-branch');
    const isNewBranch = branchSelect.value === '__OTHER__';
    const branchValue = isNewBranch ? getVal('family-new-branch') : branchSelect.value;
    
    if (!branchValue) {
        showToast('कृपया समाज चुनें या नया नाम भरें।', 'warning');
        return;
    }
    
    // Upload photo
    const photoInput = document.getElementById('family-photo');
    let photoUrl = null;
    if (photoInput && photoInput.files && photoInput.files[0]) {
        submitBtn.textContent = '📸 फोटो अपलोड हो रही है...';
        try {
            photoUrl = await uploadImageToHostinger(photoInput.files[0]);
        } catch (err) {
            showToast('फोटो अपलोड त्रुटि: ' + err.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> परिवार सेव करें';
            return;
        }
    }
    
    // Get members
    const members = Array.from(document.querySelectorAll('#members-container .member-edit-item')).map(row => ({
        name: row.querySelector('.m-name')?.value.trim() || '',
        relation: row.querySelector('.m-relation')?.value || '',
        age: parseInt(row.querySelector('.m-age')?.value) || 0,
        gender: row.querySelector('.m-gender')?.value || '',
        maritalStatus: row.querySelector('.m-status')?.value || '',
        education: row.querySelector('.m-education')?.value.trim() || '',
        occupation: row.querySelector('.m-occupation')?.value.trim() || '',
        mobile: row.querySelector('.m-mobile')?.value.trim() || ''
    })).filter(m => m.name);
    
    if (members.length === 0) {
        showToast('कम से कम एक सदस्य जोड़ें।', 'warning');
        return;
    }
    
    const familyData = {
        id: getVal('family-id'),
        branch: branchValue,  // 🔥 Use branchValue (from select or new input)
        head: getVal('family-head'),
        address: getVal('family-address'),
        village: getVal('family-village') || null,
        district: getVal('family-district'),
        pincode: getVal('family-pincode') || null,
        mobile: getVal('family-mobile'),
        email: getVal('family-email') || null,
        stdCode: getVal('family-std-code') || null,
        phone: getVal('family-phone') || null,
        nativePlace: getVal('family-native') || null,
        nativeTaluka: getVal('family-native-taluka') || null,
        nativeDistrict: getVal('family-native-district') || null,
        kuldaivat: getVal('family-kuldaivat') || null,
        kuldaivatPlace: getVal('family-kuldaivat-place') || null,
        businessName: document.getElementById('has-business').checked ? getVal('biz-name') : null,
        businessAddress: document.getElementById('has-business').checked ? getVal('biz-address') : null,
        businessMobile: document.getElementById('has-business').checked ? getVal('biz-mobile') : null,
        businessType: document.getElementById('has-business').checked ? getVal('biz-type') : null,
        businessYears: document.getElementById('has-business').checked ? getVal('biz-years') : null,
        photo: photoUrl,
        members: members
    };
    
    // Remove null/empty values
    Object.keys(familyData).forEach(key => {
        if (familyData[key] === null || familyData[key] === '' || familyData[key] === '—') {
            delete familyData[key];
        }
    });
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सेव हो रहा है...';
    
    try {
        // 🔥 If new branch, create it first
        if (isNewBranch) {
            const branches = await loadAllFromAPI('branches');
            const branchExists = branches.some(b => b.branchName?.toLowerCase() === branchValue.toLowerCase());
            
            if (!branchExists) {
                await fetch('api/add_branch.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        branchName: branchValue,
                        committee: []
                    })
                });
            }
        }
        
        const response = await fetch('api/add_family.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(familyData)
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'Save failed');
        }
        
        showToast(`परिवार "${familyData.head}" सफलतापूर्वक सेव हो गया!`);
        
        // Reset form
        e.target.reset();
        document.getElementById('members-container').innerHTML = '';
        addMemberRow();
        document.getElementById('family-id').value = 'FAM-' + Date.now().toString().slice(-6);
        document.getElementById('business-fields').style.display = 'none';
        document.getElementById('new-branch-container').style.display = 'none';
        
        const preview = document.getElementById('family-photo-preview');
        if (preview) preview.style.display = 'none';
        
        // Reload branches dropdown
        loadBranchesDropdown();
        
    } catch (error) {
        console.error("❌ Submit error:", error);
        showToast('त्रुटि: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> परिवार सेव करें';
    }
}

// 🎯 Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    checkAdminAuth();
});