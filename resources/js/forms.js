// Form enhancements (DESIGN.md §4.5): password show/hide, loading buttons, error summary focus,
// file dropzones with previews, slug autofill, live generated-cover preview, copy buttons.
import { icon, announce } from './toast';

const formatSize = (bytes) => {
    if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1).replace(/\.0$/, '')} MB`;
};

function passwordToggles() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-password-toggle]');
        if (!btn) return;
        const input = document.getElementById(btn.getAttribute('aria-controls'));
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(show));
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.textContent = show ? 'Hide' : 'Show';
    });
}

function busyButtons() {
    // Runs after cart.js, which cancels its own submits first.
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        const button = e.submitter && e.submitter.dataset.busyLabel !== undefined
            ? e.submitter
            : e.target.querySelector('button[type="submit"][data-busy-label]');
        if (!button || button.getAttribute('aria-busy') === 'true') return;
        button.dataset.idleHtml = button.innerHTML;
        button.style.minWidth = `${button.offsetWidth}px`;
        button.setAttribute('aria-busy', 'true');
        button.replaceChildren(icon('loader-circle'), document.createTextNode(button.dataset.busyLabel));
    });
    // Coming back through the bfcache must not leave buttons spinning.
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        document.querySelectorAll('[aria-busy="true"][data-idle-html]').forEach((b) => {
            b.innerHTML = b.dataset.idleHtml;
            b.removeAttribute('aria-busy');
            b.style.minWidth = '';
        });
    });
}

function errorSummary() {
    const summary = document.querySelector('[data-error-summary]');
    if (summary) {
        summary.focus({ preventScroll: true });
        summary.scrollIntoView({ block: 'center' });
    }
}

function dropzones() {
    document.querySelectorAll('[data-file-field]').forEach((field) => {
        const zone = field.querySelector('[data-dropzone]');
        const input = field.querySelector('input[type="file"]');
        const selected = field.querySelector('[data-file-selected]');
        const prompt = field.querySelector('[data-dropzone-prompt]');
        if (!zone || !input) return;
        const idle = prompt?.innerHTML;
        let url = null;

        ['dragenter', 'dragover'].forEach((t) => zone.addEventListener(t, () => {
            zone.dataset.dragover = '';
            if (prompt) prompt.innerHTML = '<strong>Drop to upload</strong>';
        }));
        ['dragleave', 'drop'].forEach((t) => zone.addEventListener(t, () => {
            delete zone.dataset.dragover;
            if (prompt && idle) prompt.innerHTML = idle;
        }));

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !selected) {
                if (selected) selected.hidden = true;
                zone.hidden = false;
                return;
            }
            const ext = (file.name.split('.').pop() || '').toUpperCase();
            selected.querySelector('[data-file-name]').textContent = file.name;
            selected.querySelector('[data-file-meta]').textContent = `${ext} · ${formatSize(file.size)}`;
            const img = selected.querySelector('[data-file-preview]');
            if (img && file.type.startsWith('image/')) {
                if (url) URL.revokeObjectURL(url);
                url = URL.createObjectURL(file);
                img.src = url;
            }
            selected.hidden = false;
            zone.hidden = true;
            field.querySelectorAll('[data-file-current]').forEach((el) => { el.hidden = true; });
            announce(`${file.name} selected.`);
        });

        field.querySelectorAll('[data-file-replace]').forEach((b) => b.addEventListener('click', () => input.click()));
    });
}

function slugs() {
    document.querySelectorAll('[data-slug-from]').forEach((slug) => {
        const source = document.getElementById(slug.dataset.slugFrom);
        if (!source) return;
        let touched = slug.value.trim() !== '';
        slug.addEventListener('input', () => { touched = slug.value.trim() !== ''; });
        source.addEventListener('input', () => {
            if (touched) return;
            slug.value = source.value
                .normalize('NFKD').replace(/[̀-ͯ]/g, '')
                .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
                .slice(0, 190);
        });
    });
}

/** Admin book form: the generated cover follows the typed title and chosen author. */
function coverPreview() {
    const host = document.querySelector('[data-cover-preview]');
    if (!host) return;
    const title = document.getElementById(host.dataset.titleInput);
    const author = document.getElementById(host.dataset.authorInput);
    const gTitle = host.querySelector('.gcover__title');
    const gAuthor = host.querySelector('.gcover__author');
    const gInitial = host.querySelector('.gcover__initial');
    const update = () => {
        const t = (title?.value || '').trim() || 'Untitled';
        const len = [...t].length;
        const byLen = len <= 14 ? 0 : len <= 32 ? 1 : len <= 60 ? 2 : 3;
        const word = Math.max(0, ...t.split(/[\s\-—]+/u).filter(Boolean).map((w) => [...w].length));
        const byWord = word <= 8 ? 0 : word <= 12 ? 1 : word <= 16 ? 2 : 3;
        if (gTitle) {
            gTitle.textContent = t;
            gTitle.dataset.len = ['s', 'm', 'l', 'xl'][Math.max(byLen, byWord)];
        }
        if (gInitial) gInitial.textContent = (t.replace(/^(the|a|an)\s+/iu, '')[0] || '').toUpperCase();
        if (gAuthor && author) {
            const opt = author.selectedOptions?.[0];
            gAuthor.textContent = opt && opt.value ? opt.textContent : '';
        }
    };
    title?.addEventListener('input', update);
    author?.addEventListener('change', update);
}

function copyButtons() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;
        try {
            await navigator.clipboard.writeText(btn.dataset.copy);
        } catch {
            return;
        }
        const label = btn.querySelector('[data-copy-label]');
        const idle = label?.textContent;
        if (label) label.textContent = 'Copied';
        announce('Copied to the clipboard.');
        setTimeout(() => { if (label && idle) label.textContent = idle; }, 2000);
    });
}

function fadeOuts() {
    document.querySelectorAll('[data-fade-out]').forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity 400ms';
            el.style.opacity = '0';
        }, Number(el.dataset.fadeOut) || 3000);
    });
}

/** GET filter forms: leave empty fields (and defaults marked data-default) out of the URL. */
function stripEmpty() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form.matches('form[data-strip-empty]') || e.defaultPrevented) return;
        form.querySelectorAll('input[name], select[name]').forEach((el) => {
            if (el.type === 'hidden' && el.value !== '') return;
            if (el.value === '' || (el.dataset.default !== undefined && el.value === el.dataset.default)) el.disabled = true;
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-strip-empty] [disabled]').forEach((el) => { el.disabled = false; });
    });
}

export function initForms() {
    stripEmpty();
    passwordToggles();
    busyButtons();
    errorSummary();
    dropzones();
    slugs();
    coverPreview();
    copyButtons();
    fadeOuts();
}
