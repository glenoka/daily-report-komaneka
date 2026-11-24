/**
 * Daily Reporting System — Client-side List Normalizer
 * Mirrors backend ReportNormalizerService for zero-click paste cleanup.
 */

const BULLET_CHARS = '[•●○◦▪▫‣⁃]';
const DASH_CHARS = '[-–—]';
const CHECKBOX_CHARS = '[☐☑☒✓✔]';
const COMMON_LIST_EMOJIS = '[📌📍🔹🔸▶►👉➡️✔️✅⚠️⭐💡⚡]';

export function cleanLine(line) {
    let current = line.trim();
    const maxPasses = 5;

    for (let pass = 0; pass < maxPasses; pass++) {
        const previous = current;

        // 1. Bullets
        current = current.replace(new RegExp(`^${BULLET_CHARS}\\s*`, 'u'), '');

        // 2. Checkboxes
        current = current.replace(new RegExp(`^(?:\\[[ xX]\\]|${CHECKBOX_CHARS})\\s*`, 'u'), '');

        // 3. Dashes & Asterisks (require following whitespace)
        current = current.replace(new RegExp(`^(?:${DASH_CHARS}|\\*)\\s+`, 'u'), '');

        // 4. Numeric markers: 1., 2), 10., 1:, 1- (require following whitespace)
        current = current.replace(/^\d{1,3}[.)\-:]\s+/u, '');

        // 5. Alphabetic markers: a., B), a), B. (require following whitespace)
        current = current.replace(/^[a-zA-Z][.)]\s+/u, '');

        // 6. Common list emojis
        current = current.replace(new RegExp(`^${COMMON_LIST_EMOJIS}\\s*`, 'u'), '');

        current = current.trim();

        if (current === previous || current === '') {
            break;
        }
    }

    return current;
}

export function normalizeListText(rawText) {
    if (!rawText) return '';

    const lines = rawText.replace(/\r\n|\r/g, '\n').split('\n');
    const cleaned = [];

    for (const rawLine of lines) {
        const trimmed = rawLine.trim();
        if (trimmed === '') continue;

        const line = cleanLine(trimmed);
        if (line !== '') {
            cleaned.push(line);
        }
    }

    return cleaned.join('\n');
}

/**
 * Capitalize first alphabetic character of each sentence in multi-line or single-line text.
 * Boundaries are the start of each line and after '.', '!', '?' plus whitespace.
 */
export function capitalizeSentences(text) {
    if (!text) return '';

    return text.split('\n').map(line => {
        if (!line.trim()) return line;

        // Capitalize first alphabetic character on the line
        let res = line.replace(/^(\s*[\p{P}\p{S}]*\s*)(\p{L})/u, (_, prefix, char) => prefix + char.toUpperCase());

        // Capitalize after sentence-ending punctuation (. ! ?) followed by whitespace
        res = res.replace(/([.!?]\s+)([\p{P}\p{S}]*)(\p{L})/gu, (_, punc, symbols, char) => punc + symbols + char.toUpperCase());

        return res;
    }).join('\n');
}

/**
 * Handle paste event on list textareas.
 */
export function initListPasteHandler() {
    document.addEventListener('paste', (event) => {
        const target = event.target;
        if (!target || !target.matches('textarea[data-list-input], .list-input-paste')) {
            return;
        }

        const clipboardData = event.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        const pastedText = clipboardData.getData('text/plain');
        if (!pastedText) return;

        let processedText = normalizeListText(pastedText);

        // Check if this input is for Departmental Activities
        const isDepartmentInput = target.matches('[data-department-input="true"]') || target.closest('[data-departmental-activities="true"]');
        if (isDepartmentInput) {
            processedText = capitalizeSentences(processedText);
        }

        // If formatting was cleaned or capitalized and differs from raw text
        if (processedText && processedText !== pastedText) {
            event.preventDefault();

            // Save for possible Undo
            target.dataset.rawPaste = pastedText;

            // Insert processed text at current cursor location
            const start = target.selectionStart || 0;
            const end = target.selectionEnd || 0;
            const originalVal = target.value;

            const before = originalVal.substring(0, start);
            const after = originalVal.substring(end);
            const newVal = before + processedText + after;

            target.value = newVal;

            // Set cursor right after the inserted text
            const newCursor = start + processedText.length;
            target.setSelectionRange(newCursor, newCursor);

            // Dispatch input event for Livewire / Alpine synchronization
            target.dispatchEvent(new Event('input', { bubbles: true }));

            // Dispatch custom notification event
            window.dispatchEvent(new CustomEvent('list-cleaned', {
                detail: { targetId: target.id || null, count: processedText.split('\n').length }
            }));
        }
    });
}

// Expose on window for inline use and testing
if (typeof window !== 'undefined') {
    window.normalizeListText = normalizeListText;
    window.cleanListLine = cleanLine;
    window.capitalizeSentences = capitalizeSentences;
}
