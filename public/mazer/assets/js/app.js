/**
 * Main App.js for Mazer + Laravel 12
 * -----------------------------------
 * - Sidebar toggle
 * - Theme (Dark/Light) switch
 * - Init Feather Icons
 * - Init Bootstrap Tooltips
 */

// Import Mazer's CSS & JS
import '../css/app.css';
import './bootstrap'; // Laravel bootstrap (axios, etc.)
import feather from 'feather-icons';

// ============ SIDEBAR TOGGLE ============
document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggle = document.querySelector('#toggleSidebar');
    const sidebar = document.querySelector('#sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
        });
    }

    // ============ THEME SWITCH ============
    const themeToggle = document.querySelector('#theme-toggle');
    const htmlElement = document.documentElement;

    // Load saved theme from localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        htmlElement.setAttribute('data-theme', savedTheme);
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }

    // ============ FEATHER ICONS ============
    feather.replace();

    // ============ BOOTSTRAP TOOLTIPS ============
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ============ MAZER DROPDOWN FIX ============
    document.querySelectorAll('.dropdown-toggle').forEach((el) => {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
});
