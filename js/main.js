// 🔥 Firebase Imports
import { 
    collection, 
    getDocs, 
    query, 
    limit, 
    orderBy,
    startAfter,
    where,
    getCountFromServer
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

import { db } from "./firebase-config.js";

// ============================================
// ✅ LOCAL CACHE (localStorage with TTL)
// ============================================

const CACHE_VERSION = 'v1'; // bump this to invalidate all caches after a data schema change
const CACHE_TTL_MS = 15 * 60 * 1000; // 15 minutes

function cacheKey(name) {
    return `fsCache_${CACHE_VERSION}_${name}`;
}

function saveCache(name, data) {
    try {
        localStorage.setItem(cacheKey(name), JSON.stringify({
            ts: Date.now(),
            data
        }));
    } catch (e) {
        // localStorage may be full or unavailable — silently ignore
    }
}

function loadCache(name) {
    try {
        const raw = localStorage.getItem(cacheKey(name));
        if (!raw) return null;
        const { ts, data } = JSON.parse(raw);
        if (Date.now() - ts > CACHE_TTL_MS) return null; // expired
        return data;
    } catch (e) {
        return null;
    }
}

// ============================================
// ✅ PERFORMANCE MONITORING
// ============================================
const performanceMarks = new Map();

function markStart(label) {
    performanceMarks.set(label, performance.now());
}

function markEnd(label) {
    const start = performanceMarks.get(label);
    if (start) {
        const duration = performance.now() - start;
        console.log(`⚡ ${label}: ${duration.toFixed(2)}ms`);
        return duration;
    }
}

// ============================================
// ✅ FIRESTORE LOADERS
// ============================================

// 📦 Optimized Firestore Loader with Pagination
export async function loadFromFirestore(collectionName, options = {}) {
    markStart(`load_${collectionName}`);
    
    const { 
        limitCount = 100, 
        lastDoc = null, 
        orderByField = null,
        orderDirection = 'desc',
        filters = []
    } = options;
    
    try {
        let constraints = [limit(limitCount)];
        
        if (orderByField) {
            constraints.unshift(orderBy(orderByField, orderDirection));
        }
        
        if (lastDoc) {
            constraints.push(startAfter(lastDoc));
        }
        
        filters.forEach(filter => {
            constraints.unshift(where(filter.field, filter.operator, filter.value));
        });
        
        const q = query(collection(db, collectionName), ...constraints);
        const snapshot = await getDocs(q);
        
        const docs = snapshot.docs.map(doc => ({ 
            id: doc.id, 
            ...doc.data() 
        }));
        
        markEnd(`load_${collectionName}`);
        
        return {
            data: docs,
            lastDoc: snapshot.docs[snapshot.docs.length - 1] || null,
            hasMore: docs.length === limitCount,
            total: snapshot.size
        };
    } catch (error) {
        console.error(`❌ Error loading ${collectionName}:`, error);
        markEnd(`load_${collectionName}`);
        return { data: [], lastDoc: null, hasMore: false, total: 0 };
    }
}

// 📦 Load All Documents (with cache support)
export async function loadAllFromFirestore(collectionName, { useCache = false } = {}) {
    if (useCache) {
        const cached = loadCache(`all_${collectionName}`);
        if (cached) return cached;
    }

    markStart(`loadAll_${collectionName}`);
    
    try {
        const q = collection(db, collectionName);
        const snapshot = await getDocs(q);
        const docs = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
        
        markEnd(`loadAll_${collectionName}`);

        if (useCache) saveCache(`all_${collectionName}`, docs);

        return docs;
    } catch (error) {
        console.error(`❌ Error loading all ${collectionName}:`, error);
        markEnd(`loadAll_${collectionName}`);
        return [];
    }
}

// ✅ Fast count query
export async function getCount(collectionName, filters = []) {
    try {
        let constraints = [];
        filters.forEach(filter => {
            constraints.push(where(filter.field, filter.operator, filter.value));
        });
        
        const q = query(collection(db, collectionName), ...constraints);
        const snapshot = await getCountFromServer(q);
        return snapshot.data().count;
    } catch (error) {
        console.error(`❌ Error counting ${collectionName}:`, error);
        return 0;
    }
}

// ============================================
// ✅ HOME PAGE STATE MANAGEMENT
// ============================================

let branchesState = { lastDoc: null, hasMore: false, allData: [] };
let announcementsState = { lastDoc: null, hasMore: false, allData: [] };
let cachedFamilies = null;
let familiesLoadPromise = null;
const PAGE_SIZE = 20;

// Lazy load families — cached aggressively (15 min in localStorage + in-memory)
async function getFamilies() {
    if (cachedFamilies) return cachedFamilies;

    // Check localStorage first
    const lsCache = loadCache('all_families');
    if (lsCache) {
        cachedFamilies = lsCache;
        return cachedFamilies;
    }
    
    if (!familiesLoadPromise) {
        familiesLoadPromise = loadAllFromFirestore('families', { useCache: true })
            .then(families => {
                cachedFamilies = families;
                return families;
            })
            .catch(err => {
                console.error('Failed to load families:', err);
                return [];
            });
    }
    
    return familiesLoadPromise;
}

// ✅ Member count — derived from cached families (no extra Firestore read)
async function getMemberCount() {
    try {
        const families = await getFamilies();
        return families.reduce((acc, f) => acc + (f.members?.length || 0), 0);
    } catch (error) {
        return 0;
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

function renderBranchesQuick(branches) {
    const branchGrid = document.getElementById('branch-overview-grid');
    if (!branchGrid || branches.length === 0) return;

    const html = branches.map(branch => `
        <div class="branch-card" onclick="window.location.href='branch.php?branch=${encodeURIComponent(branch.branchName)}'">
            <h3>${branch.branchName}</h3>
            <div class="branch-stats">
                <span>समिति सदस्य: ${branch.committee?.length || 0}</span>
                <span style="color: var(--orange);">विवरण देखें →</span>
            </div>
        </div>
    `).join('');

    branchGrid.innerHTML = html;
}

function renderBranchesWithStats(branches, families) {
    const branchGrid = document.getElementById('branch-overview-grid');
    if (!branchGrid) return;

    const branchStats = new Map();
    branches.forEach(b => branchStats.set(b.branchName, { families: 0, members: 0 }));
    
    families.forEach(f => {
        const stats = branchStats.get(f.branch);
        if (stats) {
            stats.families++;
            stats.members += f.members?.length || 0;
        }
    });

    branches.sort((a, b) => {
        return (branchStats.get(b.branchName)?.families || 0) - (branchStats.get(a.branchName)?.families || 0);
    });

    const html = branches.map(branch => {
        const stats = branchStats.get(branch.branchName) || { families: 0, members: 0 };
        return `
            <div class="branch-card" onclick="window.location.href='branch.php?branch=${encodeURIComponent(branch.branchName)}'">
                <h3>${branch.branchName}</h3>
                <div class="branch-stats">
                    <span>पंजीकृत परिवार: ${stats.families}</span>
                    <span>कुल सदस्य: ${stats.members}</span>
                    <span>समिति सदस्य: ${branch.committee?.length || 0}</span>
                </div>
            </div>
        `;
    }).join('');

    branchGrid.innerHTML = html;
}

function renderAnnouncements(announcements, append = false) {
    const announcementsList = document.getElementById('announcements-list');
    if (!announcementsList) return;

    if (announcements.length === 0 && !append) {
        announcementsList.innerHTML = '<p style="color:#666;padding:1rem;text-align:center;">कोई नई घोषणा नहीं है।</p>';
        return;
    }

    const html = announcements.map(a => {
        const branchName = a.branch || 'ALL';
        const branchBadge = branchName === 'ALL'
            ? `<span style="background:var(--maroon, #7B1A1A); color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem; display:inline-block; margin-left:8px;">सामूहिक</span>`
            : `<span style="background:var(--orange, #FF6B00); color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem; display:inline-block; margin-left:8px;"> ${branchName}</span>`;

        return `
            <div style="background: var(--bg-card); padding: 20px; margin-bottom: 15px; border-radius: 8px; box-shadow: var(--shadow); border-left: 4px solid var(--orange);">
                ${a.date ? `<small style="color: var(--orange); font-weight: bold;">${a.date}</small>` : ''}
                ${branchBadge}
                <h3 style="margin: 5px 0; color: var(--red);">${a.title || 'शीर्षक नहीं'}</h3>
                <p>${a.description || 'विवरण नहीं'}</p>
                ${a.link ? `<a href="${a.link}" target="_blank" style="color: var(--orange); font-weight: 500; margin-top: 10px; display: inline-block;">और पढ़ें →</a>` : ''}
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
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#666;padding: 2rem;">समिति का डेटा जल्द ही अपडेट किया जाएगा।</p>';
        return;
    }

    const defaultPhoto = 'https://placehold.co/150x150/cccccc/ffffff?text=Photo';
    
    grid.innerHTML = committee.map(m => {
        const name = m.name || 'अज्ञात';
        const designation = m.designation || '';
        const photoUrl = m.photo || defaultPhoto; 
        
        return `
        <div class="committee-card" style="text-align: center; padding: 20px;">
            <img src="${photoUrl}" alt="${name}" 
                 loading="lazy"
                 decoding="async"
                 width="100"
                 height="100"
                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--orange); margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"
                 onerror="this.src='${defaultPhoto}'">
            <h3 style="margin: 5px 0; color: var(--dark-blue); font-size: 1.1rem;">${name}</h3>
            ${designation ? `<p style="color: var(--orange); font-weight: bold; margin: 5px 0; font-size: 0.9rem;">${designation}</p>` : ''}
            ${m.mobile ? `<a href="https://wa.me/91${m.mobile.replace(/\D/g,'')}" target="_blank" style="display: inline-block; margin-top: 10px; background: #25D366; color: white; padding: 6px 12px; border-radius: 20px; text-decoration: none; font-size: 0.85rem;">💬 WhatsApp</a>` : ''}
        </div>
    `;
    }).join('');
}

function toggleLoadMoreButtons() {
    const branchesBtn = document.getElementById('load-more-branches');
    const announcementsBtn = document.getElementById('load-more-announcements');
    
    if (branchesBtn) {
        branchesBtn.style.display = branchesState.hasMore ? 'block' : 'none';
    }
    if (announcementsBtn) {
        announcementsBtn.style.display = announcementsState.hasMore ? 'block' : 'none';
    }
}

function showError(message) {
    const branchGrid = document.getElementById('branch-overview-grid');
    const announcementsList = document.getElementById('announcements-list');
    
    if (branchGrid) {
        branchGrid.innerHTML = `<p style="text-align:center;padding:2rem;color:#e74c3c;background:#fdf2f2;border-radius:8px;">❌ डेटा लोड करने में त्रुटि।<br><small>${message}</small></p>`;
    }
    if (announcementsList) {
        announcementsList.innerHTML = '<p style="text-align:center;padding:2rem;color:#e74c3c;background:#fdf2f2;border-radius:8px;">घोषणाएं लोड नहीं हो सकीं।</p>';
    }
}

// ============================================
// ✅ HOME PAGE DATA LOADING
// ============================================

// Show cached data instantly, then refresh from Firestore in background
async function loadCriticalData() {
    // ── STEP 1: Paint from cache immediately (zero network wait) ──────────────
    const cachedBranches = loadCache('branches_page1');
    const cachedAnnouncements = loadCache('announcements_page1');

    if (cachedBranches) {
        branchesState.allData = cachedBranches.data;
        branchesState.hasMore = cachedBranches.hasMore;
        renderBranchesQuick(cachedBranches.data);
        toggleLoadMoreButtons();
    }

    if (cachedAnnouncements) {
        announcementsState.allData = cachedAnnouncements.data;
        announcementsState.hasMore = cachedAnnouncements.hasMore;
        renderAnnouncements(cachedAnnouncements.data, false);
    }

    // Show cached stats immediately if available
    const cachedStats = loadCache('stats');
    if (cachedStats) {
        updateStatistics(cachedStats.branches, cachedStats.families, cachedStats.members);
    }

    // ── STEP 2: Fetch fresh data from Firestore in background ─────────────────
    // If cache was valid, this runs silently and updates the UI when done.
    // If no cache existed, this is the first load and the user will see a brief load.
    try {
        const [branchesResult, announcementsResult] = await Promise.all([
            loadFromFirestore('branches', { limitCount: PAGE_SIZE }),
            loadFromFirestore('announcements', { 
                limitCount: 5,
                orderByField: 'createdAt',
                orderDirection: 'desc'
            })
        ]);

        branchesState = {
            lastDoc: branchesResult.lastDoc,
            hasMore: branchesResult.hasMore,
            allData: branchesResult.data
        };

        announcementsState = {
            lastDoc: announcementsResult.lastDoc,
            hasMore: announcementsResult.hasMore,
            allData: announcementsResult.data
        };

        // Save to localStorage for next visit
        saveCache('branches_page1', { data: branchesResult.data, hasMore: branchesResult.hasMore });
        saveCache('announcements_page1', { data: announcementsResult.data, hasMore: announcementsResult.hasMore });

        renderBranchesQuick(branchesResult.data);
        renderAnnouncements(announcementsResult.data, false);
        toggleLoadMoreButtons();

        // Load background data (stats + committee + family stats)
        loadBackgroundData();

    } catch (error) {
        console.error('❌ Critical data load error:', error);
        // Only show error if we had nothing to display from cache
        if (!cachedBranches && !cachedAnnouncements) {
            showError(error.message);
        }
    }
}

async function loadBackgroundData() {
    try {
        // Load families (cached) and counts in parallel
        // Note: getMemberCount() reuses getFamilies() so no extra read
        const [branchCount, familyData] = await Promise.all([
            getCount('branches'),
            getFamilies()
        ]);

        const familyCount = familyData.length;
        const memberCount = familyData.reduce((acc, f) => acc + (f.members?.length || 0), 0);

        updateStatistics(branchCount, familyCount, memberCount);

        // Cache stats for next visit
        saveCache('stats', { branches: branchCount, families: familyCount, members: memberCount });

        // Load committee (cached) separately
        const committee = await loadAllFromFirestore('committee', { useCache: true });
        renderHomeCommittee(committee);
        renderBranchesWithStats(branchesState.allData, familyData);

    } catch (error) {
        console.error('❌ Background load error:', error);
    }
}

// ============================================
// ✅ LOAD MORE HANDLERS
// ============================================

function setupLoadMoreHandlers() {
    const loadMoreBranchesBtn = document.getElementById('load-more-branches');
    const loadMoreAnnouncementsBtn = document.getElementById('load-more-announcements');

    if (loadMoreBranchesBtn) {
        loadMoreBranchesBtn.addEventListener('click', async () => {
            const btn = loadMoreBranchesBtn;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.innerHTML = '⏳ लोड हो रहा है...';

            try {
                const families = await getFamilies();
                
                const result = await loadFromFirestore('branches', {
                    limitCount: PAGE_SIZE,
                    lastDoc: branchesState.lastDoc
                });

                branchesState.lastDoc = result.lastDoc;
                branchesState.hasMore = result.hasMore;
                branchesState.allData = [...branchesState.allData, ...result.data];

                renderBranchesWithStats(branchesState.allData, families);
                toggleLoadMoreButtons();

            } catch (error) {
                console.error('Error loading more branches:', error);
                btn.textContent = '❌ पुनः प्रयास करें';
                setTimeout(() => { btn.textContent = originalText; }, 3000);
            } finally {
                btn.disabled = false;
                if (branchesState.hasMore) {
                    btn.textContent = originalText;
                }
            }
        });
    }

    if (loadMoreAnnouncementsBtn) {
        loadMoreAnnouncementsBtn.addEventListener('click', async () => {
            const btn = loadMoreAnnouncementsBtn;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.innerHTML = '⏳ लोड हो रहा है...';

            try {
                const result = await loadFromFirestore('announcements', {
                    limitCount: 5,
                    lastDoc: announcementsState.lastDoc,
                    orderByField: 'createdAt',
                    orderDirection: 'desc'
                });

                announcementsState.lastDoc = result.lastDoc;
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
                if (announcementsState.hasMore) {
                    btn.textContent = originalText;
                }
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
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }
}

function initScrollToTop() {
    const scrollTopBtn = document.getElementById('scroll-top');
    if (!scrollTopBtn) return;
    
    let ticking = false;
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                scrollTopBtn.classList.toggle('visible', window.pageYOffset > 300);
                ticking = false;
            });
            ticking = true;
        }
    });
    
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

export function initUI() {
    initThemeToggle();
    initMobileMenu();
    initScrollToTop();
}

// ============================================
// ✅ HOME PAGE INITIALIZATION
// ============================================

export function initHomePage() {
    initUI();
    setupLoadMoreHandlers();
    loadCriticalData();
    setTimeout(reportPerformance, 3000);
}

// ============================================
// ✅ PERFORMANCE REPORTING
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
// ✅ JSON LOADER
// ============================================

const jsonCache = new Map();
const CACHE_TTL = 10 * 60 * 1000;

export async function loadData(filename) {
    const cacheKey = filename;
    
    if (jsonCache.has(cacheKey)) {
        const { data, timestamp } = jsonCache.get(cacheKey);
        if (Date.now() - timestamp < CACHE_TTL) {
            return data;
        }
    }
    
    try {
        const response = await fetch(`data/${filename}`, { 
            cache: 'force-cache',
            headers: { 'Accept': 'application/json' }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        jsonCache.set(cacheKey, { data, timestamp: Date.now() });
        
        return data;
    } catch (error) {
        console.error(`❌ Error loading ${filename}:`, error);
        
        if (window.location.protocol === 'file:') {
            console.warn('⚠️ Running on file:// protocol - use Live Server');
        }
        
        return null;
    }
}