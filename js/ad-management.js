// ============================================
// 📢 ADVERTISEMENT MANAGEMENT MODULE
// ============================================

let allAds = [];
let deleteTargetId = null;

// 🔐 1. Auth Check
async function checkPageAuth() {
    try {
        const response = await fetch('api/check_auth.php?_t=' + Date.now(), { cache: 'no-store' });
        const result = await response.json();
        if (!result.isAdmin) {
            window.location.href = 'admin-login.php';
            return false;
        }
        return true;
    } catch (error) {
        console.error('🔒 Auth check failed:', error);
        window.location.href = 'admin-login.php';
        return false;
    }
}

// 🔔 2. Toast Notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
    toast.innerHTML = `
        <div style="font-size: 1.2rem;"><i class="fas ${icon}"></i></div>
        <div>
            <strong>${type === 'success' ? 'सफल' : type === 'info' ? 'सूचना' : 'त्रुटि'}</strong><br>
            <small style="opacity: 0.95;">${message}</small>
        </div>
    `;
    toast.style.cssText = `
        display: flex; align-items: center; gap: 12px; padding: 14px 18px; margin-bottom: 10px; 
        border-radius: 8px; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
        background: ${type === 'success' ? '#2e7d32' : type === 'info' ? 'var(--saffron, #FF6B00)' : '#c62828'}; 
        transform: translateX(120%); transition: transform 0.3s ease;
        min-width: 280px; max-width: 400px;
    `;
    container.appendChild(toast);

    setTimeout(() => toast.style.transform = 'translateX(0)', 10);
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// 🖼️ 3. Image Dropzone & Preview Setup
function setupDropzone() {
    const fileInput = document.getElementById('ad-image-file');
    const previewBox = document.getElementById('ad-image-preview');
    const previewImg = document.getElementById('preview-img');
    const previewFilename = document.getElementById('preview-filename');

    if (!fileInput || !previewBox) return;

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewFilename.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                previewBox.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewBox.style.display = 'none';
        }
    });
}

// 📦 4. Load All Advertisements (Admin View)
async function loadAds() {
    const loadingEl = document.getElementById('ads-loading');
    const emptyEl = document.getElementById('ads-empty');
    const gridEl = document.getElementById('ads-grid-container');

    if (loadingEl) loadingEl.style.display = 'block';
    if (emptyEl) emptyEl.style.display = 'none';
    if (gridEl) gridEl.style.display = 'none';

    try {
        const response = await fetch('api/get_ads.php?admin_view=true&_t=' + Date.now(), {
            credentials: 'include'
        });
        const result = await response.json();

        if (loadingEl) loadingEl.style.display = 'none';

        if (!result.success || !result.data || result.data.length === 0) {
            allAds = [];
            if (emptyEl) emptyEl.style.display = 'block';
            return;
        }

        allAds = result.data;
        renderAds(allAds);
        if (gridEl) gridEl.style.display = 'grid';
    } catch (error) {
        console.error('❌ Failed to load ads:', error);
        if (loadingEl) loadingEl.style.display = 'none';
        showToast('विज्ञापन लोड करने में त्रुटि', 'error');
    }
}

const positionLabels = {
    'above_calendar': 'Priority 1 : दिनदर्शिका के ऊपर',
    'above_announcements': 'Priority 2 : घोषणाएँ के ऊपर',
    'above_committee': 'Priority 3 : कार्यकारिणी के ऊपर',
    'below_committee': 'Priority 4 : कार्यकारिणी के नीचे'
};

// 🎨 5. Render Ads in Grid
function renderAds(ads) {
    const gridEl = document.getElementById('ads-grid-container');
    if (!gridEl) return;

    gridEl.innerHTML = ads.map(ad => {
        const isActive = ad.status === 'active';
        const phoneLink = ad.phone ? `tel:${ad.phone.replace(/\D/g, '')}` : null;
        const waLink = ad.phone ? `https://wa.me/91${ad.phone.replace(/\D/g, '')}` : null;
        const webLink = ad.link_url || null;
        const posText = positionLabels[ad.position] || positionLabels['above_calendar'];

        return `
            <div class="ad-card-item" id="ad-card-${ad.id}">
                <div class="ad-card-image-wrap">
                    <img src="${ad.image_url}" alt="${escapeHtml(ad.title)}" loading="lazy" onerror="this.style.opacity='0.3'">
                    <span class="ad-card-badge ${isActive ? 'active' : 'inactive'}">
                        ${isActive ? 'सक्रिय (Active)' : 'निष्क्रिय (Inactive)'}
                    </span>
                    <span class="ad-card-order">#${ad.display_order || 0}</span>
                </div>
                <div class="ad-card-body">
                    <div class="ad-card-position-badge">
                        <i class="fas fa-map-marker-alt"></i> ${posText}
                    </div>
                    <div class="ad-card-title">${escapeHtml(ad.title)}</div>
                    ${ad.business_name ? `<div class="ad-card-biz"><i class="fas fa-store"></i> ${escapeHtml(ad.business_name)}</div>` : ''}
                    
                    <div class="ad-card-meta">
                        ${ad.phone ? `
                            <div>
                                <i class="fas fa-phone-alt"></i> 
                                <a href="${phoneLink}">${escapeHtml(ad.phone)}</a>
                                <a href="${waLink}" target="_blank" rel="noopener" style="color: #25D366; margin-left: 8px;" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        ` : ''}
                        ${webLink ? `
                            <div>
                                <i class="fas fa-external-link-alt"></i> 
                                <a href="${webLink}" target="_blank" rel="noopener">${escapeHtml(webLink)}</a>
                            </div>
                        ` : ''}
                        ${ad.end_date ? `<div><i class="far fa-calendar-alt"></i> वैधता: ${ad.end_date}</div>` : ''}
                    </div>

                    <div class="ad-card-actions">
                        <button class="btn-status-toggle ${isActive ? 'is-active' : 'is-inactive'}" 
                                onclick="toggleAdStatus(${ad.id})" 
                                title="स्थिति बदलें">
                            <i class="fas ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i> 
                            ${isActive ? 'सक्रिय' : 'निष्क्रिय'}
                        </button>
                        
                        <div class="action-btns-group">
                            <button class="btn-icon-action btn-edit-ad" onclick="openEditModal(${ad.id})" title="संपादित करें">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon-action btn-delete-ad" onclick="confirmDeleteAd(${ad.id})" title="हटाएं">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// 🛡️ Helper: Escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// ➕ 6. Handle Add Ad Form Submission
async function handleAddAd(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('submit-ad-btn');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सहेज रहा है...';

    try {
        const formData = new FormData(form);
        const response = await fetch('api/add_ad.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });

        const result = await response.json();

        if (result.success) {
            showToast('विज्ञापन सफलतापूर्वक जोड़ दिया गया!', 'success');
            form.reset();
            setDefaultEndDate();
            const previewBox = document.getElementById('ad-image-preview');
            if (previewBox) previewBox.style.display = 'none';
            await loadAds();
        } else {
            showToast(result.error || 'विज्ञापन सहेजने में विफल', 'error');
        }
    } catch (error) {
        console.error('Error adding ad:', error);
        showToast('सर्वर त्रुटि। पुनः प्रयास करें।', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// 🔄 7. Quick Toggle Status
async function toggleAdStatus(id) {
    try {
        const response = await fetch('api/update_ad.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, action: 'toggle_status' }),
            credentials: 'include'
        });
        const result = await response.json();

        if (result.success) {
            showToast(`विज्ञापन स्थिति ${result.new_status === 'active' ? 'सक्रिय' : 'निष्क्रिय'} कर दी गई!`, 'info');
            await loadAds();
        } else {
            showToast(result.error || 'स्थिति बदलने में विफल', 'error');
        }
    } catch (error) {
        console.error('Error toggling ad status:', error);
        showToast('त्रुटि हुई।', 'error');
    }
}

// ✏️ 8. Edit Ad Modal
function openEditModal(id) {
    const ad = allAds.find(a => a.id == id);
    if (!ad) return;

    document.getElementById('edit-ad-id').value = ad.id;
    document.getElementById('edit-ad-title').value = ad.title || '';
    document.getElementById('edit-ad-business').value = ad.business_name || '';
    document.getElementById('edit-ad-phone').value = ad.phone || '';
    document.getElementById('edit-ad-link').value = ad.link_url || '';
    const posSelect = document.getElementById('edit-ad-position');
    if (posSelect) posSelect.value = ad.position || 'above_calendar';
    document.getElementById('edit-ad-order').value = ad.display_order || 0;
    document.getElementById('edit-ad-status').value = ad.status || 'active';
    const endDateEl = document.getElementById('edit-ad-end-date');
    if (endDateEl) endDateEl.value = ad.end_date || '';

    const modal = document.getElementById('edit-ad-modal');
    if (modal) modal.classList.add('active');
}

function closeEditModal() {
    const modal = document.getElementById('edit-ad-modal');
    if (modal) modal.classList.remove('active');
}

async function handleSaveEdit(e) {
    e.preventDefault();
    const form = e.target;
    const saveBtn = document.getElementById('save-edit-btn');
    const originalText = saveBtn.innerHTML;

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> अपडेट हो रहा है...';

    try {
        const formData = new FormData(form);
        const response = await fetch('api/update_ad.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        const result = await response.json();

        if (result.success) {
            showToast('विज्ञापन सफलतापूर्वक अपडेट किया गया!', 'success');
            closeEditModal();
            await loadAds();
        } else {
            showToast(result.error || 'अपडेट करने में विफल', 'error');
        }
    } catch (error) {
        console.error('Error updating ad:', error);
        showToast('सर्वर त्रुटि।', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

// 🗑️ 9. Delete Ad Handlers
function confirmDeleteAd(id) {
    deleteTargetId = id;
    const overlay = document.getElementById('delete-confirm-overlay');
    if (overlay) overlay.style.display = 'flex';
}

function closeDeleteConfirm() {
    deleteTargetId = null;
    const overlay = document.getElementById('delete-confirm-overlay');
    if (overlay) overlay.style.display = 'none';
}

async function executeDeleteAd() {
    if (!deleteTargetId) return;

    const btn = document.getElementById('confirm-delete-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> हटा रहे हैं...';

    try {
        const response = await fetch('api/delete_ad.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: deleteTargetId }),
            credentials: 'include'
        });
        const result = await response.json();

        if (result.success) {
            showToast('विज्ञापन सफलतापूर्वक हटा दिया गया!', 'success');
            closeDeleteConfirm();
            await loadAds();
        } else {
            showToast(result.error || 'हटाने में विफल', 'error');
        }
    } catch (error) {
        console.error('Error deleting ad:', error);
        showToast('सर्वर त्रुटि।', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// 📅 Helper: Set default end date to 10 days ahead
function setDefaultEndDate() {
    const endDateInput = document.getElementById('ad-end-date');
    if (endDateInput) {
        const d = new Date();
        d.setDate(d.getDate() + 10);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        endDateInput.value = `${yyyy}-${mm}-${dd}`;
    }
}

// 🚀 10. Initialization
document.addEventListener('DOMContentLoaded', async () => {
    const isAuthed = await checkPageAuth();
    if (!isAuthed) return;

    setupDropzone();
    setDefaultEndDate();

    const addForm = document.getElementById('add-ad-form');
    if (addForm) addForm.addEventListener('submit', handleAddAd);

    const editForm = document.getElementById('edit-ad-form');
    if (editForm) editForm.addEventListener('submit', handleSaveEdit);

    const closeEditBtn = document.getElementById('close-edit-modal');
    if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);

    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);

    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteConfirm);

    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) confirmDeleteBtn.addEventListener('click', executeDeleteAd);

    window.toggleAdStatus = toggleAdStatus;
    window.openEditModal = openEditModal;
    window.confirmDeleteAd = confirmDeleteAd;

    await loadAds();
});
