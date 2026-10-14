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
import PageHeader from '@/components/PageHeader.vue';
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
const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success);
const flashError = computed(() => (page.props.flash as { error?: string })?.error);

// Local filter states
const search = ref(props.filters.search || '');
const selectedTahun = ref(props.filters.tahun || '');
const sortBy = ref(props.filters.sort_by || 'bulan');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 10);

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
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
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
    sortBy.value = 'bulan';
    sortOrder.value = 'desc';
    perPage.value = 10;
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return (
        search.value !== '' ||
        selectedTahun.value !== '' ||
        perPage.value !== 10
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
                        <Link :href="printListUrl">
                            <Printer class="size-4" />
                            <span>Cetak / Simpan PDF</span>
                        </Link>
                    </Button>

                    <Button
                        v-if="can.create"
                        size="sm"
                        class="gap-1.5 shadow-xs bg-emerald-600 hover:bg-emerald-700 text-white"
                        @click="openCreateModal"
                    >
                        <Plus class="size-4" />
                        <span>Tambah Realisasi</span>
                    </Button>
                </div>
            </template>
        </PageHeader>

        <!-- Flash Feedback Messages -->
        <div
            v-if="flashSuccess"
            class="flex items-center gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-xs dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <div class="flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-200/80 dark:bg-emerald-900">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200">✓</span>
            </div>
            <p class="font-medium">{{ flashSuccess }}</p>
        </div>

        <div
            v-if="flashError"
            class="flex items-center gap-2.5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-xs dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
            <AlertCircle class="size-5 shrink-0 text-red-600 dark:text-red-400" />
            <p class="font-medium">{{ flashError }}</p>
        </div>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Target -->
            <Card class="shadow-xs border-border/80 bg-card">
                <CardContent class="p-4 space-y-1.5">
                    <div class="flex items-center justify-between text-muted-foreground text-xs font-medium">
                        <span>Total Target</span>
                        <div class="flex size-7 items-center justify-center rounded-md bg-muted text-muted-foreground">
                            <DollarSign class="size-3.5" />
                        </div>
                    </div>
                    <p class="text-xl font-bold font-mono text-foreground tracking-tight">
                        {{ formatRupiah(summary.total_target) }}
                    </p>
                    <p class="text-[11px] text-muted-foreground">
                        Target penerimaan terdaftar
                    </p>
                </CardContent>
            </Card>

            <!-- Total Realisasi -->
            <Card class="shadow-xs border-border/80 bg-card">
                <CardContent class="p-4 space-y-1.5">
                    <div class="flex items-center justify-between text-muted-foreground text-xs font-medium">
                        <span>Total Realisasi</span>
                        <div class="flex size-7 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                            <TrendingUp class="size-3.5" />
                        </div>
                    </div>
                    <p class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 tracking-tight">
                        {{ formatRupiah(summary.total_realisasi) }}
                    </p>
                    <p class="text-[11px] text-muted-foreground">
                        Total penerimaan terhimpun
                    </p>
                </CardContent>
            </Card>

            <!-- Total Selisih -->
            <Card class="shadow-xs border-border/80 bg-card">
                <CardContent class="p-4 space-y-1.5">
                    <div class="flex items-center justify-between text-muted-foreground text-xs font-medium">
                        <span>Total Selisih</span>
                        <div
                            class="flex size-7 items-center justify-center rounded-md"
                            :class="summary.total_selisih >= 0 ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400'"
                        >
                            <ArrowUpRight v-if="summary.total_selisih >= 0" class="size-3.5" />
                            <ArrowDownRight v-else class="size-3.5" />
                        </div>
                    </div>
                    <p
                        class="text-xl font-bold font-mono tracking-tight"
                        :class="summary.total_selisih >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                    >
                        {{ (summary.total_selisih >= 0 ? '+ ' : '') + formatRupiah(summary.total_selisih) }}
                    </p>
                    <p class="text-[11px] text-muted-foreground">
                        {{ summary.total_selisih >= 0 ? 'Surplus realisasi' : 'Defisit target' }}
                    </p>
                </CardContent>
            </Card>

            <!-- Persentase Total -->
            <Card class="shadow-xs border-border/80 bg-card">
                <CardContent class="p-4 space-y-1.5">
                    <div class="flex items-center justify-between text-muted-foreground text-xs font-medium">
                        <span>Rasio Capaian</span>
                        <div class="flex size-7 items-center justify-center rounded-md bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400">
                            <Receipt class="size-3.5" />
                        </div>
                    </div>
                    <p class="text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400 tracking-tight">
                        {{ summary.persentase_total }}%
                    </p>
                    <p class="text-[11px] text-muted-foreground">
                        Capaian agregat terhadap target
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-xl border border-border/70 bg-card p-4 shadow-xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Search Input -->
                <div class="relative sm:col-span-2">
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
                <div>
                    <select
                        v-model="selectedTahun"
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        <option value="">Semua Tahun Rekapitulasi</option>
                        <option v-for="th in tahunOptions" :key="th" :value="th">
                            Tahun {{ th }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Footer Toolbar: Data Count & Reset -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-border/50 text-xs text-muted-foreground">
                <div class="flex items-center gap-2">
                    <span>Menampilkan</span>
                    <select
                        v-model="perPage"
                        class="h-7 rounded border border-input bg-transparent px-2 text-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        <option :value="10">10 data</option>
                        <option :value="15">15 data</option>
                        <option :value="25">25 data</option>
                        <option :value="50">50 data</option>
                    </select>
                    <span>per halaman (Total: <strong class="text-foreground font-semibold">{{ realisasiPnbp.total }}</strong> periode)</span>
                </div>

                <div v-if="hasActiveFilters">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="h-7 text-xs gap-1.5 text-muted-foreground hover:text-foreground"
                        @click="resetFilters"
                    >
                        <RefreshCw class="size-3" />
                        <span>Reset Filter</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tabel Data Realisasi PNBP -->
        <div class="rounded-xl border border-border/70 bg-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs font-semibold text-muted-foreground">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('bulan')">
                                <div class="flex items-center gap-1.5">
                                    <span>Periode Bulan</span>
                                    <ChevronDown v-if="sortBy === 'bulan'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none text-right" @click="handleSort('target')">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span>Target (Rp)</span>
                                    <ChevronDown v-if="sortBy === 'target'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none text-right" @click="handleSort('realisasi')">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span>Realisasi (Rp)</span>
                                    <ChevronDown v-if="sortBy === 'realisasi'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 text-right">Selisih (Rp)</th>
                            <th class="py-3 px-4 text-center w-28">Capaian</th>
                            <th class="py-3 px-4">Keterangan</th>
                            <th class="py-3 px-4 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <!-- Empty State -->
                        <tr v-if="realisasiPnbp.data.length === 0">
                            <td colspan="8" class="py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <Receipt class="size-10 text-muted-foreground/40" />
                                    <p class="font-medium text-base text-foreground">Tidak ada data realisasi PNBP</p>
                                    <p class="text-xs text-muted-foreground max-w-sm">
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
                                        class="mt-2 text-xs"
                                        @click="resetFilters"
                                    >
                                        Reset Filter
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <!-- Data Rows -->
                        <tr
                            v-for="(item, index) in realisasiPnbp.data"
                            :key="item.id"
                            class="hover:bg-muted/30 transition-colors"
                        >
                            <td class="py-3 px-4 text-center text-xs text-muted-foreground">
                                {{ (realisasiPnbp.from || 1) + index }}
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap font-bold text-foreground text-xs sm:text-sm">
                                {{ formatIndoMonth(item.bulan) }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono text-xs text-muted-foreground whitespace-nowrap">
                                {{ formatRupiah(item.target) }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ formatRupiah(item.realisasi) }}
                            </td>

                            <td
                                class="py-3 px-4 text-right font-mono text-xs font-semibold whitespace-nowrap"
                                :class="item.selisih >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                            >
                                {{ (item.selisih >= 0 ? '+ ' : '') + formatRupiah(item.selisih) }}
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold"
                                    :class="item.selisih >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900/60' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900/60'"
                                >
                                    <TrendingUp v-if="item.selisih >= 0" class="size-3" />
                                    <TrendingDown v-else class="size-3" />
                                    <span>{{ item.persentase }}%</span>
                                </span>
                            </td>

                            <td class="py-3 px-4 text-xs text-muted-foreground max-w-xs truncate" :title="item.keterangan || ''">
                                {{ item.keterangan || '-' }}
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="size-8">
                                            <MoreHorizontal class="size-4" />
                                            <span class="sr-only">Buka menu aksi</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-44">
                                        <DropdownMenuLabel class="text-xs">Aksi Realisasi</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem class="gap-2 cursor-pointer text-xs" @click="openDetailModal(item)">
                                            <Eye class="size-3.5 text-muted-foreground" />
                                            <span>Lihat Detail</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="gap-2 cursor-pointer text-xs" @click="openEditModal(item)">
                                            <Pencil class="size-3.5 text-muted-foreground" />
                                            <span>Ubah Data</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            class="gap-2 cursor-pointer text-xs text-red-600 focus:text-red-600 dark:text-red-400 dark:focus:text-red-400"
                                            @click="openDeleteDialog(item)"
                                        >
                                            <Trash2 class="size-3.5" />
                                            <span>Hapus Data</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                v-if="realisasiPnbp.data.length > 0"
                class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-border/70 text-xs text-muted-foreground"
            >
                <div>
                    Menampilkan
                    <strong class="font-semibold text-foreground">{{ realisasiPnbp.from || 0 }}</strong>
                    sampai
                    <strong class="font-semibold text-foreground">{{ realisasiPnbp.to || 0 }}</strong>
                    dari
                    <strong class="font-semibold text-foreground">{{ realisasiPnbp.total }}</strong>
                    data
                </div>

                <div class="flex items-center gap-1.5">
                    <template v-for="(link, i) in realisasiPnbp.links" :key="i">
                        <Button
                            v-if="link.url"
                            variant="outline"
                            size="sm"
                            :class="[
                                'h-8 min-w-8 px-2.5 text-xs',
                                link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90 font-bold' : '',
                            ]"
                            as-child
                        >
                            <Link :href="link.url" preserve-scroll preserve-state>
                                <span v-html="link.label"></span>
                            </Link>
                        </Button>
                        <span
                            v-else
                            class="inline-flex h-8 min-w-8 items-center justify-center px-2.5 text-xs text-muted-foreground/60 border border-transparent select-none"
                            v-html="link.label"
                        ></span>
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
