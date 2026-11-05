<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
const appName = computed(() => appSettings.value.app_name || (page.props.name as string) || 'Sistem Data WNA Kanim Ponorogo');
const displayTitle = computed(() => props.title ? props.title.replace('Sistem Data WNA', appName.value) : `Masuk ke ${appName.value}`);

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
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <!-- Logos horizontal row in slot order without card/border, enlarged -->
                        <div
                            v-if="availableLogos.length > 0"
                            class="flex flex-row items-center justify-center gap-4 sm:gap-6 py-2"
                        >
                            <img
                                v-for="logo in availableLogos"
                                :key="logo.slot"
                                :src="logo.url"
                                :alt="`Logo ${logo.slot}`"
                                class="h-16 sm:h-20 w-auto max-w-[100px] object-contain transition-transform hover:scale-105 select-none"
                                :title="logo.is_primary ? `Logo ${logo.slot} (Utama)` : `Logo ${logo.slot}`"
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
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ displayTitle }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
