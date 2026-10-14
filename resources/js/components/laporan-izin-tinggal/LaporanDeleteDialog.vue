<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertTriangle, Loader2 } from '@lucide/vue';
import { ref } from 'vue';
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
import type { LaporanIzinTinggalItem } from '@/types/laporan-izin-tinggal';

const props = defineProps<{
    open: boolean;
    laporan: LaporanIzinTinggalItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isDeleting = ref(false);

function handleDelete() {
    if (!props.laporan) return;

    isDeleting.value = true;
    router.delete(`/laporan-izin-tinggal/${props.laporan.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
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
                        Konfirmasi Hapus Data Laporan
                    </DialogTitle>
                </div>
                <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                    Apakah Anda yakin ingin menghapus data rekapitulasi izin tinggal berikut? Tindakan ini tidak dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>

            <div v-if="laporan" class="rounded-lg border border-red-200 bg-red-50/50 p-3 text-xs space-y-1.5 dark:border-red-950/60 dark:bg-red-950/20">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Kategori:</span>
                    <span class="font-semibold text-foreground">{{ laporan.kategori }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Jenis Layanan:</span>
                    <span class="font-medium text-foreground">{{ laporan.layanan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Disetujui:</span>
                    <span class="font-medium text-emerald-700 dark:text-emerald-400">
                        {{ laporan.total_disetujui }} (L: {{ laporan.disetujui_l }}, P: {{ laporan.disetujui_p }})
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Ditolak:</span>
                    <span class="font-medium text-rose-700 dark:text-rose-400">
                        {{ laporan.total_ditolak }} (L: {{ laporan.ditolak_l }}, P: {{ laporan.ditolak_p }})
                    </span>
                </div>
                <div class="flex justify-between border-t border-red-200/60 pt-1 dark:border-red-900/60">
                    <span class="text-muted-foreground font-semibold">Total Permohonan:</span>
                    <span class="font-bold text-foreground">{{ laporan.total }}</span>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                <DialogClose as-child>
                    <Button variant="outline" size="sm" :disabled="isDeleting">
                        Batal
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    size="sm"
                    class="gap-1.5 bg-red-600 hover:bg-red-700 text-white"
                    :disabled="isDeleting"
                    @click="handleDelete"
                >
                    <Loader2 v-if="isDeleting" class="size-4 animate-spin" />
                    <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Data' }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
