<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { AbgEarlyWarningItem, EarlyWarningAbgCounts } from '@/types/early-warning-abg';

defineProps<{
    abg: (AbgEarlyWarningItem & {
        target_milestone?: string;
        sisa_hari?: number;
        status_label?: string;
        sisa_waktu_label?: string;
        umur?: string;
    })[];
    filters: {
        search?: string;
        kategori?: string;
        jenis_kelamin?: string;
        kabupaten?: string;
        kewarganegaraan?: string;
    };
    counts: EarlyWarningAbgCounts;
    printDate: string;
}>();

const page = usePage();
const appIcon = computed(() => (((page.props.app_settings as any)?.app_icon as string | null | undefined) || undefined));
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

function formatKategoriLabel(kategori?: string): string {
    switch (kategori) {
        case 'kurang_18':
            return 'Menjelang 18 Tahun (17,5 - 18 Th)';
        case 'kurang_21':
            return 'Menjelang 21 Tahun (Batas Akhir / 20,5 - 21 Th)';
        case 'lewat_21':
            return 'Lewat 21 Tahun (Kedaluwarsa)';
        default:
            return 'Semua Kategori Early Warning ABG';
    }
}

function getUsia(item: any): string {
    if (item.umur && item.umur !== '-') {
        return item.umur;
    }
    if (!item.tanggal_lahir) return '-';
    try {
        const today = new Date();
        const dob = new Date(item.tanggal_lahir);
        let years = today.getFullYear() - dob.getFullYear();
        const birthdayThisYear = new Date(dob);
        birthdayThisYear.setFullYear(dob.getFullYear() + years);
        if (today < birthdayThisYear) {
            years--;
            birthdayThisYear.setFullYear(dob.getFullYear() + years);
        }
        const diffTime = today.getTime() - birthdayThisYear.getTime();
        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        return `${years} tahun ${days} hari`;
    } catch {
        return '-';
    }
}
</script>

<template>
    <Head title="Cetak Laporan Early Warning Data ABG" />

    <div class="min-h-screen bg-muted/30 p-4 md:p-8 font-sans text-foreground print:p-0 print:bg-white print:text-slate-900">
        <!-- Control Header on Screen (Hidden when printing) -->
        <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs text-foreground hover:text-foreground cursor-pointer">
                <Link href="/early-warning/abg">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Early Warning ABG</span>
                </Link>
            </Button>

            <div class="flex items-center gap-2">
                <Button
                    size="sm"
                    class="gap-1.5 bg-zinc-900 hover:bg-zinc-800 text-zinc-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 shadow-xs cursor-pointer"
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
                        <h3 class="text-xs sm:text-xs font-semibold uppercase tracking-wider text-slate-800 leading-tight">
                            {{ instansiName }}
                        </h3>
                    </div>
                </div>

                <!-- Judul Laporan -->
                <div class="text-center">
                    <h2 class="text-base sm:text-lg font-bold tracking-tight uppercase text-slate-900">
                        LAPORAN PERINGATAN DINI (EARLY WARNING) DATA ABG
                    </h2>
                    <p class="text-xs text-slate-600 mt-1">
                        Kategori: <span class="font-semibold text-slate-800">{{ formatKategoriLabel(filters.kategori) }}</span>
                        <span v-if="filters.kabupaten"> • Wilayah: <span class="font-semibold text-slate-800">Kab. {{ filters.kabupaten }}</span></span>
                        <span v-if="filters.kewarganegaraan"> • Kewarganegaraan: <span class="font-semibold text-slate-800">{{ filters.kewarganegaraan }}</span></span>
                    </p>
                </div>
            </div>

            <!-- Tabel Data Cetak -->
            <table class="w-full text-left text-xs border-collapse border border-slate-300">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 uppercase font-bold text-[10px] tracking-wider">
                        <th class="border border-slate-300 px-2 py-1.5 text-center w-8">No</th>
                        <th class="border border-slate-300 px-2 py-1.5 w-24">No. Register</th>
                        <th class="border border-slate-300 px-2 py-1.5">Nama Lengkap</th>
                        <th class="border border-slate-300 px-2 py-1.5">TTL & Usia</th>
                        <th class="border border-slate-300 px-2 py-1.5 text-center w-10">L/P</th>
                        <th class="border border-slate-300 px-2 py-1.5">Kewarganegaraan</th>
                        <th class="border border-slate-300 px-2 py-1.5">Batas Usia</th>
                        <th class="border border-slate-300 px-2 py-1.5">Status & Sisa Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="abg.length === 0">
                        <td colspan="8" class="border border-slate-300 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada data ABG yang sesuai dengan filter early warning.
                        </td>
                    </tr>
                    <tr
                        v-for="(item, index) in abg"
                        :key="item.id"
                        class="border-b border-slate-200"
                    >
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-mono">
                            {{ index + 1 }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono text-[11px]">
                            {{ item.no_register }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            <span class="font-semibold text-slate-900 block">{{ item.nama }}</span>
                            <span class="text-[10px] text-slate-600 block">
                                Orang Tua: {{ item.nama_ayah || '-' }} / {{ item.nama_ibu || '-' }}
                            </span>
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            <span>{{ item.tempat_lahir ? `${item.tempat_lahir}, ` : '' }}{{ formatIndoDate(item.tanggal_lahir) }}</span>
                            <span class="block text-[10px] text-slate-600 font-mono">Usia: {{ getUsia(item) }}</span>
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">
                            {{ item.jenis_kelamin }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            {{ item.kewarganegaraan }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono text-[11px]">
                            <span>{{ formatIndoDate(item.target_milestone) }}</span>
                            <span class="block text-[10px] text-slate-500">
                                {{ item.urgensi === 'kurang_18' ? 'Genap 18 Th' : 'Genap 21 Th' }}
                            </span>
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            <span class="font-semibold block" :class="{
                                'text-amber-800': item.urgensi === 'kurang_18',
                                'text-orange-800': item.urgensi === 'kurang_21',
                                'text-rose-800': item.urgensi === 'lewat_21',
                            }">
                                {{ item.status_label }}
                            </span>
                            <span class="text-[10px] text-slate-600 font-mono block">
                                {{ item.sisa_waktu_label }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Area Tanda Tangan Dokumen -->
            <div class="mt-8 pt-4 flex justify-end">
                <div class="text-center text-xs w-60 space-y-16">
                    <div>
                        <p class="font-medium">Petugas</p>
                    </div>
                    <div>
                        <div class="border-b border-slate-800 w-44 mx-auto mb-1" />
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

