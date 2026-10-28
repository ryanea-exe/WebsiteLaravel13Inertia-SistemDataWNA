<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { RealisasiPnbpItem, RealisasiPnbpSummary } from '@/types/realisasi-pnbp';

defineProps<{
    realisasiList: RealisasiPnbpItem[];
    summary: RealisasiPnbpSummary;
    filters: {
        search?: string;
        tahun?: string;
    };
    printedAt: string;
}>();

const page = usePage();
const appIcon = computed(() => (page.props.app_settings as any)?.app_icon as string | null | undefined);
const instansiName = computed(() => ((page.props.app_settings as any)?.instansi_name as string | undefined) || 'Kantor Imigrasi Kelas II Non TPI Ponorogo');

function printReport() {
    window.print();
}

function formatIndoMonth(dateStr?: string | null): string {
    if (!dateStr) return '-';
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

function formatRupiah(value?: number | string | null): string {
    if (value === null || value === undefined) return 'Rp 0';
    const num = Number(value);
    if (isNaN(num)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(num);
}
</script>

<template>
    <Head title="Cetak Laporan Realisasi PNBP" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-slate-900 print:p-0 print:bg-white">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                <Link href="/realisasi-pnbp">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Realisasi PNBP</span>
                </Link>
            </Button>

            <div class="flex items-center gap-3">
                <p class="text-xs text-muted-foreground hidden sm:block">
                    <span class="font-medium text-foreground">Petunjuk:</span> Pilih opsi <em>"Simpan sebagai PDF"</em> (Save as PDF) pada dialog cetak peramban untuk mengunduh dokumen.
                </p>
                <Button
                    size="sm"
                    class="gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs shrink-0"
                    @click="printReport"
                >
                    <Printer class="size-4" />
                    <span>Print / Download PDF</span>
                </Button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="max-w-5xl mx-auto rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:shadow-none print:border-none print:p-2">
            <!-- Kop Dokumen Resmi -->
            <div class="pb-4 border-b-2 border-slate-900 mb-6">
                <div class="flex items-center justify-center gap-4">
                    <img
                        v-if="appIcon"
                        :src="appIcon"
                        alt="Logo Instansi"
                        class="size-16 object-contain shrink-0 hidden sm:block print:block"
                    />
                    <div class="text-center flex-1">
                        <h1 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-900 leading-tight">
                            KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA
                        </h1>
                        <h2 class="text-xs sm:text-xs font-semibold uppercase tracking-wider text-slate-800 leading-tight mt-0.5">
                            DIREKTORAT JENDERAL IMIGRASI
                        </h2>
                        <h3 class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-700 leading-tight">
                            KANTOR WILAYAH DIREKTORAT JENDERAL IMIGRASI JAWA TIMUR
                        </h3>
                        <h4 class="text-sm sm:text-base font-black uppercase tracking-wider text-slate-900 mt-1 leading-snug">
                            {{ instansiName }}
                        </h4>
                        <p class="text-[9px] sm:text-[10px] text-slate-600 mt-1 leading-tight">
                            Jalan Sekar Putih Timur, Tonatan, Kec. Ponorogo, Kab. Ponorogo, Jawa Timur 63418<br>
                            Telp: (0352) 3574930 | Layanan: 0811-3558-555 | Pos-el: kanim_ponorogo@imigrasi.go.id | Laman: https://ponorogo.imigrasi.go.id/
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-300 text-center">
                    <h5 class="text-base sm:text-lg font-black uppercase tracking-wider text-slate-900">
                        REKAPITULASI LAPORAN REALISASI PENERIMAAN NEGARA BUKAN PAJAK (PNBP)
                    </h5>
                    <p class="text-xs text-slate-600 mt-1">
                        Waktu Cetak: {{ printedAt }} | Total Data: {{ realisasiList.length }} Periode Bulan
                    </p>

                    <!-- Kriteria Filter -->
                    <div v-if="filters.search || filters.tahun" class="mt-2 text-xs text-slate-600 flex flex-wrap justify-center gap-3">
                        <span v-if="filters.search">Kata Kunci: <strong>"{{ filters.search }}"</strong></span>
                        <span v-if="filters.tahun">Tahun Anggaran: <strong>{{ filters.tahun }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Tabel Cetak Data Realisasi PNBP -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-left w-36">Periode Bulan</th>
                        <th class="border border-slate-300 px-2 py-2 text-right w-36">Target (Rp)</th>
                        <th class="border border-slate-300 px-2 py-2 text-right w-36">Realisasi (Rp)</th>
                        <th class="border border-slate-300 px-2 py-2 text-right w-36">Selisih (Rp)</th>
                        <th class="border border-slate-300 px-2 py-2 text-center w-20">Capaian</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, idx) in realisasiList"
                        :key="item.id"
                        class="hover:bg-slate-50 odd:bg-white even:bg-slate-50/50"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-bold text-slate-900 whitespace-nowrap">
                            {{ formatIndoMonth(item.bulan) }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-right font-mono whitespace-nowrap">
                            {{ formatRupiah(item.target) }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-right font-mono font-semibold text-emerald-950 whitespace-nowrap">
                            {{ formatRupiah(item.realisasi) }}
                        </td>
                        <td
                            class="border border-slate-300 px-2 py-1.5 text-right font-mono font-semibold whitespace-nowrap"
                            :class="item.selisih >= 0 ? 'text-emerald-900' : 'text-amber-900'"
                        >
                            {{ (item.selisih >= 0 ? '+ ' : '') + formatRupiah(item.selisih) }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-bold whitespace-nowrap">
                            {{ item.persentase }}%
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-slate-700 leading-tight">
                            {{ item.keterangan || '-' }}
                        </td>
                    </tr>
                    <tr v-if="realisasiList.length === 0">
                        <td colspan="7" class="border border-slate-300 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada data rekapitulasi realisasi PNBP yang sesuai dengan kriteria cetak.
                        </td>
                    </tr>
                </tbody>
                <!-- Footer Baris Total -->
                <tfoot v-if="realisasiList.length > 0">
                    <tr class="bg-slate-100 font-bold border-t-2 border-slate-400">
                        <td colspan="2" class="border border-slate-300 px-2 py-2 text-center uppercase tracking-wider text-[11px]">
                            TOTAL KESELURUHAN
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-right font-mono text-xs">
                            {{ formatRupiah(summary.total_target) }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-right font-mono text-xs text-emerald-950">
                            {{ formatRupiah(summary.total_realisasi) }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-right font-mono text-xs">
                            {{ (summary.total_selisih >= 0 ? '+ ' : '') + formatRupiah(summary.total_selisih) }}
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-center font-mono text-xs">
                            {{ summary.persentase_total }}%
                        </td>
                        <td class="border border-slate-300 px-2 py-2 text-slate-600 text-[11px] italic">
                            Ringkasan akumulasi data
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Tanda Tangan / Pengesahan Cetak -->
            <div class="mt-12 flex justify-end text-xs text-slate-800 break-inside-avoid">
                <div class="w-64 text-center space-y-16">
                    <div>
                        <p>Dicetak pada: {{ printedAt }}</p>
                        <p class="font-semibold mt-1">Bendahara Penerimaan / Pengelola PNBP</p>
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
