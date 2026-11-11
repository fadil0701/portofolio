import './bootstrap';

import Alpine from 'alpinejs';

import '@fontsource-variable/geist';
import '@fontsource-variable/geist-mono';
import '@fontsource/instrument-serif/400.css';
import '@fontsource/instrument-serif/400-italic.css';

window.Alpine = Alpine;

const root = document.documentElement;
const currentTheme = () => {
    const stored = localStorage.getItem('os-theme');
    return stored === 'light' || stored === 'dark' ? stored : 'system';
};

const syncThemeToggle = () => {
    const toggle = document.querySelector('[data-theme-toggle]');
    const label = document.querySelector('[data-theme-label]');

    if (!toggle || !label) {
        return;
    }

    const theme = root.getAttribute('data-theme') ?? 'system';
    toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    label.textContent = theme === 'dark' ? 'Dark mode' : 'Light mode';
};

const setTheme = (theme) => {
    if (theme === 'light' || theme === 'dark') {
        root.setAttribute('data-theme', theme);
        localStorage.setItem('os-theme', theme);
    } else {
        root.removeAttribute('data-theme');
        localStorage.removeItem('os-theme');
    }

    syncThemeToggle();
};

const toggleTheme = () => {
    const theme = currentTheme();
    setTheme(theme === 'dark' ? 'light' : 'dark');
};

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-theme-toggle]');

    if (toggle) {
        toggle.addEventListener('click', toggleTheme);
    }

    syncThemeToggle();
});

window.OSTheme = { setTheme, toggleTheme };

Alpine.start();
