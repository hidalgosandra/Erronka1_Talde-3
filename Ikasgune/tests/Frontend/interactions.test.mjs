import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/js/app.js', import.meta.url), 'utf8');

function setup() {
    let document;
    class Element extends EventTarget {
        constructor() {
            super();
            this.dataset = {};
            this.attributes = {};
            this.inert = false;
            this.hidden = false;
            this.submissions = 0;
            const classes = new Set();
            this.classList = {
                add: (name) => classes.add(name),
                remove: (name) => classes.delete(name),
                contains: (name) => classes.has(name),
                toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name),
            };
        }
        setAttribute(key, value) { this.attributes[key] = value; }
        removeAttribute(key) { delete this.attributes[key]; }
        focus() { document.activeElement = this; }
        requestSubmit() {
            if (this.dispatchEvent(new Event('submit', { cancelable: true }))) this.submissions++;
        }
    }
    const body = new Element();
    const modal = new Element();
    modal.hidden = true;
    const trigger = new Element();
    const cancel = new Element();
    const confirm = new Element();
    const logout = new Element();
    const deletion = new Element();
    const normal = new Element();
    const window = new EventTarget();
    window.confirm = () => false;
    const forms = [logout, deletion, normal];
    const tabs = ['admin-overview', 'admin-courses', 'admin-users'].map((id) => {
        const tab = new Element();
        tab.dataset.adminTab = id;
        return tab;
    });
    const panels = tabs.map((tab) => {
        const panel = new Element();
        panel.id = tab.dataset.adminTab;
        return panel;
    });
    let lastUrl;
    body.children = [logout, normal, modal];
    logout.querySelector = () => trigger;
    modal.querySelector = (selector) => selector === '[data-close-logout-modal]' ? cancel : confirm;
    modal.querySelectorAll = () => [cancel, confirm];
    document = new EventTarget();
    Object.assign(document, {
        body, activeElement: trigger,
        querySelector: (selector) => selector === '[data-logout-modal]' ? modal : null,
        querySelectorAll: (selector) => ({
            '[data-dismiss-toast]': [], '[data-confirm-logout]': [logout],
            '[data-confirm-delete]': [deletion], form: forms,
            'form.is-submitting': forms.filter((form) => form.classList.contains('is-submitting')),
            '[data-admin-tab]': tabs, '.admin-panel': panels,
        }[selector] ?? []),
    });
    runInNewContext(source, {
        document, window, HTMLElement: Element, queueMicrotask, URL,
        location: { href: 'http://localhost/admin' },
        history: { replaceState: (_state, _title, url) => { lastUrl = url; } },
    });
    const key = (target, value, shiftKey = false) => {
        const event = new Event('keydown', { cancelable: true });
        Object.assign(event, { key: value, shiftKey });
        target.dispatchEvent(event);
    };
    return { document, window, logout, deletion, normal, trigger, modal, cancel, confirm, tabs, panels, key, url: () => lastUrl };
}

test('cancelling logout leaves the form usable and restores focus', async () => {
    const ui = setup();
    ui.logout.requestSubmit();
    await Promise.resolve();
    assert.equal(ui.modal.hidden, false);
    assert.equal(ui.logout.classList.contains('is-submitting'), false);
    assert.equal(ui.normal.inert, true);
    ui.cancel.dispatchEvent(new Event('click'));
    assert.equal(ui.modal.hidden, true);
    assert.equal(ui.normal.inert, false);
    assert.equal(ui.document.activeElement, ui.trigger);
    ui.logout.requestSubmit();
    assert.equal(ui.modal.hidden, false);
});

test('confirming logout submits once and marks the form busy', async () => {
    const ui = setup();
    ui.logout.requestSubmit();
    ui.confirm.dispatchEvent(new Event('click'));
    await Promise.resolve();
    assert.equal(ui.logout.submissions, 1);
    assert.equal(ui.logout.attributes['aria-busy'], 'true');
    ui.logout.requestSubmit();
    assert.equal(ui.logout.submissions, 1);
});

test('cancelling delete does not leave a submitting state', async () => {
    const ui = setup();
    ui.deletion.requestSubmit();
    await Promise.resolve();
    assert.equal(ui.deletion.submissions, 0);
    assert.equal(ui.deletion.classList.contains('is-submitting'), false);
});

test('logout dialog traps tab focus and Escape restores the trigger', () => {
    const ui = setup();
    ui.logout.requestSubmit();
    ui.key(ui.document, 'Tab', true);
    assert.equal(ui.document.activeElement, ui.confirm);
    ui.key(ui.document, 'Tab');
    assert.equal(ui.document.activeElement, ui.cancel);
    ui.key(ui.document, 'Escape');
    assert.equal(ui.document.activeElement, ui.trigger);
    assert.equal(ui.modal.hidden, true);
});

test('admin tabs support arrows, Home and End and persist the selected tab in the URL', () => {
    const ui = setup();
    ui.key(ui.tabs[0], 'ArrowRight');
    assert.equal(ui.document.activeElement, ui.tabs[1]);
    assert.equal(ui.panels[1].hidden, false);
    assert.equal(ui.panels[0].hidden, true);
    assert.equal(ui.tabs[1].attributes['aria-selected'], 'true');
    assert.equal(ui.tabs[0].tabIndex, -1);
    assert.equal(ui.url().searchParams.get('tab'), 'admin-courses');
    ui.key(ui.tabs[1], 'End');
    assert.equal(ui.document.activeElement, ui.tabs[2]);
    ui.key(ui.tabs[2], 'Home');
    assert.equal(ui.document.activeElement, ui.tabs[0]);
});

test('back navigation clears the submitting state', async () => {
    const ui = setup();
    ui.normal.requestSubmit();
    await Promise.resolve();
    assert.equal(ui.normal.classList.contains('is-submitting'), true);
    ui.window.dispatchEvent(new Event('pageshow'));
    assert.equal(ui.normal.classList.contains('is-submitting'), false);
    assert.equal(ui.normal.attributes['aria-busy'], undefined);
});
