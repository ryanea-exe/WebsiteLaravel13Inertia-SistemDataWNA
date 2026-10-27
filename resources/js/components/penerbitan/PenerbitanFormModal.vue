<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Award, Calendar, Loader2, X } from '@lucide/vue';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PenerbitanFormData, PenerbitanItem } from '@/types/penerbitan';

const props = defineProps<{
    open: boolean;
    penerbitan: PenerbitanItem | null;
    layananOptions: string[];
    kewarganegaraanOptions: string[];
    kabupatenOptions: string[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.penerbitan !== null);

const form = useForm<PenerbitanFormData>({
    no_paspor: '',
    nama: '',
    kewarganegaraan: '',
    layanan: '',
    tanggal: new Date().toISOString().split('T')[0],
    kabupaten: '',
    keterangan: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            if (props.penerbitan) {
                form.no_paspor = props.penerbitan.no_paspor || '';
                form.nama = props.penerbitan.nama || '';
                form.kewarganegaraan = props.penerbitan.kewarganegaraan || '';
                form.layanan = props.penerbitan.layanan || '';
                form.tanggal = props.penerbitan.tanggal ? props.penerbitan.tanggal.split('T')[0] : '';
                form.kabupaten = props.penerbitan.kabupaten || '';
                form.keterangan = props.penerbitan.keterangan || '';
            } else {
                form.reset();
                form.tanggal = new Date().toISOString().split('T')[0];
            }
        }
    }
);

function handleSubmit() {
    if (isEdit.value && props.penerbitan) {
        form.put(`/penerbitan/${props.penerbitan.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/penerbitan', {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-2xl max-h-[90vh] flex flex-col p-0 gap-0 overflow-hidden">
            <DialogHeader class="p-6 pb-4 border-b border-border/60 shrink-0">
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <Award class="size-5 text-primary" />
                    <span>{{ isEdit ? 'Ubah Data Penerbitan' : 'Tambah Data Penerbitan' }}</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    {{
                        isEdit
                            ? 'Perbarui data penerbitan dokumen atau izin keimigrasian di bawah ini.'
                            : 'Isi formulir berikut untuk mencatat penerbitan dokumen atau izin baru.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col flex-1 min-h-0 overflow-hidden" @submit.prevent="handleSubmit">
                <div class="flex-1 overflow-y-auto min-h-0 p-6 space-y-4">
                    <!-- Nama Pemohon & No. Paspor -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="nama" class="text-xs font-semibold text-foreground">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nama"
                                v-model="form.nama"
                                placeholder="Contoh: Tanaka Kenji"
                                maxlength="180"
                                required
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.nama" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="no_paspor" class="text-xs font-semibold text-foreground">
                                Nomor Paspor <span class="text-muted-foreground text-[11px] font-normal">(Opsional)</span>
                            </Label>
                            <Input
                                id="no_paspor"
                                v-model="form.no_paspor"
                                placeholder="Contoh: A12345678"
                                maxlength="80"
                                class="font-mono uppercase"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.no_paspor" />
                        </div>
                    </div>

                    <!-- Kewarganegaraan & Layanan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="kewarganegaraan" class="text-xs font-semibold text-foreground">
                                Kewarganegaraan <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="kewarganegaraan"
                                v-model="form.kewarganegaraan"
                                list="kewarganegaraan-list"
                                placeholder="Contoh: Jepang"
                                maxlength="100"
                                required
                                :disabled="form.processing"
                            />
                            <datalist id="kewarganegaraan-list">
                                <option v-for="opt in kewarganegaraanOptions" :key="opt" :value="opt" />
                            </datalist>
                            <InputError :message="form.errors.kewarganegaraan" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="layanan" class="text-xs font-semibold text-foreground">
                                Jenis Layanan <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="layanan"
                                v-model="form.layanan"
                                list="layanan-list"
                                placeholder="Contoh: Penerbitan ITAS"
                                maxlength="150"
                                required
                                :disabled="form.processing"
                            />
                            <datalist id="layanan-list">
                                <option v-for="opt in layananOptions" :key="opt" :value="opt" />
                            </datalist>
                            <InputError :message="form.errors.layanan" />
                        </div>
                    </div>

                    <!-- Tanggal Penerbitan & Kabupaten -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="tanggal" class="text-xs font-semibold text-foreground">
                                Tanggal Penerbitan <span class="text-red-500">*</span>
                            </Label>
                            <div class="relative">
                                <Input
                                    id="tanggal"
                                    type="date"
                                    v-model="form.tanggal"
                                    required
                                    class="font-mono"
                                    :disabled="form.processing"
                                />
                            </div>
                            <InputError :message="form.errors.tanggal" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="kabupaten" class="text-xs font-semibold text-foreground">
                                Kabupaten / Wilayah <span class="text-muted-foreground text-[11px] font-normal">(Opsional)</span>
                            </Label>
                            <Input
                                id="kabupaten"
                                v-model="form.kabupaten"
                                list="kabupaten-list"
                                placeholder="Contoh: Wonosobo"
                                maxlength="120"
                                :disabled="form.processing"
                            />
                            <datalist id="kabupaten-list">
                                <option v-for="opt in kabupatenOptions" :key="opt" :value="opt" />
                            </datalist>
                            <InputError :message="form.errors.kabupaten" />
                        </div>
                    </div>

                    <!-- Keterangan Tambahan -->
                    <div class="space-y-1.5">
                        <Label for="keterangan" class="text-xs font-semibold text-foreground">
                            Keterangan Tambahan <span class="text-muted-foreground text-[11px] font-normal">(Opsional)</span>
                        </Label>
                        <textarea
                            id="keterangan"
                            v-model="form.keterangan"
                            rows="3"
                            placeholder="Catatan tambahan terkait penerbitan dokumen atau izin tinggal..."
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:bg-card"
                            :disabled="form.processing"
                        ></textarea>
                        <InputError :message="form.errors.keterangan" />
                    </div>
                </div>

                <DialogFooter class="p-6 py-3 border-t border-border/60 bg-muted/20 shrink-0 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <DialogClose as-child>
                        <Button variant="outline" type="button" :disabled="form.processing">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        <span>{{ isEdit ? 'Simpan Perubahan' : 'Tambah Penerbitan' }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
