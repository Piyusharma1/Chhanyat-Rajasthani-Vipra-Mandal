// ✅ Import API loaders from home.js
import { loadFromAPI, loadAllFromAPI } from "./home.js";

// =========================================
// 📦 CONSTANTS & CONFIGURATION
// =========================================
const CONFIG = {
    ITEMS_PER_PAGE: 6,
    DEBOUNCE_DELAY: 300,
    MAX_VISIBLE_PAGES: 5,
    MAX_BRANCHES: 500,
    WHATSAPP_COUNTRY_CODE: '91'
};

const IMAGE_CONFIG = {
    DEFAULT_PHOTO: 'images/user.webp',
    DEFAULT_IDEAL_PHOTO: 'https://chhanyatvipralatur.com/idel/Gayatri%20mata.webp',
    DEFAULT_AVATAR: 'images/user.webp',
    ROOT_MARGIN: '300px 0px'
};

// =========================================
// 🔒 STATE MANAGEMENT
// =========================================
let branchFamilies = [];
let branchData = null;
let currentPage = 1;
let familyMap = new Map();

// 🧠 In-memory cache to avoid re-validating same URLs
const imageCache = new Map();

// =========================================
// 🔒 SECURITY: HTML ESCAPING
// =========================================
function escapeHTML(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// =========================================
// 🖼️ OPTIMIZED IMAGE LOADING SYSTEM
// =========================================
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            loadImageOptimized(img);
            observer.unobserve(img);
        }
    });
}, {
    rootMargin: IMAGE_CONFIG.ROOT_MARGIN,
    threshold: 0.01
});

/**
 * 🚀 Fast image loader — no double downloads
 */
function loadImageOptimized(img) {
    const url = img.dataset.src;
    const fallback = img.dataset.fallback || IMAGE_CONFIG.DEFAULT_PHOTO;

    if (!url) {
        setFinalImage(img, fallback);
        return;
    }

    // ✅ Check cache first — instant if we've seen this URL before
    if (imageCache.has(url)) {
        const cached = imageCache.get(url);
        setFinalImage(img, cached.valid ? url : cached.finalUrl);
        return;
    }

    // ✅ Show blurred placeholder immediately
    showBlurPlaceholder(img);

    // ✅ Load directly on the real <img> element — ONE download only
    img.onload = () => {
        imageCache.set(url, { valid: true, finalUrl: url });
        revealImage(img);
    };

    img.onerror = () => {
        imageCache.set(url, { valid: false, finalUrl: fallback });
        setFinalImage(img, fallback, true);
    };

    img.src = url;
}

/**
 * Handle fallback chains
 */
function setFinalImage(img, fallbackUrl, isRetry = false) {
    if (isRetry) {
        img.onload = () => revealImage(img);
        img.onerror = () => {
            img.onerror = null;
            img.src = IMAGE_CONFIG.DEFAULT_AVATAR;
            img.onload = () => revealImage(img);
        };
        img.src = fallbackUrl;
    } else {
        img.src = fallbackUrl;
        revealImage(img);
    }
}

/**
 * Show blurred placeholder for smooth perceived loading
 */
function showBlurPlaceholder(img) {
    img.style.filter = 'blur(10px)';
    img.style.transform = 'scale(1.05)';
    img.style.display = 'block';
    img.style.opacity = '0.6';
    img.src = img.dataset.placeholder || generateDataPlaceholder();
}

/**
 * Generate tiny colored data URL placeholder (instant, no network)
 */
function generateDataPlaceholder() {
    return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3Crect fill="%23f0e6d9" width="1" height="1"/%3E%3C/svg%3E';
}

/**
 * Reveal image with smooth animation
 */
function revealImage(img) {
    img.style.transition = 'filter 0.3s, opacity 0.3s, transform 0.3s';
    img.style.filter = 'blur(0)';
    img.style.opacity = '1';
    img.style.transform = 'scale(1)';

    img.classList.remove('img-loading');
    img.classList.add('img-loaded');

    const skeleton = img.previousElementSibling;
    if (skeleton && skeleton.classList.contains('image-skeleton')) {
        skeleton.style.opacity = '0';
        setTimeout(() => skeleton.style.display = 'none', 300);
    }
}

/**
 * 🎯 Observe images with priority handling
 */
function observeImages(selector, options = {}) {
    const { eager = false, priority = 'auto' } = options;

    requestAnimationFrame(() => {
        document.querySelectorAll(selector).forEach((img, index) => {
            if (eager || index < 3) {
                img.setAttribute('fetchpriority', priority === 'high' ? 'high' : 'auto');
                loadImageOptimized(img);
            } else {
                imageObserver.observe(img);
            }
        });
    });
}

// =========================================
// 🚀 INITIALIZE BRANCH PAGE
// =========================================
async function initBranchPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const branchName = urlParams.get('branch');

    if (!branchName) {
        window.location.href = '/';
        return;
    }

    showLoadingState();

    try {
        const [branchesResult, familiesResult] = await Promise.allSettled([
            loadFromAPI('branches', { limitCount: CONFIG.MAX_BRANCHES }),
            loadAllFromAPI('families')
        ]);

        if (branchesResult.status === 'rejected') {
            console.error('❌ Branches load failed:', branchesResult.reason);
            showErrorState('शाखा डेटा लोड नहीं हो सका।');
            return;
        }

        const branches = branchesResult.value.data;
        branchData = branches.find(b => b.branchName === branchName);

        if (!branchData) {
            showErrorState('शाखा नहीं मिली');
            return;
        }

        if (familiesResult.status === 'rejected') {
            console.error('❌ Families load failed:', familiesResult.reason);
            branchFamilies = [];
        } else {
            branchFamilies = familiesResult.value.filter(f => f.branch === branchData.branchName);
            familyMap = new Map(branchFamilies.map(f => [f.id, f]));
        }

        const displayBranchName = branchData.branchName || branchName;
        document.getElementById('branch-title').textContent = displayBranchName;
        document.getElementById('breadcrumb-branch').textContent = displayBranchName;
        document.title = `${displayBranchName} | छःन्यात राजस्थानी विप्र मंडल`;

        renderIdealSection(branchData);
        renderCommittee(branchData.committee);
        setupFamilyFilters();
        applyFamilyFilters();
        setupPaginationListener();

    } catch (error) {
        console.error('❌ Branch page load error:', error);
        showErrorState('डेटा लोड करने में त्रुटि। कृपया पुनः प्रयास करें।');
    }
}

// =========================================
// ⏳ LOADING & ERROR STATES
// =========================================
function showLoadingState() {
    const idealContainer = document.getElementById('ideal-section-container');
    const committeeGrid = document.getElementById('committee-grid');
    const familyGrid = document.getElementById('family-grid');

    if (idealContainer) {
        idealContainer.innerHTML = `
            <section class="loading-section">
                <div class="ideal-card skeleton-pulse">
                    <div class="image-skeleton ideal-photo-skeleton"></div>
                    <div class="text-skeleton" style="width: 60%; height: 24px;"></div>
                    <div class="text-skeleton" style="width: 80%; height: 16px;"></div>
                </div>
            </section>
        `;
    }

    if (committeeGrid) {
        committeeGrid.innerHTML = Array(3).fill(createSkeletonCard('committee')).join('');
    }

    if (familyGrid) {
        familyGrid.innerHTML = Array(2).fill(createSkeletonCard('family')).join('');
    }
}

function createSkeletonCard(type) {
    const skeletons = {
        committee: `
            <div class="committee-card skeleton-pulse">
                <div class="image-skeleton circle"></div>
                <div class="text-skeleton" style="width: 60%;"></div>
                <div class="text-skeleton short" style="width: 40%;"></div>
            </div>
        `,
        family: `
            <div class="family-card skeleton-pulse">
                <div class="image-skeleton circle"></div>
                <div class="text-skeleton" style="width: 60%;"></div>
                <div class="text-skeleton short" style="width: 40%;"></div>
            </div>
        `
    };
    return skeletons[type] || '';
}

function showErrorState(message) {
    const branchTitle = document.getElementById('branch-title');
    if (branchTitle) {
        branchTitle.textContent = '❌ त्रुटि';
    }

    const idealContainer = document.getElementById('ideal-section-container');
    if (idealContainer) {
        idealContainer.innerHTML = `
            <div class="error-msg" role="alert">
                <h3>⚠️ ${escapeHTML(message)}</h3>
                <p><a href="/" class="error-link">← होम पेज पर वापस जाएं</a></p>
            </div>
        `;
    }
}

// =========================================
// 🙏 RENDER IDEAL SECTION (with preload)
// =========================================
function renderIdealSection(branch) {
    const container = document.getElementById('ideal-section-container');
    if (!container) return;

    const photoUrl = branch.idealPhoto || IMAGE_CONFIG.DEFAULT_IDEAL_PHOTO;
    const idealName = escapeHTML(branch.idealName || 'माँ गायत्री');
    const idealQuote = escapeHTML(branch.idealQuote || '"ॐ भूर्भुवः स्वः तत्सवितुर्वरेण्यं भर्गो देवस्य धीमहि धियो यो नः प्रचोदयात्"');

    // ✅ PRELOAD the ideal photo — browser starts downloading IMMEDIATELY
    const preloadLink = document.createElement('link');
    preloadLink.rel = 'preload';
    preloadLink.as = 'image';
    preloadLink.href = photoUrl;
    preloadLink.setAttribute('fetchpriority', 'high');
    document.head.appendChild(preloadLink);

    container.innerHTML = `
        <section class="ideal-section">
            <div class="ideal-card">
                <img id="ideal-photo"
                     src="${escapeHTML(photoUrl)}"
                     alt="${idealName}" 
                     fetchpriority="high"
                     loading="eager"
                     decoding="async"
                     class="ideal-photo"
                     onload="this.classList.add('loaded')"
                     onerror="this.onerror=null; this.src='${IMAGE_CONFIG.DEFAULT_IDEAL_PHOTO}'; this.classList.add('loaded');">
                
                <h3 class="ideal-name">${idealName}</h3>
                <p class="ideal-quote">${idealQuote}</p>
            </div>
        </section>
    `;
}

// =========================================
// 👥 RENDER COMMITTEE
// =========================================
function renderCommittee(committee) {
    const grid = document.getElementById('committee-grid');
    if (!grid) return;

    if (!committee || committee.length === 0) {
        grid.innerHTML = '<p class="empty-message">इस परिवार के कार्यकारिणी का डेटा जल्द ही अपडेट किया जाएगा।</p>';
        return;
    }

    const getPhotoBasename = (url) => {
        if (!url) return '';
        const clean = String(url).split('?')[0].split('#')[0];
        const parts = clean.split('/');
        return decodeURIComponent(parts[parts.length - 1] || '').trim().toLowerCase();
    };

    grid.innerHTML = committee.map((m, index) => {
        const name = escapeHTML(m.name || 'अज्ञात');
        const designation = escapeHTML(m.designation || '');
        const photoUrl = m.photo || IMAGE_CONFIG.DEFAULT_PHOTO;
        const mobile = m.mobile ? m.mobile.replace(/\D/g, '') : '';

        // Resolve matching family ID
        let matchedFamilyId = m.family_id ? String(m.family_id) : null;
        if (!matchedFamilyId && m.photo && Array.isArray(branchFamilies)) {
            const base = getPhotoBasename(m.photo);
            if (base) {
                const match = branchFamilies.find(f => f.photo && getPhotoBasename(f.photo) === base);
                if (match) matchedFamilyId = match.id;
            }
        }
        if (!matchedFamilyId && m.name && Array.isArray(branchFamilies)) {
            const norm = String(m.name).trim().toLowerCase();
            const match = branchFamilies.find(f => f.head && String(f.head).trim().toLowerCase() === norm);
            if (match) matchedFamilyId = match.id;
        }

        const cardClickAttr = matchedFamilyId
            ? `onclick="window.location.href='family-details.php?id=${encodeURIComponent(matchedFamilyId)}'" role="link" tabindex="0" title="परिवार विवरण देखें" style="cursor: pointer;" class="committee-card committee-card-clickable"`
            : `class="committee-card"`;

        const viewFamilyBadge = matchedFamilyId
            ? `<div class="committee-family-link" style="margin-top: 8px; font-size: 0.76rem; color: var(--maroon, #7B1A1A); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; background: rgba(201, 148, 26, 0.15); padding: 3px 8px; border-radius: 10px;">
                    <i class="fas fa-home" style="color: var(--saffron, #FF6B00);"></i> परिवार विवरण देखें →
               </div>`
            : '';

        return `
        <div ${cardClickAttr}>
            <img class="committee-photo"
                 src="${escapeHTML(photoUrl)}"
                 alt="${name}"
                 loading="${index < 4 ? 'eager' : 'lazy'}"
                 decoding="async"
                 ${index < 4 ? 'fetchpriority="high"' : ''}
                 onload="this.classList.add('loaded')"
                 onerror="this.onerror=null; this.src='${IMAGE_CONFIG.DEFAULT_PHOTO}'; this.classList.add('loaded');">
            
            <h3 class="member-name">${name}</h3>
            ${designation ? `<p class="designation">${designation}</p>` : ''}
            ${viewFamilyBadge}
            ${mobile ? `${mobile ? `
<a href="https://wa.me/${CONFIG.WHATSAPP_COUNTRY_CODE}${mobile}?text=${encodeURIComponent('नमस्कार, मैं छःन्यात राजस्थानी विप्र मंडल वेबसाइट के माध्यम से संपर्क कर रहा हूँ।')}"
   target="_blank"
   rel="noopener noreferrer"
   class="whatsapp-btn"
   onclick="event.stopPropagation();">

   <svg xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        fill="currentColor"
        viewBox="0 0 16 16"
        style="margin-right: 6px; vertical-align: middle;">
      <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
   </svg>

   WhatsApp
</a>` : ''}` : ''}
        </div>
    `;
    }).join('');
}

// =========================================
// 🔍 FAMILY FILTERS
// =========================================
function setupFamilyFilters() {
    const filterHead = document.getElementById('filter-head');
    const filterSurname = document.getElementById('filter-surname');
    const filterGotra = document.getElementById('filter-gotra');

    let searchTimeout;

    const handleFilter = () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            applyFamilyFilters();
        }, CONFIG.DEBOUNCE_DELAY);
    };

    if (filterHead) filterHead.addEventListener('input', handleFilter);
    if (filterSurname) filterSurname.addEventListener('input', handleFilter);
    if (filterGotra) filterGotra.addEventListener('input', handleFilter);
}

function applyFamilyFilters() {
    const head = document.getElementById('filter-head')?.value.trim().toLowerCase() || '';
    const surname = document.getElementById('filter-surname')?.value.trim().toLowerCase() || '';
    const gotra = document.getElementById('filter-gotra')?.value.trim().toLowerCase() || '';

    const filtered = branchFamilies.filter(f => {
        if (!head && !surname && !gotra) return true;

        const fHead = (f.head || '').toLowerCase();
        const fSurname = (f.surname || '').toLowerCase();
        const fGotra = (f.gotra || '').toLowerCase();

        const matchHead = !head || fHead.includes(head);
        const matchSurname = !surname || fSurname.includes(surname);
        const matchGotra = !gotra || fGotra.includes(gotra);

        return matchHead && matchSurname && matchGotra;
    });

    renderFamilies(filtered);
    renderPagination(filtered.length);
}

function renderFamilies(families) {
    const grid = document.getElementById('family-grid');
    if (!grid) return;

    const pageItems = families.slice(
        (currentPage - 1) * CONFIG.ITEMS_PER_PAGE,
        currentPage * CONFIG.ITEMS_PER_PAGE
    );

    if (pageItems.length === 0) {
        grid.innerHTML = '<p class="empty-message">🔍 कोई परिवार नहीं मिला।</p>';
        return;
    }

    grid.innerHTML = pageItems.map(f => {
        const head = escapeHTML(f.head || 'अज्ञात');
        const branch = escapeHTML(f.branch || '—');
        const village = escapeHTML(f.village || '');
        const district = escapeHTML(f.district || '');
        const address = escapeHTML(f.address || '—');
        const mobile = f.mobile || '';
        const surname = escapeHTML(f.surname || '');
        const gotra = escapeHTML(f.gotra || '');
        const photoUrl = f.photo || IMAGE_CONFIG.DEFAULT_PHOTO;

        const location = [village, district].filter(Boolean).join(', ') || '—';

        return `
        <div class="family-card clickable-card" 
             data-family-id="${f.id}"
             role="button"
             tabindex="0"
             aria-label="View details for ${head}">
            
            <img class="family-photo"
                 src="${escapeHTML(photoUrl)}"
                 alt="${head}"
                 loading="lazy"
                 decoding="async"
                 onload="this.classList.add('loaded')"
                 onerror="this.onerror=null; this.src='${IMAGE_CONFIG.DEFAULT_PHOTO}'; this.classList.add('loaded');">
            
            <h3 class="family-name">${head}</h3>
            ${surname ? `<p><strong>उपनाम:</strong> ${surname}</p>` : ''}
            ${gotra ? `<p><strong>गोत्र:</strong> ${gotra}</p>` : ''}
            <p><strong>परिवार:</strong> ${branch}</p>
            <p><strong>गांव/जिला:</strong> ${location}</p>
            <p><strong>पता:</strong> ${address}</p>
            ${mobile ? `<p><strong>मोबाइल:</strong> <a href="tel:${mobile}" class="mobile-link" data-stop-propagation>${mobile}</a></p>` : ''}
            
            <div class="btn view-details-btn">
                विस्तृत जानकारी →
            </div>
        </div>
    `;
    }).join('');
    setupFamilyCardListeners();
}

function setupFamilyCardListeners() {
    document.querySelectorAll('.family-card.clickable-card').forEach(card => {
        const familyId = card.dataset.familyId;

        const navigate = (e) => {
            if (e.target.closest('[data-stop-propagation]')) return;
            window.location.href = `family-details.php?id=${familyId}`;
        };

        card.addEventListener('click', navigate);
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                navigate(e);
            }
        });
    });
}

// =========================================
// 📄 PAGINATION WITH EVENT DELEGATION
// =========================================
function renderPagination(totalItems) {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;

    const totalPages = Math.ceil(totalItems / CONFIG.ITEMS_PER_PAGE);

    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '';

    html += `<button class="page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled aria-disabled="true"' : ''} aria-label="Previous page">◀ पिछला</button>`;

    let startPage = Math.max(1, currentPage - Math.floor(CONFIG.MAX_VISIBLE_PAGES / 2));
    let endPage = Math.min(totalPages, startPage + CONFIG.MAX_VISIBLE_PAGES - 1);

    if (endPage - startPage < CONFIG.MAX_VISIBLE_PAGES - 1) {
        startPage = Math.max(1, endPage - CONFIG.MAX_VISIBLE_PAGES + 1);
    }

    if (startPage > 1) {
        html += `<button class="page-btn" data-page="1">1</button>`;
        if (startPage > 2) {
            html += `<span class="pagination-ellipsis">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentPage;
        html += `
            <button class="page-btn ${isActive ? 'active' : ''}" 
                data-page="${i}"
                ${isActive ? 'disabled aria-current="page"' : ''}>
                ${i}
            </button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="pagination-ellipsis">...</span>`;
        }
        html += `<button class="page-btn" data-page="${totalPages}">${totalPages}</button>`;
    }

    html += `<button class="page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled aria-disabled="true"' : ''} aria-label="Next page">अगला ▶</button>`;

    pagination.innerHTML = html;
}

// ✅ COMPLETED FUNCTION - Was cut off in your code
function setupPaginationListener() {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;

    // ✅ Use event delegation - one listener handles all pagination buttons
    pagination.addEventListener('click', (e) => {
        const btn = e.target.closest('.page-btn');
        if (!btn || btn.disabled || btn.hasAttribute('disabled')) return;

        const page = parseInt(btn.dataset.page);
        if (isNaN(page) || page < 1 || page === currentPage) return;

        currentPage = page;
        applyFamilyFilters();

        // ✅ Smooth scroll to family section when changing pages
        const familySection = document.querySelector('.family-section') || document.getElementById('family-grid');
        if (familySection) {
            const offset = 100; // Account for sticky header
            const elementPosition = familySection.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    });
}

// =========================================
// 🚀 AUTO-INITIALIZE ON DOM READY
// =========================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBranchPage);
} else {
    // DOM already loaded
    initBranchPage();
}