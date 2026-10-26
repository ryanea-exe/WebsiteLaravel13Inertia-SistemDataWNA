<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChevronDown,
    ChevronUp,
    Receipt,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

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

interface PnbpData {
    tahun: number;
    total_target: number;
    total_realisasi: number;
    selisih: number;
    persentase: number;
    bulanan: MonthlyPnbp[];
    line_series?: PnbpLineItem[];
}

interface Props {
    pnbp: PnbpData;
    compact?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    compact: false,
});

const showPnbpTable = ref(false);
const activePnbpMonth = ref<number | null>(null);

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

const pnbpLineSeries = computed<PnbpLineItem[]>(() => {
    return props.pnbp.line_series ?? [];
});

const hasPnbpDataInYear = computed<boolean>(() => {
    return props.pnbp.total_target > 0 || props.pnbp.total_realisasi > 0;
});

// SVG Chart dimensions
const svgWidth = 800;
const svgHeight = 250;
const padLeft = 85;
const padRight = 35;
const padTop = 30;
const padBottom = 45;
const plotWidth = svgWidth - padLeft - padRight; // 680
const plotHeight = svgHeight - padTop - padBottom; // 175

const pnbpMaxNominal = computed<number>(() => {
    const maxVal = Math.max(
        ...pnbpLineSeries.value.map((item) => Math.max(item.target, item.realisasi)),
        props.pnbp.total_target ? props.pnbp.total_target / 6 : 100_000_000,
        1,
    );
    // Round up to nice number
    const magnitude = Math.pow(10, Math.floor(Math.log10(maxVal)));
    return Math.ceil(maxVal / magnitude) * magnitude;
});

const yGridTicks = computed(() => {
    const max = pnbpMaxNominal.value;
    return [
        { value: max, y: padTop, label: formatShortRupiah(max) },
        { value: max * 0.75, y: padTop + plotHeight * 0.25, label: formatShortRupiah(max * 0.75) },
        { value: max * 0.5, y: padTop + plotHeight * 0.5, label: formatShortRupiah(max * 0.5) },
        { value: max * 0.25, y: padTop + plotHeight * 0.75, label: formatShortRupiah(max * 0.25) },
        { value: 0, y: padTop + plotHeight, label: 'Rp 0' },
    ];
});

const pnbpPlotPoints = computed(() => {
    const count = pnbpLineSeries.value.length || 12;
    const xStep = count > 1 ? plotWidth / (count - 1) : plotWidth;
    const max = pnbpMaxNominal.value;

    return pnbpLineSeries.value.map((item, idx) => {
        const x = padLeft + idx * xStep;
        const targetY = padTop + plotHeight - (item.target / max) * plotHeight;
        const realisasiY = padTop + plotHeight - (item.realisasi / max) * plotHeight;

        return {
            ...item,
            x,
            targetY,
            realisasiY,
        };
    });
});

const targetLinePath = computed<string>(() => {
    const pts = pnbpPlotPoints.value;
    if (pts.length === 0) return '';
    return pts.reduce((path, pt, idx) => {
        return `${path} ${idx === 0 ? 'M' : 'L'} ${pt.x.toFixed(1)} ${pt.targetY.toFixed(1)}`;
    }, '');
});

const realisasiLinePath = computed<string>(() => {
    const pts = pnbpPlotPoints.value;
    if (pts.length === 0) return '';
    return pts.reduce((path, pt, idx) => {
        return `${path} ${idx === 0 ? 'M' : 'L'} ${pt.x.toFixed(1)} ${pt.realisasiY.toFixed(1)}`;
    }, '');
});

const realisasiAreaPath = computed<string>(() => {
    const pts = pnbpPlotPoints.value;
    if (pts.length === 0) return '';
    const baseY = padTop + plotHeight;
    const firstX = pts[0].x.toFixed(1);
    const lastX = pts[pts.length - 1].x.toFixed(1);

    const linePoints = pts.map((pt) => `L ${pt.x.toFixed(1)} ${pt.realisasiY.toFixed(1)}`).join(' ');
    return `M ${firstX} ${baseY} ${linePoints} L ${lastX} ${baseY} Z`;
});

const activePnbpPoint = computed(() => {
    if (activePnbpMonth.value === null) {
        const withData = pnbpPlotPoints.value.filter((p) => p.has_data);
        return withData.length > 0 ? withData[withData.length - 1] : null;
    }
    return pnbpPlotPoints.value.find((p) => p.bulan === activePnbpMonth.value) ?? null;
});
</script>

<template>
    <Card class="border-border/80 shadow-xs flex flex-col justify-between">
        <CardHeader class="pb-3 border-b border-border/60">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="space-y-1">
                    <CardTitle class="text-sm font-semibold tracking-tight text-foreground flex items-center gap-2">
                        <Receipt class="size-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Grafik Garis Realisasi PNBP (Tahun {{ pnbp.tahun }})</span>
                    </CardTitle>
                    <CardDescription class="text-xs text-muted-foreground">
                        Tren perbandingan target dan realisasi penerimaan negara bukan pajak per bulan kalender.
                    </CardDescription>
                </div>

                <!-- Legend & Navigation -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3 text-xs font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="w-4 h-0.5 border-t-2 border-dashed border-indigo-500"></span>
                            <span class="text-muted-foreground text-[11px]">Target</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-4 h-1 rounded bg-emerald-600 dark:bg-emerald-500"></span>
                            <span class="text-foreground font-semibold text-[11px]">Realisasi</span>
                        </div>
                    </div>

                    <Button variant="ghost" size="sm" as-child class="h-7 text-xs gap-1 text-primary">
                        <Link href="/realisasi-pnbp">
                            <span>Buka Modul PNBP</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </Button>
                </div>
            </div>
        </CardHeader>

        <CardContent class="pt-4 space-y-4">
            <!-- Highlight Indicator Card if month hovered or latest data -->
            <div
                v-if="activePnbpPoint && activePnbpPoint.has_data"
                class="grid grid-cols-2 gap-2.5 p-2.5 rounded-lg bg-muted/30 border border-border/60"
                :class="compact ? 'sm:grid-cols-2' : 'sm:grid-cols-4'"
            >
                <div>
                    <span class="text-[10px] text-muted-foreground font-medium block">Bulan Periode</span>
                    <div class="text-xs font-bold text-foreground">
                        {{ activePnbpPoint.label }} {{ pnbp.tahun }}
                    </div>
                </div>
                <div>
                    <span class="text-[10px] text-muted-foreground font-medium block">Target Bulanan</span>
                    <div class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                        {{ formatRupiah(activePnbpPoint.target) }}
                    </div>
                </div>
                <div>
                    <span class="text-[10px] text-muted-foreground font-medium block">Realisasi Aktual</span>
                    <div class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                        {{ formatRupiah(activePnbpPoint.realisasi) }}
                    </div>
                </div>
                <div>
                    <span class="text-[10px] text-muted-foreground font-medium block">Capaian & Selisih</span>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="text-xs font-mono font-bold"
                            :class="activePnbpPoint.persentase >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                        >
                            {{ activePnbpPoint.persentase }}%
                        </span>
                        <span class="text-[9px] text-muted-foreground">
                            ({{ activePnbpPoint.selisih >= 0 ? '+' : '' }}{{ formatShortRupiah(activePnbpPoint.selisih) }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Interactive SVG Line Chart -->
            <div v-if="hasPnbpDataInYear" class="w-full overflow-x-auto">
                <div class="min-w-[480px] sm:min-w-0 w-full relative">
                    <svg
                        :viewBox="`0 0 ${svgWidth} ${svgHeight}`"
                        class="w-full select-none overflow-visible"
                        :class="compact ? 'h-48 sm:h-52' : 'h-64'"
                        role="img"
                        aria-label="Grafik garis target vs realisasi PNBP"
                    >
                        <defs>
                            <linearGradient :id="`pnbpGradient-${compact ? 'compact' : 'full'}`" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        <!-- Horizontal Grid Lines and Y-Axis Ticks -->
                        <g class="grid-lines">
                            <g v-for="tick in yGridTicks" :key="tick.value">
                                <line
                                    :x1="padLeft"
                                    :y1="tick.y"
                                    :x2="svgWidth - padRight"
                                    :y2="tick.y"
                                    stroke="currentColor"
                                    class="text-border/60"
                                    stroke-dasharray="3 3"
                                />
                                <text
                                    :x="padLeft - 10"
                                    :y="tick.y + 4"
                                    text-anchor="end"
                                    class="text-[10px] font-mono fill-muted-foreground"
                                >
                                    {{ tick.label }}
                                </text>
                            </g>
                        </g>

                        <!-- Gradient Area Fill under Realisasi Line -->
                        <path
                            :d="realisasiAreaPath"
                            :fill="`url(#pnbpGradient-${compact ? 'compact' : 'full'})`"
                            class="transition-all duration-300 pointer-events-none"
                        />

                        <!-- Target Dashed Line -->
                        <path
                            :d="targetLinePath"
                            fill="none"
                            stroke="#6366f1"
                            stroke-width="2"
                            stroke-dasharray="5 4"
                            class="transition-all duration-300 pointer-events-none"
                        />

                        <!-- Realisasi Solid Line -->
                        <path
                            :d="realisasiLinePath"
                            fill="none"
                            stroke="#10b981"
                            stroke-width="3"
                            class="transition-all duration-300 pointer-events-none"
                        />

                        <!-- Data Points and Interactive Hover Columns -->
                        <g
                            v-for="pt in pnbpPlotPoints"
                            :key="pt.bulan"
                            class="group cursor-pointer"
                            @mouseenter="activePnbpMonth = pt.bulan"
                        >
                            <!-- Vertical indicator on hover -->
                            <line
                                v-if="activePnbpMonth === pt.bulan"
                                :x1="pt.x"
                                :y1="padTop"
                                :x2="pt.x"
                                :y2="padTop + plotHeight"
                                stroke="currentColor"
                                class="text-primary/70"
                                stroke-dasharray="2 2"
                            />

                            <!-- Month label on X Axis -->
                            <text
                                :x="pt.x"
                                :y="padTop + plotHeight + 20"
                                text-anchor="middle"
                                class="text-[11px] font-medium transition-colors"
                                :class="activePnbpMonth === pt.bulan ? 'fill-foreground font-bold' : 'fill-muted-foreground'"
                            >
                                {{ pt.label }}
                            </text>

                            <!-- Target Point Marker -->
                            <circle
                                v-if="pt.has_data"
                                :cx="pt.x"
                                :cy="pt.targetY"
                                r="4"
                                fill="#6366f1"
                                stroke="white"
                                stroke-width="1.5"
                            />

                            <!-- Realisasi Point Marker -->
                            <circle
                                v-if="pt.has_data"
                                :cx="pt.x"
                                :cy="pt.realisasiY"
                                :r="activePnbpMonth === pt.bulan ? 6 : 4.5"
                                fill="#10b981"
                                stroke="white"
                                stroke-width="2"
                                class="transition-all duration-200"
                            />

                            <!-- Invisible hover hit area for touch/mouse -->
                            <rect
                                :x="pt.x - 20"
                                :y="padTop"
                                width="40"
                                :height="plotHeight + 25"
                                fill="transparent"
                            />
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Empty State for PNBP -->
            <div v-else class="flex min-h-[160px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-6 text-center">
                <Receipt class="size-8 text-muted-foreground/60" />
                <h4 class="mt-2 text-xs font-semibold text-foreground">
                    Belum Ada Data Realisasi PNBP di Tahun {{ pnbp.tahun }}
                </h4>
                <p class="mt-1 text-[11px] text-muted-foreground max-w-xs">
                    Grafik garis akan terbentuk secara otomatis saat target dan realisasi bulanan dimasukkan ke modul Realisasi PNBP.
                </p>
            </div>

            <!-- Summary Badges & Table Toggle -->
            <div class="flex flex-col gap-3 pt-3 border-t border-border/60" :class="compact ? '' : 'sm:flex-row sm:items-center sm:justify-between'">
                <div class="grid grid-cols-2 gap-2 text-xs" :class="compact ? '' : 'sm:grid-cols-4'">
                    <div class="p-2 rounded bg-muted/20 border border-border/40">
                        <span class="text-muted-foreground text-[10px] block">Target Tahunan</span>
                        <span class="font-bold font-mono text-foreground text-[11px]">{{ formatRupiah(pnbp.total_target) }}</span>
                    </div>
                    <div class="p-2 rounded bg-muted/20 border border-border/40">
                        <span class="text-muted-foreground text-[10px] block">Realisasi Tahunan</span>
                        <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-[11px]">{{ formatRupiah(pnbp.total_realisasi) }}</span>
                    </div>
                    <div class="p-2 rounded bg-muted/20 border border-border/40">
                        <span class="text-muted-foreground text-[10px] block">Selisih Kumulatif</span>
                        <span
                            class="font-bold font-mono text-[11px]"
                            :class="pnbp.selisih >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                        >
                            {{ pnbp.selisih >= 0 ? '+' : '' }}{{ formatRupiah(pnbp.selisih) }}
                        </span>
                    </div>
                    <div class="p-2 rounded bg-muted/20 border border-border/40">
                        <span class="text-muted-foreground text-[10px] block">Akumulasi Capaian</span>
                        <span class="font-bold font-mono text-primary text-[11px]">{{ pnbp.persentase }}%</span>
                    </div>
                </div>

                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs gap-1.5 self-start sm:self-auto"
                    @click="showPnbpTable = !showPnbpTable"
                >
                    <span>{{ showPnbpTable ? 'Sembunyikan Tabel' : 'Lihat Rincian Tabel' }}</span>
                    <component :is="showPnbpTable ? ChevronUp : ChevronDown" class="size-3.5" />
                </Button>
            </div>

            <!-- Optional Tabular Breakdown for Detailed Audit -->
            <div v-if="showPnbpTable && pnbp.bulanan.length > 0" class="overflow-x-auto rounded-lg border border-border/60">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-border/60 bg-muted/40 text-muted-foreground">
                            <th class="py-2 px-3 font-semibold">Bulan</th>
                            <th class="py-2 px-3 text-right font-semibold">Target</th>
                            <th class="py-2 px-3 text-right font-semibold">Realisasi</th>
                            <th class="py-2 px-3 text-right font-semibold">Pencapaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40">
                        <tr
                            v-for="item in pnbp.bulanan"
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
</template>
