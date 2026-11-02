// ✅ Import API loader from home.js
import { loadAllFromAPI } from "./home.js";

let allFamilies = [];
let allBranches = [];
let memberCounter = 0;

const UPLOAD_API_URL = window.location.origin + '/api/upload-image.php';

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
                await loadBranchesForEdit(); // 🔥 Load branches for edit dropdown
                await loadFamilies();
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
// 📦 LOAD BRANCHES FOR EDIT DROPDOWN
// =========================================
async function loadBranchesForEdit() {
    const select = document.getElementById('edit-branch');
    if (!select) return;

    try {
        allBranches = await loadAllFromAPI('branches');

        select.innerHTML = '<option value="">-- समाज चुनें --</option>';
        if (!currentAdminAuth || currentAdminAuth.isSuperAdmin) {
            select.innerHTML += '<option value="__OTHER__">➕ अन्य (नया समाज जोड़ें)</option>';
        }

        allBranches.forEach(branch => {
            if (branch.branchName) {
                const option = document.createElement('option');
                option.value = branch.branchName;
                option.textContent = branch.branchName;
                select.appendChild(option);
            }
        });

        // If admin is restricted to a branch, lock edit branch dropdown
        if (currentAdminAuth && !currentAdminAuth.isSuperAdmin && currentAdminAuth.branch) {
            select.value = currentAdminAuth.branch;
            select.disabled = true;
            select.style.backgroundColor = '#f0f0f0';
            select.title = `यह परिवार ${currentAdminAuth.branch} शाखा में है।`;
        }

        // 🔥 Handle "Other" option change
        select.addEventListener('change', () => {
            const newBranchContainer = document.getElementById('edit-new-branch-container');
            const newBranchInput = document.getElementById('edit-family-new-branch');

            if (select.value === '__OTHER__') {
                if (newBranchContainer) newBranchContainer.style.display = 'block';
                if (newBranchInput) {
                    newBranchInput.required = true;
                    newBranchInput.focus();
                }
            } else {
                if (newBranchContainer) newBranchContainer.style.display = 'none';
                if (newBranchInput) newBranchInput.required = false;
            }
        });

    } catch (error) {
        console.error('❌ Branch load error:', error);
    }
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
    formData.append('type', 'family');

    const response = await fetch(UPLOAD_API_URL, { method: 'POST', body: formData });
    const responseText = await response.text();
    let result;
    try { result = JSON.parse(responseText); } catch { throw new Error('Server ने invalid response भेजा।'); }

    if (!result.success) throw new Error(result.error || 'Upload failed');
    if (!result.url) throw new Error('Server ने URL नहीं भेजा।');

    return result.url;
}

// =========================================
// ⌨️ ENTER KEY NAVIGATION
// =========================================
function setupEnterKeyNavigation() {
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT') return;
        if (e.target.type === 'submit' || e.target.type === 'button') return;
        // Don't intercept if Hindi suggestion box is open
        if (document.querySelector('.hindi-translit-suggestions')) return;
        e.preventDefault();

        const fields = [...document.querySelectorAll(
            '#edit-form input:not([type="hidden"]):not([type="file"]):not([type="checkbox"]):not([readonly]), #edit-form select'
        )].filter(el => !el.disabled && el.offsetParent !== null);

        const index = fields.indexOf(e.target);
        if (index > -1 && index < fields.length - 1) {
            fields[index + 1].focus();
        }
    });
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
// 📦 LOAD FAMILIES
// =========================================
async function loadFamilies() {
    const grid = document.getElementById('families-grid');
    if (!grid) return;

    grid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> परिवार लोड हो रहे हैं...</div>';

    try {
        const families = await loadAllFromAPI('families', { adminView: true });

        if (!Array.isArray(families)) {
            throw new Error('API did not return an array');
        }

        allFamilies = families;
        renderFamilies(allFamilies);
    } catch (error) {
        console.error("❌ Load error:", error);
        grid.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i>डेटा लोड करने में त्रुटि: ${error.message}</div>`;
    }
}

function renderFamilies(families) {
    const grid = document.getElementById('families-grid');
    if (!grid) return;

    if (families.length === 0) {
        grid.innerHTML = '<div class="empty-state"><i class="fas fa-folder-open"></i>कोई परिवार नहीं मिला।</div>';
        return;
    }

    grid.innerHTML = families.map(f => {
        const safeId = String(f.id || 'unknown').replace(/"/g, '&quot;');
        const safeName = String(f.head || 'अज्ञात').replace(/"/g, '&quot;');
        const photoUrl = f.photo || 'https://placehold.net/avatar-4.png';
        const uniquePhoto = `${photoUrl}?v=${Date.now()}`; // Prevent caching

        return `
        <div class="family-card" data-id="${safeId}" data-head="${safeName}">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                <img src="${uniquePhoto}" 
                     alt="${safeName}"
                     style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--orange); flex-shrink: 0;"
                     onerror="this.src='https://chhanyatvipralatur.com/uploads/avatar.png'">
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 5px 0; font-size: 1.2rem;">${f.head || 'अज्ञात'}</h3>
                    <div class="family-meta"><i class="fas fa-id-card"></i> ${f.id || 'N/A'}</div>
                </div>
            </div>
            
            <div class="family-meta"><i class="fas fa-sitemap"></i> ${f.branch || 'N/A'}</div>
            <div class="family-meta"><i class="fas fa-map-marker-alt"></i> ${f.village || ''}, ${f.district || ''}</div>
            <div class="family-meta"><i class="fas fa-phone"></i> ${f.mobile || 'N/A'}</div>
            
            <div class="card-actions">
                <button class="btn-edit action-btn"><i class="fas fa-edit"></i> एडिट</button>
                <button class="btn-delete action-btn"><i class="fas fa-trash"></i> डिलीट</button>
            </div>
        </div>`;
    }).join('');

    grid.querySelectorAll('.family-card').forEach(card => {
        card.querySelector('.btn-edit').addEventListener('click', () => window.openEditModal(card.dataset.id));
        card.querySelector('.btn-delete').addEventListener('click', () => window.confirmDelete(card.dataset.id, card.dataset.head));
    });
}

// Search functionality
document.getElementById('search-input')?.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    const filtered = allFamilies.filter(f =>
        (f.head && f.head.toLowerCase().includes(query)) ||
        (f.id && f.id.toLowerCase().includes(query)) ||
        (f.branch && f.branch.toLowerCase().includes(query)) ||
        (f.village && f.village.toLowerCase().includes(query)) ||
        (f.district && f.district.toLowerCase().includes(query))
    );
    renderFamilies(filtered);
});

// =========================================
// ✏️ EDIT MODAL LOGIC
// =========================================
const editModal = document.getElementById('edit-modal');

window.openEditModal = async (familyId) => {
    if (!editModal) return;
    try {
        const family = allFamilies.find(f => f.id === familyId);
        if (!family) return showToast('परिवार नहीं मिला', 'error');

        const data = family;

        const fields = [
            'family-id', 'head', 'address', 'village',
            'district', 'pincode', 'mobile', 'email', 'std-code', 'phone',
            'native', 'native-taluka', 'native-district',
            'kuldaivat', 'kuldaivat-place',
            'biz-name', 'biz-address', 'biz-mobile', 'biz-type', 'biz-years'
        ];

        const dataKeys = [
            'id', 'head', 'address', 'village',
            'district', 'pincode', 'mobile', 'email', 'stdCode', 'phone',
            'nativePlace', 'nativeTaluka', 'nativeDistrict',
            'kuldaivat', 'kuldaivatPlace',
            'businessName', 'businessAddress', 'businessMobile', 'businessType', 'businessYears'
        ];

        document.getElementById('edit-doc-id').value = familyId;

        fields.forEach((f, i) => {
            const el = document.getElementById(`edit-${f}`);
            if (el) el.value = data[dataKeys[i]] || '';
        });

        // 🔥 Set branch dropdown value
        const branchSelect = document.getElementById('edit-branch');
        if (branchSelect) {
            // Check if branch exists in dropdown
            const branchExists = Array.from(branchSelect.options).some(opt => opt.value === data.branch);

            if (branchExists) {
                branchSelect.value = data.branch;
                document.getElementById('edit-new-branch-container').style.display = 'none';
            } else {
                // Branch not in list, select "Other" and show input
                branchSelect.value = '__OTHER__';
                document.getElementById('edit-new-branch-container').style.display = 'block';
                const newBranchInput = document.getElementById('edit-family-new-branch');
                if (newBranchInput) {
                    newBranchInput.value = data.branch || '';
                    newBranchInput.required = true;
                }
            }
        }

        // Show existing photo
        const editPreview = document.getElementById('edit-family-photo-preview');
        if (editPreview && data.photo) {
            editPreview.src = data.photo;
            editPreview.style.display = 'block';
        } else if (editPreview) {
            editPreview.style.display = 'none';
        }

        // Reset new photo preview
        const editNewContainer = document.getElementById('edit-new-photo-container');
        if (editNewContainer) editNewContainer.style.display = 'none';
        const editPhotoInput = document.getElementById('edit-family-photo');
        if (editPhotoInput) editPhotoInput.value = '';

        // Load members
        const membersContainer = document.getElementById('edit-members-container');
        if (membersContainer) {
            membersContainer.innerHTML = '';
            memberCounter = 0;
            if (data.members?.length > 0) {
                data.members.forEach(m => addEditMemberRow(m));
            } else {
                addEditMemberRow();
            }
        }

        setupEnterKeyNavigation();

        // ⌨️ Initialize Hindi transliteration on edit modal fields
        if (typeof window.initHindiTransliteration === 'function') {
            window.initHindiTransliteration('#edit-form [data-hindi-input="true"]', document.querySelector('.modal-content'));
        }

        editModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error("❌ Edit modal error:", error);
        showToast('डेटा लोड करने में त्रुटि', 'error');
    }
};

// Modal event listeners
if (editModal) {
    document.getElementById('modal-close-btn')?.addEventListener('click', () => {
        editModal.classList.remove('active');
        document.body.style.overflow = '';
    });

    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            editModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    const editAddMemberBtn = document.getElementById('edit-add-member-btn');
    if (editAddMemberBtn) {
        editAddMemberBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            addEditMemberRow();
        });
    }

    document.getElementById('edit-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const familyId = document.getElementById('edit-doc-id').value;
        const saveBtn = document.getElementById('save-btn');
        if (!saveBtn) return;

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सेव हो रहा है...';

        try {
            const members = Array.from(document.querySelectorAll('.member-edit-item')).map(row => ({
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
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> बदलाव सेव करें';
                return;
            }

            // 🔥 Handle branch (from dropdown or new input)
            const branchSelect = document.getElementById('edit-branch');
            const isNewBranch = branchSelect.value === '__OTHER__';
            const branchValue = isNewBranch
                ? document.getElementById('edit-family-new-branch')?.value.trim()
                : branchSelect.value;

            if (!branchValue) {
                showToast('कृपया समाज चुनें या नया नाम भरें।', 'warning');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> बदलाव सेव करें';
                return;
            }

            const editPhotoInput = document.getElementById('edit-family-photo');
            let newPhotoUrl = null;
            if (editPhotoInput && editPhotoInput.files && editPhotoInput.files[0]) {
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> फोटो अपलोड...';
                try {
                    newPhotoUrl = await uploadImageToHostinger(editPhotoInput.files[0]);
                } catch (err) {
                    showToast('फोटो अपलोड त्रुटि: ' + err.message, 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> बदलाव सेव करें';
                    return;
                }
            }

            const getEditVal = (id) => document.getElementById(id)?.value.trim() || '';

            const updateData = {
                id: familyId,
                branch: branchValue,
                head: getEditVal('edit-head'),
                address: getEditVal('edit-address'),
                village: getEditVal('edit-village'),
                district: getEditVal('edit-district'),
                pincode: getEditVal('edit-pincode'),
                mobile: getEditVal('edit-mobile'),
                email: getEditVal('edit-email'),
                stdCode: getEditVal('edit-std-code'),
                phone: getEditVal('edit-phone'),
                nativePlace: getEditVal('edit-native'),
                nativeTaluka: getEditVal('edit-native-taluka'),
                nativeDistrict: getEditVal('edit-native-district'),
                kuldaivat: getEditVal('edit-kuldaivat'),
                kuldaivatPlace: getEditVal('edit-kuldaivat-place'),
                businessName: getEditVal('edit-biz-name'),
                businessAddress: getEditVal('edit-biz-address'),
                businessMobile: getEditVal('edit-biz-mobile'),
                businessType: getEditVal('edit-biz-type'),
                businessYears: getEditVal('edit-biz-years'),
                members: members
            };

            if (newPhotoUrl) {
                updateData.photo = newPhotoUrl;
            }

            Object.keys(updateData).forEach(key => {
                if (updateData[key] === null || updateData[key] === '') delete updateData[key];
            });

            // 🔥 If new branch, create it first
            if (isNewBranch) {
                const branchExists = allBranches.some(b => b.branchName?.toLowerCase() === branchValue.toLowerCase());

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

            const response = await fetch('api/update_family.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updateData)
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error || 'Update failed');
            }

            showToast('परिवार डेटा सफलतापूर्वक अपडेट हो गया!');
            editModal.classList.remove('active');
            document.body.style.overflow = '';

            // 🔥 Reload branches dropdown (in case new branch was added)
            await loadBranchesForEdit();
            await loadFamilies();
        } catch (error) {
            console.error("❌ Save error:", error);
            showToast('अपडेट करने में त्रुटि: ' + error.message, 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> बदलाव सेव करें';
        }
    });
}

// =========================================
// 🗑️ DELETE
// =========================================
window.confirmDelete = async (familyId, familyName) => {
    if (!confirm(`⚠️ क्या आप वाकई "${familyName}" को हटाना चाहते हैं?`)) return;
    if (!confirm(`🔴 अंतिम चेतावनी! यह वापस नहीं होगा।`)) return;

    try {
        const response = await fetch('api/delete_family.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: familyId })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'Delete failed');
        }

        showToast('परिवार सफलतापूर्वक हटा दिया गया।');
        await loadFamilies();
    } catch (error) {
        showToast('हटाने में त्रुटि: ' + error.message, 'error');
    }
};

function addEditMemberRow(memberData = null) {
    const container = document.getElementById('edit-members-container');
    if (!container) return;

    memberCounter++;
    const div = document.createElement('div');
    div.className = 'member-edit-item';
    const esc = (str) => String(str ?? '').replace(/"/g, '&quot;');

    div.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <h5 style="margin:0;"><i class="fas fa-user"></i> सदस्य #${memberCounter}</h5>
            <button type="button" style="color:red" class="remove-member-btn">
                <i class="fas fa-times"></i>X
            </button>
        </div>
        <div class="form-grid">
            <div class="form-group"><label>नाम *</label><input type="text" class="m-name" data-hindi-input="true" required value="${esc(memberData?.name)}"></div>
            <div class="form-group"><label>नाता *</label>
                <select class="m-relation" required>
                    <option value="">चुनें</option>
                    ${['पति', 'पत्नी', 'पिता', 'माता', 'पुत्र', 'पुत्री', 'पुत्रवधू', 'पौत्र', 'पौत्री', 'भाई', 'भाभी', 'बहन', 'भतीजा', 'भतीजी', 'अन्य'].map(r =>
        `<option value="${r}" ${memberData?.relation === r ? 'selected' : ''}>${r}</option>`).join('')}
                </select>
            </div>
            <div class="form-group"><label>आयु *</label><input type="text" class="m-age" min="0" max="120" required value="${esc(memberData?.age)}"></div>
            <div class="form-group"><label>लिंग *</label>
                <select class="m-gender" required>
                    <option value="">चुनें</option>
                    <option value="पुरुष" ${memberData?.gender === 'पुरुष' ? 'selected' : ''}>पुरुष</option>
                    <option value="महिला" ${memberData?.gender === 'महिला' ? 'selected' : ''}>महिला</option>
                </select>
            </div>
            <div class="form-group"><label>वैवाहिक स्थिति</label>
                <select class="m-status">
                    <option value="अविवाहित" ${memberData?.maritalStatus === 'अविवाहित' ? 'selected' : ''}>अविवाहित</option>
                    <option value="विवाहित" ${memberData?.maritalStatus === 'विवाहित' ? 'selected' : ''}>विवाहित</option>
                    <option value="विधवा" ${memberData?.maritalStatus === 'विधवा' ? 'selected' : ''}>विधवा/विधुर</option>
                </select>
            </div>
            <div class="form-group"><label>शिक्षा</label><input type="text" class="m-education" data-hindi-input="true" value="${esc(memberData?.education)}"></div>
            <div class="form-group"><label>व्यवसाय</label><input type="text" class="m-occupation" data-hindi-input="true" value="${esc(memberData?.occupation)}"></div>
            <div class="form-group"><label>मोबाइल</label><input type="tel" class="m-mobile" pattern="[0-9]{10}" value="${esc(memberData?.mobile)}"></div>
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

    setTimeout(() => {
        div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const nameInput = div.querySelector('.m-name');
        if (nameInput && !memberData) nameInput.focus();
    }, 100);
}

// ✅ Edit Form Photo Preview Handler
const editPhotoInput = document.getElementById('edit-family-photo');
const editNewPreview = document.getElementById('edit-family-new-preview');
const editNewContainer = document.getElementById('edit-new-photo-container');

if (editPhotoInput && editNewPreview) {
    editPhotoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                showToast('Image 2MB से कम होनी चाहिए।', 'error');
                editPhotoInput.value = '';
                editNewContainer.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = (ev) => {
                editNewPreview.src = ev.target.result;
                editNewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            editNewContainer.style.display = 'none';
        }
    });
}

// 🎯 Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    checkAdminAuth();
});