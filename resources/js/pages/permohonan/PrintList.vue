<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { PermohonanItem } from '@/types/permohonan';

defineProps<{
    permohonanList: PermohonanItem[];
    filters: {
        search?: string;
        layanan?: string;
        kewarganegaraan?: string;
        kabupaten?: string;
        periode?: string;
    };
    printedAt: string;
}>();

const page = usePage();
const isUser = computed(() => (page.props.auth as any)?.user?.role === 'user');
const appIcon = computed(() => (((page.props.app_settings as any)?.app_icon as string | null | undefined) || undefined));
const instansiName = computed(() => ((page.props.app_settings as any)?.instansi_name as string | undefined) || 'Kantor Imigrasi Kelas II Non TPI Ponorogo');

function printReport() {
    window.print();
}

function formatIndoMonth(dateStr?: string | null): string {
    if (!dateStr) return '';
    try {
        const [year, month] = dateStr.split('-');
        if (!year || !month) return dateStr;
        const date = new Date(parseInt(year, 10), parseInt(month, 10) - 1, 1);
        return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(date);
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

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-foreground print:p-0 print:bg-white print:text-slate-900">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs text-foreground hover:text-foreground">
                <Link href="/permohonan">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Permohonan</span>
                </Link>
            </Button>

            <div class="flex items-center gap-3">
                <Button
                    size="sm"
                    class="gap-1.5 bg-zinc-900 hover:bg-zinc-800 text-zinc-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 shadow-xs shrink-0"
                    @click="printReport"
                >
                    <Printer class="size-4" />
                    <span>Print / Download PDF</span>
                </Button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="max-w-5xl mx-auto rounded-xl border border-slate-200 bg-white p-8 shadow-sm text-slate-900 print:shadow-none print:border-none print:p-2">
            <!-- Kop Dokumen Resmi (Disembunyikan Sementara) -->
            <div class="pb-4 border-b-2 border-slate-900 mb-6">
                <!-- Sembunyikan logo instansi dan kop surat untuk sementara -->
                <div v-if="false" class="flex items-center justify-center gap-4">
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

                <div class="text-center">
                    <h5 class="text-base sm:text-lg font-black uppercase tracking-wider text-slate-900">
                        REKAPITULASI LAPORAN PERMOHONAN KEIMIGRASIAN
                    </h5>
                    <p class="text-xs text-slate-600 mt-1">
                        Waktu Cetak: {{ printedAt }} | Total Data: {{ permohonanList.length }} Permohonan
                    </p>

                    <!-- Kriteria Filter & Pencarian -->
                    <div v-if="filters.search || filters.periode || filters.layanan || filters.kewarganegaraan || filters.kabupaten" class="mt-2.5 text-xs text-slate-700 flex flex-wrap items-center justify-center gap-x-3 gap-y-1">
                        <span v-if="filters.search">Pencarian: <strong>"{{ filters.search }}"</strong></span>
                        <span v-if="filters.periode">Periode: <strong>{{ formatIndoMonth(filters.periode) }}</strong></span>
                        <span v-if="filters.layanan">Layanan: <strong>{{ filters.layanan }}</strong></span>
                        <span v-if="filters.kewarganegaraan">Kewarganegaraan: <strong>{{ filters.kewarganegaraan }}</strong></span>
                        <span v-if="filters.kabupaten">Kabupaten: <strong>{{ filters.kabupaten }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Tabel Cetak Data Permohonan -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-center w-24">Tanggal</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Pemohon</th>
                        <th v-if="!isUser" class="border border-slate-300 px-2 py-2 text-left w-24">No. Paspor</th>
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
                        <td v-if="!isUser" class="border border-slate-300 px-2 py-1.5 font-mono text-[11px]">{{ item.no_paspor || '-' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-semibold text-indigo-950">{{ item.layanan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kewarganegaraan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kabupaten || '-' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-slate-700 leading-tight">{{ item.permohonan || '-' }}</td>
                    </tr>
                    <tr v-if="permohonanList.length === 0">
                        <td :colspan="isUser ? 7 : 8" class="border border-slate-300 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada data permohonan keimigrasian yang sesuai dengan kriteria cetak.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tanda Tangan / Pengesahan Cetak -->
            <div v-if="!isUser" class="mt-12 flex justify-end text-xs text-slate-800 break-inside-avoid">
                <div class="w-64 text-center space-y-16">
                    <div>
                        <p class="font-semibold">Petugas</p>
                    </div>
                    <div>
                        <div class="border-b border-slate-400 w-48 mx-auto" />
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
