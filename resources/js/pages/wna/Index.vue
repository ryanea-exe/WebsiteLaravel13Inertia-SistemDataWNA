<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CreditCard,
    Eye,
    FileSpreadsheet,
    FileText,
    Filter,
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
import WnaDeleteDialog from '@/components/wna/WnaDeleteDialog.vue';
import WnaDetailModal from '@/components/wna/WnaDetailModal.vue';
import WnaFormModal from '@/components/wna/WnaFormModal.vue';
import type { PaginatedWna, WnaFilters, WnaItem } from '@/types/wna';

const props = defineProps<{
    wna: PaginatedWna;
    filters: WnaFilters;
    kewarganegaraanOptions: string[];
    jenisIzinTinggalOptions: { value: string; label: string }[];
    genderOptions: { value: string; label: string }[];
    can: {
        create: boolean;
        printList: boolean;
        export?: boolean;
    };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success);
const flashError = computed(() => (page.props.flash as { error?: string })?.error);

// Local filter states
const search = ref(props.filters.search || '');
const selectedJenisIzin = ref(props.filters.jenis_izin_tinggal || '');
const selectedKewarganegaraan = ref(props.filters.kewarganegaraan || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 10);

// Modal states
const isFormModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const activeWna = ref<WnaItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/wna',
        {
            search: search.value || undefined,
            jenis_izin_tinggal: selectedJenisIzin.value || undefined,
            kewarganegaraan: selectedKewarganegaraan.value || undefined,
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

watch([selectedJenisIzin, selectedKewarganegaraan, perPage], () => {
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
    selectedJenisIzin.value = '';
    selectedKewarganegaraan.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return !!(search.value || selectedJenisIzin.value || selectedKewarganegaraan.value);
});

const printListUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedJenisIzin.value) params.set('jenis_izin_tinggal', selectedJenisIzin.value);
    if (selectedKewarganegaraan.value) params.set('kewarganegaraan', selectedKewarganegaraan.value);
    const query = params.toString();
    return query ? `/wna/print-list?${query}` : '/wna/print-list';
});

const exportCsvUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedJenisIzin.value) params.set('jenis_izin_tinggal', selectedJenisIzin.value);
    if (selectedKewarganegaraan.value) params.set('kewarganegaraan', selectedKewarganegaraan.value);
    if (sortBy.value) params.set('sort_by', sortBy.value);
    if (sortOrder.value) params.set('sort_order', sortOrder.value);
    const query = params.toString();
    return query ? `/wna/export?${query}` : '/wna/export';
});

// Modal triggers
function openCreateModal() {
    activeWna.value = null;
    isFormModalOpen.value = true;
}

function openEditModal(item: WnaItem) {
    activeWna.value = item;
    isFormModalOpen.value = true;
}

function openDetailModal(item: WnaItem) {
    activeWna.value = item;
    isDetailModalOpen.value = true;
}

function openDeleteDialog(item: WnaItem) {
    activeWna.value = item;
    isDeleteDialogOpen.value = true;
}

function formatIndoDate(dateStr?: string): string {
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
    <Head title="Data WNA" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Header Halaman -->
        <PageHeader
            title="Data Warga Negara Asing (WNA)"
            description="Pengelolaan arsip, data paspor, pemantauan masa berlaku izin tinggal, dan kartu identitas WNA."
        >
            <template #actions>
                <Button
                    v-if="can.export ?? can.printList"
                    variant="outline"
                    class="gap-1.5 shadow-xs"
                    as-child
                >
                    <a :href="exportCsvUrl" download>
                        <FileSpreadsheet class="size-4" />
                        <span>Ekspor CSV</span>
                    </a>
                </Button>

                <Button
                    v-if="can.printList"
                    variant="outline"
                    class="gap-1.5 shadow-xs"
                    as-child
                >
                    <a :href="printListUrl" target="_blank">
                        <Printer class="size-4" />
                        <span>Cetak Daftar</span>
                    </a>
                </Button>

                <Button
                    v-if="can.create"
                    class="gap-1.5 shadow-xs bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500"
                    @click="openCreateModal"
                >
                    <Plus class="size-4" />
                    <span>Tambah Data WNA</span>
                </Button>
            </template>
        </PageHeader>

        <!-- Flash Alert Feedback -->
        <div
            v-if="flashSuccess"
            class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-950/60 dark:bg-emerald-950/30 dark:text-emerald-300"
        >
            <div class="flex items-center gap-2">
                <span class="font-medium">{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400"
                @click="flashSuccess = ''"
            >
                <X class="size-4" />
            </button>
        </div>

        <div
            v-if="flashError"
            class="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-950/60 dark:bg-red-950/30 dark:text-red-300"
        >
            <div class="flex items-center gap-2">
                <AlertCircle class="size-4" />
                <span class="font-medium">{{ flashError }}</span>
            </div>
            <button
                type="button"
                class="text-red-600 hover:text-red-800 dark:text-red-400"
                @click="flashError = ''"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
            <div class="grid gap-3 md:grid-cols-12">
                <!-- Search Input -->
                <div class="relative md:col-span-5">
                    <Search class="absolute left-3 top-2.5 size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Cari berdasarkan nama, paspor, atau no. register..."
                        class="pl-9 pr-8"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-2.5 top-2.5 text-muted-foreground hover:text-foreground"
                        @click="search = ''"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <!-- Filter Jenis Izin Tinggal -->
                <div class="md:col-span-3">
                    <select
                        v-model="selectedJenisIzin"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-background"
                    >
                        <option value="">Semua Izin Tinggal</option>
                        <option
                            v-for="opt in jenisIzinTinggalOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <!-- Filter Kewarganegaraan -->
                <div class="md:col-span-3">
                    <select
                        v-model="selectedKewarganegaraan"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-background"
                    >
                        <option value="">Semua Kewarganegaraan</option>
                        <option
                            v-for="item in kewarganegaraanOptions"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>
                    </select>
                </div>

                <!-- Reset Filters Button -->
                <div class="flex items-center md:col-span-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Reset Filter"
                        :disabled="!hasActiveFilters"
                        class="size-9 shrink-0"
                        @click="resetFilters"
                    >
                        <RefreshCw class="size-4" :class="{ 'animate-spin': false }" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tabel Data WNA -->
        <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border/80 bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('no_register')">
                                <div class="flex items-center gap-1.5">
                                    <span>No. Register</span>
                                    <span v-if="sortBy === 'no_register'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('nama')">
                                <div class="flex items-center gap-1.5">
                                    <span>Identitas WNA</span>
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
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('kewarganegaraan')">
                                <div class="flex items-center gap-1.5">
                                    <span>Kewarganegaraan</span>
                                    <span v-if="sortBy === 'kewarganegaraan'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('jenis_izin_tinggal')">
                                <div class="flex items-center gap-1.5">
                                    <span>Izin Tinggal</span>
                                    <span v-if="sortBy === 'jenis_izin_tinggal'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('masa_berlaku')">
                                <div class="flex items-center gap-1.5">
                                    <span>Masa Berlaku</span>
                                    <span v-if="sortBy === 'masa_berlaku'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3">Penjamin</th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <!-- Empty State -->
                        <tr v-if="wna.data.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                    <UserIcon class="size-6" />
                                </div>
                                <h3 class="text-sm font-semibold text-foreground">Tidak Ada Data WNA</h3>
                                <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                                    {{ hasActiveFilters ? 'Tidak ada data WNA yang cocok dengan kriteria pencarian dan filter.' : 'Belum ada data WNA yang terdaftar dalam sistem.' }}
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

                        <!-- Row Item -->
                        <tr
                            v-for="item in wna.data"
                            :key="item.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <!-- No. Register -->
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-foreground whitespace-nowrap">
                                {{ item.no_register }}
                            </td>

                            <!-- Foto & Nama -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-9 shrink-0 overflow-hidden rounded-full border border-border bg-muted">
                                        <img
                                            v-if="item.foto_url"
                                            :src="item.foto_url"
                                            :alt="item.nama"
                                            class="size-full object-cover"
                                        />
                                        <div v-else class="flex size-full items-center justify-center bg-secondary text-muted-foreground">
                                            <UserIcon class="size-4" />
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <button
                                            type="button"
                                            class="font-medium text-foreground hover:text-indigo-600 dark:hover:text-indigo-400 text-left transition-colors truncate block max-w-[200px]"
                                            @click="openDetailModal(item)"
                                        >
                                            {{ item.nama }}
                                        </button>
                                        <span class="text-xs text-muted-foreground block">
                                            {{ item.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- No. Paspor -->
                            <td class="px-4 py-3 font-mono text-xs text-foreground whitespace-nowrap">
                                {{ item.no_paspor }}
                            </td>

                            <!-- Kewarganegaraan -->
                            <td class="px-4 py-3 text-xs text-foreground whitespace-nowrap">
                                {{ item.kewarganegaraan }}
                            </td>

                            <!-- Jenis Izin Tinggal -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold"
                                    :class="{
                                        'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300': item.jenis_izin_tinggal === 'ITAS',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300': item.jenis_izin_tinggal === 'ITAP',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': item.jenis_izin_tinggal === 'VOA',
                                    }"
                                >
                                    {{ item.jenis_izin_tinggal }}
                                </span>
                            </td>

                            <!-- Masa Berlaku & Urgensi -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs font-medium text-foreground">
                                    {{ formatIndoDate(item.masa_berlaku) }}
                                </div>
                                <div class="mt-0.5">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.2 text-[10px] font-medium"
                                        :class="{
                                            'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300': item.urgensi === 'kedaluwarsa',
                                            'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300': item.urgensi === 'merah',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': item.urgensi === 'kuning',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': item.urgensi === 'biru',
                                        }"
                                    >
                                        {{ item.sisa_hari < 0 ? 'Kedaluwarsa' : `${item.sisa_hari} hari` }}
                                    </span>
                                </div>
                            </td>

                            <!-- Penjamin -->
                            <td class="px-4 py-3 text-xs text-muted-foreground max-w-[150px] truncate">
                                {{ item.penjamin || '-' }}
                            </td>

                            <!-- Aksi Menu -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Shortcut Cetak Kartu (Admin & Petugas) -->
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-8"
                                        title="Cetak Kartu WNA"
                                        as-child
                                    >
                                        <Link :href="`/wna/${item.id}/card`">
                                            <CreditCard class="size-4 text-muted-foreground hover:text-indigo-600 dark:hover:text-indigo-400" />
                                        </Link>
                                    </Button>

                                    <!-- Dropdown Menu Aksi -->
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="size-8">
                                                <MoreHorizontal class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-44">
                                            <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                                            <DropdownMenuItem @click="openDetailModal(item)">
                                                <Eye class="mr-2 size-4" />
                                                <span>Lihat Detail</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="`/wna/${item.id}/card`">
                                                    <CreditCard class="mr-2 size-4" />
                                                    <span>Cetak Kartu</span>
                                                </Link>
                                            </DropdownMenuItem>

                                            <template v-if="can.create">
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="openEditModal(item)">
                                                    <Pencil class="mr-2 size-4 text-indigo-600" />
                                                    <span>Ubah Data</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    class="text-red-600 focus:bg-red-50 focus:text-red-700 dark:focus:bg-red-950/30"
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
                <div class="flex items-center gap-2">
                    <span>Menampilkan</span>
                    <span class="font-medium text-foreground">{{ wna.from || 0 }} - {{ wna.to || 0 }}</span>
                    <span>dari</span>
                    <span class="font-medium text-foreground">{{ wna.total }}</span>
                    <span>data</span>

                    <span class="mx-2 text-border">|</span>

                    <span>Baris per halaman:</span>
                    <select
                        v-model="perPage"
                        class="rounded border border-input bg-transparent px-2 py-0.5 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-ring dark:bg-background"
                    >
                        <option :value="10">10</option>
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <!-- Page Links -->
                <div v-if="wna.links && wna.links.length > 3" class="flex items-center gap-1">
                    <template v-for="(link, index) in wna.links" :key="index">
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
    </div>

    <!-- Modals -->
    <WnaFormModal
        :open="isFormModalOpen"
        :wna="activeWna"
        :kewarganegaraan-options="kewarganegaraanOptions"
        :jenis-izin-tinggal-options="jenisIzinTinggalOptions"
        :gender-options="genderOptions"
        @update:open="(val) => (isFormModalOpen = val)"
    />

    <WnaDetailModal
        :open="isDetailModalOpen"
        :wna="activeWna"
        @update:open="(val) => (isDetailModalOpen = val)"
    />

    <WnaDeleteDialog
        :open="isDeleteDialogOpen"
        :wna="activeWna"
        @update:open="(val) => (isDeleteDialogOpen = val)"
    />
</template>
