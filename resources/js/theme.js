// Theme: OS preference by default; an explicit choice pins <html data-theme> and is stored in
// localStorage["bp-theme"]. The inline <head> script applies it before first paint.
const KEY = 'bp-theme';
const root = document.documentElement;
const dark = matchMedia('(prefers-color-scheme: dark)');
const BG = { light: '#F6F1E7', dark: '#151413' };

const choice = () => root.dataset.theme || 'system';
const effective = () => root.dataset.theme || (dark.matches ? 'dark' : 'light');

function store(value) {
    try {
        if (value === 'system') localStorage.removeItem(KEY);
        else localStorage.setItem(KEY, value);
    } catch {
        // Private mode or blocked storage: the choice lasts for this page only.
    }
}

function sync() {
    const current = choice();
    const isDark = effective() === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((b) => b.setAttribute('aria-pressed', String(isDark)));
    document.querySelectorAll('[data-theme-choice]').forEach((b) => {
        b.setAttribute('aria-pressed', String(b.dataset.themeChoice === current));
    });
    document.querySelectorAll('meta[name="theme-color"]').forEach((m) => {
        if (current === 'system') m.setAttribute('content', m.media.includes('dark') ? BG.dark : BG.light);
        else m.setAttribute('content', BG[current]);
    });
}

export function setTheme(value) {
    if (value === 'system') delete root.dataset.theme;
    else root.dataset.theme = value;
    store(value);
    sync();
}

export function initTheme() {
    document.addEventListener('click', (e) => {
        const toggle = e.target.closest('[data-theme-toggle]');
        if (toggle) {
            setTheme(effective() === 'dark' ? 'light' : 'dark');
            return;
        }
        const option = e.target.closest('[data-theme-choice]');
        if (option) setTheme(option.dataset.themeChoice);
    });
    dark.addEventListener('change', sync);
    sync();
}
