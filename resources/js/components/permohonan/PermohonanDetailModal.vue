<script setup lang="ts">
import { Calendar, Clock, FileText, Globe, IdCard, MapPin, Tag, User } from '@lucide/vue';
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
import type { PermohonanItem } from '@/types/permohonan';

defineProps<{
    open: boolean;
    permohonan: PermohonanItem | null;
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
                    <FileText class="size-5 text-indigo-600 dark:text-indigo-400" />
                    <span>Detail Permohonan Keimigrasian</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Informasi lengkap mengenai pemohon, jenis layanan, tanggal pendaftaran, dan rincian permohonan.
                </DialogDescription>
            </DialogHeader>

            <div v-if="permohonan" class="flex-1 overflow-y-auto min-h-0 p-6 space-y-4">
                <!-- Header Banner Card -->
                <div class="flex items-start gap-4 rounded-xl border border-border/80 bg-muted/30 p-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-600 dark:border-indigo-950 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <FileText class="size-7" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground truncate">
                                {{ permohonan.nama }}
                            </h2>
                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ permohonan.layanan }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-muted-foreground">
                            {{ permohonan.kewarganegaraan }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Paspor: {{ permohonan.no_paspor || '-' }}
                            </span>
                            <span v-if="permohonan.kabupaten" class="rounded bg-background px-2 py-0.5 text-muted-foreground border border-border/60">
                                Kab: {{ permohonan.kabupaten }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Grid Data Rincian -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <User class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Nama Lengkap</p>
                            <p class="font-medium text-foreground">{{ permohonan.nama }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <IdCard class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Nomor Paspor</p>
                            <p class="font-mono font-medium text-foreground">{{ permohonan.no_paspor || '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <Globe class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Kewarganegaraan</p>
                            <p class="font-medium text-foreground">{{ permohonan.kewarganegaraan }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <Tag class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Jenis Layanan</p>
                            <p class="font-medium text-foreground">{{ permohonan.layanan }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <Calendar class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Tanggal Permohonan</p>
                            <p class="font-medium text-foreground">{{ formatIndoDate(permohonan.tanggal) }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <MapPin class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Kabupaten</p>
                            <p class="font-medium text-foreground">{{ permohonan.kabupaten || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Teks Detail Permohonan -->
                <div class="rounded-lg border border-border/60 bg-card p-3.5 shadow-2xs">
                    <p class="text-xs font-semibold text-muted-foreground mb-1.5 flex items-center gap-1.5">
                        <FileText class="size-3.5 text-muted-foreground" />
                        <span>Rincian Permohonan / Keterangan</span>
                    </p>
                    <p class="text-sm text-foreground whitespace-pre-wrap leading-relaxed">
                        {{ permohonan.permohonan || 'Tidak ada catatan tambahan untuk permohonan ini.' }}
                    </p>
                </div>

                <!-- Timestamps -->
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border/60 pt-3 text-xs text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        Didaftarkan: {{ formatIndoDate(permohonan.created_at) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        Terakhir Diperbarui: {{ formatIndoDate(permohonan.updated_at) }}
                    </span>
                </div>
            </div>

            <DialogFooter class="p-6 py-3 border-t border-border/60 bg-muted/20 shrink-0 flex items-center justify-end">
                <DialogClose as-child>
                    <Button variant="outline" size="sm">
                        Tutup
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
