<script setup lang="ts">
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="mt-auto border-t border-border/60 bg-background/95 px-4 py-3.5 text-xs text-muted-foreground backdrop-blur-xs transition-colors md:px-6"
    >
        <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
            <div class="flex items-center gap-2 text-center sm:text-left">
                <span class="font-medium text-foreground">Sistem Data WNA</span>
                <span class="hidden text-border sm:inline">•</span>
                <span>Pengelolaan Data & Layanan Administrasi Keimigrasian</span>
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
