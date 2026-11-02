/**
 * ⌨️ Hindi Transliteration Engine — Production Build
 * ─────────────────────────────────────────────────────
 * Converts Roman English phonetics to Devanagari Hindi in real-time.
 * Works with <input>, <textarea>, and contenteditable elements.
 *
 * Features:
 *  ✔ Google Input Tools API (hi-t-i0-und)
 *  ✔ Suggestion dropdown with arrow-key / click selection
 *  ✔ LRU cache with configurable size limit
 *  ✔ AbortController to cancel stale API requests
 *  ✔ Debounced pre-fetching while typing
 *  ✔ Keyboard shortcut toggle (Ctrl + Shift + H)
 *  ✔ Numbers & punctuation pass-through
 *  ✔ Graceful offline / error fallback
 *  ✔ Accessible (ARIA) suggestion list
 *  ✔ Works on both <input> and <textarea>
 *
 * Usage:
 *   window.initHindiTransliteration('[data-hindi-input="true"]', containerEl);
 *
 * @version 2.0.0
 * @license MIT
 */

(function () {
    'use strict';

    // ─── Configuration ──────────────────────────────────
    const CONFIG = {
        API_URL: 'https://inputtools.google.com/request',
        INPUT_TOOL_CODE: 'hi-t-i0-und',
        MAX_SUGGESTIONS: 5,
        CACHE_MAX_SIZE: 500,
        PREFETCH_DEBOUNCE_MS: 150,
        API_TIMEOUT_MS: 3000,
        MIN_WORD_LENGTH_PREFETCH: 2,
    };

    // ─── State ──────────────────────────────────────────
    let isHindiEnabled = true;
    let activeSuggestionBox = null;
    let activeSuggestionInput = null;
    let suggestions = [];
    let selectedIdx = -1;
    let pendingAbortController = null;

    // ─── LRU Cache ──────────────────────────────────────
    const translitCache = new Map();

    function cacheGet(key) {
        if (!translitCache.has(key)) return null;
        const val = translitCache.get(key);
        // Move to end (most recently used)
        translitCache.delete(key);
        translitCache.set(key, val);
        return val;
    }

    function cacheSet(key, val) {
        if (translitCache.has(key)) translitCache.delete(key);
        translitCache.set(key, val);
        // Evict oldest if over limit
        if (translitCache.size > CONFIG.CACHE_MAX_SIZE) {
            const oldest = translitCache.keys().next().value;
            translitCache.delete(oldest);
        }
    }

    // ─── API Layer ──────────────────────────────────────
    async function fetchTransliteration(word, signal) {
        if (!word || word.trim().length === 0) return [];
        const cleanWord = word.trim().toLowerCase();

        const cached = cacheGet(cleanWord);
        if (cached) return cached;

        try {
            const params = new URLSearchParams({
                text: cleanWord,
                itc: CONFIG.INPUT_TOOL_CODE,
                num: String(CONFIG.MAX_SUGGESTIONS),
            });

            const response = await fetch(`${CONFIG.API_URL}?${params}`, {
                signal: signal || undefined,
            });

            if (!response.ok) return [cleanWord];
            const data = await response.json();

            if (
                data[0] === 'SUCCESS' &&
                data[1] && data[1][0] && data[1][0][1]
            ) {
                const results = data[1][0][1];
                cacheSet(cleanWord, results);
                return results;
            }
        } catch (err) {
            if (err.name === 'AbortError') return []; // silently discard aborted
            // Network failure / offline — fall back to original word
        }
        return [cleanWord];
    }

    // ─── Cursor / Word Utilities ────────────────────────
    function getInputValue(el) {
        return el.value !== undefined ? el.value : el.textContent;
    }

    function setInputValue(el, val) {
        if (el.value !== undefined) {
            el.value = val;
        } else {
            el.textContent = val;
        }
    }

    function getLastWordBeforeCursor(el) {
        const val = getInputValue(el);
        const selStart = el.selectionStart !== undefined ? el.selectionStart : val.length;
        const textBefore = val.substring(0, selStart);
        // Match English letters + digits (allows mixed like "2024ka")
        const match = textBefore.match(/([a-zA-Z][a-zA-Z0-9]*)$/);
        if (match) {
            return {
                word: match[1],
                start: selStart - match[1].length,
                end: selStart,
            };
        }
        return null;
    }

    function replaceTextRange(el, start, end, newText) {
        const val = getInputValue(el);
        const before = val.substring(0, start);
        const after = val.substring(end);
        setInputValue(el, before + newText + after);
        const newPos = start + newText.length;
        if (el.setSelectionRange) {
            el.setSelectionRange(newPos, newPos);
        }
        // Fire events for framework listeners
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // ─── Suggestion Dropdown ────────────────────────────
    function createSuggestionBox(el) {
        removeSuggestionBox();

        const box = document.createElement('ul');
        box.className = 'hindi-translit-suggestions';
        box.setAttribute('role', 'listbox');
        box.setAttribute('aria-label', 'Hindi transliteration suggestions');
        box.style.cssText = `
            position: absolute;
            z-index: 10000;
            list-style: none;
            margin: 0;
            padding: 4px 0;
            background: #fff;
            border: 1px solid rgba(201, 148, 26, 0.4);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            min-width: 180px;
            max-width: 320px;
            font-family: 'Noto Sans Devanagari', 'Tiro Devanagari Hindi', sans-serif;
            font-size: 1rem;
            overflow: hidden;
        `;

        // Position near cursor
        const rect = el.getBoundingClientRect();
        box.style.left = rect.left + window.scrollX + 'px';
        box.style.top = rect.bottom + window.scrollY + 4 + 'px';

        document.body.appendChild(box);
        activeSuggestionBox = box;
        activeSuggestionInput = el;
        return box;
    }

    function populateSuggestions(box, items, wordInfo, el) {
        suggestions = items;
        selectedIdx = 0;
        box.innerHTML = '';

        items.forEach((item, idx) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', idx === 0 ? 'true' : 'false');
            li.style.cssText = `
                padding: 7px 14px;
                cursor: pointer;
                transition: background 0.15s;
                color: var(--maroon-deep, #550F0F);
                border-bottom: 1px solid rgba(201, 148, 26, 0.08);
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            if (idx === 0) {
                li.style.background = 'rgba(255, 107, 0, 0.12)';
                li.style.fontWeight = '600';
            }

            // Number badge for quick selection
            const badge = document.createElement('span');
            badge.textContent = String(idx + 1);
            badge.style.cssText = `
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 20px; height: 20px;
                border-radius: 4px;
                background: rgba(123, 26, 26, 0.1);
                color: var(--maroon, #7B1A1A);
                font-size: 0.7rem;
                font-weight: 700;
                flex-shrink: 0;
            `;

            const text = document.createElement('span');
            text.textContent = item;
            text.style.cssText = 'flex: 1;';

            li.appendChild(badge);
            li.appendChild(text);

            li.addEventListener('mouseenter', () => {
                highlightSuggestion(box, idx);
            });

            li.addEventListener('mousedown', (ev) => {
                ev.preventDefault(); // prevent blur
                applySuggestion(el, wordInfo, item);
            });

            box.appendChild(li);
        });
    }

    function highlightSuggestion(box, idx) {
        const items = box.querySelectorAll('li');
        items.forEach((li, i) => {
            li.style.background = i === idx ? 'rgba(255, 107, 0, 0.1)' : '';
            li.style.fontWeight = i === idx ? '600' : '';
            li.setAttribute('aria-selected', i === idx ? 'true' : 'false');
        });
        selectedIdx = idx;
    }

    function applySuggestion(el, wordInfo, chosen) {
        replaceTextRange(el, wordInfo.start, wordInfo.end, chosen + ' ');
        removeSuggestionBox();
        el.focus();
    }

    function removeSuggestionBox() {
        if (activeSuggestionBox) {
            activeSuggestionBox.remove();
            activeSuggestionBox = null;
            activeSuggestionInput = null;
            suggestions = [];
            selectedIdx = -1;
        }
    }

    // Close suggestion box on outside click / scroll
    document.addEventListener('click', (e) => {
        if (activeSuggestionBox && !activeSuggestionBox.contains(e.target)) {
            removeSuggestionBox();
        }
    });
    document.addEventListener('scroll', removeSuggestionBox, true);

    // ─── Input Binding ──────────────────────────────────
    function enableTransliterationOnInput(input) {
        if (!input || input.dataset.hindiTranslitBound === 'true') return;
        input.dataset.hindiTranslitBound = 'true';

        let debounceTimer = null;

        /**
         * Confirm a transliteration: always re-reads the CURRENT word at cursor
         * and fetches fresh results if the word has changed since the dropdown opened.
         */
        async function confirmTransliteration(e) {
            e.preventDefault();

            // Always read the ACTUAL current word — never rely on stale lastWordInfo
            const currentWord = getLastWordBeforeCursor(input);
            if (!currentWord || !/^[a-zA-Z][a-zA-Z0-9]*$/.test(currentWord.word)) {
                // No English word — just insert a space if Space was pressed
                removeSuggestionBox();
                if (e.key === ' ' || e.key === 'Spacebar') {
                    // Let browser handle it since we called preventDefault
                    replaceTextRange(input, input.selectionStart, input.selectionStart, ' ');
                }
                return;
            }

            removeSuggestionBox();

            // Cancel stale in-flight requests
            if (pendingAbortController) pendingAbortController.abort();
            pendingAbortController = new AbortController();

            const results = await fetchTransliteration(currentWord.word, pendingAbortController.signal);
            const converted = (results && results.length > 0) ? results[0] : currentWord.word;

            // Re-read cursor position to get the accurate range right NOW
            const freshWord = getLastWordBeforeCursor(input);
            if (freshWord && freshWord.word === currentWord.word) {
                replaceTextRange(input, freshWord.start, freshWord.end, converted + ' ');
            } else {
                // Word changed while we were fetching — apply to what we found initially
                replaceTextRange(input, currentWord.start, currentWord.end, converted + ' ');
            }
        }

        /**
         * Confirm with a specific chosen suggestion (from number key, click, or arrow+enter).
         * Still re-reads current word range to avoid stale position bugs.
         */
        function confirmWithChoice(e, chosenText) {
            e.preventDefault();
            const currentWord = getLastWordBeforeCursor(input);
            if (currentWord && /^[a-zA-Z][a-zA-Z0-9]*$/.test(currentWord.word)) {
                replaceTextRange(input, currentWord.start, currentWord.end, chosenText + ' ');
            }
            removeSuggestionBox();
        }

        // ── Live suggestions while typing ──
        input.addEventListener('input', () => {
            if (!isHindiEnabled) return;

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(async () => {
                const info = getLastWordBeforeCursor(input);

                // No English word at cursor → close any open dropdown
                if (!info || !/^[a-zA-Z][a-zA-Z0-9]*$/.test(info.word)) {
                    removeSuggestionBox();
                    return;
                }

                // Cancel any previous in-flight request
                if (pendingAbortController) pendingAbortController.abort();
                pendingAbortController = new AbortController();

                const results = await fetchTransliteration(info.word, pendingAbortController.signal);

                if (!results || results.length === 0) {
                    removeSuggestionBox();
                    return;
                }

                // Re-check that cursor is still at same word (user may have typed more)
                const currentInfo = getLastWordBeforeCursor(input);
                if (!currentInfo || currentInfo.word !== info.word) return;

                // Show / update dropdown
                const box = activeSuggestionBox && activeSuggestionInput === input
                    ? activeSuggestionBox
                    : createSuggestionBox(input);

                populateSuggestions(box, results, currentInfo, input);

            }, CONFIG.PREFETCH_DEBOUNCE_MS);
        });

        // ── Keydown: Space/Enter confirms, arrows navigate, Escape dismisses ──
        input.addEventListener('keydown', (e) => {
            if (!isHindiEnabled) return;

            // ── Suggestion box is open ──
            if (activeSuggestionBox && activeSuggestionInput === input) {

                // Arrow navigation
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    highlightSuggestion(activeSuggestionBox, (selectedIdx + 1) % suggestions.length);
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlightSuggestion(activeSuggestionBox, (selectedIdx - 1 + suggestions.length) % suggestions.length);
                    return;
                }

                // Number keys 1-9 → pick suggestion by index (re-reads current word)
                if (e.key >= '1' && e.key <= '9') {
                    const numIdx = parseInt(e.key, 10) - 1;
                    if (numIdx < suggestions.length) {
                        confirmWithChoice(e, suggestions[numIdx]);
                        return;
                    }
                }

                // Enter / Tab → confirm highlighted suggestion
                if (e.key === 'Enter' || e.key === 'Tab') {
                    if (suggestions.length > 0 && selectedIdx >= 0) {
                        confirmWithChoice(e, suggestions[selectedIdx]);
                        return;
                    }
                }

                // Escape → dismiss dropdown, keep English text
                if (e.key === 'Escape') {
                    e.preventDefault();
                    removeSuggestionBox();
                    return;
                }

                // Space → fetch the FULL current word fresh and convert it
                if (e.key === ' ' || e.key === 'Spacebar') {
                    confirmTransliteration(e);
                    return;
                }
            }

            // ── No suggestion box open, but Space pressed on English word ──
            if ((e.key === ' ' || e.key === 'Spacebar') && !activeSuggestionBox) {
                const wordInfo = getLastWordBeforeCursor(input);
                if (wordInfo && /^[a-zA-Z][a-zA-Z0-9]*$/.test(wordInfo.word)) {
                    confirmTransliteration(e);
                    return;
                }
            }

            // ── Enter in <textarea> without suggestion box → normal newline ──
            // (no interference needed, just let default happen)
        });

        // ── Close suggestions on blur (delay lets click events fire first) ──
        input.addEventListener('blur', () => {
            setTimeout(() => {
                if (activeSuggestionInput === input) {
                    removeSuggestionBox();
                }
            }, 250);
        });
    }

    // ─── Toggle Badge UI ────────────────────────────────
    function attachToggleBadge(container) {
        if (document.getElementById('hindi-translit-toggle-pill')) return;

        const pill = document.createElement('div');
        pill.id = 'hindi-translit-toggle-pill';
        pill.setAttribute('role', 'switch');
        pill.setAttribute('tabindex', '0');
        pill.setAttribute('aria-checked', String(isHindiEnabled));
        pill.setAttribute('aria-label', 'Toggle Hindi transliteration');

        pill.style.cssText = `
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--maroon, #7B1A1A), var(--maroon-deep, #550F0F));
            color: var(--gold-light, #F0C060);
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(123, 26, 26, 0.15);
            user-select: none;
            transition: all 0.3s ease;
            margin-bottom: 18px;
            border: 1px solid var(--gold-light, #F0C060);
        `;

        function updateUI() {
            pill.setAttribute('aria-checked', String(isHindiEnabled));
            pill.innerHTML = `
                <i class="fas fa-keyboard" style="color: ${isHindiEnabled ? 'var(--gold-light, #F0C060)' : '#999'}; font-size: 1.05rem;"></i>
                <span>अंग्रेजी → हिंदी टाइपिंग: <strong>${isHindiEnabled ? 'चालू (ON)' : 'बंद (OFF)'}</strong></span>
                <span style="
                    display: inline-block;
                    width: 10px; height: 10px;
                    border-radius: 50%;
                    background: ${isHindiEnabled ? '#2e7d32' : '#c62828'};
                    box-shadow: 0 0 6px ${isHindiEnabled ? '#4caf50' : '#e53935'};
                    transition: all 0.3s;
                "></span>
            `;
            const hint = document.getElementById('hindi-translit-hint');
            if (hint) hint.style.display = isHindiEnabled ? '' : 'none';
        }

        pill.addEventListener('click', () => {
            isHindiEnabled = !isHindiEnabled;
            updateUI();
            if (!isHindiEnabled) removeSuggestionBox();
        });

        // Keyboard accessible
        pill.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                pill.click();
            }
        });

        updateUI();

        // Help text
        const hint = document.createElement('div');
        hint.id = 'hindi-translit-hint';
        hint.style.cssText = `
            font-size: 0.78rem;
            color: var(--maroon, #7B1A1A);
            opacity: 0.7;
            margin-top: -12px;
            margin-bottom: 14px;
            padding-left: 4px;
        `;
        hint.innerHTML = `
            <i class="fas fa-info-circle"></i>
            अंग्रेजी में टाइप करते ही हिंदी सुझाव दिखेंगे → <strong>Space/Enter</strong> से चुनें, <strong>↑↓</strong> से बदलें, <strong>1-5</strong> नंबर से सीधे चुनें
            &nbsp;|&nbsp;
            <kbd style="padding:1px 5px;border:1px solid #ccc;border-radius:3px;font-size:0.75rem;">Ctrl+Shift+H</kbd> से टॉगल करें
        `;

        if (container) {
            container.insertBefore(hint, container.firstChild);
            container.insertBefore(pill, container.firstChild);
        }
    }

    // ─── Global Keyboard Shortcut (Ctrl+Shift+H) ───────
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && (e.key === 'H' || e.key === 'h')) {
            e.preventDefault();
            const pill = document.getElementById('hindi-translit-toggle-pill');
            if (pill) pill.click();
        }
    });

    // ─── Public API ─────────────────────────────────────
    /**
     * Initialize Hindi transliteration on matching elements.
     * @param {string} selector - CSS selector for input/textarea elements
     * @param {HTMLElement|null} toggleContainer - container to insert toggle badge
     */
    window.initHindiTransliteration = function (selector, toggleContainer) {
        selector = selector || '[data-hindi-input="true"]';
        const elements = document.querySelectorAll(selector);
        elements.forEach(enableTransliterationOnInput);
        if (toggleContainer) {
            attachToggleBadge(toggleContainer);
        }
    };

    /**
     * Programmatically set Hindi mode on/off.
     * @param {boolean} enabled
     */
    window.setHindiTransliteration = function (enabled) {
        isHindiEnabled = !!enabled;
        const pill = document.getElementById('hindi-translit-toggle-pill');
        if (pill) pill.click(); // triggers UI update
    };

    /**
     * Check if Hindi transliteration is currently enabled.
     * @returns {boolean}
     */
    window.isHindiTransliterationEnabled = function () {
        return isHindiEnabled;
    };
})();
