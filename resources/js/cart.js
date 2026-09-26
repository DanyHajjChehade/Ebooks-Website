// Add to cart / remove from cart with fetch (DESIGN.md §6.3). Without JS the same forms post
// normally and the server redirects back with a flash message.
import { play, EASE } from './motion';
import { toast, announce, icon } from './toast';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const cartUrl = () => document.querySelector('[data-cart-link]')?.getAttribute('href') ?? '/cart';
const books = (n) => `${n} ${n === 1 ? 'book' : 'books'}`;

async function send(form, method = 'POST') {
    const body = new FormData(form);
    return fetch(form.action, {
        method,
        body,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

export function setBusy(button, label) {
    if (!button) return () => {};
    const html = button.innerHTML;
    const aria = button.getAttribute('aria-label');
    button.style.minWidth = `${button.offsetWidth}px`;
    button.setAttribute('aria-busy', 'true');
    button.disabled = true;
    button.replaceChildren(icon('loader-circle'), document.createTextNode(label));
    if (aria) button.setAttribute('aria-label', aria.replace(/^Add /, 'Adding '));
    return () => {
        button.innerHTML = html;
        button.removeAttribute('aria-busy');
        button.disabled = false;
        button.style.minWidth = '';
        if (aria) button.setAttribute('aria-label', aria);
    };
}

/** Update every cart badge + label, with the bump / bag wiggle. */
export function setCartCount(n) {
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        const was = el.hidden ? 0 : Number(el.textContent) || 0;
        el.textContent = String(n);
        el.hidden = n === 0;
        if (n === 0 || n === was) return;
        if (was === 0) play(el, { opacity: [0, 1], scale: [0.4, 1] }, { duration: 0.3, ease: EASE.out });
        else play(el, { scale: [1, 1.28, 0.96, 1] }, { duration: 0.45, times: [0, 0.3, 0.7, 1], ease: 'easeOut' });
    });
    document.querySelectorAll('[data-cart-link]').forEach((a) => a.setAttribute('aria-label', `Cart, ${books(n)}`));
    document.querySelectorAll('[data-cart-icon]').forEach((i) => play(i, { rotate: [0, -10, 6, 0] }, { duration: 0.42, ease: 'easeInOut' }));
}

function flagCover(card) {
    const cover = card?.querySelector('.cover');
    if (!cover || cover.querySelector('.cover-flag')) return;
    const flag = document.createElement('span');
    flag.className = 'cover-flag';
    flag.append(icon('shopping-bag'), document.createTextNode('In cart'));
    cover.append(flag);
    play(flag, { opacity: [0, 1], scale: [0.9, 1] }, { duration: 0.18, ease: EASE.out });
}

/** Swap a form's surroundings into the "in cart" state. */
function showInCart(form) {
    const card = form.closest('[data-book-card]');
    if (card) {
        const link = document.createElement('a');
        link.className = 'btn btn-ghost btn-sm book-card__action';
        link.href = cartUrl();
        link.textContent = 'View cart';
        form.replaceWith(link);
        play(link, { opacity: [0, 1], y: [4, 0] }, { duration: 0.16, ease: EASE.out });
        flagCover(card);
        return;
    }
    // Book page / spotlight: swap every block of the same group (CTA + mobile buy bar) from its template.
    const group = form.closest('[data-cta-swap]')?.dataset.ctaSwap;
    if (!group) return;
    document.querySelectorAll(`[data-cta-swap="${group}"]`).forEach((host) => {
        const tpl = host.querySelector('template[data-in-cart]');
        if (!tpl) return;
        host.replaceChildren(tpl.content.cloneNode(true));
        play(host, { opacity: [0, 1], y: [4, 0] }, { duration: 0.16, ease: EASE.out });
    });
}

async function addToCart(form) {
    const button = form.querySelector('button[type="submit"]');
    const restore = setBusy(button, button?.dataset.busyLabel || 'Adding…');
    const title = form.dataset.title || 'That book';
    let res;
    try {
        res = await send(form);
    } catch {
        restore();
        toast({ type: 'error', message: 'We couldn’t add that book. Check your connection and try again.' });
        return;
    }
    if (res.status === 419 || res.status >= 500 || res.redirected) {
        // Session expired or something unexpected: fall back to a normal form post.
        form.submit();
        return;
    }
    let data = {};
    try { data = await res.json(); } catch { /* keep {} */ }
    if (!res.ok) {
        restore();
        toast({ type: 'error', message: res.status === 429 ? 'Too many tries at once. Wait a minute, then try again.' : (data.message || 'We couldn’t add that book. Check your connection and try again.') });
        return;
    }
    const count = Number(data.count ?? 0);
    switch (data.result) {
        case 'added':
        case 'exists':
            setCartCount(count);
            showInCart(form);
            toast({ type: 'success', message: ['Added ', { em: title }, ' to your cart.'], action: cartUrl(), actionLabel: 'View cart' });
            announce(`Added ${title}. ${books(count)} in your cart.`);
            break;
        case 'owned':
            restore();
            toast({ type: 'info', message: 'You already own this book. It’s in your library.' });
            break;
        default:
            restore();
            toast({ type: 'error', message: data.message || 'This book is not available right now.' });
    }
}

const money = (cents, el) => {
    if (cents === 0) return 'Free';
    const currency = el?.dataset.currency || 'USD';
    const locale = el?.dataset.locale || document.documentElement.lang || 'en';
    return new Intl.NumberFormat(locale, { style: 'currency', currency }).format(cents / 100);
};

async function removeFromCart(form) {
    const row = form.closest('[data-cart-row]');
    const button = form.querySelector('button[type="submit"]');
    const restore = setBusy(button, 'Removing…');
    const title = form.dataset.title || 'the book';
    let res;
    try {
        res = await send(form);
    } catch {
        restore();
        toast({ type: 'error', message: 'We couldn’t remove that book. Check your connection and try again.' });
        return;
    }
    if (!res.ok || res.redirected) {
        form.submit();
        return;
    }
    const data = await res.json().catch(() => ({}));
    const count = Number(data.count ?? 0);
    setCartCount(count);
    toast({ type: 'success', message: ['Removed ', { em: title }, ' from your cart.'] });
    announce(`Removed ${title}. ${books(count)} in your cart.`);

    if (row) {
        const fade = play(row, { opacity: 0 }, { duration: 0.15, ease: EASE.in });
        await Promise.resolve(fade?.finished ?? fade).catch(() => {});
        const collapse = play(row, { height: [`${row.offsetHeight}px`, '0px'], paddingTop: 0, paddingBottom: 0 }, { duration: 0.2, ease: EASE.inOut });
        await Promise.resolve(collapse?.finished ?? collapse).catch(() => {});
        row.remove();
    }

    const summary = document.querySelector('[data-cart-summary]');
    if (count === 0) {
        const tpl = document.querySelector('template[data-cart-empty]');
        const host = document.querySelector('[data-cart-body]');
        if (tpl && host) host.replaceChildren(tpl.content.cloneNode(true));
        document.querySelectorAll('[data-cart-books-count]').forEach((el) => { el.textContent = books(0); });
        return;
    }
    const cents = Number(data.subtotalCents ?? 0);
    document.querySelectorAll('[data-subtotal]').forEach((el) => {
        el.textContent = money(cents, summary);
        play(el, { opacity: [0.4, 1] }, { duration: 0.12 });
    });
    document.querySelectorAll('[data-cart-books-count]').forEach((el) => { el.textContent = books(count); });
    if (cents === 0) {
        document.querySelectorAll('[data-checkout-label]').forEach((el) => { el.textContent = el.dataset.freeLabel || el.textContent; });
    }
}

export function initCart() {
    document.addEventListener('submit', (e) => {
        const add = e.target.closest('form[data-add-to-cart]');
        if (add) {
            e.preventDefault();
            addToCart(add);
            return;
        }
        const remove = e.target.closest('form[data-cart-remove]');
        if (remove) {
            e.preventDefault();
            removeFromCart(remove);
        }
    });
}
