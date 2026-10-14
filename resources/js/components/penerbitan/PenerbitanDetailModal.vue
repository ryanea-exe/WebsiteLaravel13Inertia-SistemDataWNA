<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Award, Calendar, Clock, ExternalLink, Globe, IdCard, MapPin, Tag, User } from '@lucide/vue';
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
import type { PenerbitanItem } from '@/types/penerbitan';

defineProps<{
    open: boolean;
    penerbitan: PenerbitanItem | null;
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
        <DialogContent class="max-w-xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <Award class="size-5 text-indigo-600 dark:text-indigo-400" />
                    <span>Detail Data Penerbitan</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Informasi lengkap mengenai rekaman penerbitan dokumen dan izin keimigrasian.
                </DialogDescription>
            </DialogHeader>

            <div v-if="penerbitan" class="space-y-4 py-2">
                <!-- Header Banner Card -->
                <div class="flex items-start gap-4 rounded-xl border border-border/80 bg-muted/30 p-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-600 dark:border-indigo-950 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-xs">
                        <Award class="size-7" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground truncate">
                                {{ penerbitan.nama }}
                            </h2>
                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                {{ penerbitan.layanan }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-muted-foreground">
                            {{ penerbitan.kewarganegaraan }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Paspor: {{ penerbitan.no_paspor || '-' }}
                            </span>
                            <span v-if="penerbitan.kabupaten" class="rounded bg-background px-2 py-0.5 text-muted-foreground border border-border/60">
                                Kab: {{ penerbitan.kabupaten }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Grid Data Rincian -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <User class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Nama Pemohon</p>
                            <p class="font-medium text-foreground">{{ penerbitan.nama }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <IdCard class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Nomor Paspor</p>
                            <p class="font-mono font-medium text-foreground">{{ penerbitan.no_paspor || '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <Globe class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Kewarganegaraan</p>
                            <p class="font-medium text-foreground">{{ penerbitan.kewarganegaraan }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <Tag class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Jenis Layanan</p>
                            <p class="font-medium text-foreground">{{ penerbitan.layanan }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <Calendar class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Tanggal Penerbitan</p>
                            <p class="font-mono font-medium text-foreground">{{ formatIndoDate(penerbitan.tanggal) }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-card p-3 shadow-2xs">
                        <MapPin class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Kabupaten / Wilayah</p>
                            <p class="font-medium text-foreground">{{ penerbitan.kabupaten || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="rounded-lg border border-border/60 bg-card p-4 shadow-2xs space-y-1.5">
                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Keterangan Tambahan
                    </p>
                    <p class="text-sm text-foreground whitespace-pre-wrap leading-relaxed">
                        {{ penerbitan.keterangan || 'Tidak ada catatan keterangan tambahan.' }}
                    </p>
                </div>

                <!-- Timestamps -->
                <div class="flex flex-wrap items-center justify-between text-xs text-muted-foreground px-1 pt-1">
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        Dibuat: {{ formatIndoDate(penerbitan.created_at) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        Diperbarui: {{ formatIndoDate(penerbitan.updated_at) }}
                    </span>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-between items-center gap-2 pt-2 border-t border-border/60">
                <Button v-if="penerbitan" variant="ghost" size="sm" as-child class="gap-1.5 text-xs text-muted-foreground hover:text-foreground">
                    <Link :href="`/penerbitan/${penerbitan.id}`">
                        <ExternalLink class="size-3.5" />
                        <span>Buka Halaman Lengkap</span>
                    </Link>
                </Button>
                <div v-else></div>

                <DialogClose as-child>
                    <Button variant="outline" size="sm">
                        Tutup
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
