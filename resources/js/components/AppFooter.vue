<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        centered?: boolean;
    }>(),
    {
        centered: false,
    }
);

const page = usePage();
const appName = computed(() => (page.props.app_settings as any)?.app_name || (page.props.name as string) || 'Sistem Data WNA Kanim Ponorogo');
const instansiName = computed(() => (page.props.app_settings as any)?.instansi_name || 'Kantor Imigrasi Kelas II Non TPI Ponorogo');
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="mt-auto border-t border-border/60 bg-background/95 px-4 text-muted-foreground backdrop-blur-xs transition-colors md:px-6"
        :class="centered ? 'py-2.5 text-[11px]' : 'py-3 text-xs'"
    >
        <div
            v-if="centered"
            class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-center leading-normal"
        >
            <span class="font-medium text-foreground">{{ appName }}</span>
            <span class="text-border">•</span>
            <span>{{ instansiName }}</span>
            <span class="text-border">•</span>
            <span>© {{ currentYear }} Hak Cipta Dilindungi</span>
        </div>
        <div v-else class="flex flex-col items-center justify-between gap-2 sm:flex-row">
            <div class="flex items-center gap-2 text-center sm:text-left">
                <span class="font-medium text-foreground">{{ appName }}</span>
                <span class="hidden text-border sm:inline">•</span>
                <span>{{ instansiName }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Aktif
                </span>
                <span>© {{ currentYear }} Hak Cipta Dilindungi</span>
            </div>
        </div>
    </footer>
</template>
