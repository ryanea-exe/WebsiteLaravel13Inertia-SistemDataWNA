import { router } from '@inertiajs/vue3';

/**
 * Daftar rute halaman tabel utama yang URL query string-nya dibersihkan
 * demi tampilan address bar yang rapi dan bersih.
 */
export const TABLE_ROUTES: readonly string[] = [
    '/wna',
    '/abg',
    '/affidavit',
    '/early-warning',
    '/permohonan',
    '/laporan-izin-tinggal',
    '/penerbitan',
    '/realisasi-pnbp',
    '/data-user',
];

/**
 * Membersihkan query string dari browser address bar jika berada di salah satu halaman tabel,
 * dengan tetap mempertahankan internal history state dari Inertia.
 */
export function cleanBrowserUrl(): void {
    if (typeof window === 'undefined') return;

    const pathname = window.location.pathname;
    const isTableRoute = TABLE_ROUTES.some((route) => pathname === route);

    if (isTableRoute && window.location.search) {
        window.history.replaceState(window.history.state, '', pathname);
    }
}

/**
 * Menginisialisasi listener global Inertia router untuk membersihkan query string URL
 * secara otomatis setelah navigasi / filtering / sorting / pagination selesai.
 */
export function initializeCleanTableUrl(): void {
    if (typeof window !== 'undefined') {
        cleanBrowserUrl();
    }

    router.on('navigate', () => {
        cleanBrowserUrl();
    });
}
