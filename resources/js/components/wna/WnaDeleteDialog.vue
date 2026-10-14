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
import type { WnaItem } from '@/types/wna';

const props = defineProps<{
    open: boolean;
    wna: WnaItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isDeleting = ref(false);

function handleDelete() {
    if (!props.wna) return;

    isDeleting.value = true;
    router.delete(`/wna/${props.wna.id}`, {
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
                        Konfirmasi Hapus Data WNA
                    </DialogTitle>
                </div>
                <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                    Apakah Anda yakin ingin menghapus data Warga Negara Asing di bawah ini?
                </DialogDescription>
            </DialogHeader>

            <div v-if="wna" class="rounded-lg border border-red-200 bg-red-50/50 p-3 text-xs space-y-1.5 dark:border-red-950/60 dark:bg-red-950/20">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nama:</span>
                    <span class="font-semibold text-foreground">{{ wna.nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">No. Register:</span>
                    <span class="font-mono font-medium text-foreground">{{ wna.no_register }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">No. Paspor:</span>
                    <span class="font-mono font-medium text-foreground">{{ wna.no_paspor }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Kewarganegaraan:</span>
                    <span class="font-medium text-foreground">{{ wna.kewarganegaraan }}</span>
                </div>
            </div>

            <p class="text-xs text-muted-foreground">
                Tindakan ini akan menghapus catatan dan berkas pasfoto terkait dari sistem. Data yang telah dihapus tidak dapat dipulihkan kembali.
            </p>

            <DialogFooter class="gap-2 sm:gap-0 pt-2">
                <DialogClose as-child>
                    <Button variant="outline" type="button" :disabled="isDeleting">
                        Batal
                    </Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    type="button"
                    :disabled="isDeleting"
                    @click="handleDelete"
                >
                    <Loader2 v-if="isDeleting" class="mr-2 size-4 animate-spin" />
                    <span>Hapus Data</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
