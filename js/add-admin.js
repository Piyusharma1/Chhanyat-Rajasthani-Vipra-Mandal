// js/add-admin.js

let deleteTargetId = null;

// 🔐 Auth State Check (Session enforcement)
async function checkAuthState() {
    const pageContent = document.getElementById('admin-page-content');

    try {
        const response = await fetch('api/check_auth.php');
        const result = await response.json();

        if (result.isAdmin) {
            // Only Super Admins are allowed to manage admins
            if (!result.isSuperAdmin) {
                alert('⚠️ केवल सुपर एडमिन ही एडमिन प्रबंधित कर सकते हैं।');
                window.location.href = 'admin-dashboard.php';
                return;
            }

            if (pageContent) pageContent.style.display = 'block';
            await loadBranchesDropdown();
            loadAdmins();
        } else {
            window.location.href = 'admin-login.php';
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'admin-login.php';
    }
}

// 📦 Load Branches into Dropdown from Database
async function loadBranchesDropdown() {
    const select = document.getElementById('admin_branch');
    if (!select) return;

    try {
        const response = await fetch('api/get_branches.php?all=true');
        const result = await response.json();
        const branches = result.data || result || [];

        // Reset with default Super Admin option
        select.innerHTML = '<option value="ALL" selected>🌟 सभी शाखाएं (Super Admin / ALL)</option>';

        if (Array.isArray(branches)) {
            branches.forEach(b => {
                const bName = b.branchName || b;
                if (bName) {
                    const opt = document.createElement('option');
                    opt.value = bName;
                    opt.textContent = `📍 ${bName}`;
                    select.appendChild(opt);
                }
            });
        }
    } catch (error) {
        console.error('Failed to load branches for admin dropdown:', error);
    }
}

// 📋 Load Admins List from API
async function loadAdmins() {
    const tbody = document.getElementById('admins-tbody');
    const loadingState = document.getElementById('admins-loading');
    const tableWrap = document.getElementById('admins-table-wrap');

    try {
        const response = await fetch('api/get_admins.php');
        const result = await response.json();

        if (result.success && Array.isArray(result.admins)) {
            tbody.innerHTML = '';

            if (result.admins.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: var(--text-soft);">कोई एडमिन नहीं मिला।</td></tr>';
            } else {
                result.admins.forEach(admin => {
                    const tr = document.createElement('tr');

                    // Status Badge
                    const isAct = admin.is_active === 1;
                    const statusBadge = `<span class="badge-status ${isAct ? 'badge-active' : 'badge-inactive'}">${isAct ? 'सक्रिय' : 'निष्क्रिय'}</span>`;
                    const youBadge = admin.is_current ? '<span class="badge-status badge-you">(आप)</span>' : '';
                    
                    // Branch Display
                    const branchDisplay = (!admin.branch || admin.branch === 'ALL')
                        ? '<span class="badge-status badge-you" style="background: #e1f5fe; color: #0288d1; border-color: #b3e5fc;">सभी (Super Admin)</span>'
                        : `<strong>📍 ${escapeHtml(admin.branch)}</strong>`;

                    // Formatted Last Login
                    let lastLoginStr = 'कभी नहीं';
                    if (admin.last_login) {
                        const d = new Date(admin.last_login);
                        lastLoginStr = d.toLocaleDateString('hi-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('hi-IN', { hour: '2-digit', minute: '2-digit' });
                    }

                    // Action Buttons (Disabled for current session user)
                    const disableActions = admin.is_current ? 'disabled title="आप अपने स्वयं के अकाउंट पर कार्रवाई नहीं कर सकते"' : '';

                    tr.innerHTML = `
                        <td data-label="नाम"><strong>${escapeHtml(admin.full_name)}</strong></td>
                        <td data-label="यूज़रनेम"><code>${escapeHtml(admin.username)}</code></td>
                        <td data-label="शाखा">${branchDisplay}</td>
                        <td data-label="स्टेटस">${statusBadge} ${youBadge}</td>
                        <td data-label="अंतिम लॉगिन"><small style="color: var(--text-soft);">${lastLoginStr}</small></td>
                        <td data-label="कार्रवाई">
                            <button class="btn-action-icon btn-change-pwd" data-id="${admin.id}" data-name="${escapeHtml(admin.full_name)}" data-username="${escapeHtml(admin.username)}" title="पासवर्ड बदलें (Change Password)">
                                <i class="fas fa-key" style="color: var(--saffron);"></i>
                            </button>
                            <button class="btn-action-icon btn-toggle-status" data-id="${admin.id}" data-active="${admin.is_active}" ${disableActions} title="${isAct ? 'निष्क्रिय करें (Deactivate)' : 'सक्रिय करें (Activate)'}">
                                <i class="fas ${isAct ? 'fa-toggle-on' : 'fa-toggle-off'}" style="color: ${isAct ? '#2e7d32' : '#c62828'}; font-size: 1.1rem;"></i>
                            </button>
                            <button class="btn-action-icon btn-delete" data-id="${admin.id}" data-name="${escapeHtml(admin.full_name)}" ${disableActions} title="हटाएं (Delete)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            if (loadingState) loadingState.style.display = 'none';
            if (tableWrap) tableWrap.style.display = 'block';

            attachActionListeners();
        } else {
            showToast(result.error || 'एडमिन सूची लोड नहीं हो सकी', true);
        }
    } catch (error) {
        console.error('Failed to load admins:', error);
        showToast('सर्वर से कनेक्ट नहीं हो सका', true);
    }
}

// 🎯 Attach Toggle and Delete Event Listeners
function attachActionListeners() {
    // Toggle Status
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        if (btn.disabled) return;
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const currentActive = parseInt(btn.getAttribute('data-active'));
            const newActive = currentActive === 1 ? 0 : 1;

            btn.disabled = true;
            try {
                const response = await fetch('api/toggle_admin_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(id), is_active: newActive })
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message);
                    loadAdmins();
                } else {
                    showToast(result.error, true);
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Toggle status failed:', error);
                showToast('सर्वर त्रुटि', true);
                btn.disabled = false;
            }
        });
    });

    // Delete Admin
    document.querySelectorAll('.btn-delete').forEach(btn => {
        if (btn.disabled) return;
        btn.addEventListener('click', () => {
            deleteTargetId = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');

            const msgEl = document.getElementById('confirm-dialog-msg');
            if (msgEl) msgEl.textContent = `क्या आप वाकई एडमिन '${name}' को हटाना चाहते हैं?`;

            openConfirmModal();
        });
    });

    // Change Password
    document.querySelectorAll('.btn-change-pwd').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const username = btn.getAttribute('data-username');

            const targetInput = document.getElementById('pwd-target-id');
            const targetLabel = document.getElementById('pwd-dialog-target');

            if (targetInput) targetInput.value = id;
            if (targetLabel) targetLabel.innerHTML = `<strong>${name}</strong> (<code>${username}</code>) का नया पासवर्ड दर्ज करें:`;

            document.getElementById('pwd_new_password').value = '';
            document.getElementById('pwd_confirm_password').value = '';

            openPwdModal();
        });
    });
}

// ➕ Add Admin Form Handler
document.getElementById('add-admin-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fullName = document.getElementById('full_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const isActive = parseInt(document.getElementById('is_active').value);
    const branch = document.getElementById('admin_branch')?.value || 'ALL';
    const submitBtn = document.getElementById('submit-admin-btn');

    if (!fullName || !username || !password) {
        showToast('❌ सभी अनिवार्य फ़ील्ड भरें!', true);
        return;
    }

    if (password !== confirmPassword) {
        showToast('❌ दर्ज किए गए दोनों पासवर्ड मेल नहीं खाते!', true);
        return;
    }

    if (password.length < 6) {
        showToast('❌ पासवर्ड कम से कम 6 अक्षरों का होना चाहिए!', true);
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सेव हो रहा है...';

    try {
        const response = await fetch('api/add_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                full_name: fullName,
                username: username,
                password: password,
                is_active: isActive,
                branch: branch
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message);
            document.getElementById('add-admin-form').reset();
            loadAdmins();
        } else {
            showToast(result.error || 'एडमिन जोड़ने में समस्या आई', true);
        }
    } catch (error) {
        console.error('Add admin error:', error);
        showToast('सर्वर त्रुटि। कृपया पुनः प्रयास करें।', true);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-user-check"></i> एडमिन सेव करें';
    }
});

// 👁️ Password Visibility Toggle
document.getElementById('toggle-password')?.addEventListener('click', () => {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('password-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// ⚠️ Confirm Delete Modal Actions
function openConfirmModal() {
    document.getElementById('confirm-overlay')?.classList.add('active');
    document.getElementById('confirm-dialog')?.classList.add('active');
}

function closeConfirmModal() {
    document.getElementById('confirm-overlay')?.classList.remove('active');
    document.getElementById('confirm-dialog')?.classList.remove('active');
    deleteTargetId = null;
}

document.getElementById('cancel-delete-btn')?.addEventListener('click', closeConfirmModal);
document.getElementById('confirm-overlay')?.addEventListener('click', closeConfirmModal);

document.getElementById('confirm-delete-btn')?.addEventListener('click', async () => {
    if (!deleteTargetId) return;

    const btn = document.getElementById('confirm-delete-btn');
    btn.disabled = true;

    try {
        const response = await fetch('api/delete_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(deleteTargetId) })
        });

        const result = await response.json();
        closeConfirmModal();

        if (result.success) {
            showToast(result.message);
            loadAdmins();
        } else {
            showToast(result.error, true);
        }
    } catch (error) {
        console.error('Delete admin error:', error);
        showToast('सर्वर त्रुटि', true);
        closeConfirmModal();
    } finally {
        btn.disabled = false;
    }
});

// 🔑 Change Password Modal Actions
function openPwdModal() {
    document.getElementById('pwd-overlay')?.classList.add('active');
    document.getElementById('pwd-dialog')?.classList.add('active');
}

function closePwdModal() {
    document.getElementById('pwd-overlay')?.classList.remove('active');
    document.getElementById('pwd-dialog')?.classList.remove('active');
}

document.getElementById('cancel-pwd-btn')?.addEventListener('click', closePwdModal);
document.getElementById('pwd-overlay')?.addEventListener('click', closePwdModal);

document.getElementById('toggle-pwd-new')?.addEventListener('click', () => {
    const input = document.getElementById('pwd_new_password');
    const icon = document.getElementById('pwd-new-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

document.getElementById('change-pwd-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const targetId = document.getElementById('pwd-target-id')?.value;
    const newPassword = document.getElementById('pwd_new_password')?.value;
    const confirmPassword = document.getElementById('pwd_confirm_password')?.value;
    const submitBtn = document.getElementById('confirm-pwd-btn');

    if (!targetId || !newPassword) {
        showToast('❌ कृपया नया पासवर्ड दर्ज करें!', true);
        return;
    }

    if (newPassword !== confirmPassword) {
        showToast('❌ दर्ज किए गए दोनों पासवर्ड मेल नहीं खाते!', true);
        return;
    }

    if (newPassword.length < 6) {
        showToast('❌ पासवर्ड कम से कम 6 अक्षरों का होना चाहिए!', true);
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> अपडेट हो रहा है...';

    try {
        const response = await fetch('api/change_admin_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: parseInt(targetId),
                new_password: newPassword
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message);
            closePwdModal();
        } else {
            showToast(result.error || 'पासवर्ड बदलने में समस्या आई', true);
        }
    } catch (error) {
        console.error('Change password error:', error);
        showToast('सर्वर त्रुटि। कृपया पुनः प्रयास करें।', true);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'पासवर्ड अपडेट करें';
    }
});

// 🔔 Toast Notification Helper
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    const toastIcon = document.getElementById('toast-icon');

    if (!toast || !toastMsg) return;

    toastMsg.textContent = message;
    toast.className = 'toast show' + (isError ? ' error' : '');

    if (toastIcon) {
        toastIcon.className = 'fas ' + (isError ? 'fa-exclamation-circle' : 'fa-check-circle');
        toastIcon.style.color = isError ? '#d32f2f' : '#2e7d32';
    }

    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
}

// 🛡️ Helper: Escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// 🚀 DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 js/add-admin.js loaded');
    checkAuthState();
});
