<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, X } from '@lucide/vue';
import { computed, onUnmounted, ref, watch } from 'vue';

const page = usePage();

const isDismissed = ref(false);
let timer: ReturnType<typeof setTimeout> | null = null;

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);
const successMessage = computed(() => (!isDismissed.value ? flash.value?.success : undefined));
const errorMessage = computed(() => (!isDismissed.value ? flash.value?.error : undefined));

function startTimer() {
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
    if (flash.value?.success || flash.value?.error) {
        isDismissed.value = false;
        timer = setTimeout(() => {
            isDismissed.value = true;
        }, 10000);
    }
}

function dismiss() {
    isDismissed.value = true;
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
}

watch(
    () => page.props.flash,
    () => {
        startTimer();
    },
    { deep: true, immediate: true }
);

onUnmounted(() => {
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
});
</script>

<template>
    <div v-if="successMessage || errorMessage" class="space-y-3">
        <!-- Success Alert -->
        <div
            v-if="successMessage"
            class="flex items-center justify-between gap-3 rounded-lg border border-emerald-500/30 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-2xs dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200"
            role="alert"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <CheckCircle2 class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <span class="font-medium truncate">{{ successMessage }}</span>
            </div>
            <button
                type="button"
                class="shrink-0 rounded p-1 text-emerald-700 hover:bg-emerald-200/50 hover:text-emerald-950 transition-colors dark:text-emerald-400 dark:hover:bg-emerald-900/50 dark:hover:text-emerald-200 cursor-pointer"
                title="Tutup pesan"
                @click="dismiss"
            >
                <X class="size-4" />
                <span class="sr-only">Tutup pesan</span>
            </button>
        </div>

        <!-- Error Alert -->
        <div
            v-if="errorMessage"
            class="flex items-center justify-between gap-3 rounded-lg border border-red-500/30 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-2xs dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200"
            role="alert"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <AlertCircle class="size-4 shrink-0 text-red-600 dark:text-red-400" />
                <span class="font-medium truncate">{{ errorMessage }}</span>
            </div>
            <button
                type="button"
                class="shrink-0 rounded p-1 text-red-700 hover:bg-red-200/50 hover:text-red-950 transition-colors dark:text-red-400 dark:hover:bg-red-900/50 dark:hover:text-red-200 cursor-pointer"
                title="Tutup pesan"
                @click="dismiss"
            >
                <X class="size-4" />
                <span class="sr-only">Tutup pesan</span>
            </button>
        </div>
    </div>
</template>
