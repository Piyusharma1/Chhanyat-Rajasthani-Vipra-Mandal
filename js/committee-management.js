let currentAdminAuth = null;

// 🔐 Auth Check
async function checkPageAuth() {
    try {
        const response = await fetch('api/check_auth.php?_t=' + Date.now(), { cache: 'no-store' });
        const result = await response.json();
        if (!result.isAdmin) {
            window.location.href = 'admin-login.php';
            return false;
        }
        currentAdminAuth = result;
        applyAuthRestrictions();
        return true;
    } catch (error) {
        window.location.href = 'admin-login.php';
        return false;
    }
}

// 🛡️ Apply UI restrictions based on admin role
function applyAuthRestrictions() {
    if (!currentAdminAuth) return;

    const centralForm = document.getElementById('central-committee-form');
    const centralFormTitle = centralForm?.previousElementSibling;

    if (!currentAdminAuth.isSuperAdmin) {
        // Hide central committee add form for branch admins
        if (centralForm) centralForm.style.display = 'none';
        if (centralFormTitle && centralFormTitle.tagName === 'H4') {
            centralFormTitle.outerHTML = `
                <div style="padding: 12px 16px; background: var(--gold-pale); color: var(--text-mid); border-radius: var(--radius-sm); border-left: 4px solid var(--gold); margin-top: 20px; font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-lock"></i> केवल केंद्रीय कार्यकारिणी (Super Admin) को केंद्रीय समिति में नए सदस्य जोड़ने/हटाने की अनुमति है।
                </div>
            `;
        }
    }
}

// =========================================
// 🔔 TOAST NOTIFICATION (Themed)
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
    toast.style.cssText = `
        display: flex; align-items: center; gap: 12px; padding: 15px; margin-bottom: 10px; 
        border-radius: 8px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
        background: ${type === 'success' ? 'var(--maroon)' : 'var(--red-dark)'}; 
        transform: translateX(100%); transition: transform 0.3s ease;
    `;
    container.appendChild(toast);
    
    setTimeout(() => toast.style.transform = 'translateX(0)', 10); 
    
    setTimeout(() => { 
        toast.style.transform = 'translateX(100%)'; 
        setTimeout(() => toast.remove(), 300); 
    }, 4000);
}

// =========================================
// 📸 IMAGE UPLOAD
// =========================================
async function uploadImageToHostinger(fileInputId, customName = '') {
    const fileInput = document.getElementById(fileInputId);
    if (!fileInput || !fileInput.files[0]) return null;
    const file = fileInput.files[0];
    if (file.size > 2 * 1024 * 1024) { showToast('फ़ाइल 2MB से बड़ी है।', 'error'); return null; }
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'committee');
    if (customName) {
        formData.append('name', customName);
        formData.append('custom_name', customName);
    }
    try {
        const response = await fetch('api/upload-image.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) return result.url;
        else { showToast(result.error || result.message || 'छवि अपलोड त्रुटि।', 'error'); return null; }
    } catch (error) { showToast('छवि अपलोड त्रुटि: ' + error.message, 'error'); return null; }
}

// =========================================
// ➕ ADD BRANCH COMMITTEE MEMBER
// =========================================
async function handleAddMember(e) {
    e.preventDefault();
    if (!currentBranchId) { showToast('कृपया पहले एक समाज चुनें।', 'error'); return; }
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> जोड़ रहा है...';
    try {
        let photoUrl = null;
        const memberName = document.getElementById('comm-name')?.value.trim() || '';
        const photoInput = document.getElementById('comm-photo-file');
        if (photoInput && photoInput.files[0]) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> छवि अपलोड...';
            photoUrl = await uploadImageToHostinger('comm-photo-file', memberName);
        }
        if (!photoUrl) photoUrl = document.getElementById('comm-photo')?.value.trim() || null;
        
        const newMember = {
            name: document.getElementById('comm-name').value.trim(),
            designation: document.getElementById('comm-designation').value.trim(),
            mobile: document.getElementById('comm-mobile').value.trim(),
            photo: photoUrl
        };
        Object.keys(newMember).forEach(key => { if (!newMember[key]) delete newMember[key]; });
        
        const branch = allBranches.find(b => String(b.id) === String(currentBranchId));
        if (!branch) { showToast('समाज नहीं मिली।', 'error'); return; }
        
        const committee = branch.committee || [];
        committee.push(newMember);
        
        const response = await fetch('api/update_branch_committee.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ branchId: currentBranchId, committee: committee })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Update failed');
        
        showToast('नया सदस्य जोड़ा गया!');
        branch.committee = committee; renderCommitteeList(branch); e.target.reset();
        clearSelectedPhoto('branch');
    } catch (error) { showToast('जोड़ने में त्रुटि: ' + error.message, 'error'); } 
    finally { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-plus"></i> समाज सदस्य जोड़ें'; }
}

// =========================================
// =========================================
// 👁️ IMAGE PREVIEW & FAMILY PHOTO PICKER
// =========================================
let cachedFamilyPhotos = null;
let currentPickerTarget = 'branch'; // 'branch' or 'central'

function setupImagePreview(fileInputId, previewId, badgeId, labelId, hiddenId) {
    const fileInput = document.getElementById(fileInputId); 
    const preview = document.getElementById(previewId);
    const badge = document.getElementById(badgeId);
    const label = document.getElementById(labelId);
    const hidden = document.getElementById(hiddenId);
    if (!fileInput || !preview) return;

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (hidden) hidden.value = ''; // Clear selected family URL since local file is selected
            const reader = new FileReader(); 
            reader.onload = (e) => { 
                preview.src = e.target.result; 
                preview.style.display = 'block'; 
                if (badge) badge.style.display = 'flex';
                if (label) label.textContent = file.name;
                const sourceEl = badge ? badge.querySelector('small') : null;
                if (sourceEl) sourceEl.textContent = 'डिवाइस से चुनी गई फ़ाइल';
            }; 
            reader.readAsDataURL(file); 
        } else { 
            if (badge && (!hidden || !hidden.value)) {
                badge.style.display = 'none';
                preview.style.display = 'none';
            }
        }
    });
}

// 🖼️ Open Family Photo Modal
async function openFamilyPhotoPicker(target) {
    currentPickerTarget = target;
    const modal = document.getElementById('family-photo-modal');
    if (!modal) return;
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Reset filters
    const searchInput = document.getElementById('family-picker-search');
    const branchFilter = document.getElementById('family-picker-branch-filter');
    if (searchInput) searchInput.value = '';
    if (branchFilter) branchFilter.value = '';

    if (!cachedFamilyPhotos) {
        await loadFamilyPhotos();
    } else {
        renderFamilyPhotosGrid();
    }

    if (searchInput) {
        setTimeout(() => searchInput.focus(), 100);
    }
}

function closeFamilyPhotoPicker() {
    const modal = document.getElementById('family-photo-modal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

async function loadFamilyPhotos() {
    const grid = document.getElementById('family-photos-grid');
    const countEl = document.getElementById('family-photos-count');
    if (grid) grid.innerHTML = '<p style="text-align:center;color:var(--text-soft);grid-column:1/-1;"><i class="fas fa-spinner fa-spin"></i> परिवार फोटो लोड हो रही हैं...</p>';

    try {
        const response = await fetch('api/get_family_photos.php');
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'फोटो लोड करने में त्रुटि');

        const rawList = result.data || [];
        const seenUrls = new Set();
        cachedFamilyPhotos = [];
        rawList.forEach(p => {
            const normalized = (p.filename || p.url || '').toLowerCase();
            if (!seenUrls.has(normalized)) {
                seenUrls.add(normalized);
                cachedFamilyPhotos.push(p);
            }
        });

        // Populate branch dropdown
        const branchFilter = document.getElementById('family-picker-branch-filter');
        if (branchFilter) {
            const branches = [...new Set(cachedFamilyPhotos.map(p => p.branch).filter(Boolean))].sort();
            branchFilter.innerHTML = '<option value="">सभी समाज</option>' + branches.map(b => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`).join('');
        }

        renderFamilyPhotosGrid();
    } catch (error) {
        if (grid) grid.innerHTML = `<p style="text-align:center;color:var(--red);grid-column:1/-1;">त्रुटि: ${error.message}</p>`;
    }
}

function renderFamilyPhotosGrid(searchTerm = '', branchFilter = '') {
    const grid = document.getElementById('family-photos-grid');
    const countEl = document.getElementById('family-photos-count');
    if (!grid) return;

    if (!cachedFamilyPhotos || cachedFamilyPhotos.length === 0) {
        grid.innerHTML = '<p style="text-align:center;color:var(--text-soft);grid-column:1/-1;">कोई परिवार फोटो उपलब्ध नहीं है।</p>';
        if (countEl) countEl.textContent = '';
        return;
    }

    const term = searchTerm.trim().toLowerCase();
    const branch = branchFilter.trim();

    const filtered = cachedFamilyPhotos.filter(item => {
        const headMatch = item.head && item.head.toLowerCase().includes(term);
        const fileMatch = item.filename && item.filename.toLowerCase().includes(term);
        const branchMatchSearch = item.branch && item.branch.toLowerCase().includes(term);
        const matchSearch = !term || headMatch || fileMatch || branchMatchSearch;
        const matchBranch = !branch || (item.branch === branch);
        return matchSearch && matchBranch;
    });

    if (countEl) {
        countEl.textContent = `कुल ${filtered.length} परिवार फोटो उपलब्ध`;
    }

    if (filtered.length === 0) {
        grid.innerHTML = '<p style="text-align:center;color:var(--text-soft);grid-column:1/-1;">कोई परिणाम नहीं मिला।</p>';
        return;
    }

    let currentSelectedUrl = '';
    if (currentPickerTarget === 'central') {
        currentSelectedUrl = document.getElementById('central-comm-photo')?.value;
    } else if (currentPickerTarget === 'edit') {
        currentSelectedUrl = document.getElementById('edit-comm-photo')?.value;
    } else {
        currentSelectedUrl = document.getElementById('comm-photo')?.value;
    }

    grid.innerHTML = filtered.map(photo => {
        const isSelected = currentSelectedUrl && currentSelectedUrl === photo.url;
        const displayName = photo.head || photo.filename || 'परिवार सदस्य';
        const displayBranch = photo.branch ? `<span class="family-photo-branch">${escapeHtml(photo.branch)}</span>` : '';

        return `
            <div class="family-photo-card ${isSelected ? 'selected' : ''}" onclick="selectFamilyPhoto('${escapeHtml(photo.url)}', '${escapeHtml(displayName)}')">
                <img src="${escapeHtml(photo.url)}" alt="${escapeHtml(displayName)}" onerror="this.src='https://placehold.co/80x80/FF6B00/ffffff?text=P'">
                <div class="family-photo-name" title="${escapeHtml(displayName)}">${escapeHtml(displayName)}</div>
                ${displayBranch}
            </div>
        `;
    }).join('');
}

function selectFamilyPhoto(url, headName) {
    let prefix = 'comm';
    if (currentPickerTarget === 'central') {
        prefix = 'central-comm';
    } else if (currentPickerTarget === 'edit') {
        prefix = 'edit-comm';
    }

    const hiddenInput = document.getElementById(`${prefix}-photo`);
    const preview = document.getElementById(`${prefix}-photo-preview`);
    const badge = document.getElementById(`${prefix}-photo-selected`);
    const label = document.getElementById(`${prefix}-photo-label`);
    const source = document.getElementById(`${prefix}-photo-source`);
    const fileInput = document.getElementById(`${prefix}-photo-file`);
    const nameInput = document.getElementById(`${prefix}-name`);

    if (hiddenInput) hiddenInput.value = url;
    if (preview) { preview.src = url; preview.style.display = 'block'; }
    if (label) label.textContent = headName || 'परिवार फोटो';
    if (source) source.textContent = url;
    if (badge) badge.style.display = 'flex';
    if (fileInput) fileInput.value = '';

    // If member name is currently empty, auto-fill with head name
    if (nameInput && !nameInput.value.trim() && headName) {
        nameInput.value = headName;
    }

    closeFamilyPhotoPicker();
    showToast('परिवार फोटो चुनी गई!');
}

window.clearSelectedPhoto = function(target) {
    let prefix = 'comm';
    if (target === 'central') {
        prefix = 'central-comm';
    } else if (target === 'edit') {
        prefix = 'edit-comm';
    }

    const hiddenInput = document.getElementById(`${prefix}-photo`);
    const preview = document.getElementById(`${prefix}-photo-preview`);
    const badge = document.getElementById(`${prefix}-photo-selected`);
    const fileInput = document.getElementById(`${prefix}-photo-file`);

    if (hiddenInput) hiddenInput.value = '';
    if (preview) { preview.src = ''; preview.style.display = 'none'; }
    if (badge) badge.style.display = 'none';
    if (fileInput) fileInput.value = '';
};

window.openFamilyPhotoPicker = openFamilyPhotoPicker;
window.closeFamilyPhotoPicker = closeFamilyPhotoPicker;
window.selectFamilyPhoto = selectFamilyPhoto;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// =========================================
// 📦 LOAD BRANCHES
// =========================================
async function loadBranches() {
    const select = document.getElementById('committee-branch-select'); if (!select) return;
    select.innerHTML = '<option value="">लोड हो रहा है...</option>';
    try {
        const response = await fetch('api/get_branches.php?all=true');
        const result = await response.json();
        allBranches = (result.data || []).map(b => ({ ...b, id: String(b.id) }));
        select.innerHTML = '<option value="">-- समाज चुनें --</option>';
        allBranches.forEach(b => {
            const option = document.createElement('option'); option.value = b.id; option.textContent = b.branchName || 'अज्ञात'; select.appendChild(option);
        });

        // Lock dropdown if logged-in admin is restricted to a branch
        if (currentAdminAuth && !currentAdminAuth.isSuperAdmin && currentAdminAuth.branch) {
            const assignedBranch = allBranches.find(b => b.branchName?.toLowerCase() === currentAdminAuth.branch.toLowerCase());
            if (assignedBranch) {
                select.value = assignedBranch.id;
                select.disabled = true;
                select.style.backgroundColor = '#f0f0f0';
                select.title = `आप केवल ${currentAdminAuth.branch} समाज कार्यकारिणी प्रबंधित कर सकते हैं।`;
                handleBranchChange({ target: select });
            }
        }
    } catch (error) { select.innerHTML = '<option value="">त्रुटि: समाज लोड नहीं हुई</option>'; }
}

// =========================================
// 👥 RENDER BRANCH COMMITTEE (THEMED)
// =========================================
// =========================================
// 👥 RENDER BRANCH COMMITTEE (THEMED WITH ORDERING)
// =========================================
function renderCommitteeList(branch) {
    const list = document.getElementById('committee-list'); if (!list) return;
    const committee = branch.committee || [];
    if (committee.length === 0) { list.innerHTML = '<p style="color:var(--text-secondary);text-align:center;margin:0;">इस समाज में कोई सदस्य नहीं है।</p>'; return; }
    
    list.innerHTML = committee.map((m, index) => {
        const uniqueImageUrl = m.photo ? `${m.photo}?v=${new Date().getTime()}` : 'https://placehold.co/40x40/FF6B00/ffffff?text=P';
        return `
           <div class="member-item-row">
                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                    <span style="font-size:0.8rem;font-weight:700;color:var(--saffron-deep);padding:4px 8px;background:rgba(255,107,0,0.12);border-radius:12px;min-width:32px;text-align:center;">#${index + 1}</span>
                    <img src="${uniqueImageUrl}" style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid var(--saffron);" onerror="this.src='https://placehold.co/40x40/FF6B00/ffffff?text=P'">
                    <div>
                        <strong style="color:var(--maroon-deep);">${escapeHtml(m.name || 'अज्ञात')}</strong> 
                        <small style="color:var(--text-soft);display:block;">${escapeHtml(m.designation || '—')}</small>
                        ${m.mobile ? `<small style="color:var(--text-mid);">📱 ${escapeHtml(m.mobile)}</small>` : ''}
                    </div>
                </div>
                <div class="member-actions-group">
                    <button class="order-btn" onclick="moveCommitteeMember(${index}, -1)" ${index === 0 ? 'disabled' : ''} title="ऊपर लाएं">
                        <i class="fas fa-arrow-up"></i> ऊपर
                    </button>
                    <button class="order-btn" onclick="moveCommitteeMember(${index}, 1)" ${index === committee.length - 1 ? 'disabled' : ''} title="नीचे ले जाएं">
                        <i class="fas fa-arrow-down"></i> नीचे
                    </button>
                    <button onclick="openEditCommitteeMember('branch', ${index})" style="background:var(--saffron);color:white;border:none;border-radius:var(--radius-sm);padding:8px 12px;cursor:pointer;font-size:0.85rem;transition:0.2s;display:inline-flex;align-items:center;gap:4px;" title="संपादित करें" onmouseover="this.style.background='var(--saffron-deep)'" onmouseout="this.style.background='var(--saffron)'">
                        <i class="fas fa-edit"></i> एडिट
                    </button>
                    <button onclick="deleteCommitteeMember(${index})" style="background:var(--red);color:white;border:none;border-radius:var(--radius-sm);padding:8px 12px;cursor:pointer;font-size:0.85rem;transition:0.2s;display:inline-flex;align-items:center;gap:4px;" title="हटाएं" onmouseover="this.style.background='var(--red-dark)'" onmouseout="this.style.background='var(--red)'">
                        <i class="fas fa-trash"></i> हटाएं
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// =========================================
// 🔄 BRANCH SELECT CHANGE
// =========================================
function handleBranchChange(e) {
    const branchId = e.target.value; currentBranchId = branchId || null;
    if (!branchId) { renderCommitteeList({ committee: [] }); return; }
    const branch = allBranches.find(b => String(b.id) === String(branchId));
    if (branch) renderCommitteeList(branch); else renderCommitteeList({ committee: [] });
}

// =========================================
// 🏛️ CENTRAL COMMITTEE - LOAD & RENDER (THEMED WITH ORDERING)
// =========================================
async function loadCentralCommittee() {
    const list = document.getElementById('central-committee-list'); if (!list) return;
    list.innerHTML = '<p style="text-align:center;color:var(--text-secondary);">लोड हो रहा है...</p>';
    try {
        const response = await fetch('api/get_committee.php?all=true');
        const result = await response.json(); centralCommittee = result.data || []; renderCentralCommitteeList();
    } catch (error) { list.innerHTML = '<p style="color:var(--red);text-align:center;">त्रुटि: डेटा लोड नहीं हुआ।</p>'; }
}

function renderCentralCommitteeList() {
    const list = document.getElementById('central-committee-list'); if (!list) return;
    if (centralCommittee.length === 0) { list.innerHTML = '<p style="color:var(--text-secondary);text-align:center;margin:0;">केंद्रीय समिति खाली है।</p>'; return; }
    
    const isSuperAdmin = currentAdminAuth && currentAdminAuth.isSuperAdmin;

    list.innerHTML = centralCommittee.map((m, index) => {
        const uniqueImageUrl = m.photo ? `${m.photo}?v=${new Date().getTime()}` : 'https://placehold.co/40x40/7B1A1A/ffffff?text=P';
        const actionsHtml = isSuperAdmin ? `
            <div class="member-actions-group">
                <button class="order-btn" onclick="moveCentralMember(${index}, -1)" ${index === 0 ? 'disabled' : ''} title="ऊपर लाएं">
                    <i class="fas fa-arrow-up"></i> ऊपर
                </button>
                <button class="order-btn" onclick="moveCentralMember(${index}, 1)" ${index === centralCommittee.length - 1 ? 'disabled' : ''} title="नीचे ले जाएं">
                    <i class="fas fa-arrow-down"></i> नीचे
                </button>
                <button onclick="openEditCommitteeMember('central', '${m.id}')"
                    style="background:var(--saffron);color:white;border:none;border-radius:var(--radius-sm);padding:8px 12px;cursor:pointer;font-size:0.85rem;transition:0.2s;display:inline-flex;align-items:center;gap:4px;"
                    title="संपादित करें"
                    onmouseover="this.style.background='var(--saffron-deep)'"
                    onmouseout="this.style.background='var(--saffron)'">
                    <i class="fas fa-edit"></i> एडिट
                </button>
                <button onclick="deleteCentralMember('${m.id}')"
                    style="background:var(--red-dark);color:white;border:none;border-radius:var(--radius-sm);padding:8px 12px;cursor:pointer;font-size:0.85rem;transition:0.2s;display:inline-flex;align-items:center;gap:4px;"
                    title="हटाएं"
                    onmouseover="this.style.background='#8B0000'"
                    onmouseout="this.style.background='var(--red-dark)'">
                    <i class="fas fa-trash"></i> हटाएं
                </button>
            </div>
        ` : '';

        return `
          <div class="member-item-row central">
                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                    <span style="font-size:0.8rem;font-weight:700;color:var(--maroon);padding:4px 8px;background:rgba(123,26,26,0.12);border-radius:12px;min-width:32px;text-align:center;">#${index + 1}</span>
                    <img src="${uniqueImageUrl}" style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid var(--maroon);" onerror="this.src='https://placehold.co/40x40/7B1A1A/ffffff?text=P'">
                    <div>
                        <strong style="color:var(--maroon-deep);">${m.name || 'अज्ञात'}</strong> 
                        <small style="color:var(--text-soft);display:block;">${m.designation || '—'}</small>
                        ${m.mobile ? `<small style="color:var(--text-mid);">📱 ${m.mobile}</small>` : ''}
                    </div>
                </div>
              ${actionsHtml}
            </div>
        `;
    }).join('');
}

// =========================================
// ➕ ADD CENTRAL COMMITTEE MEMBER
// =========================================
async function handleAddCentralMember(e) {
    const form = e.target || e; const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
    if (!submitBtn) return;
    submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> जोड़ रहा है...';
    try {
        let photoUrl = null; 
        const memberName = document.getElementById('central-comm-name')?.value.trim() || '';
        const photoInput = document.getElementById('central-comm-photo-file');
        if (photoInput && photoInput.files[0]) { 
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> छवि अपलोड...'; 
            photoUrl = await uploadImageToHostinger('central-comm-photo-file', memberName); 
        }
        if (!photoUrl) photoUrl = document.getElementById('central-comm-photo')?.value.trim() || null;
        
        const newMember = {
            name: document.getElementById('central-comm-name').value.trim(),
            designation: document.getElementById('central-comm-designation').value.trim(),
            mobile: document.getElementById('central-comm-mobile')?.value.trim() || null,
            photo: photoUrl
        };
        Object.keys(newMember).forEach(key => { if (!newMember[key]) delete newMember[key]; });
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सेव हो रहा है...';
        const response = await fetch('api/add_committee_member.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(newMember) });
        const result = await response.json(); if (!result.success) throw new Error(result.error || 'Save failed');
        
        showToast('केंद्रीय सदस्य जोड़ा गया!'); form.reset();
        clearSelectedPhoto('central');
        loadCentralCommittee();
    } catch (error) { showToast('जोड़ने में त्रुटि: ' + error.message, 'error'); } 
    finally { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-plus"></i> केंद्रीय सदस्य जोड़ें'; }
}

// =========================================
// 🌍 GLOBAL REORDER & DELETE FUNCTIONS
// =========================================
window.moveCommitteeMember = async function(index, direction) {
    if (!currentBranchId) return;
    const branch = allBranches.find(b => String(b.id) === String(currentBranchId));
    if (!branch || !branch.committee) return;
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= branch.committee.length) return;

    // Swap elements
    const temp = branch.committee[index];
    branch.committee[index] = branch.committee[targetIndex];
    branch.committee[targetIndex] = temp;

    try {
        const response = await fetch('api/update_branch_committee.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ branchId: currentBranchId, committee: branch.committee })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Reorder failed');

        showToast('सदस्यों का क्रम अपडेट किया गया!');
        renderCommitteeList(branch);
    } catch (error) {
        showToast('क्रम बदलने में त्रुटि: ' + error.message, 'error');
        // Revert swap on error
        const temp = branch.committee[index];
        branch.committee[index] = branch.committee[targetIndex];
        branch.committee[targetIndex] = temp;
        renderCommitteeList(branch);
    }
};

window.moveCentralMember = async function(index, direction) {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= centralCommittee.length) return;

    // Swap elements in memory
    const temp = centralCommittee[index];
    centralCommittee[index] = centralCommittee[targetIndex];
    centralCommittee[targetIndex] = temp;

    renderCentralCommitteeList();

    const orderedIds = centralCommittee.map(m => m.id);

    try {
        const response = await fetch('api/reorder_central_committee.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: orderedIds })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Reorder failed');

        showToast('केंद्रीय सदस्यों का क्रम अपडेट किया गया!');
    } catch (error) {
        showToast('क्रम बदलने में त्रुटि: ' + error.message, 'error');
        loadCentralCommittee();
    }
};

window.deleteCentralMember = async function(id) {
    if (!confirm('⚠️ क्या आप इस केंद्रीय सदस्य को हटाना चाहते हैं?')) return;
    try {
        const response = await fetch('api/delete_committee_member.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id }) });
        const result = await response.json(); if (!result.success) throw new Error(result.error || 'Delete failed');
        showToast('केंद्रीय सदस्य हटा दिया गया।'); loadCentralCommittee();
    } catch (error) { showToast('हटाने में त्रुटि: ' + error.message, 'error'); }
};

window.deleteCommitteeMember = async function(index) {
    if (!currentBranchId) return;
    if (!confirm('⚠️ क्या आप इस सदस्य को हटाना चाहते हैं?')) return;
    try {
        const branch = allBranches.find(b => String(b.id) === String(currentBranchId)); if (!branch) return;
        const committee = branch.committee || [];
        const photoToDelete = committee[index]?.photo || null;
        committee.splice(index, 1);
        const response = await fetch('api/update_branch_committee.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ branchId: currentBranchId, committee: committee, deletePhoto: photoToDelete }) });
        const result = await response.json(); if (!result.success) throw new Error(result.error || 'Update failed');
        showToast('सदस्य हटा दिया गया।');
        await loadBranches();
        const select = document.getElementById('committee-branch-select');
        if (select) { select.value = currentBranchId; handleBranchChange({ target: select }); }
    } catch (error) { showToast('हटाने में त्रुटि: ' + error.message, 'error'); }
};

// =========================================
// ✏️ EDIT COMMITTEE MEMBER
// =========================================
window.openEditCommitteeMember = function(type, idOrIndex) {
    const modal = document.getElementById('edit-committee-modal');
    if (!modal) return;

    const typeInput = document.getElementById('edit-comm-type');
    const indexInput = document.getElementById('edit-comm-index');
    const idInput = document.getElementById('edit-comm-id');
    const nameInput = document.getElementById('edit-comm-name');
    const designationInput = document.getElementById('edit-comm-designation');
    const mobileInput = document.getElementById('edit-comm-mobile');
    const photoHidden = document.getElementById('edit-comm-photo');
    const origPhotoHidden = document.getElementById('edit-comm-orig-photo');
    const fileInput = document.getElementById('edit-comm-photo-file');
    const preview = document.getElementById('edit-comm-photo-preview');
    const badge = document.getElementById('edit-comm-photo-selected');
    const label = document.getElementById('edit-comm-photo-label');
    const source = document.getElementById('edit-comm-photo-source');

    if (fileInput) fileInput.value = '';

    typeInput.value = type;

    let member = null;
    if (type === 'branch') {
        indexInput.value = idOrIndex;
        idInput.value = '';
        const branch = allBranches.find(b => String(b.id) === String(currentBranchId));
        if (branch && branch.committee && branch.committee[idOrIndex]) {
            member = branch.committee[idOrIndex];
        }
    } else {
        idInput.value = idOrIndex;
        indexInput.value = '';
        member = centralCommittee.find(m => String(m.id) === String(idOrIndex));
    }

    if (!member) {
        showToast('सदस्य जानकारी नहीं मिली।', 'error');
        return;
    }

    nameInput.value = member.name || '';
    designationInput.value = member.designation || '';
    mobileInput.value = member.mobile || '';
    
    const existingPhoto = member.photo || '';
    photoHidden.value = existingPhoto;
    origPhotoHidden.value = existingPhoto;

    if (existingPhoto) {
        preview.src = existingPhoto;
        preview.style.display = 'block';
        if (badge) badge.style.display = 'flex';
        if (label) label.textContent = 'वर्तमान फोटो';
        if (source) source.textContent = existingPhoto;
    } else {
        preview.src = '';
        preview.style.display = 'none';
        if (badge) badge.style.display = 'none';
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    nameInput.focus();

    if (typeof window.initHindiTransliteration === 'function') {
        window.initHindiTransliteration('#edit-committee-form [data-hindi-input="true"]', modal);
    }
};

window.closeEditCommitteeModal = function() {
    const modal = document.getElementById('edit-committee-modal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
};

async function handleSaveEditMember(e) {
    e.preventDefault();
    const type = document.getElementById('edit-comm-type').value;
    const saveBtn = document.getElementById('btn-save-edit-comm');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सहेज रहा है...';
    }

    try {
        const name = document.getElementById('edit-comm-name').value.trim();
        const designation = document.getElementById('edit-comm-designation').value.trim();
        const mobile = document.getElementById('edit-comm-mobile').value.trim();
        const origPhoto = document.getElementById('edit-comm-orig-photo').value.trim();
        let photoUrl = document.getElementById('edit-comm-photo').value.trim();

        const fileInput = document.getElementById('edit-comm-photo-file');
        if (fileInput && fileInput.files[0]) {
            if (saveBtn) saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> फोटो अपलोड...';
            const uploadedUrl = await uploadImageToHostinger('edit-comm-photo-file', name);
            if (uploadedUrl) {
                photoUrl = uploadedUrl;
            }
        }

        if (type === 'branch') {
            const index = parseInt(document.getElementById('edit-comm-index').value, 10);
            if (!currentBranchId) throw new Error('समाज चयनित नहीं है।');
            const branch = allBranches.find(b => String(b.id) === String(currentBranchId));
            if (!branch || !branch.committee || branch.committee[index] === undefined) {
                throw new Error('सदस्य नहीं मिला।');
            }

            const updatedCommittee = [...branch.committee];
            const updatedMember = {
                ...updatedCommittee[index],
                name: name,
                designation: designation,
                mobile: mobile,
                photo: photoUrl || null
            };
            Object.keys(updatedMember).forEach(k => {
                if (updatedMember[k] === null || updatedMember[k] === '') {
                    delete updatedMember[k];
                }
            });

            updatedCommittee[index] = updatedMember;

            // If photo changed and old photo was a committee upload, request its deletion
            const deletePhoto = (origPhoto && origPhoto !== photoUrl) ? origPhoto : null;

            const response = await fetch('api/update_branch_committee.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    branchId: currentBranchId,
                    committee: updatedCommittee,
                    deletePhoto: deletePhoto
                })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Update failed');

            branch.committee = updatedCommittee;
            renderCommitteeList(branch);
            showToast('सदस्य विवरण सफलतापूर्वक अपडेट किया गया!');
            closeEditCommitteeModal();

        } else if (type === 'central') {
            const memberId = document.getElementById('edit-comm-id').value;
            const deletePhoto = (origPhoto && origPhoto !== photoUrl) ? origPhoto : null;

            const response = await fetch('api/update_committee_member.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: memberId,
                    name: name,
                    designation: designation,
                    mobile: mobile,
                    photo: photoUrl,
                    deletePhoto: deletePhoto
                })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Update failed');

            showToast('केंद्रीय कार्यकारिणी सदस्य अपडेट किया गया!');
            closeEditCommitteeModal();
            loadCentralCommittee();
        }
    } catch (error) {
        showToast('अपडेट में त्रुटि: ' + error.message, 'error');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> बदलाव सहेजें';
        }
    }
}

// =========================================
// 🚀 INITIALIZE PAGE
// =========================================
function initCommitteePage() {
    loadBranches(); loadCentralCommittee();
    setupImagePreview('comm-photo-file', 'comm-photo-preview', 'comm-photo-selected', 'comm-photo-label', 'comm-photo');
    setupImagePreview('central-comm-photo-file', 'central-comm-photo-preview', 'central-comm-photo-selected', 'central-comm-photo-label', 'central-comm-photo');
    setupImagePreview('edit-comm-photo-file', 'edit-comm-photo-preview', 'edit-comm-photo-selected', 'edit-comm-photo-label', 'edit-comm-photo');
    
    const branchSelect = document.getElementById('committee-branch-select'); if (branchSelect) branchSelect.addEventListener('change', handleBranchChange);
    const branchForm = document.getElementById('committee-form'); if (branchForm) branchForm.addEventListener('submit', handleAddMember);
    const centralForm = document.getElementById('central-committee-form');
    if (centralForm) centralForm.addEventListener('submit', function(e) { e.preventDefault(); handleAddCentralMember(e); });
    const editForm = document.getElementById('edit-committee-form');
    if (editForm) editForm.addEventListener('submit', handleSaveEditMember);

    // Family photo picker events
    const pickerSearch = document.getElementById('family-picker-search');
    const branchFilter = document.getElementById('family-picker-branch-filter');
    if (pickerSearch) {
        pickerSearch.addEventListener('input', () => {
            renderFamilyPhotosGrid(pickerSearch.value, branchFilter ? branchFilter.value : '');
        });
    }
    if (branchFilter) {
        branchFilter.addEventListener('change', () => {
            renderFamilyPhotosGrid(pickerSearch ? pickerSearch.value : '', branchFilter.value);
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeFamilyPhotoPicker();
            closeEditCommitteeModal();
        }
    });

    // Initialize Hindi transliteration for forms
    if (typeof window.initHindiTransliteration === 'function') {
        window.initHindiTransliteration('[data-hindi-input="true"]', null);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const isAuthenticated = await checkPageAuth(); 
    if (isAuthenticated) {
        initCommitteePage();
    }
});