<script setup lang="ts">
import { Calendar, Clock, Globe, IdCard, MapPin, Phone, User, Users } from '@lucide/vue';
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
import type { AffidavitItem } from '@/types/affidavit';

defineProps<{
    open: boolean;
    affidavit: AffidavitItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

function formatIndoDate(dateStr?: string | null): string {
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
        <DialogContent class="sm:max-w-xl max-h-[90vh] flex flex-col p-0 gap-0 overflow-hidden">
            <DialogHeader class="p-6 pb-4 border-b border-border/60 shrink-0">
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <IdCard class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <span>Detail Pendaftaran Affidavit</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Informasi lengkap biodata, nomor paspor, masa berlaku, dan data pendaftaran Affidavit.
                </DialogDescription>
            </DialogHeader>

            <div v-if="affidavit" class="flex-1 overflow-y-auto min-h-0 p-6 space-y-4">
                <!-- Header Card dengan Foto dan Nama -->
                <div class="flex items-start gap-4 rounded-xl border border-border/80 bg-muted/30 p-4">
                    <div class="size-20 shrink-0 overflow-hidden rounded-lg border border-border bg-muted">
                        <img
                            v-if="affidavit.foto_url"
                            :src="affidavit.foto_url"
                            :alt="affidavit.nama"
                            class="size-full object-cover"
                        />
                        <div v-else class="flex size-full items-center justify-center bg-secondary text-muted-foreground">
                            <User class="size-8" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground truncate">
                                {{ affidavit.nama }}
                            </h2>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                Affidavit
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-muted-foreground">
                            {{ affidavit.kewarganegaraan }} &bull; {{ affidavit.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Reg: {{ affidavit.no_register }}
                            </span>
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Paspor: {{ affidavit.no_paspor }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kelahiran, Kewarganegaraan, & Masa Berlaku -->
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Tempat / Tgl Lahir</span>
                        <p class="text-sm font-semibold text-foreground flex items-center gap-1.5">
                            <Calendar class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span class="truncate">{{ affidavit.tempat_lahir || '-' }}, {{ formatIndoDate(affidavit.tanggal_lahir) }}</span>
                        </p>
                    </div>

                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Kewarganegaraan</span>
                        <p class="text-sm font-semibold text-foreground flex items-center gap-1.5 truncate">
                            <Globe class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span class="truncate">{{ affidavit.kewarganegaraan }}</span>
                        </p>
                    </div>

                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Masa Berlaku</span>
                        <p class="text-sm font-semibold text-foreground flex items-center gap-1.5">
                            <Clock class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>{{ affidavit.masa_berlaku ? formatIndoDate(affidavit.masa_berlaku) : '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Informasi Orang Tua -->
                <div class="rounded-lg border border-border/80 p-3.5 space-y-3 bg-muted/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                        <Users class="size-3.5 text-emerald-600" />
                        <span>Data Orang Tua</span>
                    </h3>

                    <div class="grid gap-3 sm:grid-cols-2 text-xs">
                        <div class="p-2.5 rounded-md bg-background border border-border/60 space-y-1">
                            <span class="text-muted-foreground font-medium">Ayah</span>
                            <p class="text-sm font-semibold text-foreground">{{ affidavit.nama_ayah || '-' }}</p>
                            <p class="text-muted-foreground">Kewarganegaraan: {{ affidavit.kewarganegaraan_ayah || '-' }}</p>
                        </div>
                        <div class="p-2.5 rounded-md bg-background border border-border/60 space-y-1">
                            <span class="text-muted-foreground font-medium">Ibu</span>
                            <p class="text-sm font-semibold text-foreground">{{ affidavit.nama_ibu || '-' }}</p>
                            <p class="text-muted-foreground">Kewarganegaraan: {{ affidavit.kewarganegaraan_ibu || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="rounded-lg border border-border/80 p-3.5 space-y-3 bg-muted/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Kontak & Domisili
                    </h3>
                    <div class="grid gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <Phone class="size-3.5 text-muted-foreground shrink-0" />
                            <span class="text-muted-foreground">No. Telepon:</span>
                            <span class="font-medium text-foreground">{{ affidavit.nomor_telepon || '-' }}</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <MapPin class="size-3.5 text-muted-foreground shrink-0 mt-0.5" />
                            <span class="text-muted-foreground">Alamat:</span>
                            <span class="font-medium text-foreground leading-relaxed">{{ affidavit.alamat || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="p-6 py-3 border-t border-border/60 bg-muted/20 shrink-0 flex flex-col-reverse sm:flex-row sm:justify-end items-center gap-2">
                <DialogClose as-child>
                    <Button variant="outline">
                        Tutup
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
