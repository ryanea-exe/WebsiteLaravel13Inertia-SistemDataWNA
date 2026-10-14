<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { WnaItem } from '@/types/wna';

defineProps<{
    wnaList: WnaItem[];
    filters: {
        search?: string;
        jenis_izin_tinggal?: string;
        kewarganegaraan?: string;
    };
    printedAt: string;
}>();

function printReport() {
    window.print();
}

function formatIndoDate(dateStr?: string): string {
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
    <Head title="Cetak Laporan Data WNA" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-slate-900 print:p-0 print:bg-white">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                <Link href="/wna">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Data WNA</span>
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
                    LAPORAN DATA WARGA NEGARA ASING (WNA)
                </h3>
                <p class="text-xs text-slate-600 mt-1">
                    Waktu Cetak: {{ printedAt }} | Total Data: {{ wnaList.length }} Orang
                </p>

                <!-- Kriteria Filter -->
                <div v-if="filters.search || filters.jenis_izin_tinggal || filters.kewarganegaraan" class="mt-2 text-xs text-slate-600 flex flex-wrap justify-center gap-3">
                    <span v-if="filters.search">Pencarian: <strong>"{{ filters.search }}"</strong></span>
                    <span v-if="filters.jenis_izin_tinggal">Izin Tinggal: <strong>{{ filters.jenis_izin_tinggal }}</strong></span>
                    <span v-if="filters.kewarganegaraan">Kewarganegaraan: <strong>{{ filters.kewarganegaraan }}</strong></span>
                </div>
            </div>

            <!-- Tabel Cetak Data WNA -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">No. Register</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">No. Paspor</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Lengkap</th>
                        <th class="border border-slate-300 px-2 py-2 text-center w-10">L/P</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Kewarganegaraan</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Izin Tinggal</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Masa Berlaku</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Penjamin</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="wnaList.length === 0">
                        <td colspan="9" class="border border-slate-300 px-4 py-8 text-center text-slate-500">
                            Tidak ada data WNA yang sesuai dengan parameter laporan ini.
                        </td>
                    </tr>
                    <tr
                        v-for="(item, index) in wnaList"
                        :key="item.id"
                        class="border-b border-slate-200 even:bg-slate-50/50"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ index + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono">{{ item.no_register }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono">{{ item.no_paspor }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-semibold">{{ item.nama }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ item.jenis_kelamin }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kewarganegaraan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">{{ item.jenis_izin_tinggal }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 whitespace-nowrap">{{ formatIndoDate(item.masa_berlaku) }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 truncate max-w-[150px]">{{ item.penjamin || '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Area Tanda Tangan Dokumen -->
            <div class="mt-8 pt-4 flex justify-end">
                <div class="text-center text-xs w-60 space-y-16">
                    <div>
                        <p>Dicetak pada tanggal: {{ printedAt }}</p>
                        <p class="font-medium mt-1">Petugas Pengelola Data,</p>
                    </div>
                    <div>
                        <div class="border-b border-slate-800 w-44 mx-auto mb-1" />
                        <p class="font-bold uppercase">Seksi Pengawasan & Keimigrasian</p>
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
        margin: 10mm;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-footer-group;
    }
}
</style>
