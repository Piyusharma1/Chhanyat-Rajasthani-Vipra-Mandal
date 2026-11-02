import { loadFromFirestore } from "./main.js";

let allFamilies = [];
let filteredFamilies = [];
let currentPage = 1;
const itemsPerPage = 6;

// 🌐 HELPER: Get Hindi value if available, else English
// Priority: hindi.fieldName → fieldName → fallback
function getDisplayValue(family, fieldName, fallback = '—') {
    // 1️⃣ First priority: Hindi translation
    if (family.hindi && family.hindi[fieldName]) {
        return family.hindi[fieldName];
    }
    // 2️⃣ Second priority: English original
    if (family[fieldName]) {
        return family[fieldName];
    }
    // 3️⃣ Fallback
    return fallback;
}

// 🔍 HELPER: Search in BOTH Hindi and English fields
function matchesSearch(family, fieldName, searchTerm) {
    if (!searchTerm) return true;
    const term = searchTerm.toLowerCase();
    
    const hindiVal = (family.hindi && family.hindi[fieldName]) 
        ? family.hindi[fieldName].toLowerCase() 
        : '';
    const engVal = (family[fieldName] || '').toLowerCase();
    
    return engVal.includes(term) || hindiVal.includes(term);
}

async function initDirectory() {
    // ✅ Load from Firestore (with fallback to JSON)
    try {
        allFamilies = await loadFromFirestore('families');
        if (!allFamilies || allFamilies.length === 0) {
            allFamilies = await loadData('families.json');
        }
    } catch (err) {
        console.warn('Firestore failed, falling back to JSON:', err);
        allFamilies = await loadData('families.json');
    }
    
    if (!allFamilies || allFamilies.length === 0) {
        document.getElementById('family-grid').innerHTML = 
            '<p style="grid-column:1/-1;text-align:center;">⚠️ कोई डेटा उपलब्ध नहीं है।</p>';
        return;
    }
    
    // ✅ Populate branch dropdown (use Hindi name if available)
    const branchSelect = document.getElementById('filter-branch');
    const branches = [...new Set(allFamilies.map(f => getDisplayValue(f, 'branch', 'अज्ञात')))];
    branches.sort((a, b) => a.localeCompare(b, 'hi'));
    
    branchSelect.innerHTML = '<option value="">सभी परिवारएं</option>' + 
        branches.map(b => `<option value="${b}">${b}</option>`).join('');

    // ✅ Handle URL params (for deep linking)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('branch')) {
        const urlBranch = urlParams.get('branch');
        // Try to match with Hindi branch name
        const matched = allFamilies.find(f => 
            getDisplayValue(f, 'branch') === urlBranch || f.branch === urlBranch
        );
        if (matched) branchSelect.value = getDisplayValue(matched, 'branch');
    }

    // ✅ Bind filter events
    document.querySelectorAll('.filter-input').forEach(input => 
        input.addEventListener('input', () => { currentPage = 1; applyFilters(); })
    );
    document.getElementById('filter-branch').addEventListener('change', () => {
        currentPage = 1;
        applyFilters();
    });
    
    applyFilters();
}

function applyFilters() {
    const head = document.getElementById('filter-head').value.trim();
    const branch = document.getElementById('filter-branch').value;
    const native = document.getElementById('filter-native').value.trim();

    filteredFamilies = allFamilies.filter(f => {

        const headMatch = matchesSearch(f, 'head', head);

        // Search village + nativePlace
        const nativeMatch =
            matchesSearch(f, 'nativePlace', native) ||
            matchesSearch(f, 'village', native);

        const displayBranch = getDisplayValue(f, 'branch');
        const branchMatch = !branch || displayBranch === branch;

        return headMatch && nativeMatch && branchMatch;
    });

    renderFamilies(filteredFamilies);
    renderPagination(filteredFamilies.length);
}

function renderFamilies(families) {
    const grid = document.getElementById('family-grid');
    const pageItems = families.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
    
    if (pageItems.length === 0) { 
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center;">🔍 कोई परिवार नहीं मिला।</p>'; 
        return; 
    }
    
    // ✅ Display Hindi values with English fallback
    grid.innerHTML = pageItems.map(f => {
        // Get display values (Hindi priority)
        const head = getDisplayValue(f, 'head');
        const branch = getDisplayValue(f, 'branch');
        const gotra = getDisplayValue(f, 'gotra');
        const address = getDisplayValue(f, 'address');
        const village = getDisplayValue(f, 'village');
        
        // ✅ Show language indicator if Hindi is available
        const hasHindi = f.hindi && Object.keys(f.hindi).length > 0;
        const langBadge = hasHindi 
            ? '<span class="lang-badge" title="हिंदी में उपलब्ध">🇮🇳</span>' 
            : '';
        
        return `
        <div class="family-card">
            <h3>${head} ${langBadge}</h3>
            <p><strong>परिवार:</strong> ${branch}</p>
            <p><strong>गोत्र:</strong> ${gotra}</p>
            <p><strong>पता:</strong> ${[village, address].filter(Boolean).join(', ') || '—'}</p>
            <a href="family.php?id=${f.id}" class="btn">विस्तृत जानकारी →</a>
        </div>
    `;
    }).join('');
}

function renderPagination(totalItems) {
    const pagination = document.getElementById('pagination');
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} 
             onclick="changePage(${currentPage - 1})">◀ पिछला</button>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" 
                 onclick="changePage(${i})">${i}</button>`;
    }
    
    // Next button
    html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} 
             onclick="changePage(${currentPage + 1})">अगला ▶</button>`;
    
    pagination.innerHTML = html;
}

function changePage(page) { 
    currentPage = page; 
    applyFilters(); 
    window.scrollTo({ top: 0, behavior: 'smooth' }); 
}

// Make changePage globally accessible for onclick handlers
window.changePage = changePage;

// Initialize when DOM is ready
if (document.getElementById('family-grid')) {
    initDirectory();
}