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
    }

    async function handlePdfUpload(e) {
        e.preventDefault();
        const fileInput = document.getElementById('pdf-file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('कृपया एक PDF फ़ाइल चुनें।', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('calendar_pdf', fileInput.files[0]);

        const btn = document.getElementById('btn-submit-pdf-upload');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PDF अपलोड एवं एक्सट्रैक्ट हो रही है...';

        try {
            const res = await fetch('api/upload_calendar_pdf.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message || 'PDF सफलतापूर्वक अपलोड हो गई!', 'success');
                closePdfModal();
                // Refresh all 12 month images
                refreshAllMonthImages();
            } else {
                throw new Error(data.error || 'PDF अपलोड विफल रहा।');
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
            const res = await fetch('api/reextract_calendar.php', { method: 'POST' });
            const data = await res.json();

            if (data.success) {
                showToast(data.message || 'सभी १२ पृष्ठ तैयार हो गए!', 'success');
                refreshAllMonthImages();
            } else {
                throw new Error(data.error || 'एक्सट्रैक्शन विफल रहा।');
            }
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
    document.addEventListener('DOMContentLoaded', () => {
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
