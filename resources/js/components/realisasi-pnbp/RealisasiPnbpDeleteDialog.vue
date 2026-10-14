<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, Loader2 } from '@lucide/vue';
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

const form = useForm({});

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

function handleDelete() {
    if (!props.realisasiPnbp) return;

    form.delete(`/realisasi-pnbp/${props.realisasiPnbp.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-2 text-red-600 dark:text-red-500">
                    <div class="flex size-9 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/60">
                        <AlertTriangle class="size-5" />
                    </div>
                    <DialogTitle class="text-base font-semibold">
                        Konfirmasi Hapus Data Realisasi PNBP
                    </DialogTitle>
                </div>
                <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                    Apakah Anda yakin ingin menghapus data realisasi PNBP untuk periode berikut? Tindakan ini tidak dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>

            <div v-if="realisasiPnbp" class="rounded-lg border border-red-200 bg-red-50/50 p-3 text-xs space-y-1.5 dark:border-red-950/60 dark:bg-red-950/20">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Periode Bulan:</span>
                    <span class="font-semibold text-foreground">{{ formatIndoMonth(realisasiPnbp.bulan) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Target:</span>
                    <span class="font-mono font-medium text-foreground">{{ formatRupiah(realisasiPnbp.target) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Realisasi:</span>
                    <span class="font-mono font-medium text-emerald-600 dark:text-emerald-400">{{ formatRupiah(realisasiPnbp.realisasi) }}</span>
                </div>
                <div class="flex justify-between border-t border-red-200/60 pt-1 dark:border-red-900/60">
                    <span class="text-muted-foreground font-semibold">Rasio Capaian:</span>
                    <span class="font-bold text-foreground">{{ realisasiPnbp.persentase }}%</span>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                <DialogClose as-child>
                    <Button variant="outline" size="sm" :disabled="form.processing">
                        Batal
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    size="sm"
                    class="gap-1.5 bg-red-600 hover:bg-red-700 text-white"
                    :disabled="form.processing"
                    @click="handleDelete"
                >
                    <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                    <span>{{ form.processing ? 'Menghapus...' : 'Ya, Hapus Data' }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
