// Native <dialog> helpers (DESIGN.md §4.18, §6.3): open/close animations, Esc, backdrop clicks,
// and the shared confirm dialog driven by data-confirm on submit buttons.
import { play, EASE } from './motion';

const kindOf = (d) => {
    if (d.classList.contains('menu-sheet')) return 'menu';
    if (d.classList.contains('sheet-bottom')) return 'sheet';
    if (d.classList.contains('drawer-left')) return 'drawer';
    return 'dialog';
};

export function openDialog(dialog) {
    if (!dialog || dialog.open) return;
    delete dialog.dataset.closing;
    dialog.showModal();
    const kind = kindOf(dialog);
    if (kind === 'menu') {
        play(dialog, { opacity: [0, 1], y: [-8, 0] }, { duration: 0.2, ease: EASE.out });
        const links = dialog.querySelectorAll('[data-menu-items] > *');
        [...links].slice(0, 8).forEach((el, i) => {
            play(el, { opacity: [0, 1], y: [8, 0] }, { duration: 0.28, ease: EASE.out, delay: 0.06 + i * 0.035 });
        });
    } else if (kind === 'sheet') {
        play(dialog, { y: ['100%', 0] }, { duration: 0.32, ease: EASE.out });
    } else if (kind === 'drawer') {
        play(dialog, { x: ['-100%', 0] }, { duration: 0.28, ease: EASE.out });
    } else {
        play(dialog, { opacity: [0, 1], y: [8, 0], scale: [0.985, 1] }, { duration: 0.24, ease: EASE.out });
    }
}

export function closeDialog(dialog) {
    if (!dialog || !dialog.open || dialog.dataset.closing) return Promise.resolve();
    dialog.dataset.closing = '1';
    const kind = kindOf(dialog);
    let a;
    if (kind === 'sheet') a = play(dialog, { y: '100%' }, { duration: 0.22, ease: EASE.in });
    else if (kind === 'drawer') a = play(dialog, { x: '-100%' }, { duration: 0.2, ease: EASE.in });
    else if (kind === 'menu') a = play(dialog, { opacity: 0 }, { duration: 0.15, ease: EASE.in });
    else a = play(dialog, { opacity: 0, scale: 0.985 }, { duration: 0.15, ease: EASE.in });
    return Promise.resolve(a?.finished ?? a).catch(() => {}).then(() => {
        dialog.close();
        delete dialog.dataset.closing;
        // Reset transforms so the next open starts clean.
        dialog.style.opacity = '';
        dialog.style.transform = '';
    });
}

function setupConfirm() {
    const dialog = document.querySelector('[data-confirm-dialog]');
    if (!dialog) return;
    const title = dialog.querySelector('[data-confirm-title]');
    const text = dialog.querySelector('[data-confirm-text]');
    const ok = dialog.querySelector('[data-confirm-ok]');
    const cancel = dialog.querySelector('[data-confirm-cancel]');
    let pending = null;

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('button[data-confirm]');
        if (!trigger || trigger.dataset.confirmed) return;
        if (trigger.getAttribute('aria-disabled') === 'true') {
            e.preventDefault();
            return;
        }
        e.preventDefault();
        pending = trigger;
        title.textContent = trigger.dataset.confirm;
        text.textContent = trigger.dataset.confirmBody || '';
        text.hidden = !trigger.dataset.confirmBody;
        ok.textContent = trigger.dataset.confirmOk || 'Confirm';
        cancel.textContent = trigger.dataset.confirmCancel || 'Keep it';
        ok.className = `btn ${trigger.dataset.confirmVariant === 'primary' ? 'btn-primary' : 'btn-danger'}`;
        ok.removeAttribute('aria-busy');
        ok.disabled = false;
        openDialog(dialog);
        cancel.focus();
    });

    ok.addEventListener('click', () => {
        if (!pending) return;
        const trigger = pending;
        const form = trigger.form;
        if (trigger.dataset.confirmBusy) {
            ok.style.minWidth = `${ok.offsetWidth}px`;
            ok.setAttribute('aria-busy', 'true');
            ok.textContent = trigger.dataset.confirmBusy;
        }
        trigger.dataset.confirmed = '1';
        if (form) {
            if (form.requestSubmit) form.requestSubmit(trigger);
            else form.submit();
        }
        delete trigger.dataset.confirmed;
    });

    dialog.addEventListener('close', () => { pending = null; });
}

export function initDialogs() {
    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-dialog-open]');
        if (opener) {
            e.preventDefault();
            openDialog(document.getElementById(opener.dataset.dialogOpen));
            return;
        }
        const closer = e.target.closest('[data-dialog-close]');
        if (closer) {
            closeDialog(closer.closest('dialog'));
            return;
        }
        // A click whose target is the <dialog> itself landed on the backdrop.
        if (e.target instanceof HTMLDialogElement && e.target.open && kindOf(e.target) !== 'menu') {
            const r = e.target.getBoundingClientRect();
            const inside = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
            if (!inside) closeDialog(e.target);
        }
    });

    document.querySelectorAll('dialog').forEach((d) => {
        d.addEventListener('cancel', (e) => {
            e.preventDefault();
            closeDialog(d);
        });
    });

    // Re-open a dialog after a failed submit (e.g. account deletion password error).
    document.querySelectorAll('dialog[data-open-on-load]').forEach((d) => openDialog(d));

    setupConfirm();
}
