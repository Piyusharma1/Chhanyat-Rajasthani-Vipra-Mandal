// ============================================
// ✅ PERFORMANCE MONITORING
// ============================================
const performanceMarks = new Map();
function markStart(label) { performanceMarks.set(label, performance.now()); }
function markEnd(label) {
    const start = performanceMarks.get(label);
    if (start) {
        const duration = performance.now() - start;
        console.log(`⚡ ${label}: ${duration.toFixed(2)}ms`);
        return duration;
    }
}

// ============================================
// ✅ API LOADERS
// ============================================
export async function loadFromAPI(endpoint, options = {}) {
    markStart(`load_${endpoint}`);
    const { page = 1, limitCount = 100, adminView = false } = options;

    try {
        const params = new URLSearchParams({ page, limit: limitCount });
        if (adminView) params.append('admin_view', 'true');
        // 🎯 Added credentials: 'include' to ensure PHP sessions/cookies are sent
        const response = await fetch(`api/get_${endpoint}.php?${params.toString()}`, {
            credentials: 'include'
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const result = await response.json();
        markEnd(`load_${endpoint}`);

        return {
            data: result.data || [],
            currentPage: result.currentPage || page,
            hasMore: result.hasMore || false,
            total: result.total || 0
        };
    } catch (error) {
        console.error(`❌ API Error (${endpoint}):`, error);
        markEnd(`load_${endpoint}`);
        return { data: [], currentPage: page, hasMore: false, total: 0 };
    }
}

export async function loadAllFromAPI(endpoint, options = {}) {
    markStart(`loadAll_${endpoint}`);
    try {
        const adminParam = (options && options.adminView) ? '&admin_view=true' : '';
        // 🎯 Added credentials: 'include'
        const response = await fetch(`api/get_${endpoint}.php?all=true${adminParam}`, {
            credentials: 'include'
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const result = await response.json();
        const docs = result.data || [];

        markEnd(`loadAll_${endpoint}`);
        return docs;
    } catch (error) {
        console.error(`❌ Error loading all ${endpoint}:`, error);
        markEnd(`loadAll_${endpoint}`);
        return [];
    }
}

export async function getCount(tableName) {
    try {
        // 🎯 Added credentials: 'include'
        const response = await fetch(`api/get_count.php?table=${encodeURIComponent(tableName)}`, {
            credentials: 'include'
        });
        const result = await response.json();
        return result.count || 0;
    } catch (error) {
        console.error(`❌ Error counting ${tableName}:`, error);
        return 0;
    }
}

// ============================================
// ✅ HOME PAGE STATE MANAGEMENT
// ============================================
let branchesState = { currentPage: 1, hasMore: false, allData: [] };
let announcementsState = { currentPage: 1, hasMore: false, allData: [] };
const PAGE_SIZE = 20;

async function getFamilies() {
    try {
        return await loadAllFromAPI('families');
    } catch (err) {
        console.error('Failed to load families:', err);
        return [];
    }
}

// ============================================
// ✅ HOME PAGE RENDERING FUNCTIONS
// ============================================
function updateStatistics(branches, families, members) {
    const branchCountEl = document.getElementById('branch-count');
    const totalBranchesEl = document.getElementById('total-branches');
    const totalFamiliesEl = document.getElementById('total-families');
    const totalMembersEl = document.getElementById('total-members');

    if (branchCountEl) branchCountEl.textContent = branches;
    if (totalBranchesEl) totalBranchesEl.textContent = branches.toLocaleString('hi-IN');
    if (totalFamiliesEl) totalFamiliesEl.textContent = families.toLocaleString('hi-IN');
    if (totalMembersEl) totalMembersEl.textContent = members.toLocaleString('hi-IN');
}

// 🎯 IMPROVED: Render Branches Marquee with Scrollable Functionality
function renderBranchesMarquee(branches) {
    const marqueeTrack = document.getElementById('branches-marquee-track');
    const marqueeContainer = document.querySelector('.branches-marquee');

    if (!marqueeTrack || !marqueeContainer || branches.length === 0) return;

    const itemsHtml = branches.map(branch => {
        const encodedName = encodeURIComponent(branch.branchName);
        return `
                <a href="branch.php?branch=${encodedName}" 
                class="marquee-item" 
                title="${branch.branchName} परिवार देखें"
                data-branch="${branch.branchName}">

                    <span class="branch-name">${branch.branchName}</span>
                </a>
                <span class="marquee-separator" aria-hidden="true">✦</span>
            `;
    }).join('');

    marqueeTrack.innerHTML = itemsHtml + itemsHtml;

    setupMarqueeScrolling(marqueeContainer, marqueeTrack);
    setupMarqueeClickHandlers(marqueeContainer);
}

function setupMarqueeScrolling(container, track) {
    if (!container || !track) return;

    let isDown = false;
    let startX;
    let scrollLeft;
    let animationId = null;
    let isPaused = false;
    let lastManualScroll = 0;
    const SCROLL_SPEED = 1;
    const PAUSE_DURATION = 3000;
    let pauseTimeout = null;

    // 🎯 FIX 1: Cache geometric properties to prevent forced reflows in the 60fps loop!
    let trackScrollWidth = track.scrollWidth;
    let containerClientWidth = container.clientWidth;
    let halfWidth = trackScrollWidth / 2;

    // Update cache ONLY when the window is resized
    window.addEventListener('resize', () => {
        trackScrollWidth = track.scrollWidth;
        containerClientWidth = container.clientWidth;
        halfWidth = trackScrollWidth / 2;
    });

    function animate() {
        if (!isPaused && !isDown) {
            container.scrollLeft += SCROLL_SPEED;

            if (container.scrollLeft >= halfWidth) {
                container.scrollLeft = 0;
            }
        }

        updateFadeIndicators();
        animationId = requestAnimationFrame(animate);
    }

    function startAnimation() {
        if (!animationId) animationId = requestAnimationFrame(animate);
    }

    function stopAnimation() {
        if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
        }
    }

    function pauseAnimation(duration = PAUSE_DURATION) {
        isPaused = true;
        if (pauseTimeout) clearTimeout(pauseTimeout);
        pauseTimeout = setTimeout(() => { isPaused = false; }, duration);
    }

    function updateFadeIndicators() {
        // 🎯 FIX 2: Use the cached values instead of querying the DOM!
        const currentScroll = container.scrollLeft;
        const maxScroll = trackScrollWidth - containerClientWidth;

        container.classList.toggle('at-start', currentScroll < 10);
        container.classList.toggle('at-end', currentScroll > maxScroll - 10);
    }

    container.addEventListener('mousedown', (e) => {
        isDown = true;
        container.classList.add('active-drag');
        startX = e.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
        pauseAnimation();
    });

    container.addEventListener('mouseleave', () => {
        if (isDown) {
            isDown = false;
            container.classList.remove('active-drag');
        }
    });

    container.addEventListener('mouseup', () => {
        isDown = false;
        container.classList.remove('active-drag');
    });

    container.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 2;
        container.scrollLeft = scrollLeft - walk;
    });

    container.addEventListener('touchstart', () => { pauseAnimation(); }, { passive: true });
    container.addEventListener('touchend', () => { }, { passive: true });

    container.addEventListener('scroll', () => {
        const diff = Math.abs(container.scrollLeft - lastManualScroll);
        if (diff > SCROLL_SPEED + 2) pauseAnimation();
        lastManualScroll = container.scrollLeft;
        updateFadeIndicators();
    }, { passive: true });

    startAnimation();
    updateFadeIndicators();
    window.addEventListener('beforeunload', stopAnimation);
}

function setupMarqueeClickHandlers(marqueeContainer) {
    if (!marqueeContainer) return;

    const marqueeItems = marqueeContainer.querySelectorAll('.marquee-item');
    let isDragging = false;
    let startX = 0;

    marqueeItems.forEach(item => {
        item.addEventListener('mouseenter', () => { marqueeContainer.classList.add('paused'); });
        item.addEventListener('mouseleave', () => { if (!isDragging) marqueeContainer.classList.remove('paused'); });
        item.addEventListener('focus', () => { marqueeContainer.classList.add('paused'); });
        item.addEventListener('blur', () => { marqueeContainer.classList.remove('paused'); });

        item.addEventListener('mousedown', (e) => {
            startX = e.pageX;
            isDragging = false;
        });

        item.addEventListener('mousemove', (e) => {
            if (Math.abs(e.pageX - startX) > 5) isDragging = true;
        });

        item.addEventListener('click', function (e) {
            if (isDragging) { e.preventDefault(); return; }
            e.preventDefault();
            const clickedItem = this;
            const href = clickedItem.getAttribute('href');
            clickedItem.classList.add('clicked');
            setTimeout(() => { window.location.href = href; }, 300);
        });

        item.addEventListener('touchstart', function () { this.classList.add('clicked'); }, { passive: true });
        item.addEventListener('touchend', function () {
            setTimeout(() => { this.classList.remove('clicked'); }, 200);
        }, { passive: true });
    });
}

function renderAnnouncements(announcements, append = false) {
    const announcementsList = document.getElementById('announcements-list');
    if (!announcementsList) return;

    if (announcements.length === 0 && !append) {
        announcementsList.innerHTML = '<p style="color: var(--text-soft); padding: 2rem; text-align: center; font-family: \'Tiro Devanagari Hindi\', serif;">कोई नई घोषणा नहीं है।</p>';
        return;
    }

    const html = announcements.map(a => {
        const tagType = a.type || 'notice';
        const tagClass = `tag-${tagType}`;
        const tagLabel = tagType === 'event' ? 'कार्यक्रम' : tagType === 'update' ? 'अपडेट' : 'सूचना';

        const branchName = a.branch || 'ALL';
        const branchBadge = branchName === 'ALL'
            ? `<span class="ann-tag" style="background:var(--maroon, #7B1A1A); color:white;">केंद्रीय</span>`
            : `<span class="ann-tag" style="background:var(--saffron, #FF6B00); color:white;">📍 ${branchName}</span>`;

        const dateStr = a.date ? a.date : (a.createdAt ? new Date(a.createdAt).toLocaleDateString('hi-IN') : '');

        return `
                <div class="ann-card">
                    <div class="ann-meta" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
                        <span class="ann-tag ${tagClass}">${tagLabel}</span>
                        ${branchBadge}
                        ${dateStr ? `<span class="ann-date">📅 ${dateStr}</span>` : ''}
                    </div>
                    <h3 class="ann-title">${a.title || 'शीर्षक नहीं'}</h3>
                    <p class="ann-body">${a.description || 'विवरण नहीं'}</p>
                    <div class="ann-footer">
                        ${a.author ? `<span class="ann-author">— ${a.author}</span>` : '<span></span>'}
                        ${a.link ? `<a href="${a.link}" target="_blank" class="ann-link">और पढ़ें →</a>` : ''}
                    </div>
                </div>
            `;
    }).join('');

    if (append) {
        announcementsList.insertAdjacentHTML('beforeend', html);
    } else {
        announcementsList.innerHTML = html;
    }
}

function renderHomeCommittee(committee) {
    const grid = document.getElementById('home-committee-grid');
    if (!grid) return;

    if (!committee || committee.length === 0) {
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-soft); padding: 2rem; font-family: \'Tiro Devanagari Hindi\', serif;">समिति का डेटा जल्द ही अपडेट किया जाएगा।</p>';
        return;
    }

    const defaultPhoto = 'https://placehold.co/150x150/C9941A/FFFFFF?text=Photo';

    grid.innerHTML = committee.map(m => {
        const name = m.name || 'अज्ञात';
        const designation = m.designation || '';
        const photoUrl = m.photo || defaultPhoto;
        const branch = m.branch || '';

        return `
                <div class="committee-card">
                    <div class="avatar">
                        <img src="${photoUrl}" alt="${name}" width="300" height="300" loading="lazy" decoding="async" 
        style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
        onerror="this.parentElement.innerHTML='👤'">
                    </div>
                    <div class="member-name">${name}</div>
                    ${designation ? `<div class="member-role">${designation}</div>` : ''}
                    ${branch ? `<div class="member-branch">${branch}</div>` : ''}
                    ${m.mobile ? `<a href="https://wa.me/91${m.mobile.replace(/\D/g, '')}?text=${encodeURIComponent('नमस्कार, मैं छःन्यात राजस्थानी विप्र मंडल वेबसाइट के माध्यम से संपर्क कर रहा हूँ।')}"
    target="_blank"
    class="whatsapp-btn"
    rel="noopener">        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
    </svg> Chat on whatsapp</a>` : ''}
                </div>
            `;
    }).join('');
}

function toggleLoadMoreButtons() {
    const announcementsBtn = document.getElementById('load-more-announcements');
    if (announcementsBtn) announcementsBtn.style.display = announcementsState.hasMore ? 'block' : 'none';
}

function showError(message) {
    const announcementsList = document.getElementById('announcements-list');
    if (announcementsList) {
        announcementsList.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--maroon); background: var(--white); border-radius: var(--radius); font-family: \'Tiro Devanagari Hindi\', serif;">घोषणाएं लोड नहीं हो सकीं।</p>';
    }
}

// ============================================
// ✅ HOME PAGE DATA LOADING
// ============================================
async function loadCriticalData() {
    try {
        const [allBranches, announcementsResult] = await Promise.all([
            loadAllFromAPI('branches'),
            loadFromAPI('announcements', { limitCount: 4 })
        ]);

        announcementsState = {
            currentPage: announcementsResult.currentPage,
            hasMore: announcementsResult.hasMore,
            allData: announcementsResult.data
        };

        renderBranchesMarquee(allBranches);
        renderAnnouncements(announcementsResult.data, false);
        toggleLoadMoreButtons();
        loadBackgroundData(allBranches.length);
    } catch (error) {
        console.error('❌ Critical data load error:', error);
        showError(error.message);
    }
}

// ============================================
// 📢 DYNAMIC HOME ADS LOADER (4 POSITIONS)
// 1. above_calendar (सनातन दिनदर्शिका के ऊपर)
// 2. above_announcements (नवीनतम घोषणाएँ के ऊपर)
// 3. above_committee (कार्यकारिणी सदस्य के ऊपर)
// 4. below_committee (कार्यकारिणी सदस्य के नीचे)
// ============================================
const AD_POSITIONS = [
    { key: 'above_calendar', sectionId: 'ads-pos-above-calendar', gridId: 'ads-grid-above-calendar' },
    { key: 'above_announcements', sectionId: 'ads-pos-above-announcements', gridId: 'ads-grid-above-announcements' },
    { key: 'above_committee', sectionId: 'ads-pos-above-committee', gridId: 'ads-grid-above-committee' },
    { key: 'below_committee', sectionId: 'ads-pos-below-committee', gridId: 'ads-grid-below-committee' }
];

async function loadHomeAds() {
    try {
        let response;
        try {
            response = await fetch('api/get_sponsors.php', { credentials: 'include' });
            if (!response.ok) throw new Error('Fallback to get_ads');
        } catch (_) {
            response = await fetch('api/get_ads.php', { credentials: 'include' });
        }
        if (!response.ok) return;
        const result = await response.json();
        const ads = (result && result.success && Array.isArray(result.data)) ? result.data : [];

        // Distribute ads by position (default to above_calendar if not set)
        const groupedAds = {
            above_calendar: [],
            above_announcements: [],
            above_committee: [],
            below_committee: []
        };

        ads.forEach(ad => {
            const pos = ad.position && groupedAds[ad.position] ? ad.position : 'above_calendar';
            groupedAds[pos].push(ad);
        });

        // Render each position
        AD_POSITIONS.forEach(({ key, sectionId, gridId }) => {
            const sectionEl = document.getElementById(sectionId);
            const gridEl = document.getElementById(gridId);
            const posAds = groupedAds[key] || [];

            if (!sectionEl || !gridEl) return;

            if (posAds.length === 0) {
                sectionEl.style.display = 'none';
                gridEl.innerHTML = '';
            } else {
                gridEl.innerHTML = renderAdsHtml(posAds);
                sectionEl.style.display = 'block';
            }
        });
    } catch (err) {
        console.warn('⚠️ Could not load home ads/sponsors:', err);
    }
}

function renderAdsHtml(ads) {
    return ads.map(ad => {
        const title = ad.title || '';
        const biz = ad.business_name || '';
        const img = ad.image_url || '';
        const targetUrl = ad.link_url || (ad.phone ? `tel:${ad.phone.replace(/\D/g, '')}` : '#');
        const isExternal = !!ad.link_url;
        const waLink = ad.phone ? `https://wa.me/91${ad.phone.replace(/\D/g, '')}?text=${encodeURIComponent('नमस्कार, मैं छःन्यात विप्र समाज वेबसाइट पर आपका विज्ञापन देखकर संपर्क कर रहा हूँ।')}` : null;

        return `
                <div class="sponsor-banner ad-banner" role="listitem">
                    <a href="${targetUrl}" ${isExternal ? 'target="_blank" rel="noopener"' : ''} class="sponsor-banner-media ad-banner-media" aria-label="${title}">
                        <img src="${img}" alt="${title}" loading="lazy" decoding="async" onerror="this.parentElement.style.display='none'">
                    </a>
                    <div class="sponsor-banner-footer ad-banner-footer">
                        <div class="sponsor-banner-title ad-banner-title" title="${title}">
                            ${title}
                            ${biz ? `<small style="display:block;font-size:0.8rem;color:#777;font-weight:normal;">${biz}</small>` : ''}
                        </div>
                        ${waLink ? `
                            <a href="${waLink}" target="_blank" rel="noopener" class="sponsor-banner-contact-btn ad-banner-contact-btn" title="WhatsApp पर संपर्क करें">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>
                                <span>संपर्क</span>
                            </a>
                        ` : (ad.phone ? `
                            <a href="tel:${ad.phone.replace(/\D/g, '')}" class="sponsor-banner-contact-btn ad-banner-contact-btn" style="background:var(--maroon);">
                                <span>📞 कॉल</span>
                            </a>
                        ` : '')}
                    </div>
                </div>
            `;
    }).join('');
}

// ============================================
// 📅 UPCOMING EVENTS / CALENDAR LOADER
// ============================================
const hindiMonths = ['', 'जानेवारी', 'फेब्रुवारी', 'मार्च', 'एप्रिल', 'मे', 'जून', 'जुलै', 'ऑगस्ट', 'सप्टेंबर', 'ऑक्टोबर', 'नोव्हेंबर', 'डिसेंबर'];

function loadUpcomingEvents() {
    setupHomeCalendarWidget();
}

function setupHomeCalendarWidget() {
    const img = document.getElementById('home-cal-preview-img');
    const title = document.getElementById('home-cal-month-title');
    const strip = document.getElementById('home-cal-months-strip');
    const link = img && img.closest('a');
    if (!img || !title || !strip) return;

    const cover = { src: img.getAttribute('src'), alt: img.alt, href: link ? link.getAttribute('href') : null };
    const baseTitle = title.textContent.trim() || '2026-27';

    // Preload + decode so the swap is instant; ignore stale hovers
    const cache = new Map();
    function preload(src) {
        if (!cache.has(src)) {
            const i = new Image();
            i.src = src;
            cache.set(src, i.decode().catch(() => { }));
        }
        return cache.get(src);
    }

    let token = 0;
    async function show(src, alt, text, href) {
        const t = ++token;
        await preload(src);
        if (t !== token) return;
        img.src = src;
        img.alt = alt;
        title.textContent = text;
        if (link && href) link.href = href;
    }

    function previewFrom(e) {
        const pill = e.target.closest('.home-month-pill');
        if (!pill) return;
        const m = Number(pill.dataset.month);
        if (!(m >= 1 && m <= 12)) return;
        const label = `सनातन दिनदर्शिका: ${hindiMonths[m]}`;
        show(`uploads/calendar/pages/${m}.webp`, label, label, `calendar.php?month=${m}`);
    }
    const restore = () => show(cover.src, cover.alt, baseTitle, cover.href);

    // Mouse only (touch taps just navigate); keyboard via focus
    strip.addEventListener('pointerover', e => { if (e.pointerType === 'mouse') previewFrom(e); });
    strip.addEventListener('pointerleave', e => { if (e.pointerType === 'mouse') restore(); });
    strip.addEventListener('focusin', previewFrom);
    strip.addEventListener('focusout', e => { if (!strip.contains(e.relatedTarget)) restore(); });
}

async function loadBackgroundData(branchCount) {
    try {
        const [familyData, committee] = await Promise.all([
            getFamilies(),
            loadAllFromAPI('committee'),
            loadHomeAds(),
            loadUpcomingEvents()
        ]);

        const familyCount = familyData.length;
        const memberCount = familyData.reduce((acc, f) => acc + (f.members?.length || 0), 0);

        updateStatistics(branchCount, familyCount, memberCount);
        renderHomeCommittee(committee);
    } catch (error) {
        console.error('❌ Background load error:', error);
    }
}

// ============================================
// ✅ LOAD MORE HANDLERS
// ============================================
function setupLoadMoreHandlers() {
    const loadMoreAnnouncementsBtn = document.getElementById('load-more-announcements');

    if (loadMoreAnnouncementsBtn) {
        loadMoreAnnouncementsBtn.addEventListener('click', async () => {
            const btn = loadMoreAnnouncementsBtn;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.innerHTML = '⏳ लोड हो रहा है...';

            try {
                announcementsState.currentPage += 1;
                const result = await loadFromAPI('announcements', {
                    page: announcementsState.currentPage,
                    limitCount: 4
                });

                announcementsState.hasMore = result.hasMore;
                announcementsState.allData = [...announcementsState.allData, ...result.data];
                renderAnnouncements(result.data, true);
                toggleLoadMoreButtons();
            } catch (error) {
                console.error('Error loading more announcements:', error);
                btn.textContent = '❌ पुनः प्रयास करें';
                setTimeout(() => { btn.textContent = originalText; }, 3000);
            } finally {
                btn.disabled = false;
                if (announcementsState.hasMore) btn.textContent = originalText;
            }
        });
    }
}

// ============================================
// ✅ UI INITIALIZATION
// ============================================
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;

    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        themeToggle.textContent = '☀️';
    }

    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        themeToggle.textContent = isDark ? '☀️' : '🌙';
    });
}

function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => navLinks.classList.toggle('active'));
    }
}

function initScrollToTop() {
    const scrollTopBtn = document.getElementById('scroll-top');
    if (!scrollTopBtn) return;

    let ticking = false;
    window.addEventListener('scroll', () => {
        // 🎯 FIX 3: Read pageYOffset OUTSIDE of requestAnimationFrame
        const yOffset = window.pageYOffset;

        if (!ticking) {
            window.requestAnimationFrame(() => {
                scrollTopBtn.classList.toggle('visible', yOffset > 300);
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });

    scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

export function initUI() {
    initThemeToggle();
    initMobileMenu();
    initScrollToTop();
}

// ============================================
// ✅ GALLERY VARIABLES & API LOADER
// ============================================
let galleryImages = [];
let currentImageIndex = 0;
let currentSlide = 0;
let isAutoPlaying = true;
let autoPlayInterval;
const SLIDE_DURATION = 5000;

async function loadGalleryImages() {
    const loadingEl = document.getElementById('slider-loading');
    const errorEl = document.getElementById('slider-error');
    const trackEl = document.getElementById('slider-track');

    if (!loadingEl || !errorEl || !trackEl) return;

    loadingEl.style.display = 'flex';
    errorEl.style.display = 'none';

    try {
        const response = await fetch('api/get_gallery.php?folder=gallerys', { credentials: 'include' });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();

        if (!result.success || !result.data || result.data.length === 0) {
            throw new Error(result.error || 'कोई छवि नहीं मिली');
        }

        galleryImages = result.data;

        // 🚀 PERFORMANCE FIX 1: Preload the FIRST image immediately!
        // This forces the browser to download it at maximum priority before we even build the DOM.
        if (galleryImages.length > 0) {
            const firstImgUrl = galleryImages[0].url;

            // Inject <link rel="preload"> into <head>
            const preloadLink = document.createElement('link');
            preloadLink.rel = 'preload';
            preloadLink.as = 'image';
            preloadLink.href = firstImgUrl;
            preloadLink.setAttribute('fetchpriority', 'high');
            document.head.appendChild(preloadLink);

            // Force browser to start downloading it right now in memory
            const preloader = new Image();
            preloader.src = firstImgUrl;
        }

        loadingEl.style.display = 'none';
        buildSlider(galleryImages);
    } catch (error) {
        console.error('❌ Error loading gallery:', error);
        loadingEl.style.display = 'none';
        errorEl.style.display = 'flex';
        const errorMsg = document.getElementById('error-message');
        if (errorMsg) errorMsg.textContent = error.message;
    }
}

let originalSlideCount = 0;
const NUM_CLONES = 2; // Number of clones on each side
let isTransitioning = false;

function buildSlider(images) {
    const trackEl = document.getElementById('slider-track');
    const totalSlideNum = document.getElementById('total-slide-num');

    if (!trackEl || images.length === 0) return;

    originalSlideCount = images.length;
    if (totalSlideNum) totalSlideNum.textContent = String(originalSlideCount).padStart(2, '0');

    const getSlideHTML = (img, index, isClone) => {
        const loadingAttr = (!isClone && index === 0) ? 'eager' : 'lazy';
        const priorityAttr = (!isClone && index === 0) ? 'fetchpriority="high"' : '';
        return `
            <div class="slide ${!isClone && index === 0 ? 'active' : ''}" 
                role="group" 
                aria-roledescription="slide" 
                aria-label="स्लाइड ${index + 1}" 
                onclick="openModal(${index})">
            <picture class="slide-picture">
                <source media="(max-width: 768px)" srcset="${img.mobileUrl || img.url}">
                <img src="${img.desktopUrl || img.url}" alt="${img.title}" loading="${loadingAttr}" ${priorityAttr} decoding="async">
            </picture>
            <div class="slide-gradient"></div>
            <div class="slide-caption">
                <h3>${img.title}</h3>
                ${img.description ? `<p>${img.description}</p>` : ''}
            </div>
            </div>`;
    };

    let slidesHTML = [];

    // Clones at start
    for (let i = 0; i < NUM_CLONES; i++) {
        const idx = (images.length - NUM_CLONES + i) % images.length;
        const safeIdx = (idx + images.length) % images.length;
        slidesHTML.push(getSlideHTML(images[safeIdx], safeIdx, true));
    }

    // Real slides
    for (let i = 0; i < images.length; i++) {
        slidesHTML.push(getSlideHTML(images[i], i, false));
    }

    // Clones at end
    for (let i = 0; i < NUM_CLONES; i++) {
        const idx = i % images.length;
        slidesHTML.push(getSlideHTML(images[idx], idx, true));
    }

    trackEl.innerHTML = slidesHTML.join('');
    initSlider();
}

function initSlider() {
    currentSlide = NUM_CLONES;
    updateSlider(false);
    if (isAutoPlaying) autoPlay();

    // Ensure slider stays centered on window resize
    window.addEventListener('resize', () => updateSlider(false));
}

function updateSlider(animate = true) {
    const track = document.getElementById('slider-track');
    const slides = document.querySelectorAll('.slide');
    const currentSlideNum = document.getElementById('current-slide-num');
    const wrapper = document.querySelector('.slider-wrapper');

    const totalSlides = slides.length;
    if (totalSlides === 0 || !wrapper) return;

    if (animate) {
        track.style.transition = 'transform 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
    } else {
        track.style.transition = 'none';
    }

    // Calculate offset for center mode
    const slide = slides[0];
    const wrapperWidth = wrapper.getBoundingClientRect().width;

    // Use offsetWidth for layout width
    const slideWidth = slide.offsetWidth;

    const style = window.getComputedStyle(slide);
    const marginLeft = parseFloat(style.marginLeft) || 0;
    const marginRight = parseFloat(style.marginRight) || 0;
    const totalSlideWidth = slideWidth + marginLeft + marginRight;

    // Calculate exact translation to center active slide
    const offset = (wrapperWidth / 2) - (slideWidth / 2) - marginLeft - (currentSlide * totalSlideWidth);

    track.style.transform = `translateX(${offset}px)`;
    slides.forEach((s, i) => s.classList.toggle('active', i === currentSlide));

    if (currentSlideNum) {
        const realIdx = ((currentSlide - NUM_CLONES) % originalSlideCount + originalSlideCount) % originalSlideCount;
        currentSlideNum.textContent = String(realIdx + 1).padStart(2, '0');
    }
}

function updateProgressAnimation() {
    const progressBar = document.getElementById('slider-progress');
    if (!progressBar) return;

    progressBar.style.transition = 'none';
    progressBar.style.width = '0%';
    void progressBar.offsetWidth;

    progressBar.style.transition = `width ${SLIDE_DURATION}ms linear`;
    progressBar.style.width = '100%';
}

function checkTeleport() {
    isTransitioning = true;

    setTimeout(() => {
        isTransitioning = false;
        let teleported = false;

        if (currentSlide >= originalSlideCount + NUM_CLONES) {
            currentSlide -= originalSlideCount;
            teleported = true;
        } else if (currentSlide < NUM_CLONES) {
            currentSlide += originalSlideCount;
            teleported = true;
        }

        if (teleported) {
            updateSlider(false); // jump without animation
        }
    }, 800); // Wait for CSS transition
}

function moveSlide(dir) {
    if (isTransitioning) return;
    const slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;

    currentSlide += dir;
    updateSlider(true);
    resetAutoPlay();
    checkTeleport();
}

function goToSlide(i) {
    if (isTransitioning) return;
    currentSlide = i + NUM_CLONES;
    updateSlider(true);
    resetAutoPlay();
}

function autoPlay() {
    clearInterval(autoPlayInterval);
    updateProgressAnimation();

    autoPlayInterval = setInterval(() => {
        if (isTransitioning) return;
        const slides = document.querySelectorAll('.slide');
        if (slides.length === 0) return;

        currentSlide += 1;
        updateSlider(true);
        updateProgressAnimation();
        checkTeleport();
    }, SLIDE_DURATION);
}

function resetAutoPlay() {
    clearInterval(autoPlayInterval);
    if (isAutoPlaying) autoPlay();
}

function initSliderEvents() {
    const sliderSection = document.querySelector('.hero-slider');
    if (!sliderSection) {
        console.warn('⚠️ .hero-slider not found');
        return;
    }

    sliderSection.addEventListener('mouseenter', () => {
        if (isAutoPlaying) {
            clearInterval(autoPlayInterval);
            const progressBar = document.getElementById('slider-progress');
            if (progressBar) {
                const currentWidth = window.getComputedStyle(progressBar).width;
                progressBar.style.transition = 'none';
                progressBar.style.width = currentWidth;
            }
        }
    });

    sliderSection.addEventListener('mouseleave', () => {
        if (isAutoPlaying) autoPlay();
    });

    let touchStartX = 0;
    sliderSection.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    sliderSection.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 50) moveSlide(diff > 0 ? 1 : -1);
    }, { passive: true });
}
// ============================================
// ✅ IMAGE MODAL / LIGHTBOX
// ============================================
function openModal(index = 0) {
    if (galleryImages.length === 0) return;
    currentImageIndex = index;
    const modal = document.getElementById('image-modal');
    if (!modal) return;

    const totalImagesEl = document.getElementById('total-images');
    if (totalImagesEl) totalImagesEl.textContent = galleryImages.length;

    loadImage(index);
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    createThumbnails(index);
}

function closeModal() {
    const modal = document.getElementById('image-modal');
    if (!modal) return;

    modal.setAttribute('aria-hidden', 'true');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function loadImage(index) {
    if (index < 0 || index >= galleryImages.length) return;
    const modalImage = document.getElementById('modal-image');
    const loader = document.querySelector('.modal-loader');
    if (!modalImage) return;

    modalImage.classList.remove('loaded');
    if (loader) loader.classList.remove('hidden');

    const img = new Image();
    img.src = galleryImages[index].url;
    img.onload = () => {
        modalImage.src = galleryImages[index].url;
        modalImage.alt = galleryImages[index].title;
        modalImage.classList.add('loaded');
        if (loader) loader.classList.add('hidden');

        const titleEl = document.getElementById('modal-title');
        const descEl = document.getElementById('modal-description');
        const currentEl = document.getElementById('current-image');

        if (titleEl) titleEl.textContent = galleryImages[index].title;
        if (descEl) descEl.textContent = galleryImages[index].description || '';
        if (currentEl) currentEl.textContent = index + 1;

        updateThumbnails(index);
    };
    img.onerror = () => {
        if (loader) loader.innerHTML = '<p style="color:white;text-align:center;">छवि लोड करने में त्रुटि</p>';
    };
}

function changeImage(dir) {
    currentImageIndex = (currentImageIndex + dir + galleryImages.length) % galleryImages.length;
    loadImage(currentImageIndex);
}

function createThumbnails(activeIndex) {
    const container = document.getElementById('modal-thumbnails');
    if (!container) return;

    container.innerHTML = galleryImages.map((img, i) =>
        `<img src="${img.url}" alt="${img.title}" class="modal-thumb ${i === activeIndex ? 'active' : ''}" onclick="openModal(${i})" loading="lazy">`
    ).join('');
}

function updateThumbnails(activeIndex) {
    document.querySelectorAll('.modal-thumb').forEach((t, i) => t.classList.toggle('active', i === activeIndex));
}

function initModalEvents() {
    let modalTouchX = 0;

    document.addEventListener('touchstart', (e) => {
        const modal = document.getElementById('image-modal');
        if (!modal || modal.getAttribute('aria-hidden') === 'true') return;
        modalTouchX = e.changedTouches[0].screenX;
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        const modal = document.getElementById('image-modal');
        if (!modal || modal.getAttribute('aria-hidden') === 'true') return;
        const diff = modalTouchX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 50) changeImage(diff > 0 ? 1 : -1);
    }, { passive: true });
}

function initKeyboardEvents() {
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('image-modal');
        const isModalOpen = modal && modal.getAttribute('aria-hidden') !== 'true';

        if (isModalOpen) {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
        } else {
            if (e.key === 'ArrowLeft') moveSlide(-1);
            if (e.key === 'ArrowRight') moveSlide(1);
        }
    });
}

// ============================================
// ✅ JSON LOADER
// ============================================
export async function loadData(filename) {
    try {
        const response = await fetch(`data/${filename}`, {
            cache: 'no-cache',
            headers: { 'Accept': 'application/json' },
            credentials: 'include'
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error(`❌ Error loading ${filename}:`, error);
        if (window.location.protocol === 'file:') {
            console.warn('⚠️ Running on file:// protocol - use Live Server');
        }
        return null;
    }
}

// ============================================
// ✅ PERFORMANCE REPORT
// ============================================
export function reportPerformance() {
    if ('performance' in window && 'getEntriesByType' in performance) {
        const navigation = performance.getEntriesByType('navigation')[0];
        const resources = performance.getEntriesByType('resource');

        console.group('📊 Performance Report');
        console.log(`DOM Ready: ${navigation.domContentLoadedEventEnd.toFixed(2)}ms`);
        console.log(`Page Load: ${navigation.loadEventEnd.toFixed(2)}ms`);
        console.log(`Total Resources: ${resources.length}`);
        console.log(`Total Transfer Size: ${(navigation.transferSize / 1024).toFixed(2)} KB`);
        console.groupEnd();
    }
}

// ============================================
// ✅ MAIN INITIALIZATION
// ============================================
export function initHomePage() {
    initUI();
    setupLoadMoreHandlers();
    initSliderEvents();
    initModalEvents();
    initKeyboardEvents();
    loadGalleryImages();
    loadCriticalData();
    setTimeout(reportPerformance, 3000);
}

// ============================================
// ✅ EXPOSE GLOBAL FUNCTIONS
// ============================================
window.openModal = openModal;
window.closeModal = closeModal;
window.changeImage = changeImage;
window.moveSlide = moveSlide;
window.goToSlide = goToSlide;
window.loadGalleryImages = loadGalleryImages;

// ============================================
// ✅ GLOBAL ERROR HANDLERS
// ============================================
window.addEventListener('error', (e) => console.error('Global error:', e.error));
window.addEventListener('unhandledrejection', (e) => console.error('Unhandled promise rejection:', e.reason));

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHomePage);
} else {
    initHomePage();
}


