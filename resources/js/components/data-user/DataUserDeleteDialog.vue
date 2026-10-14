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
import type { UserItem } from '@/types/data-user';

const props = defineProps<{
    open: boolean;
    user: UserItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isDeleting = ref(false);

function handleDelete() {
    if (!props.user) return;

    isDeleting.value = true;
    router.delete(`/data-user/${props.user.id}`, {
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
                        Konfirmasi Hapus Data Pengguna
                    </DialogTitle>
                </div>
                <DialogDescription class="pt-2 text-sm text-muted-foreground leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun pengguna berikut dari sistem?
                </DialogDescription>
            </DialogHeader>

            <div v-if="user" class="rounded-lg border border-red-200 bg-red-50/50 p-3 text-xs space-y-1.5 dark:border-red-950/60 dark:bg-red-950/20">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nama Pengguna:</span>
                    <span class="font-semibold text-foreground">{{ user.nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Username:</span>
                    <span class="font-mono font-medium text-foreground">@{{ user.username }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Peran / Role:</span>
                    <span class="font-semibold uppercase tracking-wider" :class="user.role === 'admin' ? 'text-primary' : 'text-foreground'">{{ user.role_label }}</span>
                </div>
                <div v-if="user.email" class="flex justify-between">
                    <span class="text-muted-foreground">Email:</span>
                    <span class="font-medium text-foreground">{{ user.email }}</span>
                </div>
            </div>

            <p class="text-xs text-muted-foreground">
                Tindakan ini tidak dapat dibatalkan. Pengguna ini tidak akan dapat login kembali ke dalam sistem setelah akun dihapus.
            </p>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                <DialogClose as-child>
                    <Button type="button" variant="outline" :disabled="isDeleting">
                        Batal
                    </Button>
                </DialogClose>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="isDeleting"
                    @click="handleDelete"
                >
                    <Loader2 v-if="isDeleting" class="mr-1.5 size-4 animate-spin" />
                    {{ isDeleting ? 'Menghapus...' : 'Hapus Pengguna' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
