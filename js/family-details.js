import { loadAllFromAPI } from "./home.js";

// 🇮🇳 HELPER: Get value
function getDisplayValue(obj, fieldName, fallback = '—') {
    if (!obj) return fallback;
    if (obj.hindi && obj.hindi[fieldName]) return obj.hindi[fieldName];
    if (obj[fieldName] !== undefined && obj[fieldName] !== null && obj[fieldName] !== '') return obj[fieldName];
    return fallback;
}

// 🖼️ HELPER: Load Photo directly to BOTH hero + hidden container
function loadFamilyPhoto(photoUrl, fallbackText = 'Family') {
    const defaultPhoto = `https://placehold.net/avatar-4.png`;
    const src = photoUrl || defaultPhoto;

    // Hero photo element
    const fdPhoto = document.getElementById('fd-photo');
    const fdSkeleton = document.getElementById('fd-photo-skeleton');

    const testImg = new Image();
    testImg.onload = () => {
        if (fdPhoto) {
            fdPhoto.src = src;
            fdPhoto.style.display = 'block';
        }
        if (fdSkeleton) fdSkeleton.style.display = 'none';
    };
    testImg.onerror = () => {
        if (fdPhoto) {
            fdPhoto.src = defaultPhoto;
            fdPhoto.style.display = 'block';
        }
        if (fdSkeleton) fdSkeleton.style.display = 'none';
    };
    testImg.src = src;

    // Also set in hidden container (kept for backward compatibility)
    const container = document.getElementById('family-photo-container');
    if (container) {
        container.innerHTML = `<img src="${src}" style="display:none;">`;
    }
}

// ✅ NEW: Update Hero Card directly
function updateHeroCard(family) {
    const heroName   = document.getElementById('fd-name');
    const heroBranch = document.getElementById('fd-branch-label');

    const headName = getDisplayValue(family, 'head', '—');
    const branchName = getDisplayValue(family, 'branch', '—');

    if (heroName) heroName.textContent = headName;
    if (heroBranch) heroBranch.textContent = branchName;
}

// 💬 HELPER: Setup WhatsApp button
function setupWhatsAppButton(buttonId, mobileNumber) {
    const btn = document.getElementById(buttonId);
    if (!btn) return;

    // Clean mobile number
    let cleanMobile = mobileNumber ? mobileNumber.replace(/[\s\-\(\)]/g, '') : '';
    
    // If no valid mobile, hide button
    if (!cleanMobile || cleanMobile.length < 10) {
        btn.classList.add('hidden');
        return;
    }

    // Add country code if missing (assuming Indian numbers)
    if (!cleanMobile.startsWith('91')) {
        cleanMobile = '91' + cleanMobile;
    }

    // Set WhatsApp URL with pre-filled message
  btn.href = `https://wa.me/${cleanMobile}?text=${encodeURIComponent("नमस्ते! मैं छःन्यात राजस्थानी विप्र मंडल की वेबसाइट के माध्यम से आपसे संपर्क कर रहा हूँ।")}`; 
 btn.classList.remove('hidden');
}

async function initFamilyDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const familyId = urlParams.get('id');

    if (!familyId) {
        alert('❌ URL में family ID नहीं है।\nExample: family-details.php?id=FAM-512163');
        return;
    }

    try {
        const families = await loadAllFromAPI('families');

        if (!families || !Array.isArray(families) || families.length === 0) {
            alert('⚠️ कोई family data नहीं मिला।');
            return;
        }

        const family = families.find(f => f.id === familyId);

        if (!family) {
            alert(`❌ Family ID "${familyId}" नहीं मिला।\nAvailable: ${families.map(f => f.id).join(', ')}`);
            return;
        }

        // ✅ 1. LOAD PHOTO DIRECTLY TO HERO
        loadFamilyPhoto(family.photo, 'Family');

        // ✅ 2. UPDATE HERO CARD DIRECTLY (no MutationObserver needed)
        updateHeroCard(family);

        // ✅ Helper: set value + also update hero if it matches
        const setVal = (id, fieldName, obj = family) => {
            const el = document.getElementById(id);
            if (el) {
                const value = getDisplayValue(obj, fieldName, '—');
                el.textContent = value;
            }
        };

        // ✅ General Info
        setVal('family-branch',  'branch');
        setVal('family-head',    'head');
        setVal('family-address', 'address');
        setVal('family-village', 'village');
        setVal('family-district','district');
        setVal('family-pincode', 'pincode');
        setVal('family-mobile',  'mobile');
        setVal('family-email',   'email');
        setVal('family-std-code','stdCode');
        setVal('family-phone',   'phone');

        // ✅ Setup WhatsApp button for family mobile
        setupWhatsAppButton('whatsapp-btn', family.mobile);

        // ✅ Native Place
        setVal('family-native',          'nativePlace');
        setVal('family-native-taluka',   'nativeTaluka');
        setVal('family-native-district', 'nativeDistrict');

        // ✅ Kuldaivat
        setVal('family-kuldaivat',       'kuldaivat');
        setVal('family-kuldaivat-place', 'kuldaivatPlace');

        // ✅ Business section
        const hasBusiness = family.businessName || family.hindi?.businessName;
        if (hasBusiness) {
            const bizSection = document.getElementById('business-section');
            if (bizSection) bizSection.style.display = 'block';

            setVal('biz-name',    'businessName');
            setVal('biz-address', 'businessAddress');
            setVal('biz-mobile',  'businessMobile');
            setVal('biz-type',    'businessType');
            setVal('biz-years',   'businessYears');

            // ✅ Setup WhatsApp button for business mobile
            setupWhatsAppButton('whatsapp-biz-btn', family.businessMobile);
        }

        // ✅ Members table
        const tbody = document.getElementById('members-tbody');
        const membersList = family.members || family.hindi?.members || [];

        if (tbody && Array.isArray(membersList) && membersList.length > 0) {
            tbody.innerHTML = membersList.map(m => {
                const mName          = m.name          || '—';
                const mRelation      = m.relation      || '—';
                const mAge           = m.age           || '—';
                const mGender        = m.gender        || '—';
                const mMaritalStatus = m.maritalStatus || '—';
                const mEducation     = m.education     || '—';
                const mOccupation    = m.occupation    || '—';
                const mMobile = m.mobile && m.mobile !== '—' && m.mobile !== ''
                    ? `<a href="tel:${m.mobile}" style="color: var(--orange); text-decoration: none;">${m.mobile}</a>`
                    : '—';

                return `
                    <tr>
                        <td>${mName}</td>
                        <td>${mRelation}</td>
                        <td>${mAge}</td>
                        <td>${mGender}</td>
                        <td>${mMaritalStatus}</td>
                        <td>${mEducation}</td>
                        <td>${mOccupation}</td>
                        <td>${mMobile}</td>
                    </tr>
                `;
            }).join('');
        } else if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#666;">कोई सदस्य डेटा उपलब्ध नहीं है।</td></tr>';
        }

        // ✅ Print button
        const printBtn = document.getElementById('print-btn');
        if (printBtn) printBtn.addEventListener('click', () => window.print());

    } catch (error) {
        alert(`❌ Error: ${error.message}`);
    }
}

// 🎯 Run
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFamilyDetails);
} else {
    initFamilyDetails();
}