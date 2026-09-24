const dock = document.querySelector('.bottom-nav');

document.querySelectorAll('[data-dismiss-toast]').forEach((button) => {
    button.addEventListener('click', () => button.closest('[data-toast]')?.remove());
});

document.querySelectorAll('[data-confirm-logout]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('¿Quieres cerrar tu sesión?')) event.preventDefault();
    });

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm('¿Seguro que quieres eliminar este registro?')) event.preventDefault();
        });
    });
});

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => form.classList.add('is-submitting'));
});

if (dock) {
    const links = [...dock.querySelectorAll('a.dock-item')];
    const items = [...dock.querySelectorAll('.dock-item')];
    const home = dock.querySelector('[data-tab="home"]');
    const explore = dock.querySelector('[data-tab="explore"]');
    let gesture = null;
    let suppressClick = false;
    let hoverItem = null;

    const movePill = (item) => {
        if (!item) return;
        const bounds = item.getBoundingClientRect();
        const dockBounds = dock.getBoundingClientRect();
        dock.style.setProperty('--pill-x', `${bounds.left - dockBounds.left - dock.clientLeft}px`);
        dock.style.setProperty('--pill-width', `${bounds.width}px`);
    };

    const syncSelection = () => {
        if (home && explore && new URL(home.href).pathname === location.pathname) {
            const exploring = location.hash === '#espacio';
            home.removeAttribute('aria-current');
            explore.removeAttribute('aria-current');
            (exploring ? explore : home).setAttribute('aria-current', exploring ? 'location' : 'page');
        }
        movePill(dock.querySelector('[aria-current]'));
    };

    const clearPreview = () => {
        dock.classList.remove('is-dragging');
        links.forEach((link) => link.classList.remove('is-preview'));
        gesture = null;
    };

    const restoreSelection = () => {
        hoverItem?.classList.remove('is-hover');
        hoverItem = null;
        if (!dock.classList.contains('is-dragging')) movePill(dock.querySelector('[aria-current]'));
    };

    const previewItem = (item) => {
        if (dock.classList.contains('is-dragging')) return;
        restoreSelection();
        hoverItem = item;
        hoverItem.classList.add('is-hover');
        movePill(item);
    };

    items.forEach((item) => {
        item.addEventListener('pointerenter', () => previewItem(item));
        item.addEventListener('pointerleave', restoreSelection);
        item.addEventListener('focus', () => previewItem(item));
        item.addEventListener('blur', restoreSelection);
    });

    dock.addEventListener('pointerdown', (event) => {
        const link = event.target.closest('a.dock-item');
        if (!link || !event.isPrimary || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        suppressClick = false;
        gesture = { id: event.pointerId, x: event.clientX, y: event.clientY, dragging: false, target: link };
    });

    dock.addEventListener('pointermove', (event) => {
        if (!gesture || gesture.id !== event.pointerId) return;
        const dx = event.clientX - gesture.x;
        const dy = event.clientY - gesture.y;
        if (!gesture.dragging && Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 8) {
            clearPreview();
            return;
        }
        if (!gesture.dragging && Math.abs(dx) > 8) {
            gesture.dragging = true;
            dock.setPointerCapture(event.pointerId);
            dock.classList.add('is-dragging');
        }
        if (!gesture.dragging) return;
        const target = links.find((link) => {
            const rect = link.getBoundingClientRect();
            return event.clientX >= rect.left && event.clientX <= rect.right;
        });
        if (!target) return;
        gesture.target = target;
        links.forEach((link) => link.classList.toggle('is-preview', link === target));
        movePill(target);
    });

    dock.addEventListener('pointerup', (event) => {
        if (!gesture || gesture.id !== event.pointerId) return;
        const { dragging, target } = gesture;
        const bounds = dock.getBoundingClientRect();
        const inside = event.clientX >= bounds.left && event.clientX <= bounds.right
            && event.clientY >= bounds.top && event.clientY <= bounds.bottom;
        clearPreview();
        if (dragging) {
            suppressClick = true;
            if (dock.hasPointerCapture(event.pointerId)) dock.releasePointerCapture(event.pointerId);
            if (inside) {
                location.assign(target.href);
            } else {
                syncSelection();
            }
        }
    });

    dock.addEventListener('pointercancel', () => {
        clearPreview();
        syncSelection();
    });
    dock.addEventListener('lostpointercapture', () => {
        clearPreview();
        syncSelection();
    });
    dock.addEventListener('click', (event) => {
        if (suppressClick && event.detail !== 0) {
            event.preventDefault();
            suppressClick = false;
            return;
        }
        const link = event.target.closest('a.dock-item');
        if (link && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) movePill(link);
    });
    dock.addEventListener('pointerleave', restoreSelection);
    dock.addEventListener('dragstart', (event) => event.preventDefault());
    window.addEventListener('pointerup', () => {
        if (gesture && !gesture.dragging) clearPreview();
    });
    window.addEventListener('hashchange', syncSelection);
    window.addEventListener('pageshow', syncSelection);
    window.addEventListener('resize', syncSelection);
    if ('ResizeObserver' in window) new ResizeObserver(syncSelection).observe(dock);
    syncSelection();
    requestAnimationFrame(() => dock.classList.add('is-ready'));
}
