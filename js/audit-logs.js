// js/audit-logs.js
// Client logic for fetching, filtering, and displaying audit logs

let currentPage = 1;
const limit = 25;
let currentTotalPages = 1;

// 🔐 Check Auth
async function checkAuth() {
    try {
        const response = await fetch('api/check_auth.php?_t=' + Date.now(), { cache: 'no-store' });
        const result = await response.json();

        const guard = document.getElementById('auth-guard');
        const content = document.getElementById('admin-page-content');

        if (result.isAdmin && result.isSuperAdmin) {
            if (guard) guard.style.display = 'none';
            if (content) content.style.display = 'block';
            loadLogs();
        } else {
            // Not logged in or not Super Admin
            alert('❌ यह पृष्ठ केवल सुपर एडमिन के लिए उपलब्ध है।');
            window.location.href = 'admin-dashboard.php';
        }
    } catch (e) {
        console.error('Auth failed:', e);
        window.location.href = 'admin-login.php';
    }
}

// 📋 Load Logs
async function loadLogs() {
    const tbody = document.getElementById('logs-tbody');
    const pageInfo = document.getElementById('pagination-info');
    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');

    const search = document.getElementById('filter-search')?.value.trim() || '';
    const category = document.getElementById('filter-category')?.value || '';
    const action = document.getElementById('filter-action')?.value || '';

    tbody.innerHTML = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 35px; color: #777;">
                <i class="fas fa-spinner fa-spin"></i> लॉग लोड हो रहे हैं...
            </td>
        </tr>
    `;

    try {
        const params = new URLSearchParams({
            page: currentPage,
            limit: limit,
            search: search,
            category: category,
            action: action,
            _t: Date.now()
        });

        const res = await fetch(`api/get_audit_logs.php?${params.toString()}`);
        const result = await res.json();

        if (!result.success) {
            throw new Error(result.error || 'Failed to load logs');
        }

        const logs = result.data || [];
        const total = result.total || 0;
        currentTotalPages = result.totalPages || 1;

        if (logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #888;">
                        <i class="fas fa-info-circle"></i> कोई गतिविधि लॉग नहीं मिला।
                    </td>
                </tr>
            `;
            pageInfo.textContent = '0 में से 0 रिकॉर्ड';
            prevBtn.disabled = true;
            nextBtn.disabled = true;
            return;
        }

        const startIdx = (currentPage - 1) * limit + 1;
        const endIdx = Math.min(currentPage * limit, total);
        pageInfo.textContent = `${total} में से ${startIdx}-${endIdx} रिकॉर्ड (पृष्ठ ${currentPage} / ${currentTotalPages})`;

        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= currentTotalPages;

        tbody.innerHTML = logs.map(log => {
            const actionClass = getActionBadgeClass(log.action);
            const timeFormatted = formatDateTime(log.created_at);
            const targetDisplay = log.target_name ? `${escapeHtml(log.target_name)} ${log.target_id ? `<small style="color:#888;">(${escapeHtml(log.target_id)})</small>` : ''}` : (log.target_id ? escapeHtml(log.target_id) : '—');
            
            let detailsPretty = '—';
            if (log.details) {
                try {
                    const parsed = JSON.parse(log.details);
                    detailsPretty = escapeHtml(JSON.stringify(parsed));
                } catch(e) {
                    detailsPretty = escapeHtml(log.details);
                }
            }

            return `
                <tr>
                    <td data-label="समय" class="log-time">
                        <i class="far fa-clock"></i> ${timeFormatted}
                    </td>
                    <td data-label="एडमिन">
                        <span class="log-admin-user">${escapeHtml(log.admin_username)}</span>
                        ${log.admin_name && log.admin_name !== log.admin_username ? `<br><small style="color:#777;">${escapeHtml(log.admin_name)}</small>` : ''}
                    </td>
                    <td data-label="कार्य">
                        <span class="badge-action ${actionClass}">${escapeHtml(log.action)}</span>
                    </td>
                    <td data-label="श्रेणी">
                        <span class="cat-tag">${escapeHtml(log.category)}</span>
                    </td>
                    <td data-label="लक्षित रिकॉर्ड" class="log-target">
                        ${targetDisplay}
                    </td>
                    <td data-label="विवरण" class="log-details" title="${detailsPretty}">
                        ${detailsPretty}
                    </td>
                    <td data-label="IP पता" style="font-family: monospace; font-size: 0.8rem; color: #666;">
                        ${escapeHtml(log.ip_address || '—')}
                    </td>
                </tr>
            `;
        }).join('');

    } catch (err) {
        console.error('Audit load error:', err);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px; color: red;">
                    <i class="fas fa-exclamation-circle"></i> त्रुटि: ${escapeHtml(err.message)}
                </td>
            </tr>
        `;
    }
}

function getActionBadgeClass(action) {
    const act = String(action || '').toUpperCase();
    if (act.includes('CREATE') || act.includes('ADD')) return 'badge-create';
    if (act.includes('UPDATE') || act.includes('EDIT')) return 'badge-update';
    if (act.includes('DELETE') || act.includes('REMOVE')) return 'badge-delete';
    if (act.includes('LOGIN')) return 'badge-login';
    if (act.includes('LOGOUT')) return 'badge-logout';
    if (act.includes('UPLOAD')) return 'badge-upload';
    return 'badge-update';
}

function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    try {
        const d = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleString('hi-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch(e) {
        return dateStr;
    }
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();

    let debounceTimer;
    document.getElementById('filter-search')?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentPage = 1;
            loadLogs();
        }, 350);
    });

    document.getElementById('filter-category')?.addEventListener('change', () => {
        currentPage = 1;
        loadLogs();
    });

    document.getElementById('filter-action')?.addEventListener('change', () => {
        currentPage = 1;
        loadLogs();
    });

    document.getElementById('btn-refresh')?.addEventListener('click', () => {
        loadLogs();
    });

    document.getElementById('btn-prev')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadLogs();
        }
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (currentPage < currentTotalPages) {
            currentPage++;
            loadLogs();
        }
    });
});
