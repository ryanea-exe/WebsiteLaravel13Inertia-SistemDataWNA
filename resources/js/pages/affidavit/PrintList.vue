<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { AffidavitItem } from '@/types/affidavit';

defineProps<{
    affidavitList: AffidavitItem[];
    filters: {
        search?: string;
        jenis_kelamin?: string;
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
    <Head title="Cetak Laporan Pendaftaran Affidavit" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-slate-900 print:p-0 print:bg-white">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-6xl mx-auto mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                <Link href="/affidavit">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Pendaftaran Affidavit</span>
                </Link>
            </Button>

            <div class="flex items-center gap-2">
                <Button
                    size="sm"
                    class="gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs"
                    @click="printReport"
                >
                    <Printer class="size-4" />
                    <span>Print / Download PDF</span>
                </Button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="max-w-6xl mx-auto rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:shadow-none print:border-none print:p-2">
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
                        LAPORAN PENDAFTARAN AFFIDAVIT
                    </h5>
                    <p class="text-xs text-slate-600 mt-1">
                        Waktu Cetak: {{ printedAt }} | Total Data: {{ affidavitList.length }} Rekaman
                    </p>

                    <!-- Kriteria Filter -->
                    <div v-if="filters.search || filters.jenis_kelamin || filters.kewarganegaraan" class="mt-2 text-xs text-slate-600 flex flex-wrap justify-center gap-3">
                        <span v-if="filters.search">Pencarian: <strong>"{{ filters.search }}"</strong></span>
                        <span v-if="filters.jenis_kelamin">Jenis Kelamin: <strong>{{ filters.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong></span>
                        <span v-if="filters.kewarganegaraan">Kewarganegaraan: <strong>{{ filters.kewarganegaraan }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Tabel Cetak Data Affidavit -->
            <table class="w-full text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="border border-slate-300 px-2 py-2 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">No. Register</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">No. Paspor</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Lengkap</th>
                        <th class="border border-slate-300 px-2 py-2 text-center w-10">L/P</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Kewarganegaraan</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Masa Berlaku</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Tempat, Tgl Lahir</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">Nama Orang Tua</th>
                        <th class="border border-slate-300 px-2 py-2 text-left">No. Telepon</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, idx) in affidavitList"
                        :key="item.id"
                        class="hover:bg-slate-50 odd:bg-white even:bg-slate-50/50"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ idx + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono text-[11px] font-semibold">{{ item.no_register }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono text-[11px]">{{ item.no_paspor }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-medium">{{ item.nama }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ item.jenis_kelamin }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.kewarganegaraan }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono">{{ formatIndoDate(item.masa_berlaku) }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.tempat_lahir || '-' }}, {{ formatIndoDate(item.tanggal_lahir) }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            <div>A: {{ item.nama_ayah || '-' }}</div>
                            <div>I: {{ item.nama_ibu || '-' }}</div>
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ item.nomor_telepon || '-' }}</td>
                    </tr>
                    <tr v-if="affidavitList.length === 0">
                        <td colspan="10" class="border border-slate-300 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada data Pendaftaran Affidavit yang sesuai dengan kriteria cetak.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tanda Tangan / Pengesahan Cetak -->
            <div class="mt-12 flex justify-end text-xs text-slate-800 break-inside-avoid">
                <div class="w-64 text-center space-y-16">
                    <div>
                        <p>Dicetak pada: {{ printedAt }}</p>
                        <p class="font-semibold mt-1">Petugas Seksi Intelijen & Penindakan</p>
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
        margin: 1cm;
    }

    body {
        background-color: white !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
