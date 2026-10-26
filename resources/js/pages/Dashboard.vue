<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertTriangle,
    ArrowRight,
    Award,
    Clock,
    FileCheck,
    FileSpreadsheet,
    FileText,
    Globe,
    Layers,
    MapPin,
    PieChart as PieIcon,
    Receipt,
    Shield,
    TrendingUp,
    UserCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import PnbpChartCard from '@/components/PnbpChartCard.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { User } from '@/types';

defineOptions({
    layout: () => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard().url,
            },
        ],
    }),
});

interface PermitDistributionItem {
    jenis: string;
    label: string;
    total: number;
    persen: number;
}

interface RegionPieChart {
    title: string;
    kabupaten: string;
    total: number;
    items: PermitDistributionItem[];
}

interface PieIzinTinggalMap {
    semua: RegionPieChart;
    ponorogo: RegionPieChart;
    pacitan: RegionPieChart;
    trenggalek: RegionPieChart;
}

interface TopKewarganegaraan {
    negara: string;
    total: number;
    persen: number;
}

interface MonthlySeriesItem {
    bulan: number;
    label: string;
    total: number;
}

interface TrenLaporanBulananItem {
    bulan: number;
    label: string;
    total: number;
    disetujui: number;
    ditolak: number;
    volume: number;
}

interface MonthlyPnbp {
    bulan: string;
    target: number;
    realisasi: number;
    persentase: number;
}

interface PnbpLineItem {
    bulan: number;
    label: string;
    target: number;
    realisasi: number;
    persentase: number;
    selisih: number;
    has_data: boolean;
}

interface DashboardProps {
    stats: {
        total_wna: number;
        total_abg: number;
        total_affidavit: number;
        early_warning: {
            semua: number;
            merah: number;
            kuning: number;
            biru: number;
            kedaluwarsa: number;
        };
        total_permohonan?: number;
        total_laporan?: number;
        total_penerbitan?: number;
        pnbp?: {
            tahun: number;
            total_target: number;
            total_realisasi: number;
            selisih: number;
            persentase: number;
            bulanan: MonthlyPnbp[];
            line_series?: PnbpLineItem[];
        };
        admin?: {
            total_permohonan: number;
            total_penerbitan: number;
            total_laporan?: number;
            total_users: number;
            pnbp: {
                tahun: number;
                total_target: number;
                total_realisasi: number;
                selisih: number;
                persentase: number;
                bulanan: MonthlyPnbp[];
                line_series?: PnbpLineItem[];
            };
        } | null;
    };
    charts: {
        distribusi_izin: PermitDistributionItem[];
        pie_izin_tinggal?: PieIzinTinggalMap;
        top_kewarganegaraan: TopKewarganegaraan[];
        tren_wna_bulanan: MonthlySeriesItem[];
        tren_permohonan_bulanan?: MonthlySeriesItem[];
        total_permohonan_tahun_ini?: number;
        tren_laporan_bulanan?: TrenLaporanBulananItem[];
        total_laporan_tahun_ini?: number;
        tahun_berjalan: number;
    };
}

const props = defineProps<DashboardProps>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as User | undefined);
const isAdmin = computed(() => currentUser.value?.role === 'admin');

function formatNumber(num: number): string {
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatRupiah(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

function formatShortRupiah(value: number): string {
    if (value >= 1_000_000_000) {
        return `Rp ${(value / 1_000_000_000).toFixed(1)} M`;
    }
    if (value >= 1_000_000) {
        return `Rp ${(value / 1_000_000).toFixed(0)} Jt`;
    }
    return formatRupiah(value);
}

// -----------------------------------------------------------------------------
// CR-003: 4 Pie/Donut Charts Helpers
// -----------------------------------------------------------------------------
const regionKeyList = ['semua', 'ponorogo', 'pacitan', 'trenggalek'] as const;

const colorScheme: Record<string, { stroke: string; bg: string; text: string }> = {
    ITAS: {
        stroke: '#4f46e5', // indigo-600
        bg: 'bg-indigo-600 dark:bg-indigo-500',
        text: 'text-indigo-600 dark:text-indigo-400',
    },
    ITAP: {
        stroke: '#059669', // emerald-600
        bg: 'bg-emerald-600 dark:bg-emerald-500',
        text: 'text-emerald-600 dark:text-emerald-400',
    },
    VOA: {
        stroke: '#d97706', // amber-600
        bg: 'bg-amber-500 dark:bg-amber-400',
        text: 'text-amber-600 dark:text-amber-400',
    },
    ITK: {
        stroke: '#0284c7', // sky-600
        bg: 'bg-sky-500 dark:bg-sky-400',
        text: 'text-sky-600 dark:text-sky-400',
    },
};

const donutRadius = 38;
const donutCircumference = 2 * Math.PI * donutRadius; // ~238.761

function calculateDonutSlices(items: PermitDistributionItem[], total: number) {
    let accumulatedOffset = 0;
    return items.map((item) => {
        const itemPercent = total > 0 ? (item.total / total) * 100 : 0;
        const strokeLength = (itemPercent / 100) * donutCircumference;
        const strokeOffset = -accumulatedOffset;
        accumulatedOffset += strokeLength;

        return {
            ...item,
            strokeLength,
            strokeOffset,
            color: colorScheme[item.jenis]?.stroke ?? '#94a3b8',
        };
    });
}

const fourPies = computed(() => {
    const pieMap = props.charts.pie_izin_tinggal;
    return regionKeyList.map((key) => {
        const data = pieMap?.[key] ?? {
            title: key === 'semua' ? 'Seluruh Wilayah Kerja' : `Kabupaten ${key.charAt(0).toUpperCase() + key.slice(1)}`,
            kabupaten: key,
            total: key === 'semua' ? props.stats.total_wna : 0,
            items: key === 'semua' ? props.charts.distribusi_izin : [],
        };

        return {
            key,
            title: data.title,
            kabupaten: data.kabupaten,
            total: data.total,
            items: data.items,
            slices: calculateDonutSlices(data.items, data.total),
        };
    });
});

// -----------------------------------------------------------------------------
// CR-006: Monthly Permohonan Trend Helpers
// -----------------------------------------------------------------------------
const trenPermohonan = computed<MonthlySeriesItem[]>(() => {
    return props.charts.tren_permohonan_bulanan ?? props.charts.tren_wna_bulanan ?? [];
});

const totalPermohonanInYear = computed<number>(() => {
    if (props.charts.total_permohonan_tahun_ini !== undefined) {
        return props.charts.total_permohonan_tahun_ini;
    }
    return trenPermohonan.value.reduce((acc, curr) => acc + curr.total, 0);
});

const maxPermohonanMonthly = computed<number>(() => {
    const max = Math.max(...trenPermohonan.value.map((b) => b.total), 0);
    return max > 0 ? max : 1;
});

// -----------------------------------------------------------------------------
// CR-007: Monthly Laporan Izin Tinggal Trend Helpers
// -----------------------------------------------------------------------------
const trenLaporan = computed<TrenLaporanBulananItem[]>(() => {
    return props.charts.tren_laporan_bulanan ?? [];
});

const totalLaporanInYear = computed<number>(() => {
    if (props.charts.total_laporan_tahun_ini !== undefined) {
        return props.charts.total_laporan_tahun_ini;
    }
    return trenLaporan.value.reduce((acc, curr) => acc + curr.total, 0);
});

const totalLaporanVolumeInYear = computed<number>(() => {
    return trenLaporan.value.reduce((acc, curr) => acc + (curr.volume || 0), 0);
});

const maxLaporanVolume = computed<number>(() => {
    const max = Math.max(...trenLaporan.value.map((b) => b.volume || b.total), 0);
    return max > 0 ? max : 1;
});

const totalPermohonan = computed(() => props.stats.total_permohonan ?? props.stats.admin?.total_permohonan ?? 0);
const totalLaporan = computed(() => props.stats.total_laporan ?? props.stats.admin?.total_laporan ?? 0);
const totalPenerbitan = computed(() => props.stats.total_penerbitan ?? props.stats.admin?.total_penerbitan ?? 0);

const pnbpData = computed(() => {
    return (
        props.stats.pnbp ??
        props.stats.admin?.pnbp ?? {
            tahun: props.charts.tahun_berjalan,
            total_target: 0,
            total_realisasi: 0,
            selisih: 0,
            persentase: 0,
            bulanan: [],
            line_series: [],
        }
    );
});
</script>

<template>
    <Head title="Dashboard Operasional" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <!-- Header Halaman -->
        <PageHeader
            title="Dashboard Operasional"
            description="Ringkasan analitik data WNA, pendaftaran ABG & affidavit, pemantauan masa berlaku, serta rekapitulasi layanan keimigrasian."
        >
            <template #actions>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-foreground shadow-xs">
                    <Shield class="size-3.5" :class="isAdmin ? 'text-indigo-600 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400'" />
                    <span>Mode Akses:</span>
                    <span
                        class="font-semibold"
                        :class="isAdmin ? 'text-indigo-600 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400'"
                    >
                        {{ isAdmin ? 'Administrator Sistem' : 'Petugas Operasional' }}
                    </span>
                </div>
            </template>
        </PageHeader>

        <!-- Baris 1: Kartu Statistik Operasional -->
        <div class="grid gap-4 sm:grid-cols-2" :class="isAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3'">
            <StatCard
                title="Total Data WNA"
                :icon="Users"
                :value="formatNumber(stats.total_wna)"
                description="Basis data Warga Negara Asing pemegang izin tinggal ITAS, ITAP, VOA, dan ITK aktif."
                variant="primary"
            />
            <StatCard
                title="Pendaftaran ABG"
                :icon="UserCheck"
                :value="formatNumber(stats.total_abg)"
                description="Registrasi Anak Berkewarganegaraan Ganda sesuai ketentuan perundang-undangan."
                variant="default"
            />
            <StatCard
                title="Pendaftaran Affidavit"
                :icon="FileCheck"
                :value="formatNumber(stats.total_affidavit)"
                description="Penerbitan surat keimigrasian fasilitas affidavit kewarganegaraan ganda."
                variant="default"
            />
            <StatCard
                v-if="isAdmin"
                title="Early Warning Aktif"
                :icon="AlertTriangle"
                :value="formatNumber(stats.early_warning.semua)"
                :description="`${formatNumber(stats.early_warning.merah)} Sangat Mendesak, ${formatNumber(stats.early_warning.kedaluwarsa)} Kedaluwarsa.`"
                variant="warning"
            />
        </div>

        <!-- Baris 2: Kartu Statistik Layanan & PNBP (Tersedia untuk Admin & Petugas) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Permohonan Keimigrasian"
                :icon="FileText"
                :value="formatNumber(totalPermohonan)"
                description="Total permohonan layanan keimigrasian tercatat dalam sistem."
                variant="default"
            />
            <StatCard
                title="Laporan Izin Tinggal"
                :icon="FileSpreadsheet"
                :value="formatNumber(totalLaporan)"
                description="Total rekapitulasi berkas laporan izin tinggal tercatat dalam sistem."
                variant="default"
            />
            <StatCard
                title="Penerbitan Dokumen"
                :icon="Award"
                :value="formatNumber(totalPenerbitan)"
                description="Total dokumen dan izin keimigrasian yang telah diterbitkan."
                variant="default"
            />
            <StatCard
                title="Realisasi PNBP"
                :icon="Receipt"
                :value="`${pnbpData.persentase}%`"
                :description="`${formatRupiah(pnbpData.total_realisasi)} dari target ${formatRupiah(pnbpData.total_target)}`"
                variant="success"
            />
        </div>

        <!-- CR-003: EMPAT PIE CHART DISTRIBUSI IZIN TINGGAL (LEBAR PENUH DARI KIRI KE KANAN) -->
        <div class="space-y-3 w-full">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-2">
                <div class="flex items-center gap-2">
                    <PieIcon class="size-4 text-primary" />
                    <h3 class="text-sm font-semibold tracking-tight text-foreground">
                        Distribusi Izin Tinggal Berdasarkan Wilayah Kerja
                    </h3>
                </div>
                <p class="text-xs text-muted-foreground">
                    Perincian pemegang izin tinggal ITAS, ITAP, VOA, dan ITK pada seluruh wilayah dan tiap kabupaten.
                </p>
            </div>

            <!-- 4 Kolom Responsive Pie Chart (Lebar Penuh) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                <Card
                    v-for="region in fourPies"
                    :key="region.key"
                    class="border-border/80 shadow-xs flex flex-col justify-between"
                >
                    <CardHeader class="border-b border-border/60">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-xs font-bold tracking-tight text-foreground flex items-center gap-1.5 truncate">
                                <MapPin class="size-3.5 text-primary shrink-0" />
                                <span class="truncate">{{ region.title }}</span>
                            </CardTitle>
                            <span class="rounded-full bg-primary/10 text-primary px-2 py-0.5 text-[11px] font-semibold font-mono shrink-0">
                                {{ formatNumber(region.total) }} WNA
                            </span>
                        </div>
                    </CardHeader>

                    <CardContent class="pt-4 flex-1 flex flex-col items-center justify-between space-y-4">
                        <!-- SVG Donut Chart Visualizer -->
                        <div class="relative size-36 flex items-center justify-center">
                            <svg class="size-full -rotate-90" viewBox="0 0 100 100" role="img" :aria-label="`Grafik donat distribusi izin ${region.title}`">
                                <!-- Background Track Ring -->
                                <circle
                                    cx="50"
                                    cy="50"
                                    :r="donutRadius"
                                    fill="transparent"
                                    stroke="currentColor"
                                    stroke-width="12"
                                    class="text-muted/30"
                                />

                                <!-- Data Segments -->
                                <template v-if="region.total > 0">
                                    <circle
                                        v-for="slice in region.slices"
                                        :key="slice.jenis"
                                        cx="50"
                                        cy="50"
                                        :r="donutRadius"
                                        fill="transparent"
                                        :stroke="slice.color"
                                        stroke-width="12"
                                        :stroke-dasharray="`${slice.strokeLength} ${donutCircumference - slice.strokeLength}`"
                                        :stroke-dashoffset="slice.strokeOffset"
                                        class="transition-all duration-300 hover:opacity-85"
                                    >
                                        <title>{{ slice.jenis }}: {{ formatNumber(slice.total) }} ({{ slice.persen }}%)</title>
                                    </circle>
                                </template>
                            </svg>

                            <!-- Center Total Display -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                <span class="text-base font-extrabold font-mono text-foreground leading-tight">
                                    {{ formatNumber(region.total) }}
                                </span>
                                <span class="text-[9px] font-medium uppercase tracking-wider text-muted-foreground">
                                    {{ region.total > 0 ? 'WNA' : 'Kosong' }}
                                </span>
                            </div>
                        </div>

                        <!-- Legend Keterangan di Bawah Chart -->
                        <div class="w-full grid grid-cols-2 gap-1.5 pt-1">
                            <div
                                v-for="item in region.items"
                                :key="item.jenis"
                                class="flex items-center justify-between text-[10px] px-1.5 py-1 rounded-md bg-muted/20 border border-border/40 hover:bg-muted/40 transition-colors"
                            >
                                <div class="flex items-center gap-1 min-w-0 shrink-0">
                                    <span
                                        class="size-1.5 rounded-full shrink-0"
                                        :class="colorScheme[item.jenis]?.bg ?? 'bg-muted-foreground'"
                                    ></span>
                                    <span class="font-bold text-foreground text-[10px] leading-tight whitespace-nowrap">{{ item.jenis }}</span>
                                </div>
                                <div class="flex items-baseline gap-0.5 font-mono shrink-0 pl-1">
                                    <span class="font-bold text-foreground text-[10px]">{{ formatNumber(item.total) }}</span>
                                    <span class="text-[9px] text-muted-foreground whitespace-nowrap">({{ item.persen }}%)</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Baris 3: Visualisasi Grafik Bulanan (CR-006 & CR-007) -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- CR-006: Grafik Permohonan Bulanan (Menggantikan Tren Pendaftaran WNA) -->
            <Card class="border-border/80 shadow-xs flex flex-col justify-between">
                <CardHeader class="pb-3 border-b border-border/60">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                                <TrendingUp class="size-4 text-primary" />
                                <span>Grafik Permohonan Bulanan (Tahun {{ charts.tahun_berjalan }})</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                Jumlah permohonan layanan keimigrasian per bulan berdasarkan tanggal permohonan.
                            </CardDescription>
                        </div>
                        <span class="rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-semibold font-mono">
                            Total: {{ formatNumber(totalPermohonanInYear) }} Permohonan
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="pt-5 flex-1 flex flex-col justify-end">
                    <!-- Bar Chart Visualizer -->
                    <div v-if="totalPermohonanInYear > 0" class="flex items-end justify-between gap-1.5 sm:gap-2 h-44 pt-6 pb-2 px-1 border-b border-border/60">
                        <div
                            v-for="b in trenPermohonan"
                            :key="b.bulan"
                            class="flex-1 flex flex-col items-center gap-1.5 group relative"
                        >
                            <!-- Tooltip popup on hover -->
                            <div class="absolute -top-8 hidden group-hover:flex z-10 whitespace-nowrap rounded bg-foreground px-2 py-0.5 text-[10px] font-semibold text-background shadow-xs">
                                {{ b.label }}: {{ formatNumber(b.total) }} permohonan
                            </div>
                            <!-- Bar column -->
                            <div class="w-full flex items-end justify-center h-32">
                                <div
                                    class="w-full max-w-[28px] rounded-t-sm transition-all duration-300"
                                    :class="b.total > 0 ? 'bg-primary hover:bg-primary/90' : 'bg-muted/40'"
                                    :style="{
                                        height: `${Math.max(Math.round((b.total / maxPermohonanMonthly) * 100), 6)}%`,
                                    }"
                                ></div>
                            </div>
                            <!-- Month Label -->
                            <span class="text-[10px] sm:text-xs font-medium text-muted-foreground group-hover:text-foreground">
                                {{ b.label }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State for Monthly Permohonan Trend -->
                    <div v-else class="flex min-h-[180px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-6 text-center">
                        <TrendingUp class="size-8 text-muted-foreground/60" />
                        <h4 class="mt-2 text-xs font-semibold text-foreground">
                            Belum Ada Permohonan di Tahun {{ charts.tahun_berjalan }}
                        </h4>
                        <p class="mt-1 text-[11px] text-muted-foreground max-w-xs">
                            Data grafik akan terbentuk secara otomatis berdasarkan tanggal permohonan yang dimasukkan ke sistem.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- CR-007: Grafik Laporan Izin Tinggal Bulanan (Sumber: laporan_izin_tinggal.bulan) -->
            <Card class="border-border/80 shadow-xs flex flex-col justify-between">
                <CardHeader class="pb-3 border-b border-border/60">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                                <Layers class="size-4 text-emerald-600 dark:text-emerald-400" />
                                <span>Grafik Laporan Izin Tinggal Bulanan (Tahun {{ charts.tahun_berjalan }})</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                Rekapitulasi laporan izin tinggal berkala per bulan berdasarkan kolom bulan kalender resmi.
                            </CardDescription>
                        </div>
                        <span class="rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 px-2.5 py-0.5 text-xs font-semibold font-mono">
                            Total: {{ formatNumber(totalLaporanInYear) }} Laporan
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="pt-5 flex-1 flex flex-col justify-end">
                    <!-- Bar Chart Visualizer -->
                    <div v-if="totalLaporanInYear > 0" class="flex items-end justify-between gap-1.5 sm:gap-2 h-44 pt-6 pb-2 px-1 border-b border-border/60">
                        <div
                            v-for="b in trenLaporan"
                            :key="b.bulan"
                            class="flex-1 flex flex-col items-center gap-1.5 group relative"
                        >
                            <!-- Tooltip popup on hover -->
                            <div class="absolute -top-10 hidden group-hover:flex z-10 whitespace-nowrap rounded bg-foreground px-2.5 py-1 text-[10px] font-semibold text-background shadow-xs flex-col items-center">
                                <span>{{ b.label }}: {{ b.total > 0 ? `${formatNumber(b.total)} laporan (${formatNumber(b.volume)} diproses)` : 'Tidak ada laporan' }}</span>
                                <span v-if="b.total > 0" class="text-[9px] text-background/80">
                                    Disetujui: {{ formatNumber(b.disetujui) }} | Ditolak: {{ formatNumber(b.ditolak) }}
                                </span>
                            </div>
                            <!-- Bar column -->
                            <div class="w-full flex items-end justify-center h-32">
                                <div
                                    class="w-full max-w-[28px] rounded-t-sm transition-all duration-300"
                                    :class="b.total > 0 ? 'bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-500' : 'bg-muted/40'"
                                    :style="{
                                        height: `${Math.max(Math.round(((b.volume || b.total) / maxLaporanVolume) * 100), b.total > 0 ? 15 : 6)}%`,
                                    }"
                                ></div>
                            </div>
                            <!-- Month Label -->
                            <span class="text-[10px] sm:text-xs font-medium text-muted-foreground group-hover:text-foreground">
                                {{ b.label }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State for Monthly Laporan Trend -->
                    <div v-else class="flex min-h-[180px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-6 text-center">
                        <Layers class="size-8 text-muted-foreground/60" />
                        <h4 class="mt-2 text-xs font-semibold text-foreground">
                            Belum Ada Laporan Izin Tinggal di Tahun {{ charts.tahun_berjalan }}
                        </h4>
                        <p class="mt-1 text-[11px] text-muted-foreground max-w-xs">
                            Grafik akan terisi otomatis saat laporan bulanan izin tinggal diinput ke modul Laporan Izin Tinggal.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Baris 4: Top Kewarganegaraan & Pemantauan Masa Berlaku (Khusus Admin) -->
        <div class="grid gap-4" :class="isAdmin ? 'lg:grid-cols-2' : ''">
            <!-- Card: Top 5 Kewarganegaraan -->
            <Card class="border-border/80 shadow-xs">
                <CardHeader class="pb-3 border-b border-border/60">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                                <Globe class="size-4 text-primary" />
                                <span>Kewarganegaraan Terbanyak</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                5 negara asal Warga Negara Asing dengan populasi terbesar.
                            </CardDescription>
                        </div>
                        <Button variant="ghost" size="sm" as-child class="h-7 text-xs gap-1 text-primary">
                            <Link href="/wna">
                                <span>Lihat Semua</span>
                                <ArrowRight class="size-3" />
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="pt-4 space-y-3">
                    <div v-if="charts.top_kewarganegaraan.length > 0" class="space-y-3">
                        <div
                            v-for="(item, idx) in charts.top_kewarganegaraan"
                            :key="item.negara"
                            class="space-y-1.5"
                        >
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="flex size-5 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-foreground">
                                        {{ idx + 1 }}
                                    </span>
                                    <span class="font-medium text-foreground">{{ item.negara }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-semibold text-foreground">{{ formatNumber(item.total) }}</span>
                                    <span class="text-muted-foreground text-[11px]">({{ item.persen }}%)</span>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="h-2 w-full rounded-full bg-muted/60 overflow-hidden">
                                <div
                                    class="h-full bg-primary rounded-full transition-all duration-300"
                                    :style="{ width: `${item.persen}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State for Top Nationalities -->
                    <div v-else class="flex min-h-[140px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-4 text-center">
                        <Globe class="size-6 text-muted-foreground/60" />
                        <h4 class="mt-2 text-xs font-semibold text-foreground">
                            Belum Ada Data Kewarganegaraan
                        </h4>
                        <p class="mt-1 text-[11px] text-muted-foreground">
                            Data akan diperbarui setelah pendaftaran WNA dimasukkan ke sistem.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Card: Ringkasan Status Early Warning Izin Tinggal (Khusus Admin) -->
            <Card v-if="isAdmin" class="border-border/80 shadow-xs">
                <CardHeader class="pb-3 border-b border-border/60">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                                <Clock class="size-4 text-amber-500" />
                                <span>Pemantauan Masa Berlaku Izin Tinggal</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                Klasifikasi WNA berdasarkan sisa hari masa berlaku izin keimigrasian.
                            </CardDescription>
                        </div>
                        <Button
                            v-if="isAdmin"
                            variant="ghost"
                            size="sm"
                            as-child
                            class="h-7 text-xs gap-1 text-primary"
                        >
                            <Link href="/early-warning">
                                <span>Buka Early Warning</span>
                                <ArrowRight class="size-3" />
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="pt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2.5">
                        <!-- Sangat Mendesak (Merah) -->
                        <div class="rounded-lg border border-red-200 bg-red-50/50 p-3 space-y-1 dark:border-red-950/60 dark:bg-red-950/20">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-red-700 dark:text-red-400">Tingkat Merah</span>
                                <span class="size-2 rounded-full bg-red-500 animate-ping"></span>
                            </div>
                            <div class="text-xl font-bold font-mono text-red-900 dark:text-red-200">
                                {{ formatNumber(stats.early_warning.merah) }}
                            </div>
                            <p class="text-[10px] text-red-700/80 dark:text-red-400">
                                Berakhir &le; 14 hari kedepan
                            </p>
                        </div>

                        <!-- Mendesak (Kuning) -->
                        <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-3 space-y-1 dark:border-amber-950/60 dark:bg-amber-950/20">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Tingkat Kuning</span>
                                <span class="size-2 rounded-full bg-amber-500"></span>
                            </div>
                            <div class="text-xl font-bold font-mono text-amber-900 dark:text-amber-200">
                                {{ formatNumber(stats.early_warning.kuning) }}
                            </div>
                            <p class="text-[10px] text-amber-700/80 dark:text-amber-400">
                                Berakhir 15 - 30 hari kedepan
                            </p>
                        </div>

                        <!-- Perhatian (Biru) -->
                        <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-3 space-y-1 dark:border-blue-950/60 dark:bg-blue-950/20">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-blue-700 dark:text-blue-400">Tingkat Biru</span>
                                <span class="size-2 rounded-full bg-blue-500"></span>
                            </div>
                            <div class="text-xl font-bold font-mono text-blue-900 dark:text-blue-200">
                                {{ formatNumber(stats.early_warning.biru) }}
                            </div>
                            <p class="text-[10px] text-blue-700/80 dark:text-blue-400">
                                Berakhir 31 hari s/d 3 bulan
                            </p>
                        </div>

                        <!-- Sudah Kedaluwarsa -->
                        <div class="rounded-lg border border-slate-300 bg-slate-100/60 p-3 space-y-1 dark:border-slate-800 dark:bg-slate-900/40">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Kedaluwarsa</span>
                                <AlertCircle class="size-3.5 text-slate-500" />
                            </div>
                            <div class="text-xl font-bold font-mono text-slate-900 dark:text-slate-100">
                                {{ formatNumber(stats.early_warning.kedaluwarsa) }}
                            </div>
                            <p class="text-[10px] text-slate-600 dark:text-slate-400">
                                Melewati masa berlaku izin
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Grafik Garis Realisasi PNBP (Lebar Penuh untuk Semua Role) -->
        <PnbpChartCard
            v-if="pnbpData"
            :pnbp="pnbpData"
            :compact="false"
        />
    </div>
</template>
