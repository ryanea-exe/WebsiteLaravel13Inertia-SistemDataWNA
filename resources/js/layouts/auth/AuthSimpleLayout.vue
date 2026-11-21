<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppFooter from '@/components/AppFooter.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const props = defineProps<{
    title?: string;
    description?: string;
}>();

interface AvailableLogo {
    slot: number;
    path: string;
    url: string;
    is_primary: boolean;
}

const page = usePage();
const appSettings = computed(() => (page.props.app_settings as any) || {});
const appName = computed(() => appSettings.value.app_name || (page.props.name as string) || 'Sistem Data WNA');
const instansiName = computed(() => appSettings.value.instansi_name || 'Kantor Imigrasi Kelas II Non TPI Ponorogo');
const appDescription = computed(() => appSettings.value.app_description || 'Sistem Informasi Data Warga Negara Asing');
const displayTitle = computed(() => {
    if (props.title && !props.title.toLowerCase().startsWith('masuk')) {
        return props.title;
    }
    return (appName.value || 'Sistem Data WNA').trim();
});
const displayDescription = computed(() => {
    if (props.description && props.description !== 'Masukkan username dan password Anda untuk masuk ke sistem') {
        return props.description;
    }
    const desc = (appDescription.value || '').trim();
    const instansi = (instansiName.value || '').trim();
    if (desc && instansi) {
        return desc.includes(instansi) ? desc : `${desc} ${instansi}`;
    }
    return desc || instansi || '';
});

const availableLogos = computed<AvailableLogo[]>(() => {
    const logos = appSettings.value.available_logos;
    if (Array.isArray(logos) && logos.length > 0) {
        return logos;
    }
    if (appSettings.value.app_icon) {
        return [{
            slot: 1,
            path: '',
            url: appSettings.value.app_icon,
            is_primary: true,
        }];
    }
    return [];
});

const failedLogoSlots = ref<Set<number>>(new Set());

function handleLogoError(slot: number) {
    failedLogoSlots.value.add(slot);
}

watch(availableLogos, () => {
    failedLogoSlots.value = new Set();
}, { deep: true });

const visibleLogos = computed(() => {
    return availableLogos.value.filter((logo) => !failedLogoSlots.value.has(logo.slot));
});

const showLoginLogo = computed<boolean>(() => {
    return appSettings.value.show_login_logo !== false && appSettings.value.show_login_logo !== 0 && appSettings.value.show_login_logo !== '0';
});
</script>

<template>
    <div class="flex min-h-svh flex-col bg-background">
        <div
            class="flex flex-1 flex-col items-center justify-center p-6 md:p-10"
            :class="showLoginLogo ? 'gap-6' : 'gap-4'"
        >
            <div class="w-full max-w-sm">
                <div
                    class="flex flex-col"
                    :class="showLoginLogo ? 'gap-8' : 'gap-6'"
                >
                    <div
                        class="flex flex-col items-center text-center"
                        :class="showLoginLogo ? 'gap-4' : 'gap-2'"
                    >
                        <!-- Logo Section (Only if enabled in App Settings) -->
                        <Link
                            v-if="showLoginLogo"
                            :href="home()"
                            class="flex flex-col items-center gap-2 font-medium"
                        >
                            <!-- Logos horizontal row in slot order without card/border, enlarged -->
                            <div
                                v-if="visibleLogos.length > 0"
                                class="flex flex-row items-center justify-center gap-4 sm:gap-6 py-2"
                            >
                                <img
                                    v-for="logo in visibleLogos"
                                    :key="logo.slot"
                                    :src="logo.url"
                                    :alt="`Logo ${logo.slot}`"
                                    class="h-16 sm:h-20 w-auto max-w-[100px] object-contain transition-transform hover:scale-105 select-none"
                                    :title="logo.is_primary ? `Logo ${logo.slot} (Utama)` : `Logo ${logo.slot}`"
                                    @error="handleLogoError(logo.slot)"
                                />
                            </div>
                            <div
                                v-else
                                class="mb-1 flex items-center justify-center"
                            >
                                <AppLogoIcon
                                    class="size-16 fill-current text-[var(--foreground)] dark:text-white"
                                />
                            </div>
                            <span class="sr-only">{{ displayTitle }}</span>
                        </Link>

                        <div class="space-y-1.5 text-center">
                            <h1 class="text-xl font-bold tracking-tight text-foreground">{{ displayTitle }}</h1>
                            <p class="text-center text-xs sm:text-sm text-muted-foreground">
                                {{ displayDescription }}
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
        <AppFooter centered class="shrink-0" />
    </div>
</template>
