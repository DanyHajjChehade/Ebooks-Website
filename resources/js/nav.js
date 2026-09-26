// Header behaviour (DESIGN.md §4.1–4.2): mobile menu sheet, search rows, account menu,
// admin drawer, catalogue filter sheet. Every trigger is a real link without JS.
import { openDialog } from './dialogs';
import { play, EASE } from './motion';

function asButton(el) {
    el.setAttribute('role', 'button');
}

function mobileMenu() {
    const sheet = document.getElementById('mobile-menu');
    document.querySelectorAll('[data-menu-open]').forEach((trigger) => {
        if (!sheet) return;
        asButton(trigger);
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openDialog(sheet);
            trigger.setAttribute('aria-expanded', 'true');
        });
        sheet.addEventListener('close', () => trigger.setAttribute('aria-expanded', 'false'));
    });
}

function mobileSearch() {
    const row = document.querySelector('[data-mobile-search]');
    const toggle = document.querySelector('[data-mobile-search-toggle]');
    if (!row || !toggle) return;
    asButton(toggle);
    toggle.setAttribute('aria-expanded', 'false');
    const input = row.querySelector('input[type="search"]');
    const close = () => {
        row.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    };
    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        if (!row.hidden) return close();
        row.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        play(row, { opacity: [0, 1], y: [-4, 0] }, { duration: 0.16, ease: EASE.out });
        input?.focus();
    });
    row.querySelector('[data-mobile-search-close]')?.addEventListener('click', close);
    row.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
}

function headerSearch() {
    const form = document.querySelector('[data-header-search]');
    if (!form) return;
    const toggle = form.querySelector('[data-header-search-toggle]');
    const input = form.querySelector('[data-header-search-input]');
    if (!toggle || !input) return;
    asButton(toggle);
    toggle.setAttribute('aria-expanded', 'false');
    const collapse = (refocus) => {
        delete form.dataset.open;
        toggle.setAttribute('aria-expanded', 'false');
        if (refocus) toggle.focus();
    };
    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        form.dataset.open = '';
        toggle.setAttribute('aria-expanded', 'true');
        requestAnimationFrame(() => input.focus());
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && 'open' in form.dataset) collapse(true);
    });
    form.addEventListener('focusout', (e) => {
        if (!form.contains(e.relatedTarget) && input.value.trim() === '' && 'open' in form.dataset) collapse(false);
    });
}

function accountMenus() {
    document.querySelectorAll('details[data-menu]').forEach((menu) => {
        const summary = menu.querySelector('summary');
        const panel = menu.querySelector('.menu__panel');
        menu.addEventListener('toggle', () => {
            if (menu.open && panel) play(panel, { opacity: [0, 1], y: [-4, 0] }, { duration: 0.16, ease: EASE.out });
        });
        menu.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && menu.open) {
                menu.open = false;
                summary.focus();
            }
        });
        document.addEventListener('click', (e) => {
            if (menu.open && !menu.contains(e.target)) menu.open = false;
        });
        menu.addEventListener('focusout', (e) => {
            if (menu.open && e.relatedTarget && !menu.contains(e.relatedTarget)) menu.open = false;
        });
    });
}

function drawers() {
    document.querySelectorAll('[data-drawer-open]').forEach((trigger) => {
        const drawer = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!drawer) return;
        asButton(trigger);
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openDialog(drawer);
            trigger.setAttribute('aria-expanded', 'true');
        });
        drawer.addEventListener('close', () => trigger.setAttribute('aria-expanded', 'false'));
    });
}

function filterSheet() {
    document.querySelectorAll('[data-filter-open]').forEach((trigger) => {
        const sheet = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!sheet) return;
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openDialog(sheet);
            trigger.setAttribute('aria-expanded', 'true');
        });
        sheet.addEventListener('close', () => trigger.setAttribute('aria-expanded', 'false'));
    });
    // Desktop: selects submit on change (the Apply button stays as the no-JS fallback).
    // Inside an open bottom sheet they wait for "Show results".
    document.addEventListener('change', (e) => {
        const select = e.target.closest('select[data-autosubmit]');
        if (!select || select.closest('dialog[open]')) return;
        select.form?.requestSubmit();
    });
}

export function initNav() {
    mobileMenu();
    mobileSearch();
    headerSearch();
    accountMenus();
    drawers();
    filterSheet();
}
