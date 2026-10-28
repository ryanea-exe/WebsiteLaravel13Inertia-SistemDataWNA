<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { WnaItem } from '@/types/early-warning';

defineProps<{
    wnaList: (WnaItem & { urgensi?: string; sisa_hari?: number; urgensi_label?: string })[];
    filters: {
        search?: string;
        kategori?: string;
        jenis_izin_tinggal?: string;
        kewarganegaraan?: string;
    };
    printedAt: string;
}>();

const page = usePage();
const appIcon = computed(() => (page.props.app_settings as any)?.app_icon as string | null | undefined);
const instansiName = computed(() => ((page.props.app_settings as any)?.instansi_name as string | undefined) || 'Kantor Imigrasi Kelas II Non TPI Ponorogo');

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

function formatKategoriLabel(kategori?: string): string {
    switch (kategori) {
        case 'merah':
            return 'Sangat Mendesak (0-14 Hari)';
        case 'kuning':
            return 'Mendesak (15-30 Hari)';
        case 'biru':
            return 'Perhatian (>30 Hari - 3 Bulan)';
        case 'kedaluwarsa':
            return 'Sudah Kedaluwarsa (<0 Hari)';
        default:
            return 'Semua Peringatan Aktif';
    }
}
</script>

<template>
    <Head title="Cetak Laporan Early Warning Izin Tinggal WNA" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-foreground print:p-0 print:bg-white print:text-slate-900">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs text-foreground hover:text-foreground">
                <Link href="/early-warning">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Early Warning</span>
                </Link>
            </Button>

            <div class="flex items-center gap-2">
                <Button
                    size="sm"
                    class="gap-1.5 bg-zinc-900 hover:bg-zinc-800 text-zinc-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 shadow-xs"
                    @click="printReport"
                >
                    <Printer class="size-4" />
                    <span>Print / Download PDF</span>
                </Button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="max-w-5xl mx-auto rounded-xl border border-slate-200 bg-white p-8 shadow-sm text-slate-900 print:shadow-none print:border-none print:p-2">
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
                        LAPORAN EARLY WARNING MASA BERLAKU IZIN TINGGAL WNA
                    </h5>
                    <p class="text-xs text-slate-600 mt-1">
                        Waktu Cetak: {{ printedAt }} | Total Data: {{ wnaList.length }} Orang
                    </p>

                    <!-- Kriteria Filter -->
                    <div v-if="filters.search || (filters.kategori && filters.kategori !== 'semua') || filters.jenis_izin_tinggal || filters.kewarganegaraan" class="mt-2 text-xs text-slate-600 flex flex-wrap justify-center gap-3">
                        <span v-if="filters.search">Pencarian: <strong>"{{ filters.search }}"</strong></span>
                        <span v-if="filters.kategori && filters.kategori !== 'semua'">Kategori: <strong>{{ formatKategoriLabel(filters.kategori) }}</strong></span>
                        <span v-if="filters.jenis_izin_tinggal">Izin Tinggal: <strong>{{ filters.jenis_izin_tinggal }}</strong></span>
                        <span v-if="filters.kewarganegaraan">Kewarganegaraan: <strong>{{ filters.kewarganegaraan }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Tabel Cetak Data Early Warning -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Lengkap & No. Register</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">No. Paspor</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Kewarganegaraan</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Jenis Izin</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Masa Berlaku</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Sisa Waktu</th>
                        <th class="border border-slate-300 px-2 py-2 text-center">Status Peringatan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="wnaList.length === 0">
                        <td colspan="8" class="border border-slate-300 px-4 py-8 text-center text-slate-500">
                            Tidak ada data WNA dalam periode early warning yang sesuai dengan parameter laporan ini.
                        </td>
                    </tr>
                    <tr
                        v-for="(item, index) in wnaList"
                        :key="item.id"
                        class="border-b border-slate-200 even:bg-slate-50/50"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ index + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            <div class="font-semibold text-slate-900">{{ item.nama }}</div>
                            <div class="text-[10px] font-mono text-slate-600">{{ item.no_register }}</div>
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono">{{ item.no_paspor }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kewarganegaraan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">{{ item.jenis_izin_tinggal }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 whitespace-nowrap">{{ formatIndoDate(item.masa_berlaku) }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-semibold">
                            <span v-if="(item.sisa_hari ?? 0) < 0" class="text-rose-700">
                                Lewat {{ Math.abs(item.sisa_hari ?? 0) }} hari
                            </span>
                            <span v-else-if="item.sisa_hari === 0" class="text-red-700">
                                Berakhir hari ini
                            </span>
                            <span v-else class="text-slate-800">
                                {{ item.sisa_hari }} hari lagi
                            </span>
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">
                            <span
                                class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                :class="{
                                    'bg-rose-100 text-rose-800 border border-rose-300': item.urgensi === 'kedaluwarsa',
                                    'bg-red-100 text-red-800 border border-red-300': item.urgensi === 'merah',
                                    'bg-amber-100 text-amber-800 border border-amber-300': item.urgensi === 'kuning',
                                    'bg-blue-100 text-blue-800 border border-blue-300': item.urgensi === 'biru',
                                }"
                            >
                                {{ item.urgensi_label || item.urgensi }}
                            </span>
                        </td>
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
