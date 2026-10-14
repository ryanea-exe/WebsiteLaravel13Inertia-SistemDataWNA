<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Calendar, FileText, Loader2, X } from '@lucide/vue';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PermohonanFormData, PermohonanItem } from '@/types/permohonan';

const props = defineProps<{
    open: boolean;
    permohonan: PermohonanItem | null;
    layananOptions: string[];
    kewarganegaraanOptions: string[];
    kabupatenOptions: string[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.permohonan !== null);

const form = useForm<PermohonanFormData>({
    no_paspor: '',
    nama: '',
    kewarganegaraan: '',
    layanan: '',
    tanggal: new Date().toISOString().split('T')[0],
    kabupaten: '',
    permohonan: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            if (props.permohonan) {
                form.no_paspor = props.permohonan.no_paspor || '';
                form.nama = props.permohonan.nama || '';
                form.kewarganegaraan = props.permohonan.kewarganegaraan || '';
                form.layanan = props.permohonan.layanan || '';
                form.tanggal = props.permohonan.tanggal ? props.permohonan.tanggal.split('T')[0] : '';
                form.kabupaten = props.permohonan.kabupaten || '';
                form.permohonan = props.permohonan.permohonan || '';
            } else {
                form.reset();
                form.tanggal = new Date().toISOString().split('T')[0];
            }
        }
    }
);

function handleSubmit() {
    if (isEdit.value && props.permohonan) {
        form.put(`/permohonan/${props.permohonan.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/permohonan', {
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
        <DialogScrollContent class="max-w-2xl max-h-[90vh]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <FileText class="size-5 text-primary" />
                    <span>{{ isEdit ? 'Ubah Data Permohonan Keimigrasian' : 'Tambah Permohonan Keimigrasian' }}</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    {{
                        isEdit
                            ? 'Perbarui formulir permohonan keimigrasian di bawah ini.'
                            : 'Isi formulir berikut untuk mendaftarkan permohonan keimigrasian baru.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Baris 1: Nama Pemohon & No Paspor -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="nama" class="text-xs font-semibold text-foreground">
                            Nama Pemohon <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="nama"
                            v-model="form.nama"
                            type="text"
                            placeholder="Contoh: John Doe"
                            maxlength="180"
                            :disabled="form.processing"
                            required
                        />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="no_paspor" class="text-xs font-semibold text-foreground">
                            Nomor Paspor (Opsional)
                        </Label>
                        <Input
                            id="no_paspor"
                            v-model="form.no_paspor"
                            type="text"
                            placeholder="Contoh: A12345678"
                            maxlength="80"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.no_paspor" />
                    </div>
                </div>

                <!-- Baris 2: Kewarganegaraan & Layanan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="kewarganegaraan" class="text-xs font-semibold text-foreground">
                            Kewarganegaraan <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="kewarganegaraan"
                            v-model="form.kewarganegaraan"
                            type="text"
                            list="kewarganegaraan-suggestions"
                            placeholder="Contoh: Jepang"
                            maxlength="100"
                            :disabled="form.processing"
                            required
                        />
                        <datalist id="kewarganegaraan-suggestions">
                            <option v-for="item in kewarganegaraanOptions" :key="item" :value="item" />
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
                            type="text"
                            list="layanan-suggestions"
                            placeholder="Contoh: Perpanjangan ITAS"
                            maxlength="150"
                            :disabled="form.processing"
                            required
                        />
                        <datalist id="layanan-suggestions">
                            <option v-for="item in layananOptions" :key="item" :value="item" />
                        </datalist>
                        <InputError :message="form.errors.layanan" />
                    </div>
                </div>

                <!-- Baris 3: Tanggal Permohonan & Wilayah Kabupaten -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="tanggal" class="text-xs font-semibold text-foreground">
                            Tanggal Permohonan <span class="text-red-500">*</span>
                        </Label>
                        <div class="relative">
                            <Input
                                id="tanggal"
                                v-model="form.tanggal"
                                type="date"
                                :disabled="form.processing"
                                required
                            />
                        </div>
                        <InputError :message="form.errors.tanggal" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="kabupaten" class="text-xs font-semibold text-foreground">
                            Kabupaten (Opsional)
                        </Label>
                        <Input
                            id="kabupaten"
                            v-model="form.kabupaten"
                            type="text"
                            list="kabupaten-suggestions"
                            placeholder="Contoh: Wonosobo"
                            maxlength="120"
                            :disabled="form.processing"
                        />
                        <datalist id="kabupaten-suggestions">
                            <option v-for="item in kabupatenOptions" :key="item" :value="item" />
                        </datalist>
                        <InputError :message="form.errors.kabupaten" />
                    </div>
                </div>

                <!-- Baris 4: Teks Catatan / Rincian Permohonan -->
                <div class="space-y-1.5">
                    <Label for="permohonan" class="text-xs font-semibold text-foreground">
                        Rincian Permohonan / Catatan (Opsional)
                    </Label>
                    <textarea
                        id="permohonan"
                        v-model="form.permohonan"
                        rows="3"
                        placeholder="Masukkan catatan atau keterangan rincian permohonan keimigrasian..."
                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                    ></textarea>
                    <InputError :message="form.errors.permohonan" />
                </div>

                <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-border/60">
                    <DialogClose as-child>
                        <Button variant="outline" size="sm" type="button" :disabled="form.processing">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        size="sm"
                        class="gap-1.5 shadow-xs"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui Data' : 'Simpan Data') }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
