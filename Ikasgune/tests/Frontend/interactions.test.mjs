import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/js/app.js', import.meta.url), 'utf8');
const themeSource = readFileSync(new URL('../../resources/js/theme.js', import.meta.url), 'utf8');

function setup({ savedTheme = null, systemDark = false, blockedStorage = false, loading = false, withoutBundle = false, adminThemeLocked = false } = {}) {
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
    const themeToggle = new Element();
    const lightThemeOption = new Element();
    lightThemeOption.dataset.setTheme = 'light';
    const darkThemeOption = new Element();
    darkThemeOption.dataset.setTheme = 'dark';
    const themeOptions = [lightThemeOption, darkThemeOption];
    const themeColor = new Element();
    const documentElement = new Element();
    documentElement.dataset.theme = 'light';
    documentElement.dataset.themeLocked = adminThemeLocked ? 'true' : 'false';
    const trigger = new Element();
    const cancel = new Element();
    const confirm = new Element();
    const logout = new Element();
    const deletion = new Element();
    const normal = new Element();
    const window = new EventTarget();
    window.confirm = () => false;
    const media = new EventTarget();
    media.matches = systemDark;
    window.matchMedia = () => media;
    const savedPreferences = new Map();
    if (savedTheme) savedPreferences.set('eskolak-theme', savedTheme);
    const localStorage = {
        getItem: (key) => { if (blockedStorage) throw new Error('Storage blocked'); return savedPreferences.get(key) ?? null; },
        setItem: (key, value) => { if (blockedStorage) throw new Error('Storage blocked'); savedPreferences.set(key, value); },
    };
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
    themeToggle.querySelectorAll = () => themeOptions;
    modal.querySelector = (selector) => selector === '[data-close-logout-modal]' ? cancel : confirm;
    modal.querySelectorAll = () => [cancel, confirm];
    document = new EventTarget();
    Object.assign(document, {
        body, activeElement: trigger, documentElement, readyState: loading ? 'loading' : 'complete',
        querySelector: (selector) => ({
            '[data-logout-modal]': modal,
            '[data-theme-toggle]': themeToggle,
            '[data-theme-color]': themeColor,
        }[selector] ?? null),
        querySelectorAll: (selector) => ({
            '[data-dismiss-toast]': [], '[data-confirm-logout]': [logout],
            '[data-confirm-delete]': [deletion], form: forms,
            'form.is-submitting': forms.filter((form) => form.classList.contains('is-submitting')),
            '[data-admin-tab]': tabs, '.admin-panel': panels,
        }[selector] ?? []),
    });
    runInNewContext(themeSource + (withoutBundle ? '' : source), {
        document, window, HTMLElement: Element, queueMicrotask, URL, localStorage,
        location: { href: 'http://localhost/admin' },
        history: { replaceState: (_state, _title, url) => { lastUrl = url; } },
    });
    const key = (target, value, shiftKey = false) => {
        const event = new Event('keydown', { cancelable: true });
        Object.assign(event, { key: value, shiftKey });
        target.dispatchEvent(event);
    };
    return { document, window, media, logout, deletion, normal, trigger, modal, cancel, confirm, tabs, panels, themeToggle, themeOptions, themeColor, localStorage, key, url: () => lastUrl };
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

test('theme control selects and persists light and dark modes', () => {
    const ui = setup();
    ui.themeOptions[1].dispatchEvent(new Event('click'));

    assert.equal(ui.document.documentElement.dataset.theme, 'dark');
    assert.equal(ui.themeOptions[1].attributes['aria-pressed'], 'true');
    assert.equal(ui.themeOptions[0].attributes['aria-pressed'], 'false');
    assert.equal(ui.themeColor.attributes.content, '#000000');
    assert.equal(ui.localStorage.getItem('eskolak-theme'), 'dark');

    ui.themeOptions[0].dispatchEvent(new Event('click'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    assert.equal(ui.themeOptions[0].attributes['aria-pressed'], 'true');
    assert.equal(ui.themeOptions[1].attributes['aria-pressed'], 'false');
    assert.equal(ui.localStorage.getItem('eskolak-theme'), 'light');
});

test('saved choice overrides OS theme, including after back navigation', () => {
    const ui = setup({ savedTheme: 'light', systemDark: true });
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    ui.localStorage.setItem('eskolak-theme', 'dark');
    ui.window.dispatchEvent(new Event('pageshow'));
    assert.equal(ui.document.documentElement.dataset.theme, 'dark');
});

test('theme stays light by default until a choice is saved', () => {
    const ui = setup();
    ui.media.matches = true;
    ui.media.dispatchEvent(new Event('change'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    ui.themeOptions[0].dispatchEvent(new Event('click'));
    ui.media.dispatchEvent(new Event('change'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    ui.localStorage.setItem('eskolak-theme', 'dark');
    const event = new Event('storage');
    event.key = 'eskolak-theme';
    ui.window.dispatchEvent(event);
    assert.equal(ui.themeOptions[1].attributes['aria-pressed'], 'true');
});

test('theme switching works with unavailable storage', () => {
    const ui = setup({ blockedStorage: true, systemDark: true });
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    ui.themeOptions[0].dispatchEvent(new Event('click'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    ui.window.dispatchEvent(new Event('pageshow'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
});

test('admin pages stay light and ignore theme selection without changing the saved preference', () => {
    const ui = setup({ savedTheme: 'dark', adminThemeLocked: true });
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    assert.equal(ui.themeOptions[0].attributes['aria-pressed'], 'true');
    assert.equal(ui.themeOptions[0].disabled, true);
    assert.equal(ui.themeOptions[1].disabled, true);
    ui.themeOptions[1].dispatchEvent(new Event('click'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    assert.equal(ui.localStorage.getItem('eskolak-theme'), 'dark');
});

test('dark page and selector agree after DOM loads even without the Vite bundle', () => {
    const ui = setup({ savedTheme: 'dark', loading: true, withoutBundle: true });
    assert.equal(ui.document.documentElement.dataset.theme, 'dark');
    ui.document.dispatchEvent(new Event('DOMContentLoaded'));
    assert.equal(ui.themeOptions[0].attributes['aria-pressed'], 'false');
    assert.equal(ui.themeOptions[1].attributes['aria-pressed'], 'true');
    ui.themeOptions[0].dispatchEvent(new Event('click'));
    assert.equal(ui.document.documentElement.dataset.theme, 'light');
    assert.equal(ui.themeOptions[0].attributes['aria-pressed'], 'true');
    assert.equal(ui.themeOptions[1].attributes['aria-pressed'], 'false');
    assert.equal(ui.localStorage.getItem('eskolak-theme'), 'light');
});
