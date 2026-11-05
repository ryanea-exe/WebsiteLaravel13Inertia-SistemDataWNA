import { createInertiaApp, router } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { initializeCleanTableUrl } from '@/lib/cleanUrl';
import { initializeFlashToast } from '@/lib/flashToast';

const defaultAppName = 'Sistem Data WNA Kanim Ponorogo';
let currentAppName = (import.meta.env.VITE_APP_NAME && import.meta.env.VITE_APP_NAME !== 'Laravel') ? import.meta.env.VITE_APP_NAME : defaultAppName;

// Dynamic favicon sync on Inertia navigation
function syncFavicon(iconUrl?: string | null) {
    if (!iconUrl || typeof document === 'undefined') return;
    const existingIcons = document.querySelectorAll<HTMLLinkElement>("link[rel*='icon'], link[rel='apple-touch-icon']");
    let needsUpdate = false;
    existingIcons.forEach((el) => {
        if (el.href !== iconUrl) {
            needsUpdate = true;
            el.remove();
        }
    });

    if (needsUpdate || existingIcons.length === 0) {
        const link = document.createElement('link');
        link.rel = 'icon';
        link.href = iconUrl;
        document.head.appendChild(link);

        const appleLink = document.createElement('link');
        appleLink.rel = 'apple-touch-icon';
        appleLink.href = iconUrl;
        document.head.appendChild(appleLink);
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
            if (initialIcon) {
                syncFavicon(initialIcon);
            }
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
    if (iconUrl) {
        syncFavicon(iconUrl);
    }
});


