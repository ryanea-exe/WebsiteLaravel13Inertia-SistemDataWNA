<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppFooter from '@/components/AppFooter.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const scrollContainer = ref<HTMLElement | null>(null);
const page = usePage();

// Reset scroll container position to top when navigating between pages/modules
watch(
    () => page.url.split('?')[0],
    (newPath, oldPath) => {
        if (newPath !== oldPath && scrollContainer.value) {
            scrollContainer.value.scrollTop = 0;
        }
    }
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent
            variant="sidebar"
            class="min-w-0 min-h-0 flex-1 flex flex-col h-svh max-h-svh overflow-hidden"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div ref="scrollContainer" class="flex-1 min-h-0 overflow-y-auto flex flex-col">
                <div class="flex-1">
                    <slot />
                </div>
                <AppFooter class="shrink-0 mt-auto" />
            </div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>

