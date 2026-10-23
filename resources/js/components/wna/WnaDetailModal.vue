<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertCircle, Calendar, CreditCard, Globe, IdCard, MapPin, MessageCircle, Phone, Shield, User } from '@lucide/vue';
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

defineProps<{
    open: boolean;
    wna: WnaItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

function formatIndoDate(dateStr?: string): string {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-w-xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <IdCard class="size-5 text-indigo-600 dark:text-indigo-400" />
                    <span>Detail Data Warga Negara Asing</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Informasi lengkap biodata, paspor, dan izin tinggal keimigrasian WNA.
                </DialogDescription>
            </DialogHeader>

            <div v-if="wna" class="space-y-4 py-2">
                <!-- Header Card dengan Foto dan Nama -->
                <div class="flex items-start gap-4 rounded-xl border border-border/80 bg-muted/30 p-4">
                    <div class="size-20 shrink-0 overflow-hidden rounded-lg border border-border bg-muted">
                        <img
                            v-if="wna.foto_url"
                            :src="wna.foto_url"
                            :alt="wna.nama"
                            class="size-full object-cover"
                        />
                        <div v-else class="flex size-full items-center justify-center bg-secondary text-muted-foreground">
                            <User class="size-8" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground truncate">
                                {{ wna.nama }}
                            </h2>
                            <span
                                class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold uppercase"
                                :class="{
                                    'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300': wna.jenis_izin_tinggal === 'ITAS',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300': wna.jenis_izin_tinggal === 'ITAP',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': wna.jenis_izin_tinggal === 'VOA',
                                    'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300': wna.jenis_izin_tinggal === 'ITK',
                                }"
                            >
                                {{ wna.jenis_izin_tinggal }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-muted-foreground">
                            {{ wna.kewarganegaraan }} &bull; {{ wna.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Reg: {{ wna.no_register }}
                            </span>
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Paspor: {{ wna.no_paspor }}
                            </span>
                            <span class="rounded bg-background px-2 py-0.5 text-muted-foreground border border-border/60">
                                Kabupaten: <strong :class="wna.kabupaten ? 'text-foreground' : 'italic text-muted-foreground'">{{ wna.kabupaten || 'Belum ditentukan' }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Izin Tinggal & Urgensi Masa Berlaku -->
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Masa Berlaku Izin Tinggal</span>
                        <div class="flex items-center gap-2">
                            <Calendar class="size-4 text-muted-foreground" />
                            <span class="text-sm font-semibold text-foreground">
                                {{ formatIndoDate(wna.masa_berlaku) }}
                            </span>
                        </div>
                        <div class="mt-1 flex items-center gap-1.5 text-xs font-medium">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5"
                                :class="{
                                    'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300': wna.urgensi === 'merah',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': wna.urgensi === 'kuning',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': wna.urgensi === 'biru',
                                }"
                            >
                                <AlertCircle class="size-3" />
                                <span>{{ wna.sisa_hari < 0 ? 'Sudah Kedaluwarsa' : `${wna.sisa_hari} Hari Lagi` }}</span>
                            </span>
                            <span class="text-muted-foreground">({{ wna.urgensi_label }})</span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Izin Tinggal Ke</span>
                        <div class="flex items-center gap-2">
                            <Shield class="size-4 text-muted-foreground" />
                            <span class="text-sm font-semibold text-foreground">
                                {{ wna.izin_tinggal_ke || '1 (Pertama)' }}
                            </span>
                        </div>
                        <span class="text-xs text-muted-foreground">Status registrasi izin tinggal aktif</span>
                    </div>
                </div>

                <!-- Informasi Penjamin & Kontak -->
                <div class="rounded-lg border border-border/70 p-3 space-y-2.5 bg-card">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Data Penjamin & Kontak
                    </span>

                    <div class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <span class="text-xs text-muted-foreground">Nama Penjamin</span>
                            <p class="font-medium text-foreground">{{ wna.penjamin || '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-muted-foreground">Nomor Telepon</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="font-medium text-foreground flex items-center gap-1.5">
                                    <Phone v-if="wna.nomor_telepon" class="size-3.5 text-muted-foreground" />
                                    {{ wna.nomor_telepon || '-' }}
                                </p>
                                <a
                                    v-if="wna.whatsapp_url"
                                    :href="wna.whatsapp_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 rounded bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-800 dark:text-emerald-300 px-2 py-0.5 text-xs font-semibold transition-colors"
                                    title="Hubungi via WhatsApp"
                                >
                                    <MessageCircle class="size-3" />
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-muted-foreground">Alamat Penjamin</span>
                        <p class="font-medium text-foreground text-xs leading-relaxed flex items-start gap-1.5 mt-0.5">
                            <MapPin v-if="wna.alamat_penjamin" class="size-3.5 shrink-0 mt-0.5 text-muted-foreground" />
                            {{ wna.alamat_penjamin || '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-between gap-2">
                <DialogClose as-child>
                    <Button variant="outline" type="button">
                        Tutup
                    </Button>
                </DialogClose>
                <Button v-if="wna" as-child class="gap-1.5">
                    <Link :href="`/wna/${wna.id}/card`">
                        <CreditCard class="size-4" />
                        <span>Cetak Kartu WNA</span>
                    </Link>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
