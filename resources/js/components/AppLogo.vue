<script setup lang="ts">
import { ShieldCheck } from '@lucide/vue';
</script>

<template>
    <div
        class="flex aspect-square size-8.5 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-xs transition-colors group-data-[collapsible=icon]:size-8"
    >
        <ShieldCheck class="size-4.5 text-primary-foreground" />
    </div>
    <div class="ml-2 grid flex-1 text-left leading-tight group-data-[collapsible=icon]:hidden">
        <span class="truncate text-sm font-semibold tracking-tight text-sidebar-foreground">
            Sistem Data WNA
        </span>
        <span class="truncate text-[10px] font-medium text-muted-foreground">
            Layanan Keimigrasian
        </span>
    </div>
</template>
