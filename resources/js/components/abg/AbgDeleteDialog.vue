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
import type { AbgItem } from '@/types/abg';

const props = defineProps<{
    open: boolean;
    abg: AbgItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isDeleting = ref(false);

function handleDelete() {
    if (!props.abg) return;

    isDeleting.value = true;
    router.delete(`/abg/${props.abg.id}`, {
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
                        Konfirmasi Hapus Data Pendaftaran ABG
                    </DialogTitle>
                </div>
                <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                    Apakah Anda yakin ingin menghapus arsip Pendaftaran Anak Berkewarganegaraan Ganda di bawah ini?
                </DialogDescription>
            </DialogHeader>

            <div v-if="abg" class="rounded-lg border border-red-200 bg-red-50/50 p-3 text-xs space-y-1.5 dark:border-red-950/60 dark:bg-red-950/20">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nama:</span>
                    <span class="font-semibold text-foreground">{{ abg.nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">No. Register:</span>
                    <span class="font-mono font-medium text-foreground">{{ abg.no_register }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">No. Paspor:</span>
                    <span class="font-mono font-medium text-foreground">{{ abg.no_paspor }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Kewarganegaraan:</span>
                    <span class="text-foreground">{{ abg.kewarganegaraan }}</span>
                </div>
            </div>

            <p class="text-xs text-red-700 dark:text-red-400 font-medium">
                Tindakan ini tidak dapat dibatalkan. Berkas foto dan rekaman arsip akan dihapus secara permanen dari basis data.
            </p>

            <DialogFooter class="gap-2 sm:gap-0">
                <DialogClose as-child>
                    <Button variant="outline" :disabled="isDeleting">
                        Batal
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    :disabled="isDeleting"
                    class="gap-1.5"
                    @click="handleDelete"
                >
                    <Loader2 v-if="isDeleting" class="size-4 animate-spin" />
                    <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Data' }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
