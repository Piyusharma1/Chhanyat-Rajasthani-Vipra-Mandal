// ============================================
// 📢 ANNOUNCEMENT MANAGEMENT MODULE
// ============================================

let currentAdminAuth = null;
let allBranches = [];

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
        return true;
    } catch (error) {
        console.error('🔒 Auth check failed:', error);
        window.location.href = 'admin-login.php';
        return false;
    }
}

// =========================================
// 🔔 TOAST NOTIFICATION (Themed)
// =========================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) {
        const newContainer = document.createElement('div');
        newContainer.id = 'toast-container';
        newContainer.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(newContainer);
    }
    const toastContainer = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check' : type === 'info' ? 'fa-info-circle' : 'fa-times';
    toast.innerHTML = `
        <div class="toast-icon"><i class="fas ${icon}"></i></div>
        <div><strong>${type === 'success' ? 'सफल' : type === 'info' ? 'सूचना' : 'त्रुटि'}</strong><br><small>${message}</small></div>
    `;
    toast.style.cssText = `
        display: flex; align-items: center; gap: 12px; padding: 15px; margin-bottom: 10px; 
        border-radius: 8px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
        background: ${type === 'success' ? 'var(--maroon, #7B1A1A)' : type === 'info' ? 'var(--saffron, #FF6B00)' : '#c62828'}; 
        transform: translateX(120%); transition: transform 0.3s ease;
        min-width: 280px; max-width: 400px;
    `;
    toastContainer.appendChild(toast);
    
    setTimeout(() => toast.style.transform = 'translateX(0)', 10);
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// =========================================
// 📦 LOAD BRANCHES FOR DROPDOWN
// =========================================
async function loadBranchesForAnnouncement() {
    const branchSelect = document.getElementById('ann-branch');
    if (!branchSelect) return;

    try {
        const response = await fetch('api/get_branches.php?all=true');
        const result = await response.json();
        allBranches = result.data || [];

        branchSelect.innerHTML = '<option value="ALL">सभी समाज (ALL Central)</option>';
        allBranches.forEach(b => {
            if (b.branchName) {
                const opt = document.createElement('option');
                opt.value = b.branchName;
                opt.textContent = b.branchName;
                branchSelect.appendChild(opt);
            }
        });

        // If logged-in admin is restricted to a branch, pre-select & lock the dropdown
        if (currentAdminAuth && !currentAdminAuth.isSuperAdmin && currentAdminAuth.branch) {
            branchSelect.value = currentAdminAuth.branch;
            branchSelect.disabled = true;
            branchSelect.style.backgroundColor = '#f0f0f0';
            branchSelect.title = `आप केवल ${currentAdminAuth.branch} समाज की घोषणाएं जोड़ सकते हैं।`;
        }
    } catch (error) {
        console.error('❌ Failed to load branches:', error);
    }
}

// =========================================
// ➕ ADD ANNOUNCEMENT HANDLER
// =========================================
async function handleAddAnnouncement(e) {
    e.preventDefault();
    
    const title = document.getElementById('ann-title')?.value.trim();
    const description = document.getElementById('ann-desc')?.value.trim();
    const date = document.getElementById('ann-date')?.value;
    const link = document.getElementById('ann-link')?.value.trim();
    const branchSelect = document.getElementById('ann-branch');
    const branch = branchSelect ? branchSelect.value : 'ALL';
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    if (!title || !description) {
        showToast('⚠️ कृपया शीर्षक और विवरण दोनों भरें।', 'error');
        return;
    }
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सेव हो रहा है...';
    }
    
    try {
        const announcementData = {
            title: title,
            description: description,
            date: date || null,
            link: link || null,
            branch: branch || 'ALL'
        };
        
        const response = await fetch('api/add_announcement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(announcementData)
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'Failed to add announcement');
        }
        
        showToast('✅ घोषणा सफलतापूर्वक जोड़ी गई!', 'success');
        
        // Reset form inputs except locked branch
        document.getElementById('ann-title').value = '';
        document.getElementById('ann-desc').value = '';
        document.getElementById('ann-link').value = '';
        
        // Reload announcements list
        loadAnnouncements();
        
    } catch (error) {
        console.error("Error adding announcement:", error);
        showToast('❌ घोषणा जोड़ने में त्रुटि: ' + error.message, 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> घोषणा सेव करें';
        }
    }
}

// =========================================
// 📋 LOAD ANNOUNCEMENTS LIST
// =========================================
async function loadAnnouncements() {
    const loadingDiv = document.getElementById('announcements-loading');
    const listDiv = document.getElementById('announcements-list');
    const emptyDiv = document.getElementById('announcements-empty');
    
    if (!listDiv) return;
    
    if (loadingDiv) loadingDiv.style.display = 'block';
    listDiv.style.display = 'none';
    if (emptyDiv) emptyDiv.style.display = 'none';
    
    try {
        const response = await fetch('api/get_announcements.php?all=true&admin_view=true', {
            credentials: 'include'
        });
        
        if (response.status === 401 || response.status === 403) {
            window.location.href = 'admin-login.php';
            return;
        }
        
        const result = await response.json();
        const announcements = result.data || [];
        
        if (loadingDiv) loadingDiv.style.display = 'none';
        
        if (announcements.length === 0) {
            if (emptyDiv) emptyDiv.style.display = 'block';
            return;
        }
        
        listDiv.innerHTML = '';
        listDiv.style.display = 'block';
        
        const isSuperAdmin = currentAdminAuth ? currentAdminAuth.isSuperAdmin : true;
        const userBranch = currentAdminAuth ? currentAdminAuth.branch : 'ALL';
        
        announcements.forEach((announcement) => {
            const announcementEl = document.createElement('div');
            announcementEl.className = 'announcement-item';
            
            const dateStr = announcement.date ? new Date(announcement.date).toLocaleDateString('hi-IN') : 
                           (announcement.createdAt ? new Date(announcement.createdAt).toLocaleDateString('hi-IN') : 'तिथि उपलब्ध नहीं');
            
            const annBranch = announcement.branch || 'ALL';
            const canDelete = isSuperAdmin || (annBranch === userBranch);

            const deleteButtonHtml = canDelete ? `
                <button class="delete-announcement-btn" data-id="${announcement.id}" title="हटाएं">
                    <i class="fas fa-trash"></i>
                </button>
            ` : '';
            
            announcementEl.innerHTML = `
                <div class="announcement-info">
                    <h4>
                        ${announcement.title}
                        <span style="display:inline-block; margin-left:10px; padding:3px 10px; border-radius:12px; font-size:0.75rem; background:var(--maroon, #7B1A1A); color:white; font-weight:normal;">
                            <i class="fas fa-sitemap"></i> ${annBranch === 'ALL' ? 'सभी समाज (Central)' : annBranch}
                        </span>
                    </h4>
                    <p>${announcement.description}</p>
                    <small>
                        <i class="fas fa-calendar"></i> ${dateStr}
                        ${announcement.link ? `<a href="${announcement.link}" target="_blank"><i class="fas fa-link"></i> लिंक देखें</a>` : ''}
                    </small>
                </div>
                ${deleteButtonHtml}
            `;
            listDiv.appendChild(announcementEl);
        });
        
        // Add delete event listeners
        document.querySelectorAll('.delete-announcement-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const announcementId = e.currentTarget.getAttribute('data-id');
                await deleteAnnouncement(announcementId);
            });
        });
        
    } catch (error) {
        console.error("Error loading announcements:", error);
        if (loadingDiv) loadingDiv.style.display = 'none';
        listDiv.innerHTML = '<p class="error-message" style="text-align:center;padding:20px;">❌ घोषणाएं लोड करने में त्रुटि।</p>';
        listDiv.style.display = 'block';
    }
}

// =========================================
// 🗑️ DELETE ANNOUNCEMENT
// =========================================
async function deleteAnnouncement(announcementId) {
    if (!confirm('⚠️ क्या आप वाकई इस घोषणा को हटाना चाहते हैं?')) return;
    
    const btn = document.querySelector(`.delete-announcement-btn[data-id="${announcementId}"]`);
    if (!btn) return;
    
    const originalHTML = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        const response = await fetch('api/delete_announcement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id: announcementId })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'Delete failed');
        }
        
        const item = btn.closest('.announcement-item');
        item.style.transition = 'all 0.3s';
        item.style.opacity = '0';
        item.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            item.remove();
            showToast('✅ घोषणा सफलतापूर्वक हटाई गई!', 'success');
            
            const listDiv = document.getElementById('announcements-list');
            if (listDiv && listDiv.querySelectorAll('.announcement-item').length === 0) {
                listDiv.style.display = 'none';
                document.getElementById('announcements-empty').style.display = 'block';
            }
        }, 300);
        
    } catch (error) {
        console.error("Error deleting announcement:", error);
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        showToast('❌ घोषणा हटाने में त्रुटि: ' + error.message, 'error');
    }
}

// =========================================
// 🚀 INITIALIZE PAGE
// =========================================
async function initAnnouncementPage() {
    const form = document.getElementById('announcement-form');
    if (form) {
        form.addEventListener('submit', handleAddAnnouncement);
    }
    
    const dateInput = document.getElementById('ann-date');
    if (dateInput) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        dateInput.value = `${year}-${month}-${day}`;
    }
    
    await loadBranchesForAnnouncement();
    await loadAnnouncements();

    if (typeof window.initHindiTransliteration === 'function') {
        window.initHindiTransliteration('[data-hindi-input="true"]', document.querySelector('.form-section'));
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const isAuth = await checkPageAuth();
    if (isAuth) {
        initAnnouncementPage();
    }
});