/**
 * ===================================================================
 * 📅 CALENDAR MANAGEMENT (Admin CRUD)
 * Strictly matching the UI logic of calendar.php
 * Covers: Cover Page, 12 Monthly Sheets (Jan-Dec), Annual PDF
 * ===================================================================
 */

(function () {
    'use strict';

    // ================================================================
    // 🔔 TOAST NOTIFICATIONS
    // ================================================================
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `admin-toast ${type}`;
        const iconClass = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

        toast.innerHTML = `
            <i class="fas ${iconClass}"></i>
            <div>${message}</div>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(60px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ================================================================
    // 🖼️ REPLACE PAGE / COVER MODAL
    // ================================================================
    window.openReplaceModal = function (target, title, currentSrc) {
        const modal = document.getElementById('replace-modal-overlay');
        const targetInput = document.getElementById('replace-page-target');
        const titleEl = document.getElementById('replace-modal-title');
        const labelEl = document.getElementById('replace-target-label');
        const previewImg = document.getElementById('replace-preview-img');
        const fileInput = document.getElementById('replace-file-input');

        if (!modal) return;

        targetInput.value = target;
        titleEl.innerHTML = `<i class="fas fa-camera"></i> फ़ोटो बदलें: ${title}`;
        labelEl.textContent = title;
        previewImg.src = currentSrc ? `${currentSrc}?v=${Date.now()}` : '';
        fileInput.value = '';

        modal.classList.add('show');
    };

    function closeReplaceModal() {
        const modal = document.getElementById('replace-modal-overlay');
        if (modal) modal.classList.remove('show');
    }

    // Preview newly selected image file
    function handleFileSelection(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('replace-preview-img').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Submit Replace Form
    async function handleReplaceSubmit(e) {
        e.preventDefault();
        const target = document.getElementById('replace-page-target').value;
        const fileInput = document.getElementById('replace-file-input');

        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('कृपया एक इमेज फ़ाइल चुनें।', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('page_target', target);
        formData.append('page_image', fileInput.files[0]);

        const btnSubmit = document.getElementById('btn-submit-replace');
        const origHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> अपलोड हो रहा है...';

        try {
            const res = await fetch('api/update_calendar_page.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message || 'फ़ोटो सफलतापूर्वक अपडेट हो गई!', 'success');
                closeReplaceModal();

                // Refresh the corresponding image in the UI instantly
                const timestamp = Date.now();
                if (target === 'cover') {
                    const img = document.getElementById('img-preview-cover');
                    if (img) img.src = `uploads/calendar/cover.webp?v=${timestamp}`;
                } else {
                    const img = document.getElementById(`img-preview-${target}`);
                    if (img) img.src = `uploads/calendar/pages/${target}.webp?v=${timestamp}`;
                }
            } else {
                throw new Error(data.error || 'अपलोड विफल रहा।');
            }
        } catch (err) {
            console.error('Replace error:', err);
            showToast(err.message, 'error');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origHtml;
        }
    }

    // ================================================================
    // 📄 PDF MANAGEMENT MODAL
    // ================================================================
    function openPdfModal() {
        const modal = document.getElementById('pdf-modal-overlay');
        if (modal) modal.classList.add('show');
    }

    function closePdfModal() {
        const modal = document.getElementById('pdf-modal-overlay');
        if (modal) modal.classList.remove('show');
        const progressContainer = document.getElementById('pdf-extract-progress');
        if (progressContainer) progressContainer.style.display = 'none';
    }

    // ================================================================
    // 🌐 BROWSER-SIDE PDF.JS EXTRACTOR (100% Zero-Server-Dependency)
    // Extracts HD WebP calendar sheets directly in the admin's browser
    // ================================================================
    async function renderPdfPageToBlob(pdfDoc, pageNumber, scale = 2.0) {
        const page = await pdfDoc.getPage(pageNumber);
        const viewport = page.getViewport({ scale });
        const canvas = document.createElement('canvas');
        canvas.width = Math.floor(viewport.width);
        canvas.height = Math.floor(viewport.height);
        const ctx = canvas.getContext('2d', { alpha: false });

        // Crisp white background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        await page.render({ canvasContext: ctx, viewport: viewport }).promise;

        return new Promise((resolve, reject) => {
            if (canvas.toBlob) {
                canvas.toBlob((blob) => {
                    if (blob && blob.size > 0) {
                        resolve({ blob, ext: 'webp' });
                    } else {
                        canvas.toBlob((jpegBlob) => {
                            if (jpegBlob) {
                                resolve({ blob: jpegBlob, ext: 'jpg' });
                            } else {
                                reject(new Error('Canvas blob generation failed'));
                            }
                        }, 'image/jpeg', 0.90);
                    }
                }, 'image/webp', 0.88);
            } else {
                try {
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.90);
                    const byteString = atob(dataUrl.split(',')[1]);
                    const mimeString = dataUrl.split(',')[0].split(':')[1].split(';')[0];
                    const ab = new ArrayBuffer(byteString.length);
                    const ia = new Uint8Array(ab);
                    for (let i = 0; i < byteString.length; i++) {
                        ia[i] = byteString.charCodeAt(i);
                    }
                    resolve({ blob: new Blob([ab], { type: mimeString }), ext: 'jpg' });
                } catch (e) {
                    reject(e);
                }
            }
        });
    }

    async function uploadCalendarPageBlob(target, blob, ext) {
        const formData = new FormData();
        formData.append('page_target', target);
        formData.append('page_image', blob, `${target}.${ext}`);
        if (window.getCSRFToken && window.getCSRFToken()) {
            formData.append('csrf_token', window.getCSRFToken());
        }

        const res = await fetch('api/update_calendar_page.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.error || `लक्ष्य ${target} अपलोड विफल रहा`);
        }
        return data;
    }

    async function runBrowserPdfExtraction(pdfSource) {
        if (!window.pdfjsLib) {
            throw new Error('PDF.js लाइब्रेरी लोड नहीं हो सकी। कृपया इंटरनेट कनेक्शन की जांच करें।');
        }

        const progressContainer = document.getElementById('pdf-extract-progress');
        const progressText = document.getElementById('pdf-progress-text');
        const progressPercent = document.getElementById('pdf-progress-percent');
        const progressBar = document.getElementById('pdf-progress-bar');
        const progressDetail = document.getElementById('pdf-progress-detail');

        function updateProgress(pct, text, detail) {
            if (progressContainer) progressContainer.style.display = 'block';
            if (progressPercent) progressPercent.textContent = `${Math.round(pct)}%`;
            if (progressBar) progressBar.style.width = `${Math.max(3, Math.min(100, pct))}%`;
            if (progressText && text) progressText.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
            if (progressDetail && detail) progressDetail.textContent = detail;
        }

        try {
            updateProgress(5, 'PDF पढ़ी जा रही है...', 'दस्तावेज़ को मेमोरी में लोड किया जा रहा है...');

            let loadingTask;
            if (pdfSource instanceof File) {
                const arrayBuffer = await pdfSource.arrayBuffer();
                loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
            } else if (pdfSource instanceof ArrayBuffer || pdfSource instanceof Uint8Array) {
                loadingTask = pdfjsLib.getDocument({ data: pdfSource });
            } else if (typeof pdfSource === 'string') {
                loadingTask = pdfjsLib.getDocument(pdfSource);
            } else {
                throw new Error('अमान्य PDF स्रोत।');
            }

            const pdfDoc = await loadingTask.promise;
            const numPages = pdfDoc.numPages;
            console.log(`[Browser PDF Extractor] Total pages: ${numPages}`);

            // Check user-selected layout format or auto-detect
            const formatSelect = document.getElementById('pdf-format-select');
            const formatMode = formatSelect ? formatSelect.value : 'sanatan-14';

            let coverPage = null;
            let samitiPage = null;
            let monthPageMap = {}; // Maps monthNumber 1..12 -> PDF pageNumber (1-based)

            if (formatMode === 'sanatan-14' || (formatMode === 'auto' && numPages === 14)) {
                // 🕉️ Sanatan Panchang 14-page layout:
                // Page 1: Cover (सनातन दिनदर्शिका)
                // Page 2: Committee & Choughadiya table (समिति पृष्ठ)
                // Page 3: November 2026 (Month 11)
                // Page 4: December 2026 (Month 12)
                // Page 5: January 2027 (Month 1)
                // Page 6: February 2027 (Month 2)
                // Page 7: March 2027 (Month 3)
                // Page 8: April 2027 (Month 4)
                // Page 9: May 2027 (Month 5)
                // Page 10: June 2027 (Month 6)
                // Page 11: July 2027 (Month 7)
                // Page 12: August 2027 (Month 8)
                // Page 13: September 2027 (Month 9)
                // Page 14: October 2027 (Month 10)
                coverPage = 1;
                samitiPage = 2;
                monthPageMap = {
                    1: 5,   // Jan -> Page 5
                    2: 6,   // Feb -> Page 6
                    3: 7,   // Mar -> Page 7
                    4: 8,   // Apr -> Page 8
                    5: 9,   // May -> Page 9
                    6: 10,  // Jun -> Page 10
                    7: 11,  // Jul -> Page 11
                    8: 12,  // Aug -> Page 12
                    9: 13,  // Sep -> Page 13
                    10: 14, // Oct -> Page 14
                    11: 3,  // Nov -> Page 3
                    12: 4   // Dec -> Page 4
                };
            } else if (formatMode === 'sanatan-28' || (formatMode === 'auto' && numPages === 28)) {
                // 🕉️ 28-page Double-Sided Sanatan Panchang layout
                coverPage = 1;
                monthPageMap = {
                    1: 9,   // Jan -> Page 9
                    2: 11,  // Feb -> Page 11
                    3: 13,  // Mar -> Page 13
                    4: 15,  // Apr -> Page 15
                    5: 17,  // May -> Page 17
                    6: 19,  // Jun -> Page 19
                    7: 21,  // Jul -> Page 21
                    8: 23,  // Aug -> Page 23
                    9: 25,  // Sep -> Page 25
                    10: 27, // Oct -> Page 27
                    11: 5,  // Nov -> Page 5
                    12: 7   // Dec -> Page 7
                };
            } else if (formatMode === 'cover-13' || (formatMode === 'auto' && numPages === 13)) {
                coverPage = 1;
                for (let m = 1; m <= 12; m++) monthPageMap[m] = m + 1;
            } else if (formatMode === 'standard-12' || (formatMode === 'auto' && numPages === 12)) {
                for (let m = 1; m <= 12; m++) monthPageMap[m] = m;
            } else if (numPages >= 24) {
                // Alternate pages (1, 3, 5...)
                for (let m = 1; m <= 12; m++) monthPageMap[m] = 1 + (m - 1) * 2;
            } else {
                for (let m = 1; m <= Math.min(12, numPages); m++) monthPageMap[m] = m;
            }

            const totalTasks = (coverPage ? 1 : 0) + (samitiPage ? 1 : 0) + Object.keys(monthPageMap).length;
            let completedTasks = 0;

            // 1. Extract Cover if applicable
            if (coverPage && coverPage <= numPages) {
                updateProgress(
                    (completedTasks / totalTasks) * 90 + 5,
                    'मुखपृष्ठ तैयार हो रहा है...',
                    `PDF पृष्ठ ${coverPage} (सनातन दिनदर्शिका मुखपृष्ठ) को WebP में बदला जा रहा है...`
                );
                const { blob, ext } = await renderPdfPageToBlob(pdfDoc, coverPage);
                await uploadCalendarPageBlob('cover', blob, ext);
                completedTasks++;

                const coverEl = document.getElementById('img-preview-cover');
                if (coverEl) coverEl.src = `uploads/calendar/cover.webp?v=${Date.now()}`;
            }

            // 2. Extract Samiti page if applicable
            if (samitiPage && samitiPage <= numPages) {
                updateProgress(
                    (completedTasks / totalTasks) * 90 + 5,
                    'समिति पृष्ठ तैयार हो रहा है...',
                    `PDF पृष्ठ ${samitiPage} (कार्यकारिणी व सल्लागार मंडळ) को WebP में बदला जा रहा है...`
                );
                const { blob, ext } = await renderPdfPageToBlob(pdfDoc, samitiPage);
                await uploadCalendarPageBlob('samiti', blob, ext);
                completedTasks++;

                const samitiEl = document.getElementById('img-preview-samiti');
                if (samitiEl) samitiEl.src = `uploads/calendar/samiti.webp?v=${Date.now()}`;
            }

            const monthNames = [
                '', 'जानेवारी', 'फेब्रुवारी', 'मार्च', 'एप्रिल', 'मे', 'जून',
                'जुलै', 'ऑगस्ट', 'सप्टेंबर', 'ऑक्टोबर', 'नोव्हेंबर', 'डिसेंबर'
            ];

            // 2. Extract & upload all 12 month pages according to monthPageMap
            for (let m = 1; m <= 12; m++) {
                const pageNum = monthPageMap[m];
                if (!pageNum || pageNum > numPages) continue;

                const monthName = monthNames[m] || `माह ${m}`;

                updateProgress(
                    (completedTasks / totalTasks) * 90 + 5,
                    `माह ${m}/12 (${monthName}) तैयार हो रहा है...`,
                    `PDF पृष्ठ ${pageNum} (${monthName}) को HD WebP में रेंडर किया जा रहा है...`
                );

                const { blob, ext } = await renderPdfPageToBlob(pdfDoc, pageNum);
                await uploadCalendarPageBlob(String(m), blob, ext);
                completedTasks++;

                // Instantly update thumbnail in background
                const timestamp = Date.now();
                const imgEl = document.getElementById(`img-preview-${m}`);
                if (imgEl) imgEl.src = `uploads/calendar/pages/${m}.webp?v=${timestamp}`;
            }

            updateProgress(100, 'संपूर्ण!', 'सभी १२ पृष्ठ सफलतापूर्वक तैयार व अपलोड हो गए!');
            showToast('सभी १२ माह पृष्ठ ब्राउज़र द्वारा सफलतापूर्वक तैयार व अपलोड कर दिए गए!', 'success');

            setTimeout(() => {
                if (progressContainer) progressContainer.style.display = 'none';
                closePdfModal();
                refreshAllMonthImages();
            }, 1200);

            return true;
        } catch (err) {
            console.error('[Browser PDF Extractor Error]:', err);
            if (progressText) {
                progressText.innerHTML = `<i class="fas fa-exclamation-triangle" style="color:var(--red,#d32f2f);"></i> त्रुटि हुई`;
            }
            if (progressDetail) {
                progressDetail.textContent = err.message;
            }
            showToast(`एक्सट्रैक्शन त्रुटि: ${err.message}`, 'error');
            throw err;
        }
    }

    async function handlePdfUpload(e) {
        e.preventDefault();
        const fileInput = document.getElementById('pdf-file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('कृपया एक PDF फ़ाइल चुनें।', 'error');
            return;
        }

        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('calendar_pdf', file);

        const btn = document.getElementById('btn-submit-pdf-upload');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PDF अपलोड हो रही है...';

        try {
            const res = await fetch('api/upload_calendar_pdf.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.error || 'PDF अपलोड विफल रहा।');
            }

            // If server-side Python succeeded
            if (data.extracted) {
                showToast(data.message || 'PDF अपलोड हो गई और १२ पृष्ठ तैयार हो गए!', 'success');
                closePdfModal();
                refreshAllMonthImages();
            } else {
                // Server has no Python/PyMuPDF -> Auto extract in browser!
                showToast('PDF अपलोड हो गई। अब ब्राउज़र द्वारा 12 माह पृष्ठ निकाले जा रहे हैं...', 'info');
                await runBrowserPdfExtraction(file);
            }
        } catch (err) {
            console.error('PDF upload error:', err);
            showToast(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }

    async function handleReextractPdf() {
        const btn = document.getElementById('btn-reextract-pdf');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> १२ माह पृष्ठ निकाले जा रहे हैं...';

        try {
            // First attempt server-side extraction
            const res = await fetch('api/reextract_calendar.php', { method: 'POST' });
            const data = await res.json();

            if (data.success) {
                showToast(data.message || 'सभी १२ पृष्ठ तैयार हो गए!', 'success');
                closePdfModal();
                refreshAllMonthImages();
                return;
            }

            // Server does not have Python or PyMuPDF -> Seamlessly fallback to browser extractor!
            console.log('Server has no Python/PyMuPDF, falling back to browser extraction...');
            showToast('सर्वर पर Python नहीं है — ब्राउज़र द्वारा 12 माह पृष्ठ निकाले जा रहे हैं...', 'info');

            const progressContainer = document.getElementById('pdf-extract-progress');
            const progressText = document.getElementById('pdf-progress-text');
            const progressPercent = document.getElementById('pdf-progress-percent');
            const progressBar = document.getElementById('pdf-progress-bar');
            const progressDetail = document.getElementById('pdf-progress-detail');

            if (progressContainer) progressContainer.style.display = 'block';
            if (progressText) progressText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PDF डाउनलोड हो रही है...';
            if (progressPercent) progressPercent.textContent = '3%';
            if (progressBar) progressBar.style.width = '3%';
            if (progressDetail) progressDetail.textContent = 'सर्वर से PDF फ़ाइल मेमोरी में लोड की जा रही है, कृपया प्रतीक्षा करें...';

            const pdfFetch = await fetch(`uploads/calendar/calendar.pdf?v=${Date.now()}`);
            if (!pdfFetch.ok) {
                throw new Error('uploads/calendar/calendar.pdf फ़ाइल नहीं मिली। कृपया पहले नई PDF अपलोड करें।');
            }
            const arrayBuffer = await pdfFetch.arrayBuffer();
            await runBrowserPdfExtraction(arrayBuffer);

        } catch (err) {
            console.error('Reextract error:', err);
            showToast(err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }

    function refreshAllMonthImages() {
        const timestamp = Date.now();
        const coverImg = document.getElementById('img-preview-cover');
        if (coverImg) coverImg.src = `uploads/calendar/cover.webp?v=${timestamp}`;

        for (let m = 1; m <= 12; m++) {
            const img = document.getElementById(`img-preview-${m}`);
            if (img) img.src = `uploads/calendar/pages/${m}.webp?v=${timestamp}`;
        }
    }

    // ================================================================
    // 🔍 LIGHTBOX (FULL VIEW)
    // ================================================================
    window.openLightbox = function (src, title) {
        const modal = document.getElementById('lightbox-overlay');
        const img = document.getElementById('lightbox-img');
        const titleEl = document.getElementById('lightbox-title');

        if (!modal || !img) return;

        img.src = `${src}?v=${Date.now()}`;
        if (titleEl) titleEl.innerHTML = `<i class="fas fa-eye"></i> ${title}`;
        modal.classList.add('show');
    };

    function closeLightbox() {
        const modal = document.getElementById('lightbox-overlay');
        if (modal) modal.classList.remove('show');
    }

    // ================================================================
    // 🚀 INITIALIZATION & EVENT LISTENERS
    // ================================================================
    document.addEventListener('DOMContentLoaded', async () => {
        // 🔐 Client-side Super Admin check
        try {
            const authRes = await fetch('api/check_auth.php?_t=' + Date.now(), { cache: 'no-store' });
            const authData = await authRes.json();
            if (!authData.isAdmin || !authData.isSuperAdmin) {
                alert('⚠️ केवल सुपर एडमिन ही दिनदर्शिका प्रबंधित कर सकते हैं।');
                window.location.href = 'admin-dashboard.php';
                return;
            }
        } catch (e) {
            console.error('Calendar auth check error:', e);
        }

        // Replace modal listeners
        document.getElementById('btn-close-replace-modal')?.addEventListener('click', closeReplaceModal);
        document.getElementById('btn-cancel-replace-modal')?.addEventListener('click', closeReplaceModal);
        document.getElementById('replace-file-input')?.addEventListener('change', handleFileSelection);
        document.getElementById('replace-form')?.addEventListener('submit', handleReplaceSubmit);

        // PDF modal listeners
        document.getElementById('btn-open-pdf-modal')?.addEventListener('click', openPdfModal);
        document.getElementById('btn-close-pdf-modal')?.addEventListener('click', closePdfModal);
        document.getElementById('pdf-upload-form')?.addEventListener('submit', handlePdfUpload);
        document.getElementById('btn-reextract-pdf')?.addEventListener('click', handleReextractPdf);

        // Lightbox modal listeners
        document.getElementById('btn-close-lightbox')?.addEventListener('click', closeLightbox);
        document.getElementById('lightbox-overlay')?.addEventListener('click', (e) => {
            if (e.target.id === 'lightbox-overlay') closeLightbox();
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeReplaceModal();
                closePdfModal();
                closeLightbox();
            }
        });

        // Floating scroll-to-top button
        const scrollBtn = document.getElementById('scroll-top-btn');
        if (scrollBtn) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    scrollBtn.style.display = 'flex';
                } else {
                    scrollBtn.style.display = 'none';
                }
            }, { passive: true });

            scrollBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });

})();
