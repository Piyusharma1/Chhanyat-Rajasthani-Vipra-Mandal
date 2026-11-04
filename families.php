<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="छःन्यात राजस्थानी विप्र मंडल, लातूर से जुड़े सभी परिवारों की सूची">
    <meta name="keywords" content="Families Chhanyat Rajasthani Vipra Mandal Latur, Members, Brahmin Samaj Latur, छःन्यात राजस्थानी विप्र मंडल परिवार">
    <meta property="og:title" content="सभी परिवार - छःन्यात राजस्थानी विप्र मंडल">
    <meta property="og:description" content="छःन्यात राजस्थानी विप्र मंडल, लातूर से जुड़े सभी परिवारों की सूची">
    <meta property="og:url" content="https://chhanyatvipralatur.com/families.php">
    <meta property="og:type" content="website">
    <title>सभी परिवार - छःन्यात राजस्थानी विप्र मंडल</title>

     <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
   

</head>
<body>
    <div data-include="components/header.php"></div>

    <div class="container">
      <br>


        
        <div class="gallery-hero">
                <div class="gallery-hero-content">
                <h2> सभी परिवार</h2>
              <p class="gallery-hero-subtitle" id="gallery-total-info">सभी परिवारों की जानकारी एक ही जगह</p>
            </div>
        </div>

        <div class="filter-section no-print" style="margin-bottom: 2rem;">
            <div class="filter-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <input type="text" id="filter-head" class="search-input" placeholder="प्रमुख का नाम...">
                <input type="text" id="filter-member" class="search-input" placeholder="सदस्य का नाम...">
                <input type="text" id="filter-city" class="search-input" placeholder="गांव/शहर...">
                <select id="filter-branch" class="search-input">
                    <option value="">सभी परिवार</option>
                </select>
            </div>
        </div>

        <div class="family-grid" id="family-grid">
            <div class="spinner-wrap">
                <div class="spinner"></div>
                <p>परिवार लोड हो रहे हैं…</p>
            </div>
        </div>
        
        <div id="pagination" style="text-align: center; margin: 30px 0;"></div>
    </div>

    <button class="scroll-top" id="scroll-top" aria-label="ऊपर जाएं">
        <i class="fas fa-chevron-up"></i>
    </button>

    <div data-include="components/footer.php"></div>
    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>"></script>

    <script type="module">
        let families = [];
        let totalFamilies = 0;
        let allBranches = [];
        let currentPage = 1;
        const ITEMS_PER_PAGE = 12;
        const DEFAULT_PHOTO = 'https://placehold.co/150x150/cccccc/ffffff?text=No+Photo';

        const grid = document.getElementById('family-grid');
        const pagination = document.getElementById('pagination');
        const branchSelect = document.getElementById('filter-branch');

        const escape = (str) => {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        async function fetchBranches() {
            const r = await fetch(`api/get_branches.php?all=true`);
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            const j = await r.json();
            allBranches = j.data || [];
            
            if (branchSelect) {
                branchSelect.innerHTML = '<option value="">सभी परिवार</option>' + 
                    allBranches.map(b => `<option value="${escape(b.branchName)}">${escape(b.branchName)}</option>`).join('');
            }
        }

        async function fetchFamilies(page = 1) {
            currentPage = page;
            grid.innerHTML = `<div class="spinner-wrap" style="grid-column: 1/-1;"><div class="spinner"></div><p>परिवार लोड हो रहे हैं…</p></div>`;
            
            const head = document.getElementById('filter-head')?.value.trim() || '';
            const member = document.getElementById('filter-member')?.value.trim() || '';
            const city = document.getElementById('filter-city')?.value.trim() || '';
            const branch = branchSelect?.value || '';

            const params = new URLSearchParams({
                page: page,
                limit: ITEMS_PER_PAGE,
                head: head,
                member: member,
                city: city,
                branch: branch
            });

            try {
                const r = await fetch(`api/get_families.php?${params.toString()}`);
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                const j = await r.json();
                
                families = j.data || [];
                totalFamilies = j.total || 0;
                
                renderFamilies();
            } catch (err) {
                console.error(err);
                grid.innerHTML = `<div class="state-box" style="grid-column: 1/-1; border-color:var(--red)"><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i><h3>लोड नहीं हो सका</h3><p style="color:var(--red)">${err.message}</p></div>`;
            }
        }

        function renderFamilies() {
            if (!grid) return;
            
            if (families.length === 0) { 
                grid.innerHTML = '<div class="state-box" style="grid-column: 1 / -1; text-align: center; padding: 3rem;"><h3>🔍 कोई परिवार नहीं मिला।</h3></div>'; 
                pagination.innerHTML = '';
                return; 
            }
            
            grid.innerHTML = families.map((f, index) => {
                const head = escape(f.head || 'अज्ञात');
                const branch = escape(f.branch || '—');
                const village = escape(f.village || '');
                const district = escape(f.district || '');
                const address = escape(f.address || '—');
                const mobile = escape(f.mobile || '');
                const surname = escape(f.surname || '');
                const photoUrl = f.photo || DEFAULT_PHOTO;
                
                const location = [village, district].filter(Boolean).join(', ') || '—';
                
                return `
                <div class="family-card clickable-card" 
                     data-family-id="${f.id}"
                     role="button"
                     tabindex="0"
                     style="animation: fadeInUp 0.4s ease ${index * 0.05}s backwards"
                     aria-label="View details for ${head}">
                    
                    <img class="family-photo"
                         src="${escape(photoUrl)}"
                         alt="${head}"
                         loading="lazy"
                         decoding="async"
                         onload="this.classList.add('loaded')"
                         onerror="this.onerror=null; this.src='${DEFAULT_PHOTO}'; this.classList.add('loaded');">
                    
                    <h3 class="family-name">${head}</h3>
                    ${surname ? `<p><strong>उपनाम:</strong> ${surname}</p>` : ''}
                    <p><strong>परिवार:</strong> ${branch}</p>
                    <p><strong>गांव/शहर:</strong> ${location}</p>
                    <p><strong>पता:</strong> ${address}</p>
                    ${mobile ? `<p><strong>मोबाइल:</strong> <a href="tel:${mobile}" class="mobile-link" data-stop-propagation>${mobile}</a></p>` : ''}
                    
                    <div class="view-details-btn">
                        विस्तृत जानकारी →
                    </div>
                </div>
            `;
            }).join('');
            
            document.querySelectorAll('.family-card.clickable-card').forEach(card => {
                const familyId = card.dataset.familyId;
                card.addEventListener('click', (e) => {
                    if (e.target.closest('[data-stop-propagation]')) return;
                    window.location.href = `family-details.php?id=${familyId}`;
                });
            });
            
            renderPagination();
        }

        function renderPagination() {
            if (!pagination) return;
            const totalPages = Math.ceil(totalFamilies / ITEMS_PER_PAGE);
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '';
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            
            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">पिछला</button>`;
            
            if (startPage > 1) {
                html += `<button class="page-btn" data-page="1">1</button>`;
                if (startPage > 2) html += `<span class="page-dots">...</span>`;
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span class="page-dots">...</span>`;
                html += `<button class="page-btn" data-page="${totalPages}">${totalPages}</button>`;
            }

            html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">अगला</button>`;
            
            pagination.innerHTML = html;

            pagination.querySelectorAll('.page-btn:not([disabled])').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const newPage = parseInt(e.target.dataset.page);
                    if (newPage && newPage !== currentPage) {
                        fetchFamilies(newPage);
                        window.scrollTo({ top: document.getElementById('family-grid').offsetTop - 100, behavior: 'smooth' });
                    }
                });
            });
        }

        let debounceTimer;
        const handleFilter = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchFamilies(1), 300);
        };
        
        ['filter-head', 'filter-member', 'filter-city'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', handleFilter);
        });
        branchSelect?.addEventListener('change', handleFilter);

        const scrollBtn = document.getElementById('scroll-top');
        window.addEventListener('scroll', () => scrollBtn?.classList.toggle('visible', window.scrollY > 300), { passive: true });
        scrollBtn?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

        async function init() {
            try {
                await fetchBranches();
                await fetchFamilies(1);
            } catch(e) {
                console.error(e);
            }
        }
        
        init();

        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
            .family-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
            .family-card { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; transition: transform 0.3s; cursor: pointer; display: flex; flex-direction: column; }
            .family-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: var(--orange); }
            .family-photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; display: block; border: 3px solid var(--gold-light); }
            .family-name { color: var(--maroon); margin-bottom: 0.5rem; text-align: center; font-size: 1.25rem; font-weight: 700; }
            .family-card p { margin-bottom: 4px; font-size: 0.95rem; color: #555; }
            .page-btn { padding: 8px 12px; margin: 0 4px; border: 1px solid #ddd; background: #fff; cursor: pointer; border-radius: 6px; font-weight: 600; transition: all 0.2s; }
            .page-btn:hover:not([disabled]) { background: #f5f5f5; border-color: #ccc; }
            .page-btn.active { background: var(--orange); color: #fff; border-color: var(--orange); }
            .page-btn[disabled] { opacity: 0.5; cursor: not-allowed; }
            .search-input { padding: 12px; border: 2px solid #eaeaea; border-radius: 8px; width: 100%; outline: none; font-size: 1rem; color: var(--text-dark); transition: all 0.2s ease; background: #ffffff; }
            .search-input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(255,107,0,0.1); }
            .search-input:hover { border-color: #d1d1d1; }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>