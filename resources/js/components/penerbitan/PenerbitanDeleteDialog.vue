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
import type { PenerbitanItem } from '@/types/penerbitan';

const props = defineProps<{
    open: boolean;
    penerbitan: PenerbitanItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isDeleting = ref(false);

function handleDelete() {
    if (!props.penerbitan) return;

    isDeleting.value = true;
    router.delete(`/penerbitan/${props.penerbitan.id}`, {
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
                        Konfirmasi Hapus Data Penerbitan
                    </DialogTitle>
                </div>
                <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                    Apakah Anda yakin ingin menghapus data penerbitan dokumen atau izin keimigrasian di bawah ini?
                </DialogDescription>
            </DialogHeader>

            <div v-if="penerbitan" class="rounded-lg border border-red-200 bg-red-50/50 p-3 text-xs space-y-1.5 dark:border-red-950/60 dark:bg-red-950/20">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nama Pemohon:</span>
                    <span class="font-semibold text-foreground">{{ penerbitan.nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Jenis Layanan:</span>
                    <span class="font-medium text-foreground">{{ penerbitan.layanan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tanggal:</span>
                    <span class="font-medium text-foreground">{{ penerbitan.tanggal }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">No. Paspor:</span>
                    <span class="font-mono font-medium text-foreground">{{ penerbitan.no_paspor || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Kewarganegaraan:</span>
                    <span class="font-medium text-foreground">{{ penerbitan.kewarganegaraan }}</span>
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
