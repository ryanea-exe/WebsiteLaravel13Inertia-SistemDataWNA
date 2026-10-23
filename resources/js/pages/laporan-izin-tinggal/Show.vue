<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    CheckCircle2,
    Clock,
    FileSpreadsheet,
    FileText,
    Layers,
    Tag,
    XCircle,
} from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { LaporanIzinTinggalItem } from '@/types/laporan-izin-tinggal';

const props = defineProps<{
    laporan: LaporanIzinTinggalItem;
}>();

function formatIndoMonth(dateStr?: string | null): string {
    if (!dateStr) return '—';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}

function formatIndoDate(dateStr?: string | null): string {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Head :title="`Detail Laporan Izin Tinggal - ${laporan.kategori}`" />

    <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto w-full">
        <PageHeader
            :title="`Detail Laporan: ${laporan.kategori}`"
            :description="`Rincian data permohonan persetujuan dan penolakan pada jenis layanan ${laporan.layanan}.`"
        >
            <template #actions>
                <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                    <Link href="/laporan-izin-tinggal">
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
                    <FileSpreadsheet class="size-8" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-foreground">
                            {{ laporan.kategori }}
                        </h2>
                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                            {{ laporan.layanan }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1.5 text-xs text-muted-foreground font-medium">
                        <Calendar class="size-3.5 text-muted-foreground shrink-0" />
                        <span>Periode: {{ formatIndoMonth(laporan.bulan) }}</span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground font-medium">
                        Total Volume Permohonan: <strong class="text-foreground">{{ laporan.total.toLocaleString('id-ID') }}</strong> Orang
                    </p>
                </div>
            </div>

            <!-- Breakdown Tabel Data Gender & Keputusan -->
            <div class="rounded-xl border border-border overflow-hidden">
                <table class="w-full text-xs sm:text-sm">
                    <thead class="bg-muted/50 text-muted-foreground font-semibold border-b border-border">
                        <tr>
                            <th class="text-left px-4 py-3">Keputusan Permohonan</th>
                            <th class="text-right px-4 py-3">Laki-laki (L)</th>
                            <th class="text-right px-4 py-3">Perempuan (P)</th>
                            <th class="text-right px-4 py-3 font-bold text-foreground">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 font-medium flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                                <CheckCircle2 class="size-4 shrink-0" />
                                <span>Disetujui</span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ laporan.disetujui_l.toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ laporan.disetujui_p.toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-bold text-emerald-800 dark:text-emerald-300">
                                {{ laporan.total_disetujui.toLocaleString('id-ID') }}
                            </td>
                        </tr>
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 font-medium flex items-center gap-2 text-rose-700 dark:text-rose-400">
                                <XCircle class="size-4 shrink-0" />
                                <span>Ditolak</span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ laporan.ditolak_l.toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ laporan.ditolak_p.toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-bold text-rose-800 dark:text-rose-300">
                                {{ laporan.total_ditolak.toLocaleString('id-ID') }}
                            </td>
                        </tr>
                        <tr class="bg-muted/40 font-bold">
                            <td class="px-4 py-3 text-foreground">Akumulasi Total</td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ (laporan.disetujui_l + laporan.ditolak_l).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ (laporan.disetujui_p + laporan.ditolak_p).toLocaleString('id-ID') }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-base text-primary">
                                {{ laporan.total.toLocaleString('id-ID') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Keterangan Catatan Tambahan -->
            <div class="rounded-lg border border-border/60 bg-muted/20 p-4 space-y-1.5">
                <p class="text-xs font-semibold text-muted-foreground flex items-center gap-1.5">
                    <FileText class="size-3.5 text-muted-foreground" />
                    <span>Rincian / Keterangan Operasional</span>
                </p>
                <p class="text-sm text-foreground whitespace-pre-wrap leading-relaxed">
                    {{ laporan.keterangan || 'Tidak ada catatan tambahan untuk rekaman laporan ini.' }}
                </p>
            </div>

            <!-- Footer Timestamps -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border/60 pt-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    Didaftarkan: {{ formatIndoDate(laporan.created_at) }}
                </span>
                <span class="flex items-center gap-1.5">
                    <Clock class="size-3.5" />
                    Terakhir Diperbarui: {{ formatIndoDate(laporan.updated_at) }}
                </span>
            </div>
        </div>
    </div>
</template>
