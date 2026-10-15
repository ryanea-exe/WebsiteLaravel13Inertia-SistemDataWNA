<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertTriangle,
    ArrowRight,
    Award,
    Clock,
    FileCheck,
    FileText,
    Globe,
    Layers,
    Receipt,
    Shield,
    ShieldCheck,
    TrendingUp,
    UserCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
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

interface DistribusiIzin {
    jenis: string;
    label: string;
    total: number;
    persen: number;
}

interface TopKewarganegaraan {
    negara: string;
    total: number;
    persen: number;
}

interface TrenWnaBulanan {
    bulan: number;
    label: string;
    total: number;
}

interface MonthlyPnbp {
    bulan: string;
    target: number;
    realisasi: number;
    persentase: number;
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
        admin?: {
            total_permohonan: number;
            total_penerbitan: number;
            total_users: number;
            pnbp: {
                tahun: number;
                total_target: number;
                total_realisasi: number;
                selisih: number;
                persentase: number;
                bulanan: MonthlyPnbp[];
            };
        } | null;
    };
    charts: {
        distribusi_izin: DistribusiIzin[];
        top_kewarganegaraan: TopKewarganegaraan[];
        tren_wna_bulanan: TrenWnaBulanan[];
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

// Highest monthly total for bar chart height scaling
const maxTrenMonthly = computed(() => {
    const max = Math.max(...props.charts.tren_wna_bulanan.map((b) => b.total), 0);
    return max > 0 ? max : 1;
});

const totalWnaInYear = computed(() => {
    return props.charts.tren_wna_bulanan.reduce((acc, curr) => acc + curr.total, 0);
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

        <!-- Baris 1: Kartu Statistik Operasional (Tersedia untuk Semua Role) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Total Data WNA"
                :icon="Users"
                :value="formatNumber(stats.total_wna)"
                description="Basis data Warga Negara Asing pemegang izin tinggal ITAS, ITAP, dan VOA aktif."
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
                title="Early Warning Aktif"
                :icon="AlertTriangle"
                :value="formatNumber(stats.early_warning.semua)"
                :description="`${formatNumber(stats.early_warning.merah)} Sangat Mendesak, ${formatNumber(stats.early_warning.kedaluwarsa)} Kedaluwarsa.`"
                variant="warning"
            />
        </div>

        <!-- Baris 2: Kartu Statistik Tambahan Khusus Admin -->
        <div v-if="isAdmin && stats.admin" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Permohonan Keimigrasian"
                :icon="FileText"
                :value="formatNumber(stats.admin.total_permohonan)"
                description="Total permohonan layanan keimigrasian tercatat dalam sistem."
                variant="default"
            />
            <StatCard
                title="Penerbitan Dokumen"
                :icon="Award"
                :value="formatNumber(stats.admin.total_penerbitan)"
                description="Total dokumen dan izin keimigrasian yang telah diterbitkan."
                variant="default"
            />
            <StatCard
                title="Realisasi PNBP"
                :icon="Receipt"
                :value="`${stats.admin.pnbp.persentase}%`"
                :description="`${formatRupiah(stats.admin.pnbp.total_realisasi)} dari target ${formatRupiah(stats.admin.pnbp.total_target)}`"
                variant="success"
            />
            <StatCard
                title="Pengguna Sistem"
                :icon="ShieldCheck"
                :value="formatNumber(stats.admin.total_users)"
                description="Total akun administrator dan petugas operasional aktif."
                variant="default"
            />
        </div>

        <!-- Baris 3: Visualisasi Grafik Tren dan Distribusi Izin -->
        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Grafik 1: Tren Pendaftaran WNA Bulanan -->
            <Card class="border-border/80 shadow-xs flex flex-col justify-between">
                <CardHeader class="pb-3 border-b border-border/60">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                                <TrendingUp class="size-4 text-primary" />
                                <span>Tren Pendaftaran WNA (Tahun {{ charts.tahun_berjalan }})</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                Perkembangan akumulasi pendaftaran data WNA sepanjang 12 bulan tahun berjalan.
                            </CardDescription>
                        </div>
                        <span class="rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-semibold">
                            Total: {{ formatNumber(totalWnaInYear) }}
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="pt-5 flex-1 flex flex-col justify-end">
                    <!-- Bar Chart Visualizer -->
                    <div v-if="totalWnaInYear > 0" class="flex items-end justify-between gap-1.5 sm:gap-2 h-44 pt-6 pb-2 px-1 border-b border-border/60">
                        <div
                            v-for="b in charts.tren_wna_bulanan"
                            :key="b.bulan"
                            class="flex-1 flex flex-col items-center gap-1.5 group relative"
                        >
                            <!-- Tooltip popup on hover -->
                            <div class="absolute -top-8 hidden group-hover:flex z-10 whitespace-nowrap rounded bg-foreground px-2 py-0.5 text-[10px] font-semibold text-background shadow-xs">
                                {{ b.label }}: {{ formatNumber(b.total) }} data
                            </div>
                            <!-- Bar column -->
                            <div class="w-full flex items-end justify-center h-32">
                                <div
                                    class="w-full max-w-[28px] rounded-t-sm transition-all duration-300"
                                    :class="b.total > 0 ? 'bg-primary hover:bg-primary/90' : 'bg-muted/40'"
                                    :style="{
                                        height: `${Math.max(Math.round((b.total / maxTrenMonthly) * 100), 6)}%`,
                                    }"
                                ></div>
                            </div>
                            <!-- Month Label -->
                            <span class="text-[10px] sm:text-xs font-medium text-muted-foreground group-hover:text-foreground">
                                {{ b.label }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State for Monthly Trend -->
                    <div v-else class="flex min-h-[180px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-6 text-center">
                        <TrendingUp class="size-8 text-muted-foreground/60" />
                        <h4 class="mt-2 text-xs font-semibold text-foreground">
                            Belum Ada Registrasi WNA di Tahun {{ charts.tahun_berjalan }}
                        </h4>
                        <p class="mt-1 text-[11px] text-muted-foreground max-w-xs">
                            Data grafik akan terbentuk secara otomatis seiring dengan penambahan pendaftaran WNA baru.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Grafik 2: Distribusi Jenis Izin Tinggal -->
            <Card class="border-border/80 shadow-xs flex flex-col justify-between">
                <CardHeader class="pb-3 border-b border-border/60">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                                <Layers class="size-4 text-primary" />
                                <span>Distribusi Jenis Izin Tinggal</span>
                            </CardTitle>
                            <CardDescription class="text-xs text-muted-foreground">
                                Proporsi pemegang izin tinggal aktif berdasarkan kategori ITAS, ITAP, dan VOA.
                            </CardDescription>
                        </div>
                        <span class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                            {{ stats.total_wna }} WNA
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="pt-5 flex-1 flex flex-col justify-center space-y-4">
                    <div v-if="stats.total_wna > 0" class="space-y-4">
                        <!-- Visual Stacked Bar -->
                        <div class="h-4 w-full rounded-full overflow-hidden flex bg-muted/60 shadow-inner">
                            <div
                                v-for="(item, idx) in charts.distribusi_izin"
                                :key="item.jenis"
                                :style="{ width: `${item.persen}%` }"
                                :class="[
                                    idx === 0 ? 'bg-indigo-600 dark:bg-indigo-500' : '',
                                    idx === 1 ? 'bg-emerald-600 dark:bg-emerald-500' : '',
                                    idx === 2 ? 'bg-amber-500 dark:bg-amber-400' : '',
                                ]"
                                :title="`${item.jenis}: ${item.total} (${item.persen}%)`"
                            ></div>
                        </div>

                        <!-- Legend & Detail Breakdown -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                            <div
                                v-for="(item, idx) in charts.distribusi_izin"
                                :key="item.jenis"
                                class="rounded-lg border border-border/60 bg-muted/20 p-3 space-y-1"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="size-2.5 rounded-full"
                                        :class="[
                                            idx === 0 ? 'bg-indigo-600 dark:bg-indigo-500' : '',
                                            idx === 1 ? 'bg-emerald-600 dark:bg-emerald-500' : '',
                                            idx === 2 ? 'bg-amber-500 dark:bg-amber-400' : '',
                                        ]"
                                    ></span>
                                    <span class="text-xs font-bold text-foreground">{{ item.jenis }}</span>
                                </div>
                                <div class="flex items-baseline justify-between pt-1">
                                    <span class="text-lg font-bold font-mono text-foreground">{{ formatNumber(item.total) }}</span>
                                    <span class="text-xs text-muted-foreground font-semibold">{{ item.persen }}%</span>
                                </div>
                                <p class="text-[10px] text-muted-foreground leading-tight truncate">
                                    {{ item.label }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State for Permit Distribution -->
                    <div v-else class="flex min-h-[180px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-6 text-center">
                        <Layers class="size-8 text-muted-foreground/60" />
                        <h4 class="mt-2 text-xs font-semibold text-foreground">
                            Belum Ada Data Izin Tinggal
                        </h4>
                        <p class="mt-1 text-[11px] text-muted-foreground max-w-xs">
                            Statistik distribusi ITAS, ITAP, dan VOA akan muncul saat data WNA terdaftar.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Baris 4: Top Kewarganegaraan & Ringkasan Early Warning -->
        <div class="grid gap-4 lg:grid-cols-2">
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

            <!-- Card: Ringkasan Status Early Warning Izin Tinggal -->
            <Card class="border-border/80 shadow-xs">
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
                        <Button variant="ghost" size="sm" as-child class="h-7 text-xs gap-1 text-primary">
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

        <!-- Baris 5 (Khusus Admin): Rekap Realisasi PNBP Bulanan -->
        <Card v-if="isAdmin && stats.admin && stats.admin.pnbp.bulanan.length > 0" class="border-border/80 shadow-xs">
            <CardHeader class="pb-3 border-b border-border/60">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                            <Receipt class="size-4 text-emerald-600" />
                            <span>Rekapitulasi Target & Realisasi PNBP (Tahun {{ stats.admin.pnbp.tahun }})</span>
                        </CardTitle>
                        <CardDescription class="text-xs text-muted-foreground">
                            Perbandingan pencapaian target dan penerimaan PNBP aktual tiap bulan.
                        </CardDescription>
                    </div>
                    <Button variant="ghost" size="sm" as-child class="h-7 text-xs gap-1 text-primary">
                        <Link href="/realisasi-pnbp">
                            <span>Buka Modul PNBP</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="pt-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/30 text-muted-foreground">
                                <th class="py-2 px-3">Bulan</th>
                                <th class="py-2 px-3 text-right">Target</th>
                                <th class="py-2 px-3 text-right">Realisasi</th>
                                <th class="py-2 px-3 text-right">Pencapaian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr
                                v-for="item in stats.admin.pnbp.bulanan"
                                :key="item.bulan"
                                class="hover:bg-muted/20"
                            >
                                <td class="py-2 px-3 font-medium text-foreground">{{ item.bulan }}</td>
                                <td class="py-2 px-3 text-right font-mono text-muted-foreground">{{ formatRupiah(item.target) }}</td>
                                <td class="py-2 px-3 text-right font-mono font-semibold text-foreground">{{ formatRupiah(item.realisasi) }}</td>
                                <td class="py-2 px-3 text-right font-mono">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="item.persentase >= 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'"
                                    >
                                        {{ item.persentase }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
