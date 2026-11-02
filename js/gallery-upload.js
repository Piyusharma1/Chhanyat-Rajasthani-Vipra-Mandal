// ============================================
// 📸 GALLERY UPLOAD MODULE (PHP Session Based)
// ============================================
// ✅ Auth check via api/check_auth.php (same as committee module)
// ✅ Lazy loading - images load only when album is opened
// ✅ Toast notifications for feedback
// ✅ Image upload with progress tracking

let selectedFiles = [];
let allAlbums = [];
let currentAlbumImages = [];
let currentLightboxIndex = 0;

// =========================================
// 🔐 AUTH CHECK (Same as committee module)
// =========================================
let currentAdminAuth = null;

async function checkPageAuth() {
    try {
        const response = await fetch('api/check_auth.php?_t=' + Date.now(), {
            credentials: 'include',
            cache: 'no-store'
        });
        const result = await response.json();
        if (!result.isAdmin) {
            window.location.href = 'admin-login.php';
            return false;
        }
        currentAdminAuth = result;
        return true;
    } catch (error) {
        console.error('🔒 Auth check failed:', error);
        window.location.href = 'admin-login.php';
        return false;
    }
}

// =========================================
// 🔔 TOAST NOTIFICATION (Themed)
// =========================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) {
        // Create toast container if not exists
        const newContainer = document.createElement('div');
        newContainer.id = 'toast-container';
        newContainer.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(newContainer);
    }
    const toastContainer = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check' : type === 'info' ? 'fa-info-circle' : 'fa-times';
    toast.innerHTML = `
        <div class="toast-icon"><i class="fas ${icon}"></i></div>
        <div><strong>${type === 'success' ? 'सफल' : type === 'info' ? 'सूचना' : 'त्रुटि'}</strong><br><small>${message}</small></div>
    `;
    toast.style.cssText = `
        display: flex; align-items: center; gap: 12px; padding: 15px; margin-bottom: 10px; 
        border-radius: 8px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
        background: ${type === 'success' ? 'var(--maroon, #7B1A1A)' : type === 'info' ? 'var(--saffron, #FF6B00)' : '#c62828'}; 
        transform: translateX(120%); transition: transform 0.3s ease;
        min-width: 280px; max-width: 400px;
    `;
    toastContainer.appendChild(toast);
    
    setTimeout(() => toast.style.transform = 'translateX(0)', 10);
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// =========================================
// 📸 SINGLE IMAGE UPLOAD (for future use)
// =========================================
async function uploadSingleImage(fileInputId) {
    const fileInput = document.getElementById(fileInputId);
    if (!fileInput || !fileInput.files[0]) return null;
    const file = fileInput.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showToast('फ़ाइल 5MB से बड़ी है।', 'error');
        return null;
    }
    
    const formData = new FormData();
    formData.append('image', file);
    try {
        const response = await fetch('api/upload-image.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) return result.url;
        else {
            showToast(result.message || 'छवि अपलोड त्रुटि।', 'error');
            return null;
        }
    } catch (error) {
        showToast('छवि अपलोड त्रुटि: ' + error.message, 'error');
        return null;
    }
}

// =========================================
// 📤 UPLOAD SECTION SETUP
// =========================================
function setupUploadSection() {
    // Load branches for gallery
    loadBranchesForGallery();

    // Existing album selection
    const existingAlbums = document.getElementById('existing-albums');
    const albumInput = document.getElementById('album-name');
    if (existingAlbums && albumInput) {
        existingAlbums.addEventListener('change', (e) => {
            if (e.target.value) {
                albumInput.disabled = true;
                albumInput.style.opacity = '0.6';
                const selectedText = e.target.selectedOptions[0].textContent.split(' (')[0];
                albumInput.placeholder = `📁 "${selectedText}" में जोड़ा जाएगा`;
            } else {
                albumInput.disabled = false;
                albumInput.style.opacity = '1';
                albumInput.placeholder = 'उदा: वार्षिक सामुदायिक मिलन समारोह 2025';
            }
        });
    }

    // Drop zone
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');

    if (dropZone && fileInput) {
        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    // Clear button
    const clearBtn = document.getElementById('clear-btn');
    if (clearBtn) clearBtn.addEventListener('click', resetGalleryForm);

    // Form submit
    const form = document.getElementById('gallery-upload-form');
    if (form) form.addEventListener('submit', handleGalleryUpload);
}

// =========================================
// 📁 HANDLE SELECTED FILES
// =========================================
function handleFiles(files) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const maxSize = 5 * 1024 * 1024;

    Array.from(files).forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            showToast(`❌ ${file.name} - Invalid file type`, 'error');
            return;
        }
        if (file.size > maxSize) {
            showToast(`❌ ${file.name} - Too large (max 5MB)`, 'error');
            return;
        }
        selectedFiles.push(file);
    });

    renderPreviews();
}

// =========================================
// 🖼️ RENDER PREVIEWS
// =========================================
function renderPreviews() {
    const container = document.getElementById('preview-container');
    const fileInfo = document.getElementById('file-info');
    if (!container) return;

    if (selectedFiles.length === 0) {
        container.classList.remove('active');
        if (fileInfo) fileInfo.classList.remove('active');
        return;
    }

    container.classList.add('active');
    if (fileInfo) fileInfo.classList.add('active');
    container.innerHTML = '';

    let totalSize = 0;
    selectedFiles.forEach((file, index) => {
        totalSize += file.size;
        const wrapper = document.createElement('div');
        wrapper.className = 'preview-item';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        img.onload = () => URL.revokeObjectURL(img.src);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'preview-remove';
        removeBtn.innerHTML = '×';
        removeBtn.title = 'हटाएं';
        removeBtn.onclick = () => {
            selectedFiles.splice(index, 1);
            renderPreviews();
        };

        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        container.appendChild(wrapper);
    });

    const countEl = document.getElementById('file-count');
    const sizeEl = document.getElementById('total-size');
    if (countEl) countEl.textContent = selectedFiles.length;
    if (sizeEl) sizeEl.textContent = formatFileSize(totalSize);
}

// =========================================
// 📏 FORMAT FILE SIZE
// =========================================
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

// =========================================
// 🔄 RESET FORM
// =========================================
function resetGalleryForm() {
    selectedFiles = [];
    const form = document.getElementById('gallery-upload-form');
    if (form) form.reset();

    const container = document.getElementById('preview-container');
    if (container) { container.innerHTML = ''; container.classList.remove('active'); }

    const fileInfo = document.getElementById('file-info');
    if (fileInfo) fileInfo.classList.remove('active');

    const progressWrapper = document.getElementById('progress-wrapper');
    if (progressWrapper) progressWrapper.classList.remove('active');

    const albumInput = document.getElementById('album-name');
    if (albumInput) {
        albumInput.disabled = false;
        albumInput.style.opacity = '1';
        albumInput.placeholder = 'उदा: वार्षिक सामुदायिक मिलन समारोह 2025';
    }

    const uploadBtn = document.getElementById('upload-btn');
    if (uploadBtn) {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload"></i> अपलोड करें';
    }
}

// =========================================
// 📤 HANDLE GALLERY UPLOAD (XHR with Progress)
// =========================================
async function handleGalleryUpload(e) {
    e.preventDefault();

    const albumInput = document.getElementById('album-name');
    const existingAlbums = document.getElementById('existing-albums');
    const uploadBtn = document.getElementById('upload-btn');
    const progressWrapper = document.getElementById('progress-wrapper');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');

    let albumName = '';
    if (existingAlbums && existingAlbums.value) {
        albumName = existingAlbums.value;
    } else if (albumInput && albumInput.value.trim()) {
        albumName = albumInput.value.trim();
    } else {
        showToast('⚠️ कृपया Album का नाम चुनें या नया नाम भरें।', 'error');
        return;
    }

    if (selectedFiles.length === 0) {
        showToast('⚠️ कृपया कम से कम एक फोटो चुनें।', 'error');
        return;
    }

    const branchSelect = document.getElementById('gallery-branch');
    const branch = branchSelect ? branchSelect.value : 'ALL';

    const formData = new FormData();
    formData.append('album', albumName);
    formData.append('branch', branch);
    selectedFiles.forEach(file => formData.append('images[]', file));

    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> अपलोड हो रहा है...';
    }
    if (progressWrapper) progressWrapper.classList.add('active');
    if (progressBar) { progressBar.style.width = '0%'; progressBar.textContent = '0%'; }

    try {
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                if (progressBar) {
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                }
                if (progressText) {
                    progressText.textContent = `अपलोड हो रहा है... ${formatFileSize(e.loaded)} / ${formatFileSize(e.total)}`;
                }
            }
        });

        xhr.addEventListener('load', () => {
            // 🔐 Handle auth errors during upload
            if (xhr.status === 401 || xhr.status === 403) {
                showToast('❌ Session expired. Please login again.', 'error');
                setTimeout(() => {
                    window.location.href = 'admin-login.php';
                }, 1500);
                return;
            }

            if (xhr.status === 200) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        showToast(
                            `✅ ${result.uploaded_count || selectedFiles.length} फोटो सफलतापूर्वक अपलोड हुईं! Album: ${result.album_title || result.album || albumName}`,
                            'success'
                        );
                        setTimeout(() => {
                            resetGalleryForm();
                            loadAlbumsList();
                        }, 1000);
                    } else {
                        showToast(`❌ Error: ${result.error || 'Upload failed'}`, 'error');
                    }
                } catch (err) {
                    showToast('❌ Invalid response from server', 'error');
                    console.error('Parse error:', err, 'Response:', xhr.responseText);
                }
            } else {
                showToast(`❌ Upload failed (HTTP ${xhr.status})`, 'error');
            }
        });

        xhr.addEventListener('error', () => {
            showToast('❌ Network error. Please check your connection.', 'error');
        });

        xhr.open('POST', 'api/upload-gallery.php');
        xhr.withCredentials = true;
        xhr.send(formData);

    } catch (error) {
        console.error('Upload error:', error);
        showToast('❌ Upload failed: ' + error.message, 'error');
    } finally {
        if (uploadBtn) {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> अपलोड करें';
        }
    }
}

// =========================================
// 📁 LOAD ALBUMS LIST (Lazy Loading Covers)
// =========================================
async function loadAlbumsList() {
    const grid = document.getElementById('albums-grid');
    const loading = document.getElementById('albums-loading');
    const empty = document.getElementById('albums-empty');

    if (!grid) return;

    try {
        const response = await fetch('api/gallery.php?admin_view=true', {
            credentials: 'include'
        });

        // 🔐 Handle auth errors
        if (response.status === 401 || response.status === 403) {
            window.location.href = 'admin-login.php';
            return;
        }

        const data = await response.json();

        if (loading) loading.style.display = 'none';

        if (!data.albums || data.albums.length === 0) {
            if (empty) empty.style.display = 'block';
            grid.innerHTML = '';
            allAlbums = [];
            updateAlbumSelect([]);
            return;
        }

        allAlbums = data.albums;
        if (empty) empty.style.display = 'none';
        grid.innerHTML = '';

        updateAlbumSelect(data.albums);

        data.albums.forEach((album, index) => {
            const card = createAlbumCard(album, index);
            grid.appendChild(card);
        });

        setupLazyAlbumCovers();

    } catch (error) {
        console.error('Failed to load albums:', error);
        if (loading) loading.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#c62828;"></i><p>Albums लोड नहीं हो पाए।</p>';
    }
}

function updateAlbumSelect(albums) {
    const select = document.getElementById('existing-albums');
    if (!select) return;

    select.innerHTML = '<option value="">-- नया Album बनाएं (ऊपर नाम भरें) --</option>';
    albums.forEach(album => {
        const option = document.createElement('option');
        option.value = album.folder;
        option.textContent = `${album.title} (${album.count} photos)`;
        select.appendChild(option);
    });
}

function createAlbumCard(album, index) {
    const card = document.createElement('div');
    card.className = 'album-card';
    card.style.position = 'relative';
    card.dataset.albumFolder = album.folder;
    card.dataset.albumIndex = index;

    const albumBranch = album.branch || 'ALL';
    const branchLabel = albumBranch === 'ALL' ? 'सभी समाज (Central)' : albumBranch;

    const isSuperAdmin = currentAdminAuth ? currentAdminAuth.isSuperAdmin : true;
    const userBranch = currentAdminAuth ? currentAdminAuth.branch : 'ALL';
    const canDeleteAlbum = isSuperAdmin || (albumBranch && albumBranch === userBranch);

    const deleteBtnHtml = canDeleteAlbum ? `
        <button class="album-delete-btn" title="Album हटाएं" data-folder="${album.folder}">
            <i class="fas fa-trash"></i>
        </button>
    ` : '';

    card.innerHTML = `
        <div class="album-cover">
            <img data-src="${album.cover || album.thumbnail || ''}" alt="${album.title}" loading="lazy">
            <div class="lazy-placeholder">
                <i class="fas fa-image"></i>
                <span>Loading...</span>
            </div>
            ${deleteBtnHtml}
            <span class="album-badge"><i class="fas fa-images"></i> ${album.count || 0}</span>
            <span class="album-badge" style="left: 10px; right: auto; background: var(--maroon, #7B1A1A); color: white;">
                <i class="fas fa-sitemap"></i> ${branchLabel}
            </span>
        </div>
        <div class="album-info">
            <h3>${album.title || album.folder}</h3>
            <p>${album.date || ''} • ${album.count || 0} photos</p>
        </div>
    `;

    card.addEventListener('click', (e) => {
        if (e.target.closest('.album-delete-btn')) return;
        openAlbum(album);
    });

    const deleteBtn = card.querySelector('.album-delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            deleteAlbum(album.folder, album.title || album.folder);
        });
    }

    return card;
}

// =========================================
// 👁️ LAZY LOAD ALBUM COVERS
// =========================================
function setupLazyAlbumCovers() {
    const coverImages = document.querySelectorAll('.album-cover img[data-src]');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    if (src) {
                        img.src = src;
                        img.onload = () => img.classList.add('loaded');
                        img.removeAttribute('data-src');
                    }
                    obs.unobserve(img);
                }
            });
        }, {
            rootMargin: '100px',
            threshold: 0.1
        });

        coverImages.forEach(img => observer.observe(img));
    } else {
        coverImages.forEach(img => {
            img.src = img.dataset.src;
            img.onload = () => img.classList.add('loaded');
            img.removeAttribute('data-src');
        });
    }
}

// =========================================
// 🖼️ ALBUM DETAIL VIEW (Images load ONLY on open)
// =========================================
function setupAlbumDetailNavigation() {
    const backBtn = document.getElementById('back-to-albums-btn');
    if (backBtn) {
        backBtn.addEventListener('click', closeAlbumDetail);
    }
}

async function openAlbum(album) {
    const listView = document.getElementById('albums-list-view');
    const detailView = document.getElementById('album-detail-view');
    const detailTitle = document.getElementById('detail-album-title');
    const detailLoading = document.getElementById('detail-loading');
    const imagesGrid = document.getElementById('album-images-grid');

    if (!detailView || !listView) return;

    listView.style.display = 'none';
    detailView.classList.add('active');

    if (detailTitle) {
        detailTitle.querySelector('span').textContent = album.title || album.folder;
    }

    if (detailLoading) detailLoading.style.display = 'block';
    if (imagesGrid) imagesGrid.innerHTML = '';
    currentAlbumImages = [];

    window.scrollTo({ top: 0, behavior: 'smooth' });

    try {
        const response = await fetch(`api/gallery.php?album=${encodeURIComponent(album.folder)}&admin_view=true`, {
            credentials: 'include'
        });

        if (response.status === 401 || response.status === 403) {
            window.location.href = 'admin-login.php';
            return;
        }

        const data = await response.json();

        if (detailLoading) detailLoading.style.display = 'none';

        if (data.images && data.images.length > 0) {
            currentAlbumImages = data.images;
            renderAlbumImages(data.images, album.folder, album.branch || 'ALL');
        } else {
            if (imagesGrid) {
                imagesGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-image"></i>
                        <p>इस Album में कोई photo नहीं है।</p>
                    </div>
                `;
            }
        }

    } catch (error) {
        console.error('Failed to load album images:', error);
        if (detailLoading) detailLoading.style.display = 'none';
        if (imagesGrid) {
            imagesGrid.innerHTML = `
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-exclamation-triangle" style="color:#c62828;"></i>
                    <p>Photos लोड नहीं हो पाईं।</p>
                </div>
            `;
        }
    }
}

function renderAlbumImages(images, albumFolder, albumBranch = 'ALL') {
    const grid = document.getElementById('album-images-grid');
    if (!grid) return;

    grid.innerHTML = '';
    currentAlbumImages = images || [];

    const isSuperAdmin = currentAdminAuth ? currentAdminAuth.isSuperAdmin : true;
    const userBranch = currentAdminAuth ? currentAdminAuth.branch : 'ALL';
    const canDelete = isSuperAdmin || (albumBranch && albumBranch === userBranch);

    images.forEach((image, index) => {
        const item = document.createElement('div');
        item.className = 'album-image-item';

        const imgSrc = typeof image === 'string' ? image : (image.url || image.src || '');

        const deleteBtnHtml = canDelete ? `
            <button class="image-delete-btn" 
                    title="Delete" 
                    data-src="${imgSrc}" 
                    data-folder="${albumFolder}">
                <i class="fas fa-trash"></i>
            </button>
        ` : '';

        item.innerHTML = `
            <img src="${imgSrc}" alt="Photo ${index + 1}" loading="lazy">
            ${deleteBtnHtml}
        `;

        item.addEventListener('click', (e) => {
            if (e.target.closest('.image-delete-btn')) return;
            openLightbox(index);
        });

        const deleteBtn = item.querySelector('.image-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteImage(imgSrc, albumFolder);
            });
        }

        grid.appendChild(item);
    });
}

function setupLazyAlbumImages() {
    const images = document.querySelectorAll('.album-image-item img[data-src]');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    if (src) {
                        img.src = src;
                        img.onload = () => img.classList.add('loaded');
                        img.onerror = () => {
                            img.parentElement.innerHTML = '<div class="lazy-placeholder"><i class="fas fa-broken-image" style="color:#c62828;"></i></div>';
                        };
                        img.removeAttribute('data-src');
                    }
                    obs.unobserve(img);
                }
            });
        }, {
            rootMargin: '200px',
            threshold: 0.05
        });

        images.forEach(img => observer.observe(img));
    } else {
        images.forEach(img => {
            img.src = img.dataset.src;
            img.onload = () => img.classList.add('loaded');
            img.removeAttribute('data-src');
        });
    }
}

function closeAlbumDetail() {
    const listView = document.getElementById('albums-list-view');
    const detailView = document.getElementById('album-detail-view');

    if (detailView) detailView.classList.remove('active');
    if (listView) listView.style.display = 'block';

    currentAlbumImages = [];
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// =========================================
// 🗑️ DELETE IMAGE (Global)
// =========================================
window.deleteImage = async function(imageSrc, albumFolder) {
    if (!confirm('⚠️ क्या आप यह photo हटाना चाहते हैं?')) return;

    try {
        const response = await fetch('api/delete-gallery-image.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                folder: albumFolder,
                image: imageSrc
            })
        });

        if (response.status === 401 || response.status === 403) {
            showToast('❌ Session expired. Please login again.', 'error');
            setTimeout(() => {
                window.location.href = 'admin-login.php';
            }, 1500);
            return;
        }

        const result = await response.json();
        if (result.success) {
            showToast('📸 Photo हटा दी गई।', 'success');
            const grid = document.getElementById('album-images-grid');
            const items = grid.querySelectorAll('.album-image-item');
            items.forEach(item => {
                const img = item.querySelector('img');
                const src = img?.src || img?.dataset?.src || '';
                if (src.includes(imageSrc.split('/').pop())) {
                    item.style.transition = 'opacity 0.3s, transform 0.3s';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => item.remove(), 300);
                }
            });
            loadAlbumsList();
        } else {
            showToast('❌ Photo हटाने में error: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('❌ Network error: ' + error.message, 'error');
    }
};

// =========================================
// 🗑️ DELETE ALBUM (Global)
// =========================================
window.deleteAlbum = async function(albumFolder, albumTitle = '') {
    if (!confirm(`⚠️ क्या आप "${albumTitle}" Album और इसकी सभी Photos हटाना चाहते हैं?`)) return;

    try {
        const response = await fetch('api/delete-album.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ folder: albumFolder })
        });

        if (response.status === 401 || response.status === 403) {
            const err = await response.json().catch(() => ({}));
            showToast('❌ ' + (err.error || 'Unauthorized action'), 'error');
            return;
        }

        const result = await response.json();
        if (result.success) {
            showToast('🗑️ Album सफलतापूर्वक हटा दिया गया।', 'success');
            loadAlbumsList();
        } else {
            showToast('❌ Album हटाने में error: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('❌ Network error: ' + error.message, 'error');
    }
};

// =========================================
// 🔍 LIGHTBOX
// =========================================
function setupLightbox() {
    const lightbox = document.getElementById('lightbox');
    const closeBtn = document.getElementById('lightbox-close');
    const prevBtn = document.getElementById('lightbox-prev');
    const nextBtn = document.getElementById('lightbox-next');

    if (closeBtn) closeBtn.addEventListener('click', (e) => { e.stopPropagation(); closeLightbox(); });
    if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); navigateLightbox(-1); });
    if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); navigateLightbox(1); });

    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target.id === 'lightbox') closeLightbox();
        });
    }

    document.addEventListener('keydown', (e) => {
        const lb = document.getElementById('lightbox');
        if (!lb || (lb.style.display !== 'flex' && !lb.classList.contains('active'))) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    });
}

function openLightbox(input) {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    if (!lightbox || !img) {
        console.error('Lightbox element or image element not found');
        return;
    }

    let imageSrc = '';
    if (typeof input === 'number') {
        if (!currentAlbumImages || !currentAlbumImages[input]) return;
        currentLightboxIndex = input;
        const item = currentAlbumImages[input];
        imageSrc = typeof item === 'string' ? item : (item.url || item.src || '');
    } else if (typeof input === 'string') {
        imageSrc = input;
        if (currentAlbumImages && currentAlbumImages.length > 0) {
            const idx = currentAlbumImages.findIndex(i => (typeof i === 'string' ? i : (i.url || i.src)) === input);
            if (idx !== -1) currentLightboxIndex = idx;
        }
    }

    if (!imageSrc) return;

    img.src = imageSrc;
    lightbox.classList.add('active');
    lightbox.classList.add('show');
    lightbox.style.cssText = 'display: flex !important; visibility: visible !important; opacity: 1 !important; z-index: 999999 !important;';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.classList.remove('active');
        lightbox.classList.remove('show');
        lightbox.style.cssText = 'display: none !important;';
    }
    document.body.style.overflow = '';
}

function navigateLightbox(direction) {
    if (!currentAlbumImages || currentAlbumImages.length === 0) return;
    const newIndex = currentLightboxIndex + direction;
    if (newIndex >= 0 && newIndex < currentAlbumImages.length) {
        openLightbox(newIndex);
    }
}

window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;

// =========================================
// 🚀 INITIALIZE PAGE (Same pattern as committee)
// =========================================
async function loadBranchesForGallery() {
    const select = document.getElementById('gallery-branch');
    if (!select) return;

    try {
        const response = await fetch('api/get_branches.php?all=true');
        const result = await response.json();
        const branches = result.data || [];

        select.innerHTML = '<option value="ALL">सभी समाज (ALL Central)</option>';
        branches.forEach(b => {
            if (b.branchName) {
                const opt = document.createElement('option');
                opt.value = b.branchName;
                opt.textContent = b.branchName;
                select.appendChild(opt);
            }
        });

        if (currentAdminAuth && !currentAdminAuth.isSuperAdmin && currentAdminAuth.branch) {
            select.value = currentAdminAuth.branch;
            select.disabled = true;
            select.style.backgroundColor = '#f0f0f0';
            select.title = `आप केवल ${currentAdminAuth.branch} समाज की गैलरी में फोटो अपलोड कर सकते हैं।`;
        }
    } catch (error) {
        console.error('Failed to load branches:', error);
    }
}

function initGalleryPage() {
    setupUploadSection();
    loadBranchesForGallery();
    loadAlbumsList();
    setupAlbumDetailNavigation();
    setupLightbox();
    console.log('📸 Gallery upload page initialized');
}

// 🔐 Auth check first, then init page
document.addEventListener('DOMContentLoaded', async () => {
    const isAuth = await checkPageAuth();
    if (isAuth) {
        initGalleryPage();
    }
});