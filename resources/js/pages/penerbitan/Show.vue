<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    FileCheck2,
    Globe,
    IdCard,
    MapPin,
    Tag,
    User,
} from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { PenerbitanItem } from '@/types/penerbitan';

const props = defineProps<{
    penerbitan: PenerbitanItem;
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
    <Head :title="`Detail Penerbitan - ${penerbitan.nama}`" />

    <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto w-full">
        <PageHeader
            :title="`Detail Penerbitan: ${penerbitan.nama}`"
            description="Informasi rinci penerbitan dokumen keimigrasian, pemegang paspor, dan catatan penerbitan."
        >
            <template #actions>
                <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                    <Link href="/penerbitan">
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
                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-950 dark:bg-emerald-950/40 dark:text-emerald-400 shadow-xs">
                    <FileCheck2 class="size-8" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-foreground">
                            {{ penerbitan.nama }}
                        </h2>
                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                            {{ penerbitan.layanan }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground font-medium">
                        Kewarganegaraan: {{ penerbitan.kewarganegaraan }}
                    </p>
                </div>
            </div>

            <!-- Grid Rincian Penerbitan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <User class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Nama Pemegang Dokumen</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ penerbitan.nama }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <IdCard class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Nomor Paspor</p>
                        <p class="font-mono font-semibold text-foreground text-sm mt-0.5">{{ penerbitan.no_paspor || '-' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <Globe class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Kewarganegaraan</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ penerbitan.kewarganegaraan }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <Tag class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Jenis Layanan</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ penerbitan.layanan }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <Calendar class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Tanggal Penerbitan</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ formatIndoDate(penerbitan.tanggal) }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-lg border border-border/60 bg-muted/20 p-3.5">
                    <MapPin class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Kabupaten / Wilayah</p>
                        <p class="font-semibold text-foreground text-sm mt-0.5">{{ penerbitan.kabupaten || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Keterangan / Catatan -->
            <div class="rounded-lg border border-border/60 bg-muted/15 p-4 space-y-2">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <FileCheck2 class="size-4 text-primary" />
                    <span>Keterangan Penerbitan</span>
                </div>
                <div class="text-sm text-foreground/90 leading-relaxed whitespace-pre-wrap bg-background/60 p-3 rounded-md border border-border/40">
                    {{ penerbitan.keterangan || 'Tidak ada catatan tambahan untuk penerbitan dokumen ini.' }}
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-border/60 text-xs text-muted-foreground">
                <div class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    <span>Dibuat: {{ formatIndoDate(penerbitan.created_at) }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    <span>Terakhir Diperbarui: {{ formatIndoDate(penerbitan.updated_at) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
