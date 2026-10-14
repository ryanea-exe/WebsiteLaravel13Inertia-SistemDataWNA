<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    FileText,
    Receipt,
    TrendingDown,
    TrendingUp,
} from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { RealisasiPnbpItem } from '@/types/realisasi-pnbp';

const props = defineProps<{
    realisasiPnbp: RealisasiPnbpItem;
}>();

function formatIndoMonth(dateStr?: string | null): string {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}

function formatIndoDate(dateStr?: string | null): string {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}

function formatRupiah(value?: number | string | null): string {
    if (value === null || value === undefined) return 'Rp 0';
    const num = Number(value);
    if (isNaN(num)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(num);
}

const isSurplus = computed(() => {
    return Number(props.realisasiPnbp.selisih) >= 0;
});
</script>

<template>
    <Head :title="`Detail Realisasi PNBP - ${formatIndoMonth(realisasiPnbp.bulan)}`" />

    <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto w-full">
        <PageHeader
            :title="`Realisasi PNBP: ${formatIndoMonth(realisasiPnbp.bulan)}`"
            description="Informasi rinci target penerimaan, realisasi anggaran, dan evaluasi capaian PNBP bulanan."
        >
            <template #actions>
                <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                    <Link href="/realisasi-pnbp">
                        <ArrowLeft class="size-4" />
                        <span>Kembali ke Daftar</span>
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <!-- Main Detail Card -->
        <div class="rounded-xl border border-border/80 bg-card p-6 shadow-2xs space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 pb-6 border-b border-border/60">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-950 dark:bg-emerald-950/40 dark:text-emerald-400 shadow-xs">
                        <Receipt class="size-7" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Periode Rekapitulasi</p>
                        <h2 class="text-xl font-bold text-foreground">
                            {{ formatIndoMonth(realisasiPnbp.bulan) }}
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold"
                        :class="isSurplus ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-900/60' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-900/60'"
                    >
                        <TrendingUp v-if="isSurplus" class="size-4" />
                        <TrendingDown v-else class="size-4" />
                        <span>Capaian: {{ realisasiPnbp.persentase }}%</span>
                    </span>
                </div>
            </div>

            <!-- Grid Angka Target & Realisasi -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <!-- Target -->
                <div class="rounded-lg border border-border/60 bg-muted/20 p-4 space-y-1">
                    <p class="text-xs text-muted-foreground font-medium">Target Penerimaan</p>
                    <p class="font-mono text-base font-bold text-foreground">
                        {{ formatRupiah(realisasiPnbp.target) }}
                    </p>
                    <p class="text-[11px] text-muted-foreground">Target anggaran yang ditetapkan</p>
                </div>

                <!-- Realisasi -->
                <div class="rounded-lg border border-border/60 bg-muted/20 p-4 space-y-1">
                    <p class="text-xs text-muted-foreground font-medium">Realisasi Penerimaan</p>
                    <p class="font-mono text-base font-bold text-emerald-600 dark:text-emerald-400">
                        {{ formatRupiah(realisasiPnbp.realisasi) }}
                    </p>
                    <p class="text-[11px] text-muted-foreground">Total realisasi dihimpun</p>
                </div>

                <!-- Selisih -->
                <div class="rounded-lg border border-border/60 bg-muted/20 p-4 space-y-1">
                    <p class="text-xs text-muted-foreground font-medium">Selisih Realisasi vs Target</p>
                    <p
                        class="font-mono text-base font-bold"
                        :class="isSurplus ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                    >
                        {{ (isSurplus ? '+ ' : '') + formatRupiah(realisasiPnbp.selisih) }}
                    </p>
                    <p class="text-[11px]" :class="isSurplus ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                        {{ isSurplus ? 'Surplus penerimaan' : 'Defisit terhadap target' }}
                    </p>
                </div>
            </div>

            <!-- Keterangan / Evaluasi -->
            <div class="rounded-lg border border-border/60 bg-muted/15 p-4 space-y-2">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <FileText class="size-4 text-primary" />
                    <span>Catatan dan Keterangan Realisasi</span>
                </div>
                <div class="text-sm text-foreground/90 leading-relaxed whitespace-pre-wrap bg-background/60 p-3 rounded-md border border-border/40">
                    {{ realisasiPnbp.keterangan || 'Tidak ada catatan atau keterangan tambahan untuk rekapitulasi realisasi periode bulan ini.' }}
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-border/60 text-xs text-muted-foreground">
                <div class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    <span>Dibuat: {{ formatIndoDate(realisasiPnbp.created_at) }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    <span>Terakhir Diperbarui: {{ formatIndoDate(realisasiPnbp.updated_at) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
