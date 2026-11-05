/**
 * Universal Header & Footer Loader
 * Loads components/header.php and components/footer.php
 * into elements with data-include attribute
 */

// Sliding name animation configuration
const SLIDING_NAME_CONFIG = {
  text: 'छःन्यात राजस्थानी विप्र मंडल, लातूर',
  separator: '   ✦   ',
  repeatCount: 6, // Increased for a smoother, longer loop
  duration: 30, // Animation duration in seconds
};

// 🛡️ Global CSRF Token Management & Automatic Fetch Interceptor
window.getCSRFToken = function() {
  try {
    return sessionStorage.getItem('csrf_token') || '';
  } catch(e) {
    return '';
  }
};

window.setCSRFToken = function(token) {
  if (token) {
    try { sessionStorage.setItem('csrf_token', token); } catch(e) {}
  }
};

// Automatically inject X-CSRF-Token into all state-changing same-origin API fetch calls
if (typeof window.fetch === 'function' && !window.__csrfFetchPatched) {
  const originalFetch = window.fetch;
  window.fetch = async function(resource, init = {}) {
    const method = String(init.method || 'GET').toUpperCase();
    if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
      const token = window.getCSRFToken();
      if (token) {
        if (!init.headers) {
          init.headers = {};
        }
        if (init.headers instanceof Headers) {
          if (!init.headers.has('X-CSRF-Token')) init.headers.set('X-CSRF-Token', token);
        } else if (Array.isArray(init.headers)) {
          if (!init.headers.some(h => h[0].toLowerCase() === 'x-csrf-token')) {
            init.headers.push(['X-CSRF-Token', token]);
          }
        } else {
          if (!init.headers['X-CSRF-Token'] && !init.headers['x-csrf-token']) {
            init.headers['X-CSRF-Token'] = token;
          }
        }
      }
    }

    const response = await originalFetch(resource, init);

    // If check_auth returns a fresh csrf_token, capture it
    try {
      const urlStr = typeof resource === 'string' ? resource : (resource && resource.url ? resource.url : '');
      if (urlStr.includes('check_auth.php')) {
        const clone = response.clone();
        clone.json().then(data => {
          if (data && data.csrf_token) {
            window.setCSRFToken(data.csrf_token);
          }
        }).catch(() => {});
      }
    } catch(e) {}

    return response;
  };
  window.__csrfFetchPatched = true;
}

/**
 * Load and inject HTML components
 */
function loadComponents() {
  var includes = document.querySelectorAll('[data-include]');
  if (includes.length === 0) return Promise.resolve(); // Fix: Return resolved promise to prevent .then() errors
  
  var promises = Array.from(includes).map(function(el) {
    var path = el.getAttribute('data-include');
    return fetch(path)
      .then(function(response) {
        if (!response.ok) throw new Error('Failed: ' + path);
        return response.text();
      })
      .then(function(html) {
        el.innerHTML = html;
      })
      .catch(function(err) {
        console.error('Component error:', err);
        el.innerHTML = '<p style="color:red;">Failed to load ' + path + '</p>';
      });
  });
  
  return Promise.all(promises);
}

/**
 * Initialize the sliding name animation
 */
function initSlidingName() {
  const slidingNameEl = document.getElementById('sliding-name-track');
  if (!slidingNameEl) return;

  const { text, separator, repeatCount, duration } = SLIDING_NAME_CONFIG;
  
  // Build the sliding content
  let content = '';
  for (let i = 0; i < repeatCount; i++) {
    content += `<span class="sliding-name-item">${text}</span>`;
    content += `<span class="sliding-name-separator">${separator}</span>`; // Fix: Always add separator to prevent mashing at the loop seam
  }
  
  // Duplicate for seamless infinite scroll
  slidingNameEl.innerHTML = content + content;
  
  // Set animation duration
  slidingNameEl.style.animationDuration = `${duration}s`;
}

/**
 * Update active navigation link based on current page
 */
function updateActiveNavLink() {
  const currentPath = window.location.pathname.split('/').pop() || 'index.php';
  const navLinks = document.querySelectorAll('.desktop-nav a, .mobile-nav a');
  
  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.php')) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

/**
 * Initialize mobile menu toggle
 */
function initMobileMenu() {
  const toggle = document.getElementById('menuToggle');
  const nav = document.getElementById('mobileNav');
  
  if (!toggle || !nav) return;
  
  toggle.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('active');
    toggle.classList.toggle('active');
    toggle.setAttribute('aria-expanded', isOpen);
    toggle.setAttribute('aria-label', isOpen ? 'मेनू बंद करें' : 'मेनू खोलें');
  });
  
  // Close menu when clicking a link
  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('active');
      toggle.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
  
  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (!nav.contains(e.target) && !toggle.contains(e.target)) {
      if (nav.classList.contains('active')) {
        nav.classList.remove('active');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });
}

/**
 * Initialize scroll-to-top button
 */
function initScrollToTop() {
  const scrollBtn = document.getElementById('scrollToTop');
  if (!scrollBtn) return;
  
  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      scrollBtn.classList.add('visible');
    } else {
      scrollBtn.classList.remove('visible');
    }
  }, { passive: true });
  
  scrollBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/**
 * Initialize header shrink on scroll
 */
function initHeaderShrink() {
  const header = document.querySelector('.site-header');
  if (!header) return;
  
  window.addEventListener('scroll', () => {
    if (window.scrollY > 100) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }, { passive: true });
}

/**
 * Master initialization function
 * Consolidates all setup logic to run exactly once after components load
 */
function initializeApp() {
  loadComponents()
    .then(() => {
      // Initialize features that depend on loaded HTML
      initSlidingName();
      updateActiveNavLink();
      initMobileMenu();
      initScrollToTop();
      initHeaderShrink();
    })
    .catch(err => {
      console.error('Initialization failed:', err);
    });
}

// Run on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeApp);
} else {
  initializeApp();
}