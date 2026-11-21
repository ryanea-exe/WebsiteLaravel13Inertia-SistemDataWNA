import { createInertiaApp, router } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { initializeCleanTableUrl } from '@/lib/cleanUrl';
import { initializeFlashToast } from '@/lib/flashToast';

const defaultAppName = 'Sistem Data WNA Kanim Ponorogo';
let currentAppName = (import.meta.env.VITE_APP_NAME && import.meta.env.VITE_APP_NAME !== 'Laravel') ? import.meta.env.VITE_APP_NAME : defaultAppName;
const defaultFavicon = '/favicon.ico';
const defaultAppleIcon = '/apple-touch-icon.png';

// Dynamic favicon sync on Inertia navigation with local fallback
function syncFavicon(iconUrl?: string | null) {
    if (typeof document === 'undefined') return;

    const targetFavicon = iconUrl && iconUrl.trim() !== '' ? iconUrl : defaultFavicon;
    const targetAppleIcon = iconUrl && iconUrl.trim() !== '' ? iconUrl : defaultAppleIcon;

    let mimeType = 'image/png';
    const lowerUrl = targetFavicon.toLowerCase();
    if (lowerUrl.includes('.svg')) {
        mimeType = 'image/svg+xml';
    } else if (lowerUrl.includes('.ico')) {
        mimeType = 'image/x-icon';
    } else if (lowerUrl.includes('.webp')) {
        mimeType = 'image/webp';
    } else if (lowerUrl.includes('.jpg') || lowerUrl.includes('.jpeg')) {
        mimeType = 'image/jpeg';
    }

    // Update or create standard icon without removing existing node
    let iconLink = document.querySelector<HTMLLinkElement>("link[rel='icon']");
    if (!iconLink) {
        iconLink = document.createElement('link');
        iconLink.rel = 'icon';
        document.head.appendChild(iconLink);
    }
    if (iconLink.getAttribute('href') !== targetFavicon && iconLink.href !== targetFavicon) {
        iconLink.href = targetFavicon;
    }
    if (iconLink.type !== mimeType) {
        iconLink.type = mimeType;
    }

    // Update or create shortcut icon for Chromium / Chrome tab recognition
    let shortcutLink = document.querySelector<HTMLLinkElement>("link[rel='shortcut icon']");
    if (!shortcutLink) {
        shortcutLink = document.createElement('link');
        shortcutLink.rel = 'shortcut icon';
        document.head.appendChild(shortcutLink);
    }
    if (shortcutLink.getAttribute('href') !== targetFavicon && shortcutLink.href !== targetFavicon) {
        shortcutLink.href = targetFavicon;
    }
    if (shortcutLink.type !== mimeType) {
        shortcutLink.type = mimeType;
    }

    // Update or create apple-touch-icon
    let appleLink = document.querySelector<HTMLLinkElement>("link[rel='apple-touch-icon']");
    if (!appleLink) {
        appleLink = document.createElement('link');
        appleLink.rel = 'apple-touch-icon';
        document.head.appendChild(appleLink);
    }
    if (appleLink.getAttribute('href') !== targetAppleIcon && appleLink.href !== targetAppleIcon) {
        appleLink.href = targetAppleIcon;
    }
}

// Read dynamic app_name and app_icon from initial server-rendered page data
if (typeof document !== 'undefined') {
    const appEl = document.getElementById('app');
    if (appEl?.dataset.page) {
        try {
            const pageData = JSON.parse(appEl.dataset.page);
            const dynamicName = pageData.props?.app_settings?.app_name || pageData.props?.name;
            if (dynamicName && typeof dynamicName === 'string') {
                currentAppName = dynamicName;
            }
            const initialIcon = pageData.props?.app_settings?.app_icon;
            syncFavicon(initialIcon);
        } catch {}
    }
}

function resolvePageTitle(title?: string): string {
    const activeName = currentAppName || defaultAppName;
    if (!title || title.trim() === '') {
        return activeName;
    }

    // Strip any legacy or duplicated suffixes
    const cleanTitle = title
        .replace(/\s*-\s*Sistem Data WNA.*$/i, '')
        .replace(new RegExp(`\\s*-\\s*${activeName}$`, 'i'), '')
        .trim();

    return cleanTitle ? `${cleanTitle} - ${activeName}` : activeName;
}

void createInertiaApp({
    title: (title) => resolvePageTitle(title),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name.endsWith('/Card'):
            case name.endsWith('/PrintList'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

// This will clean URL query parameters on table pages for a clean address bar...
initializeCleanTableUrl();

router.on('navigate', (event) => {
    const newAppName = (event.detail.page.props.app_settings as any)?.app_name || (event.detail.page.props.name as string);
    if (newAppName && typeof newAppName === 'string' && newAppName !== currentAppName) {
        currentAppName = newAppName;
    }

    const iconUrl = (event.detail.page.props.app_settings as any)?.app_icon;
    syncFavicon(iconUrl);
});


