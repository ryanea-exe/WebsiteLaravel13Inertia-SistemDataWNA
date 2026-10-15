<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();

interface AppSettings {
    app_name?: string;
    app_icon?: string | null;
    instansi_name?: string | null;
    app_description?: string | null;
}

const appSettings = computed(() => page.props.app_settings as AppSettings | undefined);
const appName = computed(() => appSettings.value?.app_name || (page.props.name as string) || 'Sistem Data WNA');
const appIcon = computed(() => appSettings.value?.app_icon || null);
const appDescription = computed(() => appSettings.value?.app_description || 'Layanan Keimigrasian');
</script>

<template>
    <div
        class="flex aspect-square size-8.5 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-xs transition-colors group-data-[collapsible=icon]:size-8 overflow-hidden"
    >
        <img
            v-if="appIcon"
            :src="appIcon"
            :alt="appName"
            class="size-full object-cover rounded-lg"
        />
        <ShieldCheck v-else class="size-4.5 text-primary-foreground" />
    </div>
    <div class="ml-2 grid flex-1 text-left leading-tight group-data-[collapsible=icon]:hidden">
        <span class="truncate text-sm font-semibold tracking-tight text-sidebar-foreground">
            {{ appName }}
        </span>
        <span class="truncate text-[10px] font-medium text-muted-foreground">
            {{ appDescription }}
        </span>
    </div>
</template>
