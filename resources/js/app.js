/**
 * Sistem Nomor Surat - SJP Holding
 * Asynchronous UX & SweetAlert2 / Toast Integration
 */

import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.css';

flatpickr.localize(Indonesian);
window.flatpickr = flatpickr;
window.flatpickrIndonesian = Indonesian;

// Initialize Toast & SweetAlert2 Helpers
function getToastMixin() {
    if (typeof Swal === 'undefined') return null;

    const isDark = document.documentElement.getAttribute('data-theme') === 'forest' ||
                   document.documentElement.getAttribute('data-theme') === 'dark';

    return Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: isDark ? '#1e293b' : '#ffffff',
        color: isDark ? '#f8fafc' : '#1e293b',
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        },
        customClass: {
            popup: 'sjp-toast-popup rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-700/80 font-sans',
            title: 'text-xs sm:text-sm font-bold text-slate-900 dark:text-white',
            timerProgressBar: 'bg-emerald-500',
        }
    });
}

window.showToast = function(type = 'success', message = '', title = '') {
    if (typeof Swal === 'undefined') {
        console.log(`[Toast ${type}]:`, title, message);
        return;
    }

    const toast = getToastMixin();
    if (!toast) return;

    let defaultTitle = 'Berhasil';
    if (type === 'error') defaultTitle = 'Terjadi Kesalahan';
    if (type === 'warning') defaultTitle = 'Peringatan';
    if (type === 'info') defaultTitle = 'Informasi';

    toast.fire({
        icon: type,
        title: title || defaultTitle,
        text: message || undefined,
    });
};

window.showSwal = function(options = {}) {
    if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: false });

    const isDark = document.documentElement.getAttribute('data-theme') === 'forest' ||
                   document.documentElement.getAttribute('data-theme') === 'dark';

    return Swal.fire({
        background: isDark ? '#1e293b' : '#ffffff',
        color: isDark ? '#f8fafc' : '#1e293b',
        confirmButtonColor: '#269639',
        cancelButtonColor: '#94a3b8',
        customClass: {
            popup: 'rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 p-6 sm:p-7 font-sans',
            title: 'text-lg sm:text-xl font-black text-slate-900 dark:text-white',
            htmlContainer: 'text-xs sm:text-sm text-slate-600 dark:text-slate-300',
            confirmButton: 'btn btn-primary rounded-xl px-5 py-2.5 font-bold text-white shadow-md shadow-primary-600/20 text-xs sm:text-sm mx-1',
            cancelButton: 'btn btn-ghost rounded-xl px-5 py-2.5 font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs sm:text-sm mx-1',
        },
        buttonsStyling: false,
        ...options
    });
};

window.confirmAction = function({ title, text, icon = 'warning', confirmButtonText = 'Ya, Lanjutkan', cancelButtonText = 'Batal' }) {
    return window.showSwal({
        title: title || 'Apakah Anda yakin?',
        text: text || '',
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        reverseButtons: true,
    });
};

// Clipboard helper with automatic Toast
window.copyToClipboard = function(text, label = 'Nomor surat') {
    if (!navigator.clipboard) {
        window.showToast('info', text, `${label} Tersalin`);
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        window.showToast('success', text, `${label} Tersalin`);
    }).catch(() => {
        window.showToast('info', text, label);
    });
};

// Setup top SPA navigation progress bar
let navProgressBar = null;
let navProgressInterval = null;

function createProgressBar() {
    if (navProgressBar) return navProgressBar;
    navProgressBar = document.createElement('div');
    navProgressBar.id = 'livewire-nav-progress';
    navProgressBar.className = 'fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-primary-500 to-teal-400 z-50 transition-all duration-300 pointer-events-none opacity-0';
    navProgressBar.style.width = '0%';
    document.body.appendChild(navProgressBar);
    return navProgressBar;
}

function startNavigationProgress() {
    const bar = createProgressBar();
    bar.style.transition = 'width 0.4s ease, opacity 0.15s ease';
    bar.style.opacity = '1';
    bar.style.width = '20%';

    clearInterval(navProgressInterval);
    let progress = 20;
    navProgressInterval = setInterval(() => {
        if (progress < 85) {
            progress += Math.random() * 15;
            bar.style.width = `${progress}%`;
        }
    }, 150);
}

function finishNavigationProgress() {
    clearInterval(navProgressInterval);
    if (!navProgressBar) return;
    navProgressBar.style.width = '100%';
    setTimeout(() => {
        navProgressBar.style.opacity = '0';
        setTimeout(() => {
            navProgressBar.style.width = '0%';
        }, 300);
    }, 150);
}

document.addEventListener('livewire:navigating', startNavigationProgress);
document.addEventListener('livewire:navigated', finishNavigationProgress);

// Register Livewire global event listeners
document.addEventListener('livewire:init', () => {
    // Listen for 'toast' events from any component: $this->dispatch('toast', type: 'success', message: '...')
    Livewire.on('toast', (event) => {
        const payload = Array.isArray(event) ? event[0] : event;
        window.showToast(payload.type || 'success', payload.message || '', payload.title || '');
    });

    // Listen for 'swal' events: $this->dispatch('swal', title: '...', text: '...', icon: '...')
    Livewire.on('swal', (event) => {
        const payload = Array.isArray(event) ? event[0] : event;
        window.showSwal(payload);
    });

    // Listen for 'swal:confirm' events: $this->dispatch('swal:confirm', title: '...', callbackEvent: '...')
    Livewire.on('swal:confirm', (event) => {
        const payload = Array.isArray(event) ? event[0] : event;
        window.confirmAction({
            title: payload.title,
            text: payload.text,
            icon: payload.icon || 'warning',
            confirmButtonText: payload.confirmButtonText || 'Ya, Lanjutkan',
            cancelButtonText: payload.cancelButtonText || 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                if (payload.callbackEvent) {
                    Livewire.dispatch(payload.callbackEvent, payload.params || {});
                }
            }
        });
    });
});
