<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ImagePlus, Loader2, Trash2 } from '@lucide/vue';
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
import type { GenderType, JenisIzinTinggalType, WnaFormData, WnaItem } from '@/types/wna';

const props = defineProps<{
    open: boolean;
    wna: WnaItem | null;
    kewarganegaraanOptions: string[];
    jenisIzinTinggalOptions: { value: string; label: string }[];
    genderOptions: { value: string; label: string }[];
    kabupatenOptions?: string[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.wna !== null);
const photoPreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm<WnaFormData>({
    no_register: '',
    no_paspor: '',
    nama: '',
    jenis_kelamin: '',
    kewarganegaraan: '',
    kabupaten: '',
    jenis_izin_tinggal: '',
    izin_tinggal_ke: '',
    masa_berlaku: '',
    penjamin: '',
    alamat_penjamin: '',
    nomor_telepon: '',
    foto: null,
    remove_foto: false,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            if (props.wna) {
                form.no_register = props.wna.no_register;
                form.no_paspor = props.wna.no_paspor;
                form.nama = props.wna.nama;
                form.jenis_kelamin = props.wna.jenis_kelamin;
                form.kewarganegaraan = props.wna.kewarganegaraan;
                form.kabupaten = props.wna.kabupaten || '';
                form.jenis_izin_tinggal = props.wna.jenis_izin_tinggal;
                form.izin_tinggal_ke = props.wna.izin_tinggal_ke || '';
                form.masa_berlaku = props.wna.masa_berlaku ? props.wna.masa_berlaku.split('T')[0] : '';
                form.penjamin = props.wna.penjamin || '';
                form.alamat_penjamin = props.wna.alamat_penjamin || '';
                form.nomor_telepon = props.wna.nomor_telepon || '';
                form.foto = null;
                form.remove_foto = false;
                photoPreview.value = props.wna.foto_url;
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
    if (isEdit.value && props.wna) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/wna/${props.wna.id}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/wna', {
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
                    {{ isEdit ? 'Ubah Data Warga Negara Asing (WNA)' : 'Tambah Data Warga Negara Asing (WNA)' }}
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Pastikan informasi dokumen keimigrasian diisi dengan benar sesuai data resmi paspor dan izin tinggal.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <!-- Section 1: Data Identitas & Paspor -->
                <div class="rounded-lg border border-border/70 bg-card p-4 space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Dokumen & Identitas Pokok
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="no_register">
                                Nomor Register <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="no_register"
                                v-model="form.no_register"
                                placeholder="Contoh: REG-WNA-00123"
                                :disabled="form.processing"
                                required
                            />
                            <InputError :message="form.errors.no_register" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="no_paspor">
                                Nomor Paspor <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="no_paspor"
                                v-model="form.no_paspor"
                                placeholder="Contoh: A12345678"
                                :disabled="form.processing"
                                required
                            />
                            <InputError :message="form.errors.no_paspor" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="nama">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nama"
                                v-model="form.nama"
                                placeholder="Nama lengkap sesuai paspor"
                                :disabled="form.processing"
                                required
                            />
                            <InputError :message="form.errors.nama" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="jenis_kelamin">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="jenis_kelamin"
                                v-model="form.jenis_kelamin"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:bg-background"
                                :disabled="form.processing"
                                required
                            >
                                <option value="" disabled>Pilih Jenis Kelamin</option>
                                <option
                                    v-for="opt in genderOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.jenis_kelamin" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="kewarganegaraan">
                                Kewarganegaraan <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="kewarganegaraan"
                                v-model="form.kewarganegaraan"
                                list="kewarganegaraan-list"
                                placeholder="Ketik atau pilih kewarganegaraan"
                                :disabled="form.processing"
                                required
                            />
                            <datalist id="kewarganegaraan-list">
                                <option
                                    v-for="item in kewarganegaraanOptions"
                                    :key="item"
                                    :value="item"
                                />
                            </datalist>
                            <InputError :message="form.errors.kewarganegaraan" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="kabupaten">
                                Wilayah Kabupaten <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="kabupaten"
                                v-model="form.kabupaten"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:bg-background"
                                :disabled="form.processing"
                                required
                            >
                                <option value="" disabled>Pilih Kabupaten</option>
                                <option
                                    v-for="kab in (kabupatenOptions || ['Ponorogo', 'Pacitan', 'Trenggalek'])"
                                    :key="kab"
                                    :value="kab"
                                >
                                    {{ kab }}
                                </option>
                            </select>
                            <InputError :message="form.errors.kabupaten" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Izin Tinggal & Masa Berlaku -->
                <div class="rounded-lg border border-border/70 bg-card p-4 space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Izin Tinggal & Masa Berlaku
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <Label for="jenis_izin_tinggal">
                                Jenis Izin Tinggal <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="jenis_izin_tinggal"
                                v-model="form.jenis_izin_tinggal"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:bg-background"
                                :disabled="form.processing"
                                required
                            >
                                <option value="" disabled>Pilih Jenis</option>
                                <option
                                    v-for="opt in jenisIzinTinggalOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.jenis_izin_tinggal" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="izin_tinggal_ke">
                                Izin Tinggal Ke
                            </Label>
                            <Input
                                id="izin_tinggal_ke"
                                v-model="form.izin_tinggal_ke"
                                placeholder="Contoh: 1 (Pertama)"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.izin_tinggal_ke" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="masa_berlaku">
                                Masa Berlaku <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="masa_berlaku"
                                v-model="form.masa_berlaku"
                                type="date"
                                :disabled="form.processing"
                                required
                            />
                            <InputError :message="form.errors.masa_berlaku" />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Penjamin & Kontak -->
                <div class="rounded-lg border border-border/70 bg-card p-4 space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Informasi Penjamin & Kontak
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="penjamin">Nama Penjamin / Perusahaan</Label>
                            <Input
                                id="penjamin"
                                v-model="form.penjamin"
                                placeholder="Nama perorangan atau badan usaha penjamin"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.penjamin" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="nomor_telepon">Nomor Telepon / Kontak</Label>
                            <Input
                                id="nomor_telepon"
                                v-model="form.nomor_telepon"
                                placeholder="Contoh: 08123456789 atau +62..."
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.nomor_telepon" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="alamat_penjamin">Alamat Penjamin</Label>
                        <textarea
                            id="alamat_penjamin"
                            v-model="form.alamat_penjamin"
                            rows="2"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:bg-background"
                            placeholder="Alamat lengkap domisili penjamin"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.alamat_penjamin" />
                    </div>
                </div>

                <!-- Section 4: Foto Dokumen -->
                <div class="rounded-lg border border-border/70 bg-card p-4 space-y-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Pasfoto WNA
                    </h3>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div
                            class="relative flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-border bg-muted/40"
                        >
                            <img
                                v-if="photoPreview"
                                :src="photoPreview"
                                alt="Pratinjau Foto"
                                class="size-full object-cover"
                            />
                            <div v-else class="flex flex-col items-center justify-center p-2 text-center text-muted-foreground">
                                <ImagePlus class="size-6 text-muted-foreground/70" />
                                <span class="mt-1 text-[10px]">Belum Ada</span>
                            </div>
                        </div>

                        <div class="flex-1 space-y-2">
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
                                    :disabled="form.processing"
                                    @click="fileInputRef?.click()"
                                >
                                    <ImagePlus class="mr-1.5 size-4" />
                                    {{ photoPreview ? 'Ganti Foto' : 'Pilih Foto' }}
                                </Button>
                                <Button
                                    v-if="photoPreview"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/30"
                                    :disabled="form.processing"
                                    @click="removePhoto"
                                >
                                    <Trash2 class="mr-1.5 size-4" />
                                    Hapus Foto
                                </Button>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Format: JPG, PNG, WEBP. Ukuran maksimal 2 MB. Disarankan foto formal latar belakang polos.
                            </p>
                            <InputError :message="form.errors.foto" />
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-0 pt-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" :disabled="form.processing">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 size-4 animate-spin" />
                        <span>{{ isEdit ? 'Simpan Perubahan' : 'Tambah WNA' }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
