/**
 * ==============================================================================
 * FILE: utils/helpers.js
 * TUJUAN: Utility functions untuk aplikasi
 * DESKRIPSI: Helper functions untuk akses CSRF token, konfigurasi aplikasi halaman,
 *            dan formatting currency/number yang digunakan di berbagai modul.
 * ==============================================================================
 */

/**
 * Ambil CSRF token dari meta tag
 */
export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export function getAppConfig() {
    const el = document.getElementById('app-config');

    if (! el) {
        return { basePath: '' };
    }

    return {
        basePath: el.dataset.basePath ?? '',
    };
}

/**
 * Ambil konfigurasi halaman dari elemen dengan ID tertentu
 */
export function getPageConfig(id) {
    const el = document.getElementById(id);

    if (! el) {
        return {};
    }

    if (el._parsedConfig) {
        return el._parsedConfig;
    }

    if (el.dataset.config) {
        try {
            el._parsedConfig = JSON.parse(el.dataset.config);
            return el._parsedConfig;
        } catch {
            return {};
        }
    }

    el._parsedConfig = { ...el.dataset };
    return el._parsedConfig;
}

/**
 * Format angka menjadi currency dengan pemisah titik
 */
export function formatCurrency(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/**
 * Format angka dengan pemisah titik
 */
export function numberFormat(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
