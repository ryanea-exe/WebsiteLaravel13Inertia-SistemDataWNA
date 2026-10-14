<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    ExternalLink,
    FileText,
    TrendingDown,
    TrendingUp,
    X,
} from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { RealisasiPnbpItem } from '@/types/realisasi-pnbp';

const props = defineProps<{
    open: boolean;
    realisasiPnbp: RealisasiPnbpItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
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
    if (!props.realisasiPnbp) return true;
    return props.realisasiPnbp.selisih >= 0;
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-w-xl max-h-[90vh]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <DollarSign class="size-5 text-primary" />
                    <span>Detail Realisasi PNBP</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Rincian data target, capaian realisasi, dan rasio persentase penerimaan negara bukan pajak.
                </DialogDescription>
            </DialogHeader>

            <div v-if="realisasiPnbp" class="space-y-4 pt-2">
                <!-- Banner Periode -->
                <div class="flex items-center justify-between rounded-xl border border-border/80 bg-muted/20 p-4">
                    <div class="space-y-0.5">
                        <p class="text-xs text-muted-foreground font-medium">Periode Rekapitulasi</p>
                        <h3 class="text-lg font-bold text-foreground">
                            {{ formatIndoMonth(realisasiPnbp.bulan) }}
                        </h3>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold"
                            :class="isSurplus ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300'"
                        >
                            <TrendingUp v-if="isSurplus" class="size-3.5" />
                            <TrendingDown v-else class="size-3.5" />
                            <span>{{ realisasiPnbp.persentase }}%</span>
                        </span>
                        <span class="text-[10px] text-muted-foreground font-medium">
                            {{ isSurplus ? 'Tercapai / Surplus' : 'Belum Tercapai' }}
                        </span>
                    </div>
                </div>

                <!-- Grid Data Angka -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="rounded-lg border border-border/60 bg-muted/15 p-3 space-y-1">
                        <p class="text-[11px] text-muted-foreground font-medium">Target Penerimaan</p>
                        <p class="font-mono text-sm font-bold text-foreground">
                            {{ formatRupiah(realisasiPnbp.target) }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-border/60 bg-muted/15 p-3 space-y-1">
                        <p class="text-[11px] text-muted-foreground font-medium">Realisasi Penerimaan</p>
                        <p class="font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">
                            {{ formatRupiah(realisasiPnbp.realisasi) }}
                        </p>
                    </div>

                    <div class="col-span-2 rounded-lg border border-border/60 bg-muted/15 p-3 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] text-muted-foreground font-medium">Selisih (Realisasi - Target)</p>
                            <p
                                class="font-mono text-sm font-bold"
                                :class="isSurplus ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                            >
                                {{ (isSurplus ? '+ ' : '') + formatRupiah(realisasiPnbp.selisih) }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded" :class="isSurplus ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300'">
                            {{ isSurplus ? 'Surplus' : 'Defisit' }}
                        </span>
                    </div>
                </div>

                <!-- Keterangan Tambahan -->
                <div class="rounded-lg border border-border/60 bg-muted/10 p-3 space-y-1">
                    <p class="text-[11px] text-muted-foreground font-semibold flex items-center gap-1.5">
                        <FileText class="size-3.5" />
                        <span>Keterangan Realisasi</span>
                    </p>
                    <p class="text-xs text-foreground/90 whitespace-pre-wrap leading-relaxed">
                        {{ realisasiPnbp.keterangan || 'Tidak ada catatan keterangan tambahan untuk periode ini.' }}
                    </p>
                </div>

                <!-- Timestamps -->
                <div class="flex items-center justify-between pt-2 border-t border-border/50 text-[11px] text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        <span>Input: {{ formatIndoDate(realisasiPnbp.created_at) }}</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        <span>Update: {{ formatIndoDate(realisasiPnbp.updated_at) }}</span>
                    </span>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-between items-center gap-2 pt-2 border-t border-border/60">
                <Button v-if="realisasiPnbp" variant="ghost" size="sm" as-child class="gap-1.5 text-xs text-muted-foreground hover:text-foreground">
                    <Link :href="`/realisasi-pnbp/${realisasiPnbp.id}`">
                        <ExternalLink class="size-3.5" />
                        <span>Buka Halaman Lengkap</span>
                    </Link>
                </Button>
                <div v-else></div>

                <DialogClose as-child>
                    <Button variant="outline" size="sm">
                        Tutup
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
