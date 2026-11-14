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

            // Page mapping (1-based for PDF.js getPage)
            let coverPage = null;
            let monthPages = [];

            if (numPages >= 25) {
                coverPage = 1;
                monthPages = Array.from({ length: 12 }, (_, i) => 2 + i * 2);
            } else if (numPages >= 24) {
                monthPages = Array.from({ length: 12 }, (_, i) => 1 + i * 2);
            } else if (numPages === 13) {
                coverPage = 1;
                monthPages = Array.from({ length: 12 }, (_, i) => 2 + i);
            } else if (numPages >= 12) {
                monthPages = Array.from({ length: 12 }, (_, i) => 1 + i);
            } else {
                monthPages = Array.from({ length: numPages }, (_, i) => 1 + i);
            }

            const totalTasks = (coverPage ? 1 : 0) + monthPages.length;
            let completedTasks = 0;

            // Extract Cover if applicable
            if (coverPage && coverPage <= numPages) {
                updateProgress(
                    (completedTasks / totalTasks) * 90 + 5,
                    'मुखपृष्ठ तैयार हो रहा है...',
                    `पृष्ठ ${coverPage} को WebP में बदला जा रहा है...`
                );
                const { blob, ext } = await renderPdfPageToBlob(pdfDoc, coverPage);
                await uploadCalendarPageBlob('cover', blob, ext);
                completedTasks++;

                const coverEl = document.getElementById('img-preview-cover');
                if (coverEl) coverEl.src = `uploads/calendar/cover.webp?v=${Date.now()}`;
            }

            const monthNames = [
                'जनवरी', 'फ़रवरी', 'मार्च', 'अप्रैल', 'मई', 'जून',
                'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर', 'नवंबर', 'दिसंबर'
            ];

            // Extract & upload all 12 month pages
            for (let m = 1; m <= monthPages.length; m++) {
                const pageNum = monthPages[m - 1];
                const monthName = monthNames[m - 1] || `माह ${m}`;

                updateProgress(
                    (completedTasks / totalTasks) * 90 + 5,
                    `माह ${m}/12 (${monthName}) तैयार हो रहा है...`,
                    `PDF पृष्ठ ${pageNum} को HD WebP में बदला जा रहा है...`
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

            const pdfFetch = await fetch(`uploads/calendar/calendar.pdf?v=${Date.now()}`);
            if (!pdfFetch.ok) {
                throw new Error('uploads/calendar/calendar.pdf फ़ाइल नहीं मिली। कृपया पहले PDF अपलोड करें।');
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
