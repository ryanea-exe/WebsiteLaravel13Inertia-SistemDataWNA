<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileDown,
    FileText,
    Filter,
    MapPin,
    MoreHorizontal,
    Pencil,
    Plus,
    Printer,
    RefreshCw,
    Search,
    Trash2,
    User as UserIcon,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import FlashAlert from '@/components/FlashAlert.vue';
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
import PermohonanDeleteDialog from '@/components/permohonan/PermohonanDeleteDialog.vue';
import PermohonanDetailModal from '@/components/permohonan/PermohonanDetailModal.vue';
import PermohonanFormModal from '@/components/permohonan/PermohonanFormModal.vue';
import type {
    PaginatedPermohonan,
    PermohonanFilters,
    PermohonanItem,
} from '@/types/permohonan';

const props = defineProps<{
    permohonan: PaginatedPermohonan;
    filters: PermohonanFilters;
    layananOptions: string[];
    kewarganegaraanOptions: string[];
    kabupatenOptions: string[];
    can: {
        create: boolean;
        printList: boolean;
    };
}>();

const page = usePage();

// Local filter states
const search = ref(props.filters.search || '');
const selectedLayanan = ref(props.filters.layanan || '');
const selectedKewarganegaraan = ref(props.filters.kewarganegaraan || '');
const selectedKabupaten = ref(props.filters.kabupaten || '');
const sortBy = ref(props.filters.sort_by || '');
const sortOrder = ref<'asc' | 'desc'>((props.filters.sort_order as 'asc' | 'desc') || 'desc');
const perPage = ref(props.filters.per_page || 50);

// Modal states
const isFormModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const activePermohonan = ref<PermohonanItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/permohonan',
        {
            search: search.value || undefined,
            layanan: selectedLayanan.value || undefined,
            kewarganegaraan: selectedKewarganegaraan.value || undefined,
            kabupaten: selectedKabupaten.value || undefined,
            sort_by: sortBy.value || undefined,
            sort_order: sortBy.value ? sortOrder.value : undefined,
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

watch([selectedLayanan, selectedKewarganegaraan, selectedKabupaten, perPage], () => {
    applyFilters();
});

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
    selectedLayanan.value = '';
    selectedKewarganegaraan.value = '';
    selectedKabupaten.value = '';
    sortBy.value = '';
    sortOrder.value = 'desc';
    perPage.value = 50;
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return (
        search.value !== '' ||
        selectedLayanan.value !== '' ||
        selectedKewarganegaraan.value !== '' ||
        selectedKabupaten.value !== '' ||
        !!sortBy.value ||
        perPage.value !== 50
    );
});

function openCreateModal() {
    activePermohonan.value = null;
    isFormModalOpen.value = true;
}

function openEditModal(item: PermohonanItem) {
    activePermohonan.value = item;
    isFormModalOpen.value = true;
}

function openDetailModal(item: PermohonanItem) {
    activePermohonan.value = item;
    isDetailModalOpen.value = true;
}

function openDeleteDialog(item: PermohonanItem) {
    activePermohonan.value = item;
    isDeleteDialogOpen.value = true;
}

const printListUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedLayanan.value) params.set('layanan', selectedLayanan.value);
    if (selectedKewarganegaraan.value) params.set('kewarganegaraan', selectedKewarganegaraan.value);
    if (selectedKabupaten.value) params.set('kabupaten', selectedKabupaten.value);
    const queryString = params.toString();
    return queryString ? `/permohonan/print-list?${queryString}` : '/permohonan/print-list';
});

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
    <Head title="Permohonan Keimigrasian" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
            <!-- Header Section -->
            <PageHeader
                title="Permohonan Keimigrasian"
                description="Kelola dan pantau seluruh berkas pendaftaran permohonan keimigrasian secara terpadu."
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
                            <span>Tambah Permohonan</span>
                        </Button>
                    </div>
                </template>
            </PageHeader>

            <!-- Flash Feedback Messages -->
            <FlashAlert />

            <!-- Filter & Search Toolbar -->
            <div class="rounded-xl border border-border/70 bg-card p-4 shadow-xs">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                    <!-- Search Input -->
                    <div class="relative md:col-span-4">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama, paspor, layanan..."
                            class="pl-9 text-xs sm:text-sm h-9"
                        />
                        <button
                            v-if="search"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="search = ''"
                        >
                            <X class="size-3.5" />
                        </button>
                    </div>

                    <!-- Filter Jenis Layanan -->
                    <div class="relative md:col-span-3">
                        <select
                            v-model="selectedLayanan"
                            class="h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-8 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer dark:bg-card"
                        >
                            <option value="">Semua Jenis Layanan</option>
                            <option v-for="item in layananOptions" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    </div>

                    <!-- Filter Kewarganegaraan -->
                    <div class="relative md:col-span-2">
                        <select
                            v-model="selectedKewarganegaraan"
                            class="h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-8 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer dark:bg-card"
                        >
                            <option value="">Semua Kewarganegaraan</option>
                            <option v-for="item in kewarganegaraanOptions" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    </div>

                    <!-- Filter Kabupaten -->
                    <div class="relative md:col-span-2">
                        <select
                            v-model="selectedKabupaten"
                            class="h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-8 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer dark:bg-card"
                        >
                            <option value="">Semua Kabupaten</option>
                            <option v-for="item in kabupatenOptions" :key="item" :value="item">
                                {{ item }}
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

            <!-- Tabel Data Permohonan -->
            <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border/80 bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-12 text-center">No</th>
                                <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('tanggal')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Tanggal</span>
                                        <span v-if="sortBy === 'tanggal'" class="text-foreground">
                                            {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('nama')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Nama Pemohon</span>
                                        <span v-if="sortBy === 'nama'" class="text-foreground">
                                            {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('no_paspor')">
                                    <div class="flex items-center gap-1.5">
                                        <span>No. Paspor</span>
                                        <span v-if="sortBy === 'no_paspor'" class="text-foreground">
                                            {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('layanan')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Layanan</span>
                                        <span v-if="sortBy === 'layanan'" class="text-foreground">
                                            {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('kewarganegaraan')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kewarganegaraan</span>
                                        <span v-if="sortBy === 'kewarganegaraan'" class="text-foreground">
                                            {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('kabupaten')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kabupaten</span>
                                        <span v-if="sortBy === 'kabupaten'" class="text-foreground">
                                            {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <!-- Empty State -->
                            <tr v-if="permohonan.data.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                        <FileText class="size-6" />
                                    </div>
                                    <h3 class="text-sm font-semibold text-foreground">Tidak Ada Data Permohonan</h3>
                                    <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                                        {{
                                            hasActiveFilters
                                                ? 'Tidak ditemukan data permohonan yang sesuai dengan filter pencarian Anda.'
                                                : 'Belum ada data permohonan keimigrasian yang tercatat dalam sistem.'
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
                                v-for="(item, index) in permohonan.data"
                                :key="item.id"
                                class="transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                    {{ (permohonan.from || 1) + index }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap text-xs text-foreground font-medium">
                                    {{ formatIndoDate(item.tanggal) }}
                                </td>

                                <td class="px-4 py-3 font-semibold text-foreground">
                                    {{ item.nama }}
                                </td>

                                <td class="px-4 py-3 font-mono text-xs text-foreground whitespace-nowrap">
                                    {{ item.no_paspor || '-' }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-900/60">
                                        {{ item.layanan }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-xs text-muted-foreground">
                                    {{ item.kewarganegaraan }}
                                </td>

                                <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ item.kabupaten || '-' }}
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
                        <strong class="font-medium text-foreground">{{ permohonan.from || 0 }}</strong>
                        sampai
                        <strong class="font-medium text-foreground">{{ permohonan.to || 0 }}</strong>
                        dari
                        <strong class="font-medium text-foreground">{{ permohonan.total }}</strong>
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
                    <div v-if="permohonan.links && permohonan.links.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, index) in permohonan.links" :key="index">
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
        <PermohonanFormModal
            v-model:open="isFormModalOpen"
            :permohonan="activePermohonan"
            :layanan-options="layananOptions"
            :kewarganegaraan-options="kewarganegaraanOptions"
            :kabupaten-options="kabupatenOptions"
        />

        <PermohonanDetailModal
            v-model:open="isDetailModalOpen"
            :permohonan="activePermohonan"
        />

        <PermohonanDeleteDialog
            v-model:open="isDeleteDialogOpen"
            :permohonan="activePermohonan"
        />
    </div>
</template>
