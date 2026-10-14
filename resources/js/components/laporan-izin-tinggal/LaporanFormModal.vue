<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, FileSpreadsheet, Loader2, XCircle } from '@lucide/vue';
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
import type { LaporanIzinTinggalFormData, LaporanIzinTinggalItem } from '@/types/laporan-izin-tinggal';

const props = defineProps<{
    open: boolean;
    laporan: LaporanIzinTinggalItem | null;
    kategoriOptions: string[];
    layananOptions: string[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.laporan !== null);

const form = useForm<LaporanIzinTinggalFormData>({
    kategori: '',
    layanan: '',
    disetujui_l: 0,
    disetujui_p: 0,
    ditolak_l: 0,
    ditolak_p: 0,
    keterangan: '',
});

// Live calculated totals for preview
const previewDisetujui = computed(() => {
    const l = Number(form.disetujui_l) || 0;
    const p = Number(form.disetujui_p) || 0;
    return l + p;
});

const previewDitolak = computed(() => {
    const l = Number(form.ditolak_l) || 0;
    const p = Number(form.ditolak_p) || 0;
    return l + p;
});

const previewTotal = computed(() => previewDisetujui.value + previewDitolak.value);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            if (props.laporan) {
                form.kategori = props.laporan.kategori || '';
                form.layanan = props.laporan.layanan || '';
                form.disetujui_l = props.laporan.disetujui_l ?? 0;
                form.disetujui_p = props.laporan.disetujui_p ?? 0;
                form.ditolak_l = props.laporan.ditolak_l ?? 0;
                form.ditolak_p = props.laporan.ditolak_p ?? 0;
                form.keterangan = props.laporan.keterangan || '';
            } else {
                form.reset();
                form.disetujui_l = 0;
                form.disetujui_p = 0;
                form.ditolak_l = 0;
                form.ditolak_p = 0;
            }
        }
    }
);

function handleSubmit() {
    if (isEdit.value && props.laporan) {
        form.put(`/laporan-izin-tinggal/${props.laporan.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/laporan-izin-tinggal', {
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
                    <FileSpreadsheet class="size-5 text-primary" />
                    <span>{{ isEdit ? 'Ubah Data Rekapitulasi Laporan' : 'Tambah Rekapitulasi Laporan Izin Tinggal' }}</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    {{
                        isEdit
                            ? 'Perbarui rincian jumlah persetujuan dan penolakan izin tinggal di bawah ini.'
                            : 'Isi formulir berikut untuk mendaftarkan rekapitulasi data layanan izin tinggal baru.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-5 py-2">
                <!-- Kategori & Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="kategori" class="text-xs font-semibold text-foreground">
                            Kategori Izin Tinggal <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="kategori"
                            v-model="form.kategori"
                            list="kategori-list"
                            placeholder="cth. Izin Tinggal Kunjungan"
                            required
                            maxlength="120"
                            :class="{ 'border-destructive': form.errors.kategori }"
                        />
                        <datalist id="kategori-list">
                            <option value="Izin Tinggal Kunjungan" />
                            <option value="Izin Tinggal Terbatas" />
                            <option value="Izin Tinggal Tetap" />
                            <option v-for="opt in kategoriOptions" :key="opt" :value="opt" />
                        </datalist>
                        <InputError :message="form.errors.kategori" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="layanan" class="text-xs font-semibold text-foreground">
                            Jenis Layanan <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="layanan"
                            v-model="form.layanan"
                            list="layanan-list"
                            placeholder="cth. Perpanjangan / Pemberian Baru"
                            required
                            maxlength="150"
                            :class="{ 'border-destructive': form.errors.layanan }"
                        />
                        <datalist id="layanan-list">
                            <option value="Pemberian Baru" />
                            <option value="Perpanjangan" />
                            <option value="Alih Status" />
                            <option value="Pemberian EPO" />
                            <option v-for="opt in layananOptions" :key="opt" :value="opt" />
                        </datalist>
                        <InputError :message="form.errors.layanan" />
                    </div>
                </div>

                <!-- Petunjuk Keterangan Gender L/P -->
                <div class="rounded-lg border border-border/80 bg-muted/40 p-3 text-xs text-muted-foreground flex items-center justify-between">
                    <span>Singkatan Gender: <strong>L</strong> = Laki-laki | <strong>P</strong> = Perempuan</span>
                    <span class="text-[11px] text-muted-foreground/80">Masukkan angka bulat non-negatif</span>
                </div>

                <!-- Bagian Jumlah Disetujui -->
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 dark:border-emerald-950/60 dark:bg-emerald-950/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="size-4 text-emerald-600 dark:text-emerald-400" />
                            <h4 class="text-xs font-bold text-emerald-900 dark:text-emerald-300 uppercase tracking-wider">
                                Jumlah Permohonan Disetujui
                            </h4>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            Subtotal: {{ previewDisetujui.toLocaleString('id-ID') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="disetujui_l" class="text-xs font-medium text-foreground">
                                Disetujui (Laki-laki) <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="disetujui_l"
                                v-model.number="form.disetujui_l"
                                type="number"
                                min="0"
                                max="1000000"
                                step="1"
                                required
                                class="bg-white dark:bg-card"
                                :class="{ 'border-destructive': form.errors.disetujui_l }"
                            />
                            <InputError :message="form.errors.disetujui_l" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="disetujui_p" class="text-xs font-medium text-foreground">
                                Disetujui (Perempuan) <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="disetujui_p"
                                v-model.number="form.disetujui_p"
                                type="number"
                                min="0"
                                max="1000000"
                                step="1"
                                required
                                class="bg-white dark:bg-card"
                                :class="{ 'border-destructive': form.errors.disetujui_p }"
                            />
                            <InputError :message="form.errors.disetujui_p" />
                        </div>
                    </div>
                </div>

                <!-- Bagian Jumlah Ditolak -->
                <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 dark:border-rose-950/60 dark:bg-rose-950/20 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <XCircle class="size-4 text-rose-600 dark:text-rose-400" />
                            <h4 class="text-xs font-bold text-rose-900 dark:text-rose-300 uppercase tracking-wider">
                                Jumlah Permohonan Ditolak
                            </h4>
                        </div>
                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">
                            Subtotal: {{ previewDitolak.toLocaleString('id-ID') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="ditolak_l" class="text-xs font-medium text-foreground">
                                Ditolak (Laki-laki) <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="ditolak_l"
                                v-model.number="form.ditolak_l"
                                type="number"
                                min="0"
                                max="1000000"
                                step="1"
                                required
                                class="bg-white dark:bg-card"
                                :class="{ 'border-destructive': form.errors.ditolak_l }"
                            />
                            <InputError :message="form.errors.ditolak_l" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="ditolak_p" class="text-xs font-medium text-foreground">
                                Ditolak (Perempuan) <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="ditolak_p"
                                v-model.number="form.ditolak_p"
                                type="number"
                                min="0"
                                max="1000000"
                                step="1"
                                required
                                class="bg-white dark:bg-card"
                                :class="{ 'border-destructive': form.errors.ditolak_p }"
                            />
                            <InputError :message="form.errors.ditolak_p" />
                        </div>
                    </div>
                </div>

                <!-- Total Keseluruhan Preview -->
                <div class="flex items-center justify-between rounded-lg border border-indigo-200 bg-indigo-50/70 p-3 text-xs text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200">
                    <span class="font-medium">Total Akumulasi Record Ini:</span>
                    <span class="font-bold text-sm tabular-nums">{{ previewTotal.toLocaleString('id-ID') }} Pemohon</span>
                </div>

                <!-- Keterangan Tambahan -->
                <div class="space-y-1.5">
                    <Label for="keterangan" class="text-xs font-semibold text-foreground">
                        Keterangan Tambahan <span class="text-muted-foreground font-normal">(Opsional)</span>
                    </Label>
                    <textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        rows="3"
                        placeholder="Catatan khusus atau dasar hukum layanan bila ada..."
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring"
                    ></textarea>
                    <InputError :message="form.errors.keterangan" />
                </div>

                <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-border/60">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" size="sm" :disabled="form.processing">
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
