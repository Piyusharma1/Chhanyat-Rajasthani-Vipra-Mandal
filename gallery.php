<?php
// Optional: Public page, no auth needed
?>
<!DOCTYPE html>
<html lang="hi" dir="ltr">

<head>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="canonical" href="https://chhanyatvipralatur.com/gallery.php">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#7B1A1A">

    <title>गैलरी | छःन्यात राजस्थानी विप्र मंडल, लातूर</title>
    <meta name="description" content="छःन्यात राजस्थानी विप्र मंडल, लातूर की गैलरी - कार्यक्रम, समारोह और अन्य आयोजन की तस्वीरें">
    <meta name="keywords" content="Gallery Chhanyat Rajasthani Vipra Mandal Latur, Photos, Events, Programs, Brahmin Samaj Latur, छःन्यात राजस्थानी विप्र मंडल गैलरी">
    <meta property="og:title" content="गैलरी | छःन्यात राजस्थानी विप्र मंडल, लातूर">
    <meta property="og:description" content="छःन्यात राजस्थानी विप्र मंडल, लातूर की गैलरी - कार्यक्रम, समारोह और अन्य आयोजन की तस्वीरें">
    <meta property="og:url" content="https://chhanyatvipralatur.com/gallery.php">
    <meta property="og:type" content="website">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ImageGallery",
      "name": "छःन्यात राजस्थानी विप्र मंडल, लातूर - फोटो गैलरी",
      "description": "छःन्यात राजस्थानी विप्र मंडल, लातूर की गैलरी - कार्यक्रम, समारोह और अन्य आयोजन की तस्वीरें",
      "url": "https://chhanyatvipralatur.com/gallery.php"
    }
    </script>

    <!-- Google Analytics & Counter -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-DE5KD051LL"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-DE5KD051LL');
    </script>
    <script src="https://cdn.counter.dev/script.js" data-id="1f92108b-8857-410e-b5a8-c1ab33e859f8" data-utcoffset="6"></script>

    <!-- Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__.'/css/style.css') ?: time()?>">
</head>
<body>
    <div data-include="components/header.php"></div>

    <div class="container" style="padding-top: 15px;">
        <!-- Breadcrumb -->

        <section>
            <div class="gallery-hero">
                <div class="gallery-hero-content">
                    <h2>फोटो गैलरी</h2>
                    <p class="gallery-hero-subtitle" id="gallery-total-info"></p>
                </div>
            </div>

            <div id="gallery-container">
                <div class="loading-message">
                    <div class="spinner"></div>
                    <div>गैलरी लोड हो रही है...</div>
                </div>
            </div>
        </section>
    </div>

    <!-- 🖼️ PREMIUM LIGHTBOX -->
    <div id="lightbox" role="dialog" aria-modal="true" aria-label="तस्वीर विस्तार (Lightbox)">
        <button class="lb-close" id="lb-close-btn" aria-label="बंद करें (Close)" style="background:none;border:none;"><i class="fas fa-times"></i></button>
        <button class="lb-nav lb-prev" id="lb-prev-btn" aria-label="पिछली तस्वीर (Previous)"><i class="fas fa-chevron-left"></i></button>
        <img id="lightbox-img" src="" alt="तस्वीर विस्तार">
        <button class="lb-nav lb-next" id="lb-next-btn" aria-label="अगली तस्वीर (Next)"><i class="fas fa-chevron-right"></i></button>
        <div class="lb-caption" id="lb-caption"></div>
        <div class="lb-counter-wrap">
            <div class="lb-counter" id="lb-counter"></div>
            <div class="lb-progress">
                <div class="lb-progress-bar" id="lb-progress-bar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <div data-include="components/footer.php"></div>
    <script src="js/include.js?v=<?= @filemtime(__DIR__.'/js/include.js') ?: time()?>"></script>
    
<script>
// ============================================
// 📸 GALLERY — TWO-STEP LAZY LOADING (OPTIMIZED & FIXED)
// ============================================

const INITIAL_VISIBLE = 12;
const PAGE_SIZE       = 24;
const IO_MARGIN       = '200px';

let allImages = [];        // Fixed-size array to map exactly with globalIdx
let currentIndex = 0;
const albumState = new Map();
const albumRanges = [];    // Tracks [start,end) global index range + DOM refs per album for on-demand loading
let lazyObserver = null;
let globalImageCounter = 0;

// ============================================
// 1. INIT LAZY OBSERVER (WITH CLEANUP LOGIC)
// ============================================
function initObserver() {
    if (!('IntersectionObserver' in window)) return;
    lazyObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const item = entry.target;
            const img  = item.querySelector('img');
            
            if (img && img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                img.onload  = () => item.removeAttribute('data-lazy-pending');
                img.onerror = () => {
                    item.removeAttribute('data-lazy-pending');
                    item.innerHTML = `
                        <div style="display:flex;flex-direction:column;align-items:center;
                                    justify-content:center;height:100%;color:var(--text-soft);gap:6px;">
                            <i class="fas fa-image" style="font-size:1.5rem;color:var(--saffron);"></i>
                            <small style="font-size:0.7rem;">लोड विफल</small>
                        </div>`;
                };
            }
            lazyObserver.unobserve(item);
        });
    }, { rootMargin: IO_MARGIN, threshold: 0 });
}

// ============================================
// 2. BUILD A SINGLE GALLERY ITEM
// ============================================
function buildItem(imgUrl, localIdx, globalIdx, albumTitle, albumTotal) {
    const item = document.createElement('div');
    item.className = 'gallery-item';
    item.setAttribute('data-index', globalIdx);
    item.setAttribute('data-lazy-pending', '');

    const numBadge = document.createElement('div');
    numBadge.className = 'img-number';
    numBadge.innerHTML = `<i class="fas fa-hashtag"></i>${localIdx + 1}`;
    item.appendChild(numBadge);

    const overlay = document.createElement('div');
    overlay.className = 'overlay';
    overlay.innerHTML = `
        <div class="overlay-icon"><i class="fas fa-search-plus"></i></div>
        <div class="overlay-text">क्लिक करके देखें</div>`;
    item.appendChild(overlay);

    const img = document.createElement('img');
    img.alt = `${albumTitle} - ${localIdx + 1}`;
    img.decoding = 'async';

    if (lazyObserver) {
        img.dataset.src = imgUrl;
        lazyObserver.observe(item);
    } else {
        img.src = imgUrl;
        img.loading = 'lazy';
        img.onload  = () => item.removeAttribute('data-lazy-pending');
        img.onerror = () => item.removeAttribute('data-lazy-pending');
    }

    item.appendChild(img);
    return item;
}

// ============================================
// 3. RENDER A BATCH OF ITEMS
// ============================================
function renderBatch(albumId, grid, from, count) {
    const state = albumState.get(albumId);
    if (!state) return 0;

    const end    = Math.min(from + count, state.images.length);
    const frag   = document.createDocumentFragment();
    let rendered = 0;

    for (let i = from; i < end; i++) {
        const imgObj  = state.images[i];
        const imgUrl  = typeof imgObj === 'string' ? imgObj : (imgObj.url || imgObj.src || '');
        if (!imgUrl) continue;

        const globalIdx = state.globalOffset + i;
        const item = buildItem(imgUrl, i, globalIdx, state.title, state.images.length);
        frag.appendChild(item);
        rendered++;
    }

    grid.appendChild(frag);
    state.renderedCount = end;
    return rendered;
}

// ============================================
// 4. LOAD MORE HANDLER
// ============================================
function attachLoadMore(albumId, grid, wrapEl) {
    const state = albumState.get(albumId);
    if (!state || state.renderedCount >= state.images.length) return;

    const existing = wrapEl.querySelector('.load-more-wrap');
    if (existing) existing.remove();

    const wrap = document.createElement('div');
    wrap.className = 'load-more-wrap';
    wrap.innerHTML = `
        <button class="load-more-btn" id="lm-${albumId}">
            <i class="fas fa-images"></i> और तस्वीरें देखें
        </button>
        <div class="load-more-info" id="lmi-${albumId}">
            ${state.renderedCount} / ${state.images.length} दिखाई जा रही हैं
        </div>`;

    const btn  = wrap.querySelector(`#lm-${albumId}`);
    const info = wrap.querySelector(`#lmi-${albumId}`);

    btn.addEventListener('click', () => {
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> लोड हो रहा है…`;

        requestAnimationFrame(() => {
            renderBatch(albumId, grid, state.renderedCount, PAGE_SIZE);
            info.textContent = `${state.renderedCount} / ${state.images.length} दिखाई जा रही हैं`;

            if (state.renderedCount >= state.images.length) {
                wrap.remove();
            } else {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-images"></i> और तस्वीरें देखें`;
            }
            
            const gridWrap = wrapEl.closest('.album-grid-wrap');
            if (gridWrap && !gridWrap.classList.contains('collapsed')) {
                gridWrap.style.maxHeight = gridWrap.scrollHeight + 'px';
            }
        });
    });

    wrapEl.appendChild(wrap);
}

// ============================================
// 5. FETCH & RENDER IMAGES FOR AN ALBUM
// ============================================
async function loadAlbumImages(albumId, folder, grid, gridWrap, header) {
    const state = albumState.get(albumId);
    if (!state) return;
    
    if (state.imagesLoaded) {
        if (state.renderedCount === 0 && state.images.length > 0) {
            renderBatch(albumId, grid, 0, INITIAL_VISIBLE);
            attachLoadMore(albumId, grid, gridWrap);
            requestAnimationFrame(() => {
                gridWrap.style.maxHeight = gridWrap.scrollHeight + 'px';
            });
        }
        return;
    }

    // FIXED: Guard against concurrent calls (e.g. the first-album auto-load
    // timer firing at the same time as a manual header click, or the lightbox
    // Prev/Next jumping into an album that's already mid-fetch). Without this,
    // two callers could both pass the `imagesLoaded` check above, both fetch,
    // and both render the same photos twice into the grid.
    if (state.loadPromise) {
        return state.loadPromise;
    }

    state.loadPromise = (async () => {

    header.classList.add('loading');
    grid.innerHTML = `
        <div class="album-loading-placeholder" style="grid-column: 1/-1;">
            <div class="spinner"></div>
            <div>तस्वीरें लोड हो रही हैं...</div>
        </div>`;

    try {
        const response = await fetch(`api/gallery.php?album=${encodeURIComponent(folder)}`);
        if (!response.ok) throw new Error('Server error');
        const data = await response.json();
        
        const images = data.images || [];
        state.images = images;
        state.imagesLoaded = true;

        // FIXED: Map safely to predefined slots
        images.forEach((imgObj, idx) => {
            const url = typeof imgObj === 'string' ? imgObj : (imgObj.url || imgObj.src || '');
            allImages[state.globalOffset + idx] = {
                src: url,
                albumTitle: state.title,
                albumPosition: idx + 1,
                albumTotal: images.length
            };
        });

        grid.innerHTML = '';
        if (images.length === 0) {
            grid.innerHTML = `
                <div class="album-loading-placeholder" style="grid-column: 1/-1;">
                    <i class="fas fa-image" style="font-size:2rem;color:var(--saffron);margin-bottom:10px;display:block;"></i>
                    <div>इस एल्बम में कोई तस्वीर नहीं है</div>
                </div>`;
        } else {
            renderBatch(albumId, grid, 0, INITIAL_VISIBLE);
            attachLoadMore(albumId, grid, gridWrap);
        }

        requestAnimationFrame(() => {
            gridWrap.style.maxHeight = gridWrap.scrollHeight + 'px';
        });

        header.classList.remove('loading');
    } catch (err) {
        console.error('❌ Failed to load album:', err);
        grid.innerHTML = `
            <div class="album-loading-placeholder" style="grid-column: 1/-1; border-color: var(--red);">
                <i class="fas fa-exclamation-triangle" style="font-size:1.5rem;color:var(--red);margin-bottom:8px;display:block;"></i>
                <div style="color:var(--red);">तस्वीरें लोड नहीं हो पाईं</div>
                <button onclick="retryAlbumLoad('${albumId}', '${folder}')"
                        style="margin-top:10px;padding:6px 16px;background:var(--saffron);
                               color:white;border:none;border-radius:15px;cursor:pointer;
                               font-size:0.8rem;">
                    <i class="fas fa-redo"></i> पुनः प्रयास
                </button>
            </div>`;
        header.classList.remove('loading');
    }

    })(); // end loadPromise IIFE

    try {
        await state.loadPromise;
    } finally {
        state.loadPromise = null;
    }
}

window.retryAlbumLoad = function(albumId, folder) {
    const albumDiv = document.querySelector(`[data-album-id="${albumId}"]`);
    if (!albumDiv) return;
    const grid = albumDiv.querySelector('.gallery-grid');
    const gridWrap = albumDiv.querySelector('.album-grid-wrap');
    const header = albumDiv.querySelector('.album-header');
    
    const state = albumState.get(albumId);
    if (state) {
        state.imagesLoaded = false;
        state.loadPromise = null; // allow a fresh fetch after a failed attempt
    }
    
    loadAlbumImages(albumId, folder, grid, gridWrap, header);
};

// ============================================
// 6. MAIN LOAD FUNCTION
// ============================================
async function loadGallery() {
    const container = document.getElementById('gallery-container');

    try {
        const response = await fetch('api/gallery.php');
        if (!response.ok) throw new Error('Server error: ' + response.status);
        const data = await response.json();
        if (data.error) throw new Error(data.error);

        const albums = data.albums || [];
        if (albums.length === 0) {
            container.innerHTML = `
                <div class="no-albums">
                    <i class="fas fa-camera-retro"></i>
                    <h3>कोई गैलरी उपलब्ध नहीं</h3>
                    <p>जल्द ही नई तस्वीरें जोड़ी जाएंगी</p>
                </div>`;
            return;
        }

        container.innerHTML = '';
        albumState.clear();
        albumRanges.length = 0;
        globalImageCounter = 0;

        const heroInfo = document.getElementById('gallery-total-info');
        if (heroInfo) {
            const totalPhotos = albums.reduce((sum, a) => sum + (a.count || 0), 0);
            heroInfo.textContent = `${albums.length} एल्बम • ${totalPhotos} तस्वीरें`;
            heroInfo.style.display = 'block';
        }

        const totalCount = albums.reduce((sum, a) => sum + (a.count || 0), 0);
        allImages = new Array(totalCount);

        albums.forEach((album, idx) => {
            const albumId = 'album-' + idx + '-' + Math.random().toString(36).slice(2, 6);
            const folder = album.folder || album.name || '';
            const count = album.count || 0;
            const rangeStart = globalImageCounter;

            albumState.set(albumId, {
                title: album.title || folder,
                folder: folder,
                images: [],
                imagesLoaded: false,
                renderedCount: 0,
                globalOffset: globalImageCounter
            });

            globalImageCounter += count;

            const albumDiv = document.createElement('div');
            albumDiv.className = 'gallery-album';
            albumDiv.dataset.albumId = albumId;

            const isFirst = idx === 0;

            const header = document.createElement('div');
            header.className = 'album-header' + (isFirst ? '' : ' collapsed');
            header.innerHTML = `
                <div class="album-icon"><i class="fas fa-folder-open"></i></div>
                <div class="album-info">
                    <h3 class="album-title">${album.title || folder}</h3>
                    <div class="album-meta">
                        <span><i class="fas fa-camera"></i> ${count} तस्वीरें</span>
                        ${album.date ? `<span><i class="fas fa-calendar"></i> ${album.date}</span>` : ''}
                        <span style="background:var(--maroon, #7B1A1A); color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem; display:inline-block; margin-left:8px;">
                            <i class="fas fa-sitemap"></i> ${album.branch === 'ALL' || !album.branch ? 'सामूहिक' : album.branch}
                        </span>
                    </div>
                </div>
                <div class="album-badge"><i class="fas fa-eye"></i> ${isFirst ? 'खुला' : 'खोलें'}</div>
                <i class="fas fa-chevron-down album-toggle"></i>`;
            albumDiv.appendChild(header);

            const gridWrap = document.createElement('div');
            gridWrap.className = 'album-grid-wrap' + (isFirst ? '' : ' collapsed');

            const grid = document.createElement('div');
            grid.className = 'gallery-grid';
            gridWrap.appendChild(grid);
            albumDiv.appendChild(gridWrap);

            container.appendChild(albumDiv);

            // Track this album's global index range + DOM refs so the lightbox
            // can lazily fetch it on demand when Next/Prev reaches an unopened album
            albumRanges.push({
                albumId, folder,
                start: rangeStart,
                end: rangeStart + count,
                grid, gridWrap, header
            });

            // FIXED: Trigger load safely after DOM paint
            if (isFirst) {
                setTimeout(() => {
                    loadAlbumImages(albumId, folder, grid, gridWrap, header);
                }, 50);
            }

            header.addEventListener('click', () => {
                const collapsed = gridWrap.classList.toggle('collapsed');
                header.classList.toggle('collapsed', collapsed);
                
                const badge = header.querySelector('.album-badge');
                if (badge) {
                    badge.innerHTML = collapsed 
                        ? '<i class="fas fa-eye"></i> खोलें' 
                        : '<i class="fas fa-eye"></i> खुला';
                }

                if (!collapsed) {
                    const state = albumState.get(albumId);
                    if (state && !state.imagesLoaded) {
                        loadAlbumImages(albumId, folder, grid, gridWrap, header);
                    } else {
                        requestAnimationFrame(() => {
                            gridWrap.style.maxHeight = gridWrap.scrollHeight + 'px';
                        });
                    }
                } else {
                    // Cleanup height on collapse
                    gridWrap.style.maxHeight = null;
                }
            });
        });

    } catch (err) {
        console.error('❌ Gallery error:', err);
        container.innerHTML = `<div class="no-albums"><h3>त्रुटि हुई</h3></div>`;
    }
}

// ============================================
// 7. PREMIUM LIGHTBOX (FIXED CRASH BUG + GLOBAL COUNTER)
// ============================================
function updateLightboxDisplay() {
    const data = allImages[currentIndex];
    if (!data) return; 

    const img         = document.getElementById('lightbox-img');
    const caption     = document.getElementById('lb-caption');
    const counter     = document.getElementById('lb-counter');
    const progressBar = document.getElementById('lb-progress-bar');

    img.style.opacity = '0';
    setTimeout(() => {
        img.src = data.src;
        img.alt = `${data.albumTitle} - ${data.albumPosition || currentIndex + 1}`;
        img.style.opacity = '1';
    }, 150);

    caption.innerHTML  = `<i class="fas fa-folder-open" style="color:var(--gold-light);margin-right:6px;"></i>${data.albumTitle}`;
    // FIXED: use the global position/total across ALL albums instead of the
    // per-album position, so the counter and progress bar don't jump/reset
    // when the visitor crosses from one album into the next.
    counter.innerHTML  = `<strong>${currentIndex + 1}</strong> / ${allImages.length}`;
    progressBar.style.width = `${((currentIndex + 1) / allImages.length) * 100}%`;

    // FIXED: Optional Chaining (?.) used to prevent crash on undefined slots
    const preload = (idx) => {
        if (idx < 0 || idx >= allImages.length || !allImages[idx]) return;
        const pre = new Image();
        pre.src = allImages[idx]?.src; 
    };
    preload(currentIndex - 1);
    preload(currentIndex + 1);
}

function openLightbox(index) {
    if (index < 0 || index >= allImages.length || !allImages[index]) return;
    currentIndex = index;
    const lightbox = document.getElementById('lightbox');
    if (!lightbox) return;
    updateLightboxDisplay();
    lightbox.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// FIXED: navigateLightbox is async and handles gaps or albums mid-load safely
async function navigateLightbox(direction) {
    if (allImages.length === 0) return;

    let attempts = 0;
    let nextIndex = currentIndex;

    while (attempts < allImages.length) {
        attempts++;
        nextIndex = (nextIndex + direction + allImages.length) % allImages.length;

        if (!allImages[nextIndex]) {
            const range = albumRanges.find(r => nextIndex >= r.start && nextIndex < r.end);
            if (range) {
                const state = albumState.get(range.albumId);
                if (state && !state.imagesLoaded) {
                    await loadAlbumImages(range.albumId, range.folder, range.grid, range.gridWrap, range.header);
                }
            }
        }

        if (allImages[nextIndex] && allImages[nextIndex].src) {
            currentIndex = nextIndex;
            updateLightboxDisplay();
            return;
        }
    }
}

// ============================================
// 8. EVENT LISTENERS
// ============================================
document.addEventListener('click', function(e) {
    const item = e.target.closest('.gallery-item');
    if (item) {
        e.preventDefault();
        const idx = parseInt(item.getAttribute('data-index'), 10);
        if (!isNaN(idx)) openLightbox(idx);
        return;
    }
    if (e.target.closest('#lb-close-btn'))  { closeLightbox();        return; }
    if (e.target.closest('#lb-prev-btn'))   { navigateLightbox(-1);   return; }
    if (e.target.closest('#lb-next-btn'))   { navigateLightbox(1);    return; }
    if (e.target.id === 'lightbox')         { closeLightbox();        return; }
});

document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('lightbox');
    if (!lightbox || !lightbox.classList.contains('show')) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowLeft')   navigateLightbox(-1);
    if (e.key === 'ArrowRight')  navigateLightbox(1);
});

// Touch swipe
let touchStartX = 0;
const lightboxEl = document.getElementById('lightbox');
if (lightboxEl) {
    lightboxEl.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    lightboxEl.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 50) navigateLightbox(diff > 0 ? 1 : -1);
    }, { passive: true });
}

// ============================================
// 9. BOOT
// ============================================
initObserver();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadGallery);
} else {
    loadGallery();
}
</script>
</body>
</html>