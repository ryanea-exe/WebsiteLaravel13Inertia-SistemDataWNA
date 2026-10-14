<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { PermohonanItem } from '@/types/permohonan';

defineProps<{
    permohonanList: PermohonanItem[];
    filters: {
        search?: string;
        layanan?: string;
        kewarganegaraan?: string;
        kabupaten?: string;
    };
    printedAt: string;
}>();

function printReport() {
    window.print();
}

function formatIndoDate(dateStr?: string | null): string {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Head title="Cetak Laporan Permohonan Keimigrasian" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-slate-900 print:p-0 print:bg-white">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                <Link href="/permohonan">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Permohonan</span>
                </Link>
            </Button>

            <div class="flex items-center gap-2">
                <Button
                    size="sm"
                    class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs"
                    @click="printReport"
                >
                    <Printer class="size-4" />
                    <span>Cetak / Simpan PDF</span>
                </Button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="max-w-5xl mx-auto rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:shadow-none print:border-none print:p-2">
            <!-- Kop Dokumen Resmi -->
            <div class="text-center pb-4 border-b-2 border-slate-900 mb-6">
                <h1 class="text-base font-bold uppercase tracking-wider text-slate-900">
                    KEMENTERIAN HUKUM DAN HAM REPUBLIK INDONESIA
                </h1>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-800">
                    DIREKTORAT JENDERAL IMIGRASI
                </h2>
                <h3 class="text-lg font-black uppercase tracking-wider text-slate-900 mt-2">
                    REKAPITULASI LAPORAN PERMOHONAN KEIMIGRASIAN
                </h3>
                <p class="text-xs text-slate-600 mt-1">
                    Waktu Cetak: {{ printedAt }} | Total Data: {{ permohonanList.length }} Permohonan
                </p>

                <!-- Kriteria Filter -->
                <div v-if="filters.search || filters.layanan || filters.kewarganegaraan || filters.kabupaten" class="mt-2 text-xs text-slate-600 flex flex-wrap justify-center gap-3">
                    <span v-if="filters.search">Pencarian: <strong>"{{ filters.search }}"</strong></span>
                    <span v-if="filters.layanan">Layanan: <strong>{{ filters.layanan }}</strong></span>
                    <span v-if="filters.kewarganegaraan">Kewarganegaraan: <strong>{{ filters.kewarganegaraan }}</strong></span>
                    <span v-if="filters.kabupaten">Kabupaten: <strong>{{ filters.kabupaten }}</strong></span>
                </div>
            </div>

            <!-- Tabel Cetak Data Permohonan -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-center w-24">Tanggal</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Pemohon</th>
                        <th class="border border-slate-300 px-2 py-2 text-left w-24">No. Paspor</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Layanan</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Kewarganegaraan</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Kabupaten</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Rincian Permohonan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, idx) in permohonanList"
                        :key="item.id"
                        class="hover:bg-slate-50 odd:bg-white even:bg-slate-50/50"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center whitespace-nowrap">{{ formatIndoDate(item.tanggal) }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-medium">{{ item.nama }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono text-[11px]">{{ item.no_paspor || '-' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-semibold text-indigo-950">{{ item.layanan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kewarganegaraan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kabupaten || '-' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-slate-700 leading-tight">{{ item.permohonan || '-' }}</td>
                    </tr>
                    <tr v-if="permohonanList.length === 0">
                        <td colspan="8" class="border border-slate-300 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada data permohonan keimigrasian yang sesuai dengan kriteria cetak.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tanda Tangan / Pengesahan Cetak -->
            <div class="mt-12 flex justify-end text-xs text-slate-800 break-inside-avoid">
                <div class="w-64 text-center space-y-16">
                    <div>
                        <p>Dicetak pada: {{ printedAt }}</p>
                        <p class="font-semibold mt-1">Petugas Seksi Pelayanan & Verifikasi</p>
                    </div>
                    <div>
                        <div class="border-b border-slate-400 w-48 mx-auto" />
                        <p class="text-[11px] text-slate-500 mt-1">NIP. Petugas Terkait</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: A4 landscape;
        margin: 1.2cm;
    }
    body {
        background-color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
