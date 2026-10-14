<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { LaporanIzinTinggalItem, LaporanIzinTinggalSummary } from '@/types/laporan-izin-tinggal';

defineProps<{
    laporanList: LaporanIzinTinggalItem[];
    summary: LaporanIzinTinggalSummary;
    filters: {
        search?: string;
        kategori?: string;
        layanan?: string;
    };
    printedAt: string;
}>();

function printReport() {
    window.print();
}
</script>

<template>
    <Head title="Cetak Rekapitulasi Laporan Izin Tinggal" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-slate-900 print:p-0 print:bg-white">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-6xl mx-auto mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                <Link href="/laporan-izin-tinggal">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Laporan</span>
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
        <div class="max-w-6xl mx-auto rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:shadow-none print:border-none print:p-2">
            <!-- Kop Dokumen Resmi -->
            <div class="text-center pb-4 border-b-2 border-slate-900 mb-6">
                <h1 class="text-base font-bold uppercase tracking-wider text-slate-900">
                    KEMENTERIAN HUKUM DAN HAM REPUBLIK INDONESIA
                </h1>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-800">
                    DIREKTORAT JENDERAL IMIGRASI
                </h2>
                <h3 class="text-lg font-black uppercase tracking-wider text-slate-900 mt-2">
                    REKAPITULASI LAPORAN LAYANAN IZIN TINGGAL
                </h3>
                <p class="text-xs text-slate-600 mt-1">
                    Waktu Cetak: {{ printedAt }} | Total Data: {{ laporanList.length }} Rekapitulasi Layanan
                </p>

                <!-- Kriteria Filter -->
                <div v-if="filters.search || filters.kategori || filters.layanan" class="mt-2 text-xs text-slate-600 flex flex-wrap justify-center gap-3">
                    <span v-if="filters.search">Pencarian: <strong>"{{ filters.search }}"</strong></span>
                    <span v-if="filters.kategori">Kategori: <strong>{{ filters.kategori }}</strong></span>
                    <span v-if="filters.layanan">Layanan: <strong>{{ filters.layanan }}</strong></span>
                </div>
            </div>

            <!-- Tabel Cetak Data Laporan Izin Tinggal -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left">Kategori Izin Tinggal</th>
                        <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left">Jenis Layanan</th>
                        <th colspan="3" class="border border-slate-300 px-2 py-1 text-center bg-emerald-50 text-emerald-950 font-bold">
                            Disetujui
                        </th>
                        <th colspan="3" class="border border-slate-300 px-2 py-1 text-center bg-rose-50 text-rose-950 font-bold">
                            Ditolak
                        </th>
                        <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-bold bg-slate-200 text-slate-900 w-20">
                            Total
                        </th>
                        <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left">Keterangan</th>
                    </tr>
                    <tr class="bg-slate-50 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-1 text-center w-12 bg-emerald-50/50">L</th>
                        <th class="border border-slate-300 px-2 py-1 text-center w-12 bg-emerald-50/50">P</th>
                        <th class="border border-slate-300 px-2 py-1 text-center w-14 bg-emerald-100/60 font-semibold">Subtotal</th>
                        <th class="border border-slate-300 px-2 py-1 text-center w-12 bg-rose-50/50">L</th>
                        <th class="border border-slate-300 px-2 py-1 text-center w-12 bg-rose-50/50">P</th>
                        <th class="border border-slate-300 px-2 py-1 text-center w-14 bg-rose-100/60 font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, idx) in laporanList"
                        :key="item.id"
                        class="hover:bg-slate-50 odd:bg-white even:bg-slate-50/40"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                        <td class="border border-slate-300 px-3 py-1.5 font-medium">{{ item.kategori }}</td>
                        <td class="border border-slate-300 px-3 py-1.5">{{ item.layanan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums bg-emerald-50/20">{{ item.disetujui_l }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums bg-emerald-50/20">{{ item.disetujui_p }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums font-bold text-emerald-900 bg-emerald-50/50">
                            {{ item.total_disetujui }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums bg-rose-50/20">{{ item.ditolak_l }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums bg-rose-50/20">{{ item.ditolak_p }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums font-bold text-rose-900 bg-rose-50/50">
                            {{ item.total_ditolak }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center tabular-nums font-black bg-slate-100 text-slate-900">
                            {{ item.total }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 text-slate-600 text-[11px] leading-tight">
                            {{ item.keterangan || '-' }}
                        </td>
                    </tr>
                    <tr v-if="laporanList.length === 0">
                        <td colspan="11" class="border border-slate-300 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada data laporan izin tinggal yang sesuai dengan kriteria cetak.
                        </td>
                    </tr>
                </tbody>
                <!-- Footer Baris Total Akumulasi -->
                <tfoot v-if="laporanList.length > 0">
                    <tr class="bg-slate-200 border-t-2 border-slate-400 font-bold text-slate-900">
                        <td colspan="3" class="border border-slate-300 px-3 py-2 text-right uppercase tracking-wider">
                            Total Akumulasi:
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums bg-emerald-100/70">
                            {{ summary.total_disetujui_l }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums bg-emerald-100/70">
                            {{ summary.total_disetujui_p }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums font-black text-emerald-950 bg-emerald-200/80">
                            {{ summary.grand_total_disetujui }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums bg-rose-100/70">
                            {{ summary.total_ditolak_l }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums bg-rose-100/70">
                            {{ summary.total_ditolak_p }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums font-black text-rose-950 bg-rose-200/80">
                            {{ summary.grand_total_ditolak }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center tabular-nums font-black text-slate-950 bg-slate-300">
                            {{ summary.grand_total }}
                        </td>
                        <td class="border border-slate-300 px-3 py-2 text-slate-600 text-[11px]">
                            {{ summary.total_records }} layanan
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Catatan Keterangan Singkatan -->
            <div class="mt-3 text-[11px] text-slate-500 italic">
                * Keterangan: L = Laki-laki, P = Perempuan. Seluruh angka di atas merupakan akumulasi resmi dari basis data keimigrasian.
            </div>

            <!-- Tanda Tangan / Pengesahan Cetak -->
            <div class="mt-12 flex justify-end text-xs text-slate-800 break-inside-avoid">
                <div class="w-64 text-center space-y-16">
                    <div>
                        <p>Dicetak pada: {{ printedAt }}</p>
                        <p class="font-semibold mt-1">Petugas Seksi Izin Tinggal Keimigrasian</p>
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
