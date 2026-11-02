// ============================================
// 📅 12 MONTHS CALENDAR CONTROLLER (cleaned up)
// सनातन दिनदर्शिका | छःन्यात राजस्थानी विप्र मंडल, लातूर
// ============================================

if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var tabs = Array.prototype.slice.call(document.querySelectorAll('.month-btn'));
    var cards = Array.prototype.slice.call(document.querySelectorAll('.month-row-card'));
    var tabRow = document.getElementById('month-bar-row');

    var SCROLL_OFFSET = 16;
    var activeMonth = null;
    var isAutoScrolling = false;
    var autoScrollTimer = null;
    var userInteracted = false;

    ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(function (ev) {
        window.addEventListener(ev, function () { userInteracted = true; }, { passive: true, once: true });
    });

    function lockAutoScroll(ms) {
        isAutoScrolling = true;
        clearTimeout(autoScrollTimer);
        autoScrollTimer = setTimeout(function () { isAutoScrolling = false; }, ms || 900);
    }

    // ── 1. Highlight tab (scrolls ONLY the pill row, never the page) ─────────
    function highlightTab(m) {
        m = String(m);
        if (m === activeMonth) return;
        activeMonth = m;

        tabs.forEach(function (tab) {
            var isMatch = String(tab.dataset.month) === m;
            tab.classList.toggle('active', isMatch);
            if (isMatch && tabRow && tabRow.scrollWidth > tabRow.clientWidth) {
                tabRow.scrollTo({
                    left: tab.offsetLeft - (tabRow.clientWidth - tab.offsetWidth) / 2,
                    behavior: 'smooth'
                });
            }
        });
    }

    // ── 2. Scroll to a month card ────────────────────────────────────────────
    function pulse(el) {
        if (!el) return;
        el.classList.remove('highlight-pulse');
        void el.offsetWidth;
        el.classList.add('highlight-pulse');
        setTimeout(function () { el.classList.remove('highlight-pulse'); }, 2200);
    }

    function cardTop(target) {
        return Math.max(0, window.pageYOffset + target.getBoundingClientRect().top - SCROLL_OFFSET);
    }

    function scrollToCard(m, smooth, doPulse) {
        var target = document.getElementById('month-card-' + m);
        if (!target) return false;
        var top = (m === 'cover') ? 0 : cardTop(target);
        window.scrollTo({ top: top, behavior: smooth ? 'smooth' : 'auto' });
        if (doPulse) pulse(target);
        return true;
    }

    function isValidMonth(v) {
        if (v === 'cover') return true;
        var n = parseInt(v, 10);
        return String(n) === String(v) && n >= 1 && n <= 12;
    }

    // ── 3. Tab clicks ────────────────────────────────────────────────────────
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            var m = tab.dataset.month;
            lockAutoScroll(900);
            highlightTab(m);
            scrollToCard(m, true, true);
            if (history.replaceState) history.replaceState(null, '', '?month=' + m);
        });
    });

    // ── 4. Scroll-spy: card nearest the top reading line ─────────────────────
    function updateSpy() {
        if (isAutoScrolling || !cards.length) return;

        var line = window.innerHeight * 0.35;
        var atBottom = window.innerHeight + window.pageYOffset >= document.documentElement.scrollHeight - 8;
        var best = null, bestTop = -Infinity;

        cards.forEach(function (card) {
            var top = card.getBoundingClientRect().top;
            if (atBottom) {
                // at the very bottom: last card that is at least partly on screen
                if (top < window.innerHeight) { best = card; bestTop = top; }
            } else if (top <= line && top > bestTop + 1) {
                // strictly greater (+1px tolerance) keeps the FIRST card of a grid row
                best = card;
                bestTop = top;
            }
        });

        highlightTab(best ? best.id.replace('month-card-', '') : 'cover');
    }

    var spyQueued = false;
    window.addEventListener('scroll', function () {
        if (spyQueued) return;
        spyQueued = true;
        requestAnimationFrame(function () { spyQueued = false; updateSpy(); });
    }, { passive: true });

    // ── 5. Incoming ?month=X ─────────────────────────────────────────────────
    function getIncomingMonth() {
        var m = new URLSearchParams(window.location.search).get('month');
        if (!m && window.location.hash) {
            m = window.location.hash.replace('#month-card-', '').replace('#', '').trim();
        }
        return isValidMonth(m) ? m : null;
    }

    var incoming = getIncomingMonth();
    if (incoming) {
        if (window.location.hash && history.replaceState) {
            history.replaceState(null, '', '?month=' + incoming);
        }
        lockAutoScroll(1200);
        highlightTab(incoming);
        scrollToCard(incoming, false, false);   // instant jump
        setTimeout(function () { pulse(document.getElementById('month-card-' + incoming)); }, 150);

        // Safety net: fonts/header can shift layout slightly. Re-align once after load.
        window.addEventListener('load', function () {
            if (userInteracted || incoming === 'cover') return;
            var t = document.getElementById('month-card-' + incoming);
            if (t && Math.abs(t.getBoundingClientRect().top - SCROLL_OFFSET) > 40) {
                lockAutoScroll(600);
                scrollToCard(incoming, false, false);
            }
        }, { once: true });
    } else {
        highlightTab('cover');
    }

    // ── 6. Scroll-to-top button ──────────────────────────────────────────────
    var scrollTopBtn = document.getElementById('scroll-top-btn');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            scrollTopBtn.style.display = window.scrollY > 350 ? 'flex' : 'none';
        }, { passive: true });
        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ── 7. Lightbox ──────────────────────────────────────────────────────────
    var pages = window.CALENDAR_PAGES || [];
    var lightbox = document.getElementById('cal-lightbox');
    var lbImg = document.getElementById('cal-lightbox-img');
    var lbClose = document.getElementById('cal-lightbox-close');
    var lbPrev = document.getElementById('cal-lightbox-prev');
    var lbNext = document.getElementById('cal-lightbox-next');
    var lbStage = document.getElementById('cal-lightbox-stage');
    var btnZoomIn = document.getElementById('cal-lb-zoom-in');
    var btnZoomOut = document.getElementById('cal-lb-zoom-out');
    var btnZoomReset = document.getElementById('cal-lb-zoom-reset');

    if (!lightbox || !lbImg || !lbStage) return;

    var MIN_SCALE = 1, MAX_SCALE = 4.5;
    var currentIndex = 0;
    var scale = 1, panX = 0, panY = 0;
    var lastFocus = null;
    var inertEls = [];
    var switchTimer = null;

    function isOpen() { return lightbox.classList.contains('active'); }

    // --- transform helpers ---
    function stageCenterOffset(clientX, clientY) {
        var r = lbStage.getBoundingClientRect();
        return { x: clientX - (r.left + r.width / 2), y: clientY - (r.top + r.height / 2) };
    }

    function clampPan() {
        var maxX = Math.max(0, (lbImg.offsetWidth * scale - lbStage.clientWidth) / 2);
        var maxY = Math.max(0, (lbImg.offsetHeight * scale - lbStage.clientHeight) / 2);
        panX = Math.min(maxX, Math.max(-maxX, panX));
        panY = Math.min(maxY, Math.max(-maxY, panY));
    }

    function applyTransform(smooth) {
        lbImg.classList.toggle('dragging', !smooth);
        if (scale <= MIN_SCALE) { scale = MIN_SCALE; panX = 0; panY = 0; }
        else clampPan();
        lbImg.classList.toggle('zoomed', scale > MIN_SCALE);
        lbImg.style.transform = 'translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ')';
    }

    // Zoom keeping the point (cx, cy) — relative to stage centre — fixed on screen
    function setZoom(newScale, cx, cy, smooth) {
        newScale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, newScale));
        cx = cx || 0; cy = cy || 0;
        var ratio = newScale / scale;
        panX = cx - (cx - panX) * ratio;
        panY = cy - (cy - panY) * ratio;
        scale = newScale;
        applyTransform(smooth !== false);
    }

    function zoomBy(factor, cx, cy) { setZoom(scale * factor, cx, cy, true); }

    function resetZoom() { scale = 1; panX = 0; panY = 0; applyTransform(true); }

    // --- page content ---
    function preload(i) {
        if (!pages.length) return;
        var p = pages[(i + pages.length) % pages.length];
        if (p && p.src) { var im = new Image(); im.src = p.src; }
    }

    function showPage(index, animate) {
        if (!pages.length) return;
        index = (index + pages.length) % pages.length;
        currentIndex = index;
        var p = pages[index];

        resetZoom();
        clearTimeout(switchTimer);

        if (animate) {
            lbImg.classList.add('switching');
            switchTimer = setTimeout(function () {
                lbImg.src = p.src;
                lbImg.alt = p.title || 'दिनदर्शिका पृष्ठ';
                lbImg.classList.remove('switching');
            }, 80);
        } else {
            lbImg.src = p.src;
            lbImg.alt = p.title || 'दिनदर्शिका पृष्ठ';
            lbImg.classList.remove('switching');
        }

        preload(index + 1);
        preload(index - 1);
    }

    function navigate(delta) { showPage(currentIndex + delta, true); }

    // --- open / close with focus management ---
    function setBackgroundInert(on) {
        if (on) {
            inertEls = Array.prototype.filter.call(document.body.children, function (el) {
                return el !== lightbox && el.tagName !== 'SCRIPT' && !el.inert;
            });
            inertEls.forEach(function (el) { el.inert = true; });
        } else {
            inertEls.forEach(function (el) { el.inert = false; });
            inertEls = [];
        }
    }

    function openLightbox(index) {
        lastFocus = document.activeElement;
        showPage(index, false);
        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setBackgroundInert(true);
        if (lbClose) lbClose.focus();
    }

    function closeLightbox() {
        resetZoom();
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        setBackgroundInert(false);
        if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }

    document.querySelectorAll('.cal-lb-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            openLightbox(parseInt(trigger.dataset.lbIndex || '0', 10));
        });
    });

    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lbPrev) lbPrev.addEventListener('click', function (e) { e.stopPropagation(); navigate(-1); });
    if (lbNext) lbNext.addEventListener('click', function (e) { e.stopPropagation(); navigate(1); });
    if (btnZoomIn) btnZoomIn.addEventListener('click', function (e) { e.stopPropagation(); zoomBy(1.4); });
    if (btnZoomOut) btnZoomOut.addEventListener('click', function (e) { e.stopPropagation(); zoomBy(1 / 1.4); });
    if (btnZoomReset) btnZoomReset.addEventListener('click', function (e) { e.stopPropagation(); resetZoom(); });

    // --- wheel zoom (towards cursor) ---
    lbStage.addEventListener('wheel', function (e) {
        e.preventDefault();
        var c = stageCenterOffset(e.clientX, e.clientY);
        setZoom(scale * (e.deltaY < 0 ? 1.18 : 1 / 1.18), c.x, c.y, true);
    }, { passive: false });

    // --- double click (desktop) ---
    lbImg.addEventListener('dblclick', function (e) {
        e.preventDefault();
        var c = stageCenterOffset(e.clientX, e.clientY);
        if (scale > 1.2) resetZoom(); else setZoom(2.4, c.x, c.y, true);
    });

    // --- mouse drag pan ---
    var dragging = false, dragStartX = 0, dragStartY = 0;

    lbImg.addEventListener('mousedown', function (e) {
        if (scale <= 1) return;
        e.preventDefault();
        dragging = true;
        dragStartX = e.clientX - panX;
        dragStartY = e.clientY - panY;
    });
    window.addEventListener('mousemove', function (e) {
        if (!dragging) return;
        panX = e.clientX - dragStartX;
        panY = e.clientY - dragStartY;
        applyTransform(false);
    });
    window.addEventListener('mouseup', function () {
        if (!dragging) return;
        dragging = false;
        applyTransform(true);
    });

    // --- touch: pinch, pan, swipe-to-navigate, double-tap ---
    var pinchStartDist = 0, pinchStartScale = 1;
    var touchPanning = false, tPanX = 0, tPanY = 0;
    var swipeStartX = 0, swipeStartY = 0, swipeStartT = 0, swipeValid = false;
    var lastTapT = 0, lastTapX = 0, lastTapY = 0;
    var wasPinching = false;

    function touchDist(t) { return Math.hypot(t[0].clientX - t[1].clientX, t[0].clientY - t[1].clientY); }

    lbStage.addEventListener('touchstart', function (e) {
        if (e.touches.length === 2) {
            wasPinching = true;
            swipeValid = false;
            touchPanning = false;
            pinchStartDist = touchDist(e.touches);
            pinchStartScale = scale;
        } else if (e.touches.length === 1) {
            var t = e.touches[0];
            if (scale > 1) {
                touchPanning = true;
                tPanX = t.clientX - panX;
                tPanY = t.clientY - panY;
            } else {
                swipeValid = !wasPinching;
                swipeStartX = t.clientX;
                swipeStartY = t.clientY;
                swipeStartT = Date.now();
            }
        }
    }, { passive: true });

    lbStage.addEventListener('touchmove', function (e) {
        if (e.touches.length === 2 && pinchStartDist > 0) {
            e.preventDefault();
            var mid = stageCenterOffset(
                (e.touches[0].clientX + e.touches[1].clientX) / 2,
                (e.touches[0].clientY + e.touches[1].clientY) / 2
            );
            lbImg.classList.add('dragging');
            setZoom(pinchStartScale * (touchDist(e.touches) / pinchStartDist), mid.x, mid.y, false);
        } else if (e.touches.length === 1 && touchPanning && scale > 1) {
            e.preventDefault();
            panX = e.touches[0].clientX - tPanX;
            panY = e.touches[0].clientY - tPanY;
            applyTransform(false);
        }
    }, { passive: false });

    lbStage.addEventListener('touchend', function (e) {
        if (e.touches.length < 2) pinchStartDist = 0;

        if (e.touches.length === 0) {
            var ct = e.changedTouches[0];
            touchPanning = false;
            applyTransform(true);

            if (wasPinching) {
                // ignore the lift-off of a pinch gesture
                wasPinching = false;
                swipeValid = false;
                return;
            }

            var now = Date.now();
            var dx = ct.clientX - swipeStartX;
            var dy = ct.clientY - swipeStartY;

            // swipe left/right to change month (only when not zoomed)
            if (swipeValid && scale === 1 && Math.abs(dx) > 55 &&
                Math.abs(dx) > Math.abs(dy) * 1.5 && now - swipeStartT < 600) {
                navigate(dx < 0 ? 1 : -1);
                swipeValid = false;
                lastTapT = 0;
                return;
            }

            // double-tap to zoom (iOS doesn't fire dblclick reliably)
            if (Math.abs(dx) < 10 && Math.abs(dy) < 10 || scale > 1) {
                if (now - lastTapT < 300 &&
                    Math.hypot(ct.clientX - lastTapX, ct.clientY - lastTapY) < 30) {
                    var c = stageCenterOffset(ct.clientX, ct.clientY);
                    if (scale > 1.2) resetZoom(); else setZoom(2.4, c.x, c.y, true);
                    lastTapT = 0;
                } else {
                    lastTapT = now; lastTapX = ct.clientX; lastTapY = ct.clientY;
                }
            }
            swipeValid = false;
        }
    }, { passive: true });

    // --- close on backdrop click ---
    lightbox.addEventListener('click', function (e) {
        if (scale > 1) return;
        if (e.target === lightbox || e.target === lbStage) closeLightbox();
    });

    // --- keyboard: Esc, arrows, +/-, 0, focus trap ---
    window.addEventListener('keydown', function (e) {
        if (!isOpen()) return;

        switch (e.key) {
            case 'Escape': closeLightbox(); return;
            case 'ArrowLeft': e.preventDefault(); navigate(-1); return;
            case 'ArrowRight': e.preventDefault(); navigate(1); return;
            case '+':
            case '=': e.preventDefault(); zoomBy(1.4); return;
            case '-':
            case '_': e.preventDefault(); zoomBy(1 / 1.4); return;
            case '0': e.preventDefault(); resetZoom(); return;
            case 'Tab':
                var focusables = Array.prototype.filter.call(
                    lightbox.querySelectorAll('button'),
                    function (b) { return b.offsetParent !== null; }
                );
                if (!focusables.length) return;
                var first = focusables[0], last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
                else if (!lightbox.contains(document.activeElement)) { e.preventDefault(); first.focus(); }
        }
    });

    // keep pan in bounds if the window is resized / rotated while zoomed
    window.addEventListener('resize', function () { if (isOpen()) applyTransform(true); });
});