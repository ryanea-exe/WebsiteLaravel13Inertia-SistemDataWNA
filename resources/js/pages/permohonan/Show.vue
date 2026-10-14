<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    FileText,
    Globe,
    IdCard,
    MapPin,
    Tag,
    User,
} from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { PermohonanItem } from '@/types/permohonan';

const props = defineProps<{
    permohonan: PermohonanItem;
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
    <Head :title="`Detail Permohonan - ${permohonan.nama}`" />

    <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto w-full">
        <PageHeader
            :title="`Detail Permohonan: ${permohonan.nama}`"
            description="Informasi rinci data pemohon, jenis layanan, tanggal pendaftaran, dan catatan permohonan."
        >
            <template #actions>
                <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                    <Link href="/permohonan">
                        <ArrowLeft class="size-4" />
                        <span>Kembali ke Daftar</span>
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <!-- Main Detail Card -->
        <div class="rounded-xl border border-border/80 bg-card p-6 shadow-2xs space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 pb-6 border-b border-border/60">
                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 dark:border-indigo-950 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-xs">
                    <FileText class="size-8" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-foreground">
                            {{ permohonan.nama }}
                        </h2>
                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                            {{ permohonan.layanan }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground font-medium">
                        Kewarganegaraan: {{ permohonan.kewarganegaraan }}
                    </p>
                </div>
            </div>

            <!-- Grid Rincian Permohonan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <User class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Nama Pemohon</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ permohonan.nama }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <IdCard class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Nomor Paspor</p>
                        <p class="font-mono font-semibold text-foreground text-sm mt-0.5">{{ permohonan.no_paspor || '-' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <Globe class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Kewarganegaraan</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ permohonan.kewarganegaraan }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <Tag class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Jenis Layanan</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ permohonan.layanan }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <Calendar class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Tanggal Permohonan</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ formatIndoDate(permohonan.tanggal) }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <MapPin class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Kabupaten</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ permohonan.kabupaten || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Catatan Permohonan -->
            <div class="rounded-lg border border-border/60 bg-muted/20 p-4 space-y-1.5">
                <p class="text-xs font-semibold text-muted-foreground flex items-center gap-1.5">
                    <FileText class="size-3.5 text-muted-foreground" />
                    <span>Rincian Permohonan / Catatan</span>
                </p>
                <p class="text-sm text-foreground whitespace-pre-wrap leading-relaxed">
                    {{ permohonan.permohonan || 'Tidak ada catatan tambahan untuk permohonan ini.' }}
                </p>
            </div>

            <!-- Footer Timestamps -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border/60 pt-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    Didaftarkan: {{ formatIndoDate(permohonan.created_at) }}
                </span>
                <span class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    Terakhir Diperbarui: {{ formatIndoDate(permohonan.updated_at) }}
                </span>
            </div>
        </div>
    </div>
</template>
