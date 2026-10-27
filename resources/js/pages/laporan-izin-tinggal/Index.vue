<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileDown,
    FileSpreadsheet,
    FileText,
    Filter,
    Layers,
    MoreHorizontal,
    Pencil,
    Plus,
    Printer,
    RefreshCw,
    Search,
    Trash2,
    X,
    XCircle,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import LaporanDeleteDialog from '@/components/laporan-izin-tinggal/LaporanDeleteDialog.vue';
import LaporanDetailModal from '@/components/laporan-izin-tinggal/LaporanDetailModal.vue';
import LaporanFormModal from '@/components/laporan-izin-tinggal/LaporanFormModal.vue';
import type {
    LaporanIzinTinggalFilters,
    LaporanIzinTinggalItem,
    LaporanIzinTinggalSummary,
    PaginatedLaporanIzinTinggal,
} from '@/types/laporan-izin-tinggal';

const props = defineProps<{
    laporan: PaginatedLaporanIzinTinggal;
    summary: LaporanIzinTinggalSummary;
    filters: LaporanIzinTinggalFilters;
    kategoriOptions: string[];
    layananOptions: string[];
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
const selectedKategori = ref(props.filters.kategori || '');
const selectedLayanan = ref(props.filters.layanan || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 50);

// Modal states
const isFormModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const activeLaporan = ref<LaporanIzinTinggalItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/laporan-izin-tinggal',
        {
            search: search.value || undefined,
            kategori: selectedKategori.value || undefined,
            layanan: selectedLayanan.value || undefined,
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

function handleFilterChange() {
    applyFilters();
}

function handleSort(column: string) {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = 'asc';
    }
    applyFilters();
}

function resetFilters() {
    search.value = '';
    selectedKategori.value = '';
    selectedLayanan.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    perPage.value = 50;
    router.get('/laporan-izin-tinggal', {}, { preserveState: true, preserveScroll: true });
}

const isFiltered = computed(() => {
    return !!(search.value || selectedKategori.value || selectedLayanan.value);
});

function openCreateModal() {
    activeLaporan.value = null;
    isFormModalOpen.value = true;
}

function openEditModal(item: LaporanIzinTinggalItem) {
    activeLaporan.value = item;
    isFormModalOpen.value = true;
}

function openDetailModal(item: LaporanIzinTinggalItem) {
    activeLaporan.value = item;
    isDetailModalOpen.value = true;
}

function openDeleteModal(item: LaporanIzinTinggalItem) {
    activeLaporan.value = item;
    isDeleteDialogOpen.value = true;
}

const printListUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedKategori.value) params.set('kategori', selectedKategori.value);
    if (selectedLayanan.value) params.set('layanan', selectedLayanan.value);
    const queryString = params.toString();
    return queryString ? `/laporan-izin-tinggal/print-list?${queryString}` : '/laporan-izin-tinggal/print-list';
});

function formatIndoMonth(dateStr?: string | null): string {
    if (!dateStr) return '—';
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
</script>

<template>
    <Head title="Laporan Izin Tinggal" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <!-- Header Section -->
        <PageHeader
            title="Laporan Izin Tinggal"
            description="Rekapitulasi data statistik persetujuan dan penolakan layanan izin tinggal keimigrasian."
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
                        <span>Tambah Laporan</span>
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

        <!-- Ringkasan Statistik Data (Summary Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Disetujui -->
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/40 p-4 shadow-2xs dark:border-emerald-950/60 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                        Total Disetujui
                    </span>
                    <CheckCircle2 class="size-4 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold tracking-tight text-emerald-950 dark:text-emerald-100 tabular-nums">
                        {{ summary.grand_total_disetujui.toLocaleString('id-ID') }}
                    </span>
                    <span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400">
                        L: {{ summary.total_disetujui_l }} | P: {{ summary.total_disetujui_p }}
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    {{ isFiltered ? 'Hasil filter aktif' : 'Akumulasi keseluruhan' }}
                </p>
            </div>

            <!-- Card 2: Total Ditolak -->
            <div class="rounded-xl border border-rose-200/80 bg-rose-50/40 p-4 shadow-2xs dark:border-rose-950/60 dark:bg-rose-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-rose-800 dark:text-rose-300 uppercase tracking-wider">
                        Total Ditolak
                    </span>
                    <XCircle class="size-4 text-rose-600 dark:text-rose-400" />
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold tracking-tight text-rose-950 dark:text-rose-100 tabular-nums">
                        {{ summary.grand_total_ditolak.toLocaleString('id-ID') }}
                    </span>
                    <span class="text-[11px] font-medium text-rose-700 dark:text-rose-400">
                        L: {{ summary.total_ditolak_l }} | P: {{ summary.total_ditolak_p }}
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    {{ isFiltered ? 'Hasil filter aktif' : 'Akumulasi penolakan' }}
                </p>
            </div>

            <!-- Card 3: Grand Total Permohonan -->
            <div class="rounded-xl border border-indigo-200/80 bg-indigo-50/40 p-4 shadow-2xs dark:border-indigo-950/60 dark:bg-indigo-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-900 dark:text-indigo-300 uppercase tracking-wider">
                        Grand Total
                    </span>
                    <FileSpreadsheet class="size-4 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold tracking-tight text-indigo-950 dark:text-indigo-100 tabular-nums">
                        {{ summary.grand_total.toLocaleString('id-ID') }}
                    </span>
                    <span class="text-[11px] font-medium text-indigo-700 dark:text-indigo-400">
                        Pemohon
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    Total disetujui & ditolak
                </p>
            </div>

            <!-- Card 4: Total Record Kategori -->
            <div class="rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        Total Baris Data
                    </span>
                    <Layers class="size-4 text-muted-foreground" />
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold tracking-tight text-foreground tabular-nums">
                        {{ summary.total_records.toLocaleString('id-ID') }}
                    </span>
                    <span class="text-[11px] font-medium text-muted-foreground">
                        Entri Layanan
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    Terdaftar di sistem
                </p>
            </div>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <!-- Search Input -->
                <div class="relative md:col-span-5">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Cari kategori, layanan, atau keterangan..."
                        class="pl-9 text-xs sm:text-sm"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                        @click="search = ''"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <!-- Filter Kategori -->
                <div class="md:col-span-3">
                    <select
                        v-model="selectedKategori"
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring"
                        @change="handleFilterChange"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="kat in kategoriOptions" :key="kat" :value="kat">
                            {{ kat }}
                        </option>
                    </select>
                </div>

                <!-- Filter Layanan -->
                <div class="md:col-span-3">
                    <select
                        v-model="selectedLayanan"
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring"
                        @change="handleFilterChange"
                    >
                        <option value="">Semua Layanan</option>
                        <option v-for="lay in layananOptions" :key="lay" :value="lay">
                            {{ lay }}
                        </option>
                    </select>
                </div>

                <!-- Reset Filter -->
                <div class="flex items-center md:col-span-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Reset Filter"
                        :disabled="!isFiltered"
                        class="size-9 shrink-0"
                        @click="resetFilters"
                    >
                        <RefreshCw class="size-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tabel Data Laporan Izin Tinggal -->
        <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left">
                    <thead class="border-b border-border/80 bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-center w-12">No</th>
                            <th
                                scope="col"
                                class="px-4 py-3 cursor-pointer select-none"
                                @click="handleSort('bulan')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Bulan</span>
                                    <span v-if="sortBy === 'bulan'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th
                                scope="col"
                                class="px-4 py-3 cursor-pointer select-none"
                                @click="handleSort('kategori')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Kategori Izin Tinggal</span>
                                    <span v-if="sortBy === 'kategori'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th
                                scope="col"
                                class="px-4 py-3 cursor-pointer select-none"
                                @click="handleSort('layanan')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Jenis Layanan</span>
                                    <span v-if="sortBy === 'layanan'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3 text-center bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-300">
                                Disetujui (L)
                            </th>
                            <th scope="col" class="px-3 py-3 text-center bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-300">
                                Disetujui (P)
                            </th>
                            <th scope="col" class="px-3 py-3 text-center bg-emerald-100/60 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-200 font-bold">
                                Total Disetujui
                            </th>
                            <th scope="col" class="px-3 py-3 text-center bg-rose-50/50 dark:bg-rose-950/20 text-rose-900 dark:text-rose-300">
                                Ditolak (L)
                            </th>
                            <th scope="col" class="px-3 py-3 text-center bg-rose-50/50 dark:bg-rose-950/20 text-rose-900 dark:text-rose-300">
                                Ditolak (P)
                            </th>
                            <th scope="col" class="px-3 py-3 text-center bg-rose-100/60 dark:bg-rose-950/40 text-rose-950 dark:text-rose-200 font-bold">
                                Total Ditolak
                            </th>
                            <th scope="col" class="px-3 py-3 text-center font-bold text-foreground bg-muted/30">
                                Grand Total
                            </th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <tr
                            v-for="(item, index) in laporan.data"
                            :key="item.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-center text-xs text-muted-foreground font-mono">
                                {{ (laporan.current_page - 1) * laporan.per_page + index + 1 }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground whitespace-nowrap text-xs font-medium">
                                <div class="flex items-center gap-1.5">
                                    <Calendar class="size-3.5 text-muted-foreground shrink-0" />
                                    <span>{{ formatIndoMonth(item.bulan) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-foreground">
                                <span class="inline-flex items-center gap-1.5">
                                    <Layers class="size-3.5 text-primary shrink-0" />
                                    <span>{{ item.kategori }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-medium text-foreground">
                                {{ item.layanan }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums text-emerald-800 dark:text-emerald-300 bg-emerald-50/20 dark:bg-emerald-950/10">
                                {{ item.disetujui_l }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums text-emerald-800 dark:text-emerald-300 bg-emerald-50/20 dark:bg-emerald-950/10">
                                {{ item.disetujui_p }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums font-bold text-emerald-900 dark:text-emerald-200 bg-emerald-100/30 dark:bg-emerald-950/30">
                                {{ item.total_disetujui }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums text-rose-800 dark:text-rose-300 bg-rose-50/20 dark:bg-rose-950/10">
                                {{ item.ditolak_l }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums text-rose-800 dark:text-rose-300 bg-rose-50/20 dark:bg-rose-950/10">
                                {{ item.ditolak_p }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums font-bold text-rose-900 dark:text-rose-200 bg-rose-100/30 dark:bg-rose-950/30">
                                {{ item.total_ditolak }}
                            </td>
                            <td class="px-3 py-3 text-center text-xs tabular-nums font-bold text-primary bg-muted/20">
                                {{ item.total }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="size-8"
                                            >
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
                                                    @click="openDeleteModal(item)"
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

                        <!-- Empty State -->
                        <tr v-if="laporan.data.length === 0">
                            <td colspan="12" class="px-4 py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                    <FileSpreadsheet class="size-6" />
                                </div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Tidak Ada Data Rekapitulasi Izin Tinggal
                                </h3>
                                <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                                    {{
                                        isFiltered
                                            ? 'Tidak ada data laporan yang cocok dengan kriteria filter atau pencarian Anda.'
                                            : 'Belum ada data laporan izin tinggal yang tercatat. Silakan tambah data baru.'
                                    }}
                                </p>
                                <Button
                                    v-if="isFiltered"
                                    variant="outline"
                                    size="sm"
                                    class="mt-4"
                                    @click="resetFilters"
                                >
                                    Reset Filter
                                </Button>
                                <Button
                                    v-else-if="can.create"
                                    size="sm"
                                    class="mt-4 gap-1.5"
                                    @click="openCreateModal"
                                >
                                    <Plus class="size-4" />
                                    <span>Tambah Data Sekarang</span>
                                </Button>
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
                        <strong class="font-medium text-foreground">{{ laporan.from || 0 }}</strong>
                        sampai
                        <strong class="font-medium text-foreground">{{ laporan.to || 0 }}</strong>
                        dari
                        <strong class="font-medium text-foreground">{{ laporan.total }}</strong>
                        data
                    </span>

                    <div class="flex items-center gap-1.5">
                        <span>Per baris:</span>
                        <select
                            v-model="perPage"
                            class="h-7 rounded border border-input bg-transparent px-2 text-xs shadow-2xs"
                            @change="handleFilterChange"
                        >
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Page Links -->
                <div v-if="laporan.links && laporan.links.length > 3" class="flex items-center gap-1">
                    <template v-for="(link, index) in laporan.links" :key="index">
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
        <LaporanFormModal
            v-model:open="isFormModalOpen"
            :laporan="activeLaporan"
            :kategori-options="kategoriOptions"
            :layanan-options="layananOptions"
        />

        <LaporanDetailModal
            v-model:open="isDetailModalOpen"
            :laporan="activeLaporan"
        />

        <LaporanDeleteDialog
            v-model:open="isDeleteDialogOpen"
            :laporan="activeLaporan"
        />
    </div>
</template>
