// Toasts (DESIGN.md §4.12). Server-rendered flashes and JS toasts share one markup.
//   window.dispatchEvent(new CustomEvent('toast', { detail: { type, message, action, actionLabel } }))
// `message` is a string or an array of strings and { em: 'Title' } parts (titles are italic).
import { play, EASE } from './motion';

const ICONS = { success: 'circle-check', error: 'circle-alert', info: 'info' };
const MAX = 3;
const TIMEOUT = 5000;

export function icon(name) {
    const svg = document.querySelector(`#tpl-icons`)?.content.querySelector(`[data-icon="${name}"]`);
    return svg ? svg.cloneNode(true) : document.createElement('span');
}

export function announce(text) {
    const live = document.getElementById('announcer');
    if (!live) return;
    live.textContent = '';
    // A fresh text node after a tick makes screen readers repeat identical messages.
    setTimeout(() => { live.textContent = text; }, 50);
}

function region() {
    return document.querySelector('[data-toast-region]');
}

function dismiss(el) {
    if (!el || el.dataset.leaving) return;
    el.dataset.leaving = '1';
    clearTimeout(el._timer);
    const out = play(el, { opacity: 0, y: 6 }, { duration: 0.18, ease: EASE.in });
    Promise.resolve(out?.finished ?? out)
        .then(() => play(el, { height: [`${el.offsetHeight}px`, '0px'], marginTop: 0, paddingTop: 0, paddingBottom: 0 }, { duration: 0.16, ease: EASE.inOut }))
        .then((a) => a?.finished ?? a)
        .finally(() => el.remove());
}

function arm(el) {
    const type = el.dataset.type || 'success';
    el.querySelector('[data-toast-dismiss]')?.addEventListener('click', () => dismiss(el));
    if (type === 'error') return; // errors stay until dismissed
    let remaining = TIMEOUT;
    let started = Date.now();
    const start = () => { started = Date.now(); el._timer = setTimeout(() => dismiss(el), remaining); };
    const pause = () => { clearTimeout(el._timer); remaining -= Date.now() - started; };
    el.addEventListener('mouseenter', pause);
    el.addEventListener('mouseleave', start);
    el.addEventListener('focusin', pause);
    el.addEventListener('focusout', (e) => { if (!el.contains(e.relatedTarget)) start(); });
    start();
}

function trim() {
    const live = [...(region()?.querySelectorAll('[data-toast]:not([data-leaving])') ?? [])];
    live.slice(0, Math.max(0, live.length - MAX)).forEach(dismiss);
}

export function toast({ type = 'success', message = '', action = null, actionLabel = null } = {}) {
    const tpl = document.getElementById('tpl-toast');
    const host = region();
    if (!tpl || !host) return null;
    const el = tpl.content.firstElementChild.cloneNode(true);
    el.classList.add(`toast--${type}`);
    el.dataset.type = type;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.querySelector('[data-toast-icon]').replaceWith(icon(ICONS[type] || 'info'));
    const msg = el.querySelector('[data-toast-message]');
    (Array.isArray(message) ? message : [message]).forEach((part) => {
        if (part && typeof part === 'object' && 'em' in part) {
            const em = document.createElement('em');
            em.className = 'font-serif';
            em.textContent = part.em;
            msg.append(em);
        } else {
            msg.append(document.createTextNode(String(part ?? '')));
        }
    });
    if (action) {
        const a = document.createElement('a');
        a.className = 'toast__action';
        a.href = action;
        a.textContent = actionLabel || 'View';
        msg.after(document.createElement('br'), a);
    }
    host.append(el);
    play(el, { opacity: [0, 1], y: [12, 0], scale: [0.98, 1] }, { duration: 0.28, ease: EASE.out });
    arm(el);
    trim();
    return el;
}

export function initToasts() {
    region()?.querySelectorAll('[data-toast]').forEach(arm);
    trim();
    window.addEventListener('toast', (e) => toast(e.detail || {}));
}
