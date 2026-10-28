<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowDownRight,
    ArrowUpRight,
    Calendar,
    ChevronDown,
    DollarSign,
    Eye,
    FileDown,
    MoreHorizontal,
    Pencil,
    Plus,
    Printer,
    Receipt,
    RefreshCw,
    Search,
    Trash2,
    TrendingDown,
    TrendingUp,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { cleanBrowserUrl } from '@/lib/cleanUrl';
import PageHeader from '@/components/PageHeader.vue';
import FlashAlert from '@/components/FlashAlert.vue';
import RealisasiPnbpDeleteDialog from '@/components/realisasi-pnbp/RealisasiPnbpDeleteDialog.vue';
import RealisasiPnbpDetailModal from '@/components/realisasi-pnbp/RealisasiPnbpDetailModal.vue';
import RealisasiPnbpFormModal from '@/components/realisasi-pnbp/RealisasiPnbpFormModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import type {
    PaginatedRealisasiPnbp,
    RealisasiPnbpFilters,
    RealisasiPnbpItem,
    RealisasiPnbpSummary,
} from '@/types/realisasi-pnbp';

const props = defineProps<{
    realisasiPnbp: PaginatedRealisasiPnbp;
    filters: RealisasiPnbpFilters;
    tahunOptions: string[];
    summary: RealisasiPnbpSummary;
    can: {
        create: boolean;
        printList: boolean;
    };
}>();

const page = usePage();

// Local filter states
const search = ref(props.filters.search || '');
const selectedTahun = ref(props.filters.tahun || '');
const sortBy = ref(props.filters.sort_by || '');
const sortOrder = ref<'asc' | 'desc'>((props.filters.sort_order as 'asc' | 'desc') || 'desc');
const perPage = ref(props.filters.per_page || 50);

// Modal states
const isFormModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const activeRealisasi = ref<RealisasiPnbpItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/realisasi-pnbp',
        {
            search: search.value || undefined,
            tahun: selectedTahun.value || undefined,
            sort_by: sortBy.value || undefined,
            sort_order: sortBy.value ? sortOrder.value : undefined,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                cleanBrowserUrl();
            },
        }
    );
}

watch(search, () => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

watch([selectedTahun, perPage], () => {
    applyFilters();
});

function handleSort(column: string) {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = 'desc';
    }
    applyFilters();
}

function resetFilters() {
    search.value = '';
    selectedTahun.value = '';
    sortBy.value = '';
    sortOrder.value = 'desc';
    perPage.value = 50;
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return (
        search.value !== '' ||
        selectedTahun.value !== '' ||
        !!sortBy.value ||
        perPage.value !== 50
    );
});

function openCreateModal() {
    activeRealisasi.value = null;
    isFormModalOpen.value = true;
}

function openEditModal(item: RealisasiPnbpItem) {
    activeRealisasi.value = item;
    isFormModalOpen.value = true;
}

function openDetailModal(item: RealisasiPnbpItem) {
    activeRealisasi.value = item;
    isDetailModalOpen.value = true;
}

function openDeleteDialog(item: RealisasiPnbpItem) {
    activeRealisasi.value = item;
    isDeleteDialogOpen.value = true;
}

const printListUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedTahun.value) params.set('tahun', selectedTahun.value);
    const queryString = params.toString();
    return queryString ? `/realisasi-pnbp/print-list?${queryString}` : '/realisasi-pnbp/print-list';
});

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
    <Head title="Realisasi PNBP" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <!-- Header Section -->
        <PageHeader
            title="Realisasi PNBP"
            description="Kelola dan pantau target serta capaian realisasi Penerimaan Negara Bukan Pajak (PNBP) secara berkala."
        >
            <template #actions>
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        v-if="can.printList"
                        variant="outline"
                        size="sm"
                        as-child
                        class="gap-1.5 shadow-xs"
                    >
                        <Link :href="printListUrl" title="Ekspor atau cetak daftar laporan ke format PDF">
                            <FileDown class="size-4" />
                            <span>Ekspor PDF</span>
                        </Link>
                    </Button>

                    <Button
                        v-if="can.create"
                        size="sm"
                        class="gap-1.5 shadow-xs"
                        @click="openCreateModal"
                    >
                        <Plus class="size-4" />
                        <span>Tambah Realisasi</span>
                    </Button>
                </div>
            </template>
        </PageHeader>

        <!-- Flash Feedback Messages -->
        <FlashAlert />

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Target -->
            <div class="flex flex-col justify-between rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Total Target</span>
                    <div class="flex size-7 items-center justify-center rounded-md bg-muted text-muted-foreground">
                        <DollarSign class="size-3.5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold font-mono text-foreground tracking-tight">
                        {{ formatRupiah(summary.total_target) }}
                    </div>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Target penerimaan terdaftar
                    </p>
                </div>
            </div>

            <!-- Total Realisasi -->
            <div class="flex flex-col justify-between rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Total Realisasi</span>
                    <div class="flex size-7 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                        <TrendingUp class="size-3.5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 tracking-tight">
                        {{ formatRupiah(summary.total_realisasi) }}
                    </div>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Total penerimaan terhimpun
                    </p>
                </div>
            </div>

            <!-- Total Selisih -->
            <div class="flex flex-col justify-between rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Total Selisih</span>
                    <div
                        class="flex size-7 items-center justify-center rounded-md"
                        :class="summary.total_selisih >= 0 ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400'"
                    >
                        <ArrowUpRight v-if="summary.total_selisih >= 0" class="size-3.5" />
                        <ArrowDownRight v-else class="size-3.5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div
                        class="text-2xl font-bold font-mono tracking-tight"
                        :class="summary.total_selisih >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                    >
                        {{ (summary.total_selisih >= 0 ? '+ ' : '') + formatRupiah(summary.total_selisih) }}
                    </div>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        {{ summary.total_selisih >= 0 ? 'Surplus realisasi' : 'Defisit target' }}
                    </p>
                </div>
            </div>

            <!-- Persentase Total -->
            <div class="flex flex-col justify-between rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Rasio Capaian</span>
                    <div class="flex size-7 items-center justify-center rounded-md bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400">
                        <Receipt class="size-3.5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400 tracking-tight">
                        {{ summary.persentase_total }}%
                    </div>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Capaian agregat terhadap target
                    </p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-xl border border-border/70 bg-card p-4 shadow-xs">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <!-- Search Input -->
                <div class="relative md:col-span-7">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Cari berdasarkan bulan (YYYY-MM) atau keterangan..."
                        class="pl-9 text-xs sm:text-sm h-9"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                        @click="search = ''"
                    >
                        <X class="size-3.5" />
                    </button>
                </div>

                <!-- Filter Tahun -->
                <div class="relative md:col-span-4">
                    <select
                        v-model="selectedTahun"
                        class="h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-8 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer dark:bg-card"
                    >
                        <option value="">Semua Tahun Rekapitulasi</option>
                        <option v-for="th in tahunOptions" :key="th" :value="th">
                            Tahun {{ th }}
                        </option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                </div>

                <!-- Reset Filter -->
                <div class="flex items-center justify-end md:col-span-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Reset Filter"
                        :disabled="!hasActiveFilters"
                        class="size-9 shrink-0"
                        @click="resetFilters"
                    >
                        <RefreshCw class="size-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tabel Data Realisasi PNBP -->
        <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border/80 bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th scope="col" class="px-4 py-3 w-12 text-center">No</th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('bulan')">
                                <div class="flex items-center gap-1.5">
                                    <span>Bulan</span>
                                    <span v-if="sortBy === 'bulan'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none text-right" @click="handleSort('target')">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span>Target (Rp)</span>
                                    <span v-if="sortBy === 'target'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none text-right" @click="handleSort('realisasi')">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span>Realisasi (Rp)</span>
                                    <span v-if="sortBy === 'realisasi'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 text-right">Selisih (Rp)</th>
                            <th scope="col" class="px-4 py-3 text-right w-28">Capaian</th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <!-- Empty State -->
                        <tr v-if="realisasiPnbp.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                    <Receipt class="size-6" />
                                </div>
                                <h3 class="text-sm font-semibold text-foreground">Tidak Ada Data Realisasi PNBP</h3>
                                <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                                    {{
                                        hasActiveFilters
                                            ? 'Tidak ditemukan data realisasi PNBP yang sesuai dengan filter pencarian Anda.'
                                            : 'Belum ada data rekapitulasi penerimaan PNBP yang tercatat dalam sistem.'
                                    }}
                                </p>
                                <Button
                                    v-if="hasActiveFilters"
                                    variant="outline"
                                    size="sm"
                                    class="mt-4"
                                    @click="resetFilters"
                                >
                                    Reset Filter
                                </Button>
                            </td>
                        </tr>

                        <!-- Data Rows -->
                        <tr
                            v-for="(item, index) in realisasiPnbp.data"
                            :key="item.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                {{ (realisasiPnbp.from || 1) + index }}
                            </td>

                            <td class="px-4 py-3 text-muted-foreground whitespace-nowrap text-xs font-medium">
                                <div class="flex items-center gap-1.5">
                                    <Calendar class="size-3.5 text-muted-foreground shrink-0" />
                                    <span>{{ formatIndoMonth(item.bulan) }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-right font-mono text-xs text-muted-foreground whitespace-nowrap">
                                {{ formatRupiah(item.target) }}
                            </td>

                            <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ formatRupiah(item.realisasi) }}
                            </td>

                            <td
                                class="px-4 py-3 text-right font-mono text-xs font-semibold whitespace-nowrap"
                                :class="item.selisih >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                            >
                                {{ (item.selisih >= 0 ? '+ ' : '') + formatRupiah(item.selisih) }}
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold"
                                    :class="item.selisih >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900/60' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900/60'"
                                >
                                    <TrendingUp v-if="item.selisih >= 0" class="size-3" />
                                    <TrendingDown v-else class="size-3" />
                                    <span>{{ item.persentase }}%</span>
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="size-8">
                                                <MoreHorizontal class="size-4" />
                                                <span class="sr-only">Menu Aksi</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-44">
                                            <DropdownMenuLabel class="text-xs font-normal text-muted-foreground">
                                                Aksi Data
                                            </DropdownMenuLabel>
                                            <DropdownMenuItem class="cursor-pointer" @click="openDetailModal(item)">
                                                <Eye class="mr-2 size-4" />
                                                <span>Lihat Detail</span>
                                            </DropdownMenuItem>
                                            <template v-if="can.create">
                                                <DropdownMenuItem class="cursor-pointer" @click="openEditModal(item)">
                                                    <Pencil class="mr-2 size-4" />
                                                    <span>Ubah Data</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    class="cursor-pointer text-red-600 focus:bg-red-50 focus:text-red-700 dark:focus:bg-red-950/30"
                                                    @click="openDeleteDialog(item)"
                                                >
                                                    <Trash2 class="mr-2 size-4" />
                                                    <span>Hapus Data</span>
                                                </DropdownMenuItem>
                                            </template>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-border/80 px-4 py-3 bg-muted/20 text-xs text-muted-foreground">
                <div class="flex items-center gap-3">
                    <span>
                        Menampilkan
                        <strong class="font-medium text-foreground">{{ realisasiPnbp.from || 0 }}</strong>
                        sampai
                        <strong class="font-medium text-foreground">{{ realisasiPnbp.to || 0 }}</strong>
                        dari
                        <strong class="font-medium text-foreground">{{ realisasiPnbp.total }}</strong>
                        data
                    </span>

                    <div class="flex items-center gap-1.5">
                        <span>Baris per halaman:</span>
                        <select
                            v-model="perPage"
                            class="h-7 rounded border border-input bg-transparent px-2 text-xs shadow-2xs"
                        >
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Page Links -->
                <div v-if="realisasiPnbp.links && realisasiPnbp.links.length > 3" class="flex items-center gap-1">
                    <template v-for="(link, index) in realisasiPnbp.links" :key="index">
                        <Component
                            :is="link.url ? Link : 'span'"
                            :href="link.url || undefined"
                            class="flex min-w-[32px] h-8 items-center justify-center rounded px-2 text-xs transition-colors"
                            :class="{
                                'bg-indigo-600 font-semibold text-white dark:bg-indigo-600': link.active,
                                'text-muted-foreground hover:bg-muted hover:text-foreground': !link.active && link.url,
                                'text-muted-foreground/40 cursor-not-allowed': !link.url,
                            }"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <RealisasiPnbpFormModal
            v-model:open="isFormModalOpen"
            :realisasi-pnbp="activeRealisasi"
        />

        <RealisasiPnbpDetailModal
            v-model:open="isDetailModalOpen"
            :realisasi-pnbp="activeRealisasi"
        />

        <RealisasiPnbpDeleteDialog
            v-model:open="isDeleteDialogOpen"
            :realisasi-pnbp="activeRealisasi"
        />
    </div>
</template>
