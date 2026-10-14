<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Calendar, CheckCircle2, DollarSign, Loader2, TrendingDown, TrendingUp, X } from '@lucide/vue';
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
import type { RealisasiPnbpFormData, RealisasiPnbpItem } from '@/types/realisasi-pnbp';

const props = defineProps<{
    open: boolean;
    realisasiPnbp: RealisasiPnbpItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.realisasiPnbp !== null);

const monthInput = ref('');

const form = useForm<RealisasiPnbpFormData>({
    bulan: '',
    target: '',
    realisasi: '',
    keterangan: '',
});

function formatRupiah(value: number | string): string {
    const num = Number(value);
    if (isNaN(num)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(num);
}

// Live calculation for preview
const calculatedSelisih = computed(() => {
    const t = Number(form.target) || 0;
    const r = Number(form.realisasi) || 0;
    return r - t;
});

const calculatedPersentase = computed(() => {
    const t = Number(form.target) || 0;
    const r = Number(form.realisasi) || 0;
    if (t <= 0) return 0;
    return Math.round((r / t) * 10000) / 100;
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            if (props.realisasiPnbp) {
                // Extracts YYYY-MM from YYYY-MM-DD
                const rawBulan = props.realisasiPnbp.bulan || '';
                monthInput.value = rawBulan.length >= 7 ? rawBulan.slice(0, 7) : '';
                form.bulan = rawBulan.length >= 7 ? `${rawBulan.slice(0, 7)}-01` : '';
                form.target = props.realisasiPnbp.target ?? 0;
                form.realisasi = props.realisasiPnbp.realisasi ?? 0;
                form.keterangan = props.realisasiPnbp.keterangan || '';
            } else {
                form.reset();
                const now = new Date();
                const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                monthInput.value = currentMonth;
                form.bulan = `${currentMonth}-01`;
                form.target = '';
                form.realisasi = '';
                form.keterangan = '';
            }
        }
    }
);

watch(monthInput, (val) => {
    if (val && val.includes('-')) {
        form.bulan = `${val}-01`;
    } else {
        form.bulan = '';
    }
});

function handleSubmit() {
    if (isEdit.value && props.realisasiPnbp) {
        form.put(`/realisasi-pnbp/${props.realisasiPnbp.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/realisasi-pnbp', {
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
                    <DollarSign class="size-5 text-primary" />
                    <span>{{ isEdit ? 'Ubah Data Realisasi PNBP' : 'Tambah Realisasi PNBP' }}</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    {{
                        isEdit
                            ? 'Perbarui rincian target dan capaian realisasi penerimaan negara bukan pajak di bawah ini.'
                            : 'Isi formulir berikut untuk mencatat target dan realisasi penerimaan negara bukan pajak.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-2">
                <!-- Periode Bulan & Tahun -->
                <div class="space-y-1.5">
                    <Label for="bulan_input" class="text-xs font-semibold text-foreground">
                        Periode Bulan & Tahun <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground pointer-events-none" />
                        <Input
                            id="bulan_input"
                            v-model="monthInput"
                            type="month"
                            class="pl-9 font-medium"
                            :disabled="form.processing"
                            required
                        />
                    </div>
                    <p class="text-[11px] text-muted-foreground">
                        Tiap bulan kalender hanya dapat didaftarkan satu kali untuk mencegah pencatatan ganda.
                    </p>
                    <InputError :message="form.errors.bulan" />
                </div>

                <!-- Grid Target & Realisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Target Penerimaan -->
                    <div class="space-y-1.5">
                        <Label for="target" class="text-xs font-semibold text-foreground">
                            Target Penerimaan (Rp) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="target"
                            v-model="form.target"
                            type="number"
                            min="0"
                            step="any"
                            placeholder="Contoh: 100000000"
                            class="font-mono"
                            :disabled="form.processing"
                            required
                        />
                        <p v-if="form.target !== '' && Number(form.target) >= 0" class="text-xs font-mono font-medium text-muted-foreground truncate">
                            {{ formatRupiah(form.target) }}
                        </p>
                        <InputError :message="form.errors.target" />
                    </div>

                    <!-- Realisasi Penerimaan -->
                    <div class="space-y-1.5">
                        <Label for="realisasi" class="text-xs font-semibold text-foreground">
                            Realisasi Penerimaan (Rp) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="realisasi"
                            v-model="form.realisasi"
                            type="number"
                            min="0"
                            step="any"
                            placeholder="Contoh: 125000000"
                            class="font-mono"
                            :disabled="form.processing"
                            required
                        />
                        <p v-if="form.realisasi !== '' && Number(form.realisasi) >= 0" class="text-xs font-mono font-medium text-muted-foreground truncate">
                            {{ formatRupiah(form.realisasi) }}
                        </p>
                        <InputError :message="form.errors.realisasi" />
                    </div>
                </div>

                <!-- Live Calculation Preview Card (Pola LaporanFormModal) -->
                <div
                    v-if="form.target !== '' || form.realisasi !== ''"
                    class="flex flex-col sm:flex-row sm:items-center justify-between rounded-lg border border-indigo-200 bg-indigo-50/70 p-3 text-xs text-indigo-950 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200 gap-2"
                >
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Estimasi Capaian:</span>
                        <span class="font-bold tabular-nums">
                            {{ (calculatedSelisih >= 0 ? '+ ' : '') + formatRupiah(calculatedSelisih) }} ({{ calculatedPersentase }}%)
                        </span>
                    </div>
                    <span
                        class="font-semibold text-xs self-start sm:self-auto px-2 py-0.5 rounded"
                        :class="calculatedSelisih >= 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200'"
                    >
                        {{ calculatedSelisih >= 0 ? 'Surplus (+)' : 'Defisit (-)' }}
                    </span>
                </div>

                <!-- Keterangan -->
                <div class="space-y-1.5">
                    <Label for="keterangan" class="text-xs font-semibold text-foreground">
                        Keterangan Tambahan <span class="text-muted-foreground font-normal">(Opsional)</span>
                    </Label>
                    <textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        rows="3"
                        placeholder="Catatan tambahan terkait realisasi penerimaan negara bukan pajak..."
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                    ></textarea>
                    <InputError :message="form.errors.keterangan" />
                </div>

                <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-4 border-t border-border/60">
                    <DialogClose as-child>
                        <Button variant="outline" type="button" :disabled="form.processing">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        class="gap-1.5 shadow-xs"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                        <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Realisasi') }}</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
