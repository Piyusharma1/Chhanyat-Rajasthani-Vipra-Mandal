// js/admin-dashboard.js
// ✅ Firebase imports removed - Now using PHP API

// 🔐 Auth State Check (Enforces Login via PHP Session)
async function checkAuthState() {
    const authContainer = document.getElementById('auth-container') || document.getElementById('auth-screen');
    const adminContent = document.getElementById('admin-content') || document.getElementById('dash-content');
    
    const uidDisplay = document.getElementById('user-uid');
    const errDiv = document.getElementById('auth-error');
    
    try {
        const response = await fetch('api/check_auth.php?_t=' + Date.now(), { cache: 'no-store' });
        const result = await response.json();
        
        if (result.isAdmin) {
            if (authContainer) authContainer.classList.add('hidden');
            if (adminContent) adminContent.classList.remove('hidden');
            
            const branchText = result.isSuperAdmin ? ' (Super Admin)' : ` (शाखा: ${result.branch})`;
            if (uidDisplay) uidDisplay.textContent = (result.full_name || result.username || 'admin') + branchText;
            
            // Hide "Add Admin" card if user is not Super Admin
            const addAdminCard = document.querySelector('.admin-card[onclick*="add-admin.php"]');
            if (addAdminCard && !result.isSuperAdmin) {
                addAdminCard.style.display = 'none';
            }

            if (typeof initDashboardModals === 'function') initDashboardModals();
            loadDashboardStats();
        } else {
            window.location.href = 'admin-login.php';
            return; 
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'admin-login.php';
        return;
    }
}

// 📊 Load Dashboard Statistics
async function loadDashboardStats() {
    try {
        const [branchesRes, familiesRes] = await Promise.all([
            fetch('api/get_count.php?table=branches').then(r => r.json()),
            fetch('api/get_count.php?table=families').then(r => r.json())
        ]);
        
        const branchesCount = document.getElementById('total-branches-stat');
        const familiesCount = document.getElementById('total-families-stat');
        
        if (branchesCount) branchesCount.textContent = branchesRes.count || 0;
        if (familiesCount) familiesCount.textContent = familiesRes.count || 0;
    } catch (error) {
        console.error('Stats load error:', error);
    }
}

// 🎯 Dashboard Modal Handlers
function initDashboardModals() {
    // ✅ Committee Management - Navigate to dedicated page
    document.getElementById('manage-committee-card')?.addEventListener('click', () => {
        window.location.href = 'committee-management.php';
    });
    
    // ✅ Announcement Management - Single card navigates to dedicated page
    document.getElementById('announcement-management-card')?.addEventListener('click', () => {
        window.location.href = 'announcement-management.php';
    });
    
    // ✅ Gallery Upload - Navigate to dedicated page (if exists)
    document.getElementById('manage-gallery-card')?.addEventListener('click', () => {
        window.location.href = 'gallery-upload.php';
    });

    // Modal close handlers (keep for any remaining modals)
    document.getElementById('committee-modal-close')?.addEventListener('click', () => {
        document.getElementById('committee-modal')?.classList.remove('active');
    });
    document.getElementById('modal-close')?.addEventListener('click', () => {
        document.getElementById('announcement-modal')?.classList.remove('active');
    });
    document.getElementById('delete-modal-close')?.addEventListener('click', () => {
        document.getElementById('delete-modal')?.classList.remove('active');
    });

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });
}

// 🔐 Login Handler (PHP Session Based)
document.getElementById('login-btn')?.addEventListener('click', async () => {
    const username = document.getElementById('admin-email')?.value.trim() || document.getElementById('admin-username')?.value.trim();
    const password = document.getElementById('admin-password')?.value || document.getElementById('admin-pass')?.value; 
    const errorDiv = document.getElementById('auth-error');
    const loginBtn = document.getElementById('login-btn');
    
    if (!username || !password) {
        if (errorDiv) errorDiv.textContent = '⚠️ Username और पासवर्ड दोनों भरें।';
        return;
    }
    
    if (errorDiv) errorDiv.textContent = '';
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> लॉगिन हो रहा है...';
    
    try {
        const response = await fetch('api/check_auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const result = await response.json();
        
        if (result.isAdmin) {
            checkAuthState();
        } else {
            if (errorDiv) errorDiv.textContent = result.error || '❌ गलत username या password।';
        }
    } catch (error) {
        console.error("Login Error:", error);
        if (errorDiv) errorDiv.textContent = '❌ लॉगिन विफल। कृपया पुनः प्रयास करें।';
    } finally {
        loginBtn.disabled = false;
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> लॉगिन करें';
    }
});

// 🚪 Logout Handler
document.getElementById('logout-btn')?.addEventListener('click', async () => {
    if (!confirm('क्या आप वाकई logout करना चाहते हैं?')) return;
    
    try {
        await fetch('api/logout.php');
        window.location.href = 'admin-login.php';
    } catch (error) {
        console.error("Logout Error:", error);
        window.location.href = 'admin-login.php';
    }
});

// 🎯 Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 admin-dashboard.js loaded');
    checkAuthState();
});