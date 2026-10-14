<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Calendar, ImagePlus, Loader2, Trash2, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
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
import type { AffidavitFormData, AffidavitItem, GenderType } from '@/types/affidavit';

const props = defineProps<{
    open: boolean;
    affidavit: AffidavitItem | null;
    kewarganegaraanOptions: string[];
    genderOptions: { value: string; label: string }[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.affidavit !== null);
const photoPreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm<AffidavitFormData>({
    no_register: '',
    no_paspor: '',
    nama: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    jenis_kelamin: '',
    kewarganegaraan: '',
    masa_berlaku: '',
    nama_ayah: '',
    kewarganegaraan_ayah: '',
    nama_ibu: '',
    kewarganegaraan_ibu: '',
    nomor_telepon: '',
    alamat: '',
    foto: null,
    remove_foto: false,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            if (props.affidavit) {
                form.no_register = props.affidavit.no_register;
                form.no_paspor = props.affidavit.no_paspor;
                form.nama = props.affidavit.nama;
                form.tempat_lahir = props.affidavit.tempat_lahir || '';
                form.tanggal_lahir = props.affidavit.tanggal_lahir ? props.affidavit.tanggal_lahir.split('T')[0] : '';
                form.jenis_kelamin = props.affidavit.jenis_kelamin;
                form.kewarganegaraan = props.affidavit.kewarganegaraan;
                form.masa_berlaku = props.affidavit.masa_berlaku ? props.affidavit.masa_berlaku.split('T')[0] : '';
                form.nama_ayah = props.affidavit.nama_ayah || '';
                form.kewarganegaraan_ayah = props.affidavit.kewarganegaraan_ayah || '';
                form.nama_ibu = props.affidavit.nama_ibu || '';
                form.kewarganegaraan_ibu = props.affidavit.kewarganegaraan_ibu || '';
                form.nomor_telepon = props.affidavit.nomor_telepon || '';
                form.alamat = props.affidavit.alamat || '';
                form.foto = null;
                form.remove_foto = false;
                photoPreview.value = props.affidavit.foto_url;
            } else {
                form.reset();
                photoPreview.value = null;
            }
        }
    }
);

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    form.foto = file;
    form.remove_foto = false;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function removePhoto() {
    form.foto = null;
    form.remove_foto = true;
    photoPreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function handleSubmit() {
    if (isEdit.value && props.affidavit) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/affidavit/${props.affidavit.id}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/affidavit', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
                form.reset();
                photoPreview.value = null;
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogScrollContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold text-foreground">
                    {{ isEdit ? 'Ubah Data Pendaftaran Affidavit' : 'Tambah Data Pendaftaran Affidavit' }}
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Isi lengkap formulir data identitas pendaftaran Affidavit sesuai dokumen paspor, kewarganegaraan, dan catatan sipil.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6 py-2">
                <!-- Bagian 1: Identitas Utama Affidavit -->
                <div class="space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b pb-1">
                        1. Identitas Subjek Affidavit
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- No. Register -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-no-register" class="text-xs font-medium">
                                Nomor Register <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="affidavit-no-register"
                                v-model="form.no_register"
                                placeholder="Contoh: REG-AFF-00123"
                                :class="{ 'border-destructive': form.errors.no_register }"
                                required
                            />
                            <InputError :message="form.errors.no_register" />
                        </div>

                        <!-- No. Paspor -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-no-paspor" class="text-xs font-medium">
                                Nomor Paspor <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="affidavit-no-paspor"
                                v-model="form.no_paspor"
                                placeholder="Contoh: A12345678"
                                :class="{ 'border-destructive': form.errors.no_paspor }"
                                required
                            />
                            <InputError :message="form.errors.no_paspor" />
                        </div>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5">
                        <Label for="affidavit-nama" class="text-xs font-medium">
                            Nama Lengkap <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="affidavit-nama"
                            v-model="form.nama"
                            placeholder="Nama sesuai paspor / akta kelahiran"
                            :class="{ 'border-destructive': form.errors.nama }"
                            required
                        />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Tempat Lahir -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-tempat-lahir" class="text-xs font-medium">Tempat Lahir</Label>
                            <Input
                                id="affidavit-tempat-lahir"
                                v-model="form.tempat_lahir"
                                placeholder="Contoh: Jakarta / Sydney"
                            />
                            <InputError :message="form.errors.tempat_lahir" />
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-tanggal-lahir" class="text-xs font-medium">Tanggal Lahir</Label>
                            <Input
                                id="affidavit-tanggal-lahir"
                                v-model="form.tanggal_lahir"
                                type="date"
                            />
                            <InputError :message="form.errors.tanggal_lahir" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Jenis Kelamin -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-jenis-kelamin" class="text-xs font-medium">
                                Jenis Kelamin <span class="text-destructive">*</span>
                            </Label>
                            <select
                                id="affidavit-jenis-kelamin"
                                v-model="form.jenis_kelamin"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                                :class="{ 'border-destructive': form.errors.jenis_kelamin }"
                                required
                            >
                                <option value="" disabled>Pilih Jenis Kelamin</option>
                                <option
                                    v-for="opt in genderOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                    class="bg-popover text-popover-foreground"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.jenis_kelamin" />
                        </div>

                        <!-- Kewarganegaraan -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-kewarganegaraan" class="text-xs font-medium">
                                Kewarganegaraan <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="affidavit-kewarganegaraan"
                                v-model="form.kewarganegaraan"
                                placeholder="Contoh: Indonesia / Australia"
                                list="affidavit-kewarganegaraan-list"
                                :class="{ 'border-destructive': form.errors.kewarganegaraan }"
                                required
                            />
                            <datalist id="affidavit-kewarganegaraan-list">
                                <option v-for="c in kewarganegaraanOptions" :key="c" :value="c" />
                            </datalist>
                            <InputError :message="form.errors.kewarganegaraan" />
                        </div>
                    </div>

                    <!-- Masa Berlaku (Nullable / Opsional) -->
                    <div class="space-y-1.5 rounded-lg border border-border/70 bg-muted/20 p-3">
                        <div class="flex items-center justify-between">
                            <Label for="affidavit-masa-berlaku" class="text-xs font-medium flex items-center gap-1.5">
                                <Calendar class="size-3.5 text-emerald-600" />
                                <span>Masa Berlaku Affidavit (Opsional)</span>
                            </Label>
                            <button
                                v-if="form.masa_berlaku"
                                type="button"
                                class="text-[11px] text-muted-foreground hover:text-foreground flex items-center gap-0.5"
                                @click="form.masa_berlaku = ''"
                            >
                                <X class="size-3" />
                                <span>Kosongkan</span>
                            </button>
                        </div>
                        <Input
                            id="affidavit-masa-berlaku"
                            v-model="form.masa_berlaku"
                            type="date"
                            :class="{ 'border-destructive': form.errors.masa_berlaku }"
                        />
                        <p class="text-[11px] text-muted-foreground">
                            Kosongkan jika masa berlaku belum ditentukan atau mengikuti ketentuan seumur hidup.
                        </p>
                        <InputError :message="form.errors.masa_berlaku" />
                    </div>
                </div>

                <!-- Bagian 2: Data Orang Tua -->
                <div class="space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b pb-1">
                        2. Data Orang Tua
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Nama Ayah -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-nama-ayah" class="text-xs font-medium">Nama Ayah</Label>
                            <Input
                                id="affidavit-nama-ayah"
                                v-model="form.nama_ayah"
                                placeholder="Nama lengkap ayah"
                            />
                            <InputError :message="form.errors.nama_ayah" />
                        </div>

                        <!-- Kewarganegaraan Ayah -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-kw-ayah" class="text-xs font-medium">Kewarganegaraan Ayah</Label>
                            <Input
                                id="affidavit-kw-ayah"
                                v-model="form.kewarganegaraan_ayah"
                                placeholder="Contoh: Australia"
                            />
                            <InputError :message="form.errors.kewarganegaraan_ayah" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Nama Ibu -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-nama-ibu" class="text-xs font-medium">Nama Ibu</Label>
                            <Input
                                id="affidavit-nama-ibu"
                                v-model="form.nama_ibu"
                                placeholder="Nama lengkap ibu"
                            />
                            <InputError :message="form.errors.nama_ibu" />
                        </div>

                        <!-- Kewarganegaraan Ibu -->
                        <div class="space-y-1.5">
                            <Label for="affidavit-kw-ibu" class="text-xs font-medium">Kewarganegaraan Ibu</Label>
                            <Input
                                id="affidavit-kw-ibu"
                                v-model="form.kewarganegaraan_ibu"
                                placeholder="Contoh: Indonesia"
                            />
                            <InputError :message="form.errors.kewarganegaraan_ibu" />
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: Kontak & Domisili -->
                <div class="space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b pb-1">
                        3. Kontak & Domisili
                    </h3>

                    <!-- Nomor Telepon -->
                    <div class="space-y-1.5">
                        <Label for="affidavit-telepon" class="text-xs font-medium">Nomor Telepon</Label>
                        <Input
                            id="affidavit-telepon"
                            v-model="form.nomor_telepon"
                            placeholder="Contoh: 081234567890"
                        />
                        <InputError :message="form.errors.nomor_telepon" />
                    </div>

                    <!-- Alamat -->
                    <div class="space-y-1.5">
                        <Label for="affidavit-alamat" class="text-xs font-medium">Alamat Tempat Tinggal</Label>
                        <textarea
                            id="affidavit-alamat"
                            v-model="form.alamat"
                            rows="2"
                            placeholder="Alamat domisili saat ini"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                        />
                        <InputError :message="form.errors.alamat" />
                    </div>
                </div>

                <!-- Bagian 4: Pasfoto Subjek (Opsional) -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b pb-1">
                        4. Pasfoto Subjek (Opsional)
                    </h3>

                    <div class="flex items-center gap-4">
                        <div class="size-20 shrink-0 overflow-hidden rounded-lg border border-border bg-muted flex items-center justify-center">
                            <img
                                v-if="photoPreview"
                                :src="photoPreview"
                                alt="Pratinjau Foto"
                                class="size-full object-cover"
                            />
                            <ImagePlus v-else class="size-8 text-muted-foreground" />
                        </div>

                        <div class="flex-1 space-y-1.5">
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <div class="flex flex-wrap items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="fileInputRef?.click()"
                                >
                                    {{ photoPreview ? 'Ganti Foto' : 'Unggah Foto' }}
                                </Button>
                                <Button
                                    v-if="photoPreview"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:bg-destructive/10"
                                    @click="removePhoto"
                                >
                                    <Trash2 class="size-4 mr-1" />
                                    Hapus
                                </Button>
                            </div>
                            <p class="text-[11px] text-muted-foreground">
                                Format: JPG, PNG, WEBP. Ukuran maksimum: 2 MB. Disimpan di storage privat terproteksi.
                            </p>
                            <InputError :message="form.errors.foto" />
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-0 pt-2 border-t">
                    <DialogClose as-child>
                        <Button variant="outline" :disabled="form.processing">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        <span>{{ isEdit ? 'Simpan Perubahan' : 'Tambah Pendaftaran' }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
