// Motion (motion.dev, vanilla API). DESIGN.md §6. Every animation goes through play(),
// which honours prefers-reduced-motion. Nothing is hidden by CSS waiting for JS: reveals
// only hide elements that start below the fold, and only from this script.
import { animate, inView } from 'motion';

export const reduce = matchMedia('(prefers-reduced-motion: reduce)');
export const EASE = {
    out: [0.25, 1, 0.5, 1],
    in: [0.5, 0, 0.75, 0],
    inOut: [0.76, 0, 0.24, 1],
    standard: [0.2, 0, 0, 1],
};

const last = (v) => (Array.isArray(v) ? v[v.length - 1] : v);

/** The only way to animate. Under reduced motion: opacity only (≤120ms), everything else jumps. */
export function play(el, keyframes, options = {}) {
    if (!reduce.matches) return animate(el, keyframes, options);
    if ('opacity' in keyframes) {
        return animate(el, { opacity: keyframes.opacity }, { duration: Math.min(options.duration ?? 0.12, 0.12) });
    }
    return animate(el, Object.fromEntries(Object.entries(keyframes).map(([k, v]) => [k, last(v)])), { duration: 0 });
}

/** Stagger book grids / section heads that start below the fold (never on-screen content). */
export function revealBelowFold() {
    if (reduce.matches) return;
    document.querySelectorAll('[data-reveal], [data-reveal-grid]').forEach((el) => {
        if (el.getBoundingClientRect().top < innerHeight) return;
        const grid = el.hasAttribute('data-reveal-grid');
        const items = grid ? [...el.children] : [el];
        const dy = grid ? 16 : 10;
        animate(items, { opacity: 0, y: dy }, { duration: 0 });
        inView(el, () => {
            animate(items, { opacity: [0, 1], y: [dy, 0] }, {
                duration: grid ? 0.45 : 0.5,
                ease: EASE.out,
                delay: (i) => Math.min(i, 7) * 0.04,
            });
        }, { amount: el.offsetHeight > innerHeight * 2 ? 0 : 0.2 }); // very tall blocks could never be 20% visible
    });
}

/** Header hairline appears once the page scrolls 8px (inView sentinel, no scroll listeners). */
function headerBorder() {
    const header = document.querySelector('[data-site-header]');
    const sentinel = document.querySelector('[data-scroll-sentinel]');
    if (!header || !sentinel) return;
    inView(sentinel, () => {
        header.classList.remove('is-scrolled');
        return () => header.classList.add('is-scrolled');
    });
}

/** Fade uploaded cover images in once loaded (sunken frame shows meanwhile). */
function imageFade() {
    document.querySelectorAll('.cover > img').forEach((img) => {
        if (img.complete) return;
        img.classList.add('is-loading');
        const done = () => img.classList.remove('is-loading');
        img.addEventListener('load', done, { once: true });
        img.addEventListener('error', done, { once: true });
    });
}

/** Book page: the cover tilts toward a fine pointer (spring), never under reduced motion. */
function coverTilt() {
    if (reduce.matches || !matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    document.querySelectorAll('.cover-stage').forEach((stage) => {
        const cover = stage.querySelector('.cover');
        if (!cover) return;
        stage.addEventListener('pointermove', (e) => {
            const r = cover.getBoundingClientRect();
            const x = Math.max(-0.5, Math.min(0.5, (e.clientX - r.left) / r.width - 0.5));
            const y = Math.max(-0.5, Math.min(0.5, (e.clientY - r.top) / r.height - 0.5));
            animate(cover, { rotateY: x * 10, rotateX: -y * 8 }, { type: 'spring', stiffness: 260, damping: 24 });
        });
        stage.addEventListener('pointerleave', () => {
            animate(cover, { rotateY: 0, rotateX: 0 }, { type: 'spring', stiffness: 200, damping: 20 });
        });
    });
}

/** Mobile sticky buy bar: shown while the main CTA is off screen and the footer isn't in view. */
function buyBar() {
    const bar = document.querySelector('[data-buy-bar]');
    const cta = document.querySelector('[data-cta]');
    if (!bar || !cta) return;
    const footer = document.querySelector('#site-footer');
    let ctaVisible = true;
    let footerVisible = false;
    let shown = false;
    const update = () => {
        const want = !ctaVisible && !footerVisible;
        if (want === shown) return;
        shown = want;
        if (want) {
            bar.hidden = false;
            play(bar, { y: ['100%', 0] }, { duration: 0.24, ease: EASE.out });
        } else {
            const a = play(bar, { y: [0, '100%'] }, { duration: 0.18, ease: EASE.in });
            Promise.resolve(a?.finished ?? a).then(() => { if (!shown) bar.hidden = true; });
        }
    };
    inView(cta, () => { ctaVisible = true; update(); return () => { ctaVisible = false; update(); }; });
    if (footer) inView(footer, () => { footerVisible = true; update(); return () => { footerVisible = false; update(); }; });
}

/** Legal pages: aria-current follows the section in view. */
function legalToc() {
    const toc = document.querySelector('[data-toc]');
    if (!toc) return;
    const links = [...toc.querySelectorAll('a[href^="#"]')];
    const heads = [...document.querySelectorAll('[data-toc-target] h2[id]')];
    const visible = new Set();
    const set = (id) => links.forEach((a) => {
        if (id && a.getAttribute('href') === `#${id}`) a.setAttribute('aria-current', 'true');
        else a.removeAttribute('aria-current');
    });
    // Current = the first heading inside the reading zone; otherwise the last one above it.
    const update = () => {
        const inZone = heads.find((h) => visible.has(h));
        if (inZone) return set(inZone.id);
        const above = heads.filter((h) => h.getBoundingClientRect().top < innerHeight * 0.4).pop();
        set(above?.id ?? null);
    };
    heads.forEach((h) => {
        inView(h, () => {
            visible.add(h);
            update();
            return () => { visible.delete(h); update(); };
        }, { amount: 0.5, margin: '0px 0px -60% 0px' });
    });
}

export function initMotion() {
    headerBorder();
    imageFade();
    revealBelowFold();
    coverTilt();
    buyBar();
    legalToc();
}
