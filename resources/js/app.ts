import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { initializeCleanTableUrl } from '@/lib/cleanUrl';
import { initializeFlashToast } from '@/lib/flashToast';

const defaultAppName = 'Sistem Data WNA Kanim Ponorogo';
const appName = (import.meta.env.VITE_APP_NAME && import.meta.env.VITE_APP_NAME !== 'Laravel') ? import.meta.env.VITE_APP_NAME : defaultAppName;

void createInertiaApp({
    title: (title) => (title ? (title.includes(appName) ? title : `${title} - ${appName}`) : appName),
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
