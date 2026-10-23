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
const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success);
const flashError = computed(() => (page.props.flash as { error?: string })?.error);

// Local filter states
const search = ref(props.filters.search || '');
const selectedLayanan = ref(props.filters.layanan || '');
const selectedKewarganegaraan = ref(props.filters.kewarganegaraan || '');
const selectedKabupaten = ref(props.filters.kabupaten || '');
const sortBy = ref(props.filters.sort_by || 'tanggal');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 10);

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
    sortBy.value = 'tanggal';
    sortOrder.value = 'desc';
    perPage.value = 10;
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return (
        search.value !== '' ||
        selectedLayanan.value !== '' ||
        selectedKewarganegaraan.value !== '' ||
        selectedKabupaten.value !== '' ||
        perPage.value !== 10
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

            <!-- Filter & Search Toolbar -->
            <div class="rounded-xl border border-border/70 bg-card p-4 shadow-xs space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="relative lg:col-span-1">
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
                    <div>
                        <select
                            v-model="selectedLayanan"
                            class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option value="">Semua Jenis Layanan</option>
                            <option v-for="item in layananOptions" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Kewarganegaraan -->
                    <div>
                        <select
                            v-model="selectedKewarganegaraan"
                            class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option value="">Semua Kewarganegaraan</option>
                            <option v-for="item in kewarganegaraanOptions" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Kabupaten -->
                    <div>
                        <select
                            v-model="selectedKabupaten"
                            class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option value="">Semua Kabupaten</option>
                            <option v-for="item in kabupatenOptions" :key="item" :value="item">
                                {{ item }}
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
                        <span>per halaman (Total: <strong class="text-foreground font-semibold">{{ permohonan.total }}</strong> permohonan)</span>
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

            <!-- Tabel Data Permohonan -->
            <div class="rounded-xl border border-border/70 bg-card shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-muted/40 text-xs font-semibold text-muted-foreground">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('tanggal')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Tanggal</span>
                                        <ChevronDown v-if="sortBy === 'tanggal'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                    </div>
                                </th>
                                <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('nama')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Nama Pemohon</span>
                                        <ChevronDown v-if="sortBy === 'nama'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                    </div>
                                </th>
                                <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('no_paspor')">
                                    <div class="flex items-center gap-1.5">
                                        <span>No. Paspor</span>
                                        <ChevronDown v-if="sortBy === 'no_paspor'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                    </div>
                                </th>
                                <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('layanan')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Layanan</span>
                                        <ChevronDown v-if="sortBy === 'layanan'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                    </div>
                                </th>
                                <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('kewarganegaraan')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kewarganegaraan</span>
                                        <ChevronDown v-if="sortBy === 'kewarganegaraan'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                    </div>
                                </th>
                                <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('kabupaten')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kabupaten</span>
                                        <ChevronDown v-if="sortBy === 'kabupaten'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                    </div>
                                </th>
                                <th class="py-3 px-4">Rincian Permohonan</th>
                                <th class="py-3 px-4 text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <!-- Empty State -->
                            <tr v-if="permohonan.data.length === 0">
                                <td colspan="9" class="py-12 text-center text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <FileText class="size-10 text-muted-foreground/40" />
                                        <p class="font-medium text-base text-foreground">Tidak ada data permohonan</p>
                                        <p class="text-xs text-muted-foreground max-w-sm">
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
                                v-for="(item, index) in permohonan.data"
                                :key="item.id"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <td class="py-3 px-4 text-center text-xs text-muted-foreground">
                                    {{ (permohonan.from || 1) + index }}
                                </td>

                                <td class="py-3 px-4 whitespace-nowrap text-xs text-foreground font-medium">
                                    {{ formatIndoDate(item.tanggal) }}
                                </td>

                                <td class="py-3 px-4 font-semibold text-foreground">
                                    {{ item.nama }}
                                </td>

                                <td class="py-3 px-4 font-mono text-xs text-muted-foreground whitespace-nowrap">
                                    {{ item.no_paspor || '-' }}
                                </td>

                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-900/60">
                                        {{ item.layanan }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-xs text-muted-foreground">
                                    {{ item.kewarganegaraan }}
                                </td>

                                <td class="py-3 px-4 text-xs text-muted-foreground whitespace-nowrap">
                                    {{ item.kabupaten || '-' }}
                                </td>

                                <td class="py-3 px-4 text-xs text-muted-foreground max-w-xs truncate" :title="item.permohonan || ''">
                                    {{ item.permohonan || '-' }}
                                </td>

                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <Button
                                        v-if="!can.create"
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 gap-1 text-xs text-muted-foreground hover:text-foreground"
                                        @click="openDetailModal(item)"
                                    >
                                        <Eye class="size-3.5" />
                                        <span>Detail</span>
                                    </Button>
                                    <DropdownMenu v-else>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="size-8">
                                                <MoreHorizontal class="size-4" />
                                                <span class="sr-only">Buka menu aksi</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-44">
                                            <DropdownMenuLabel class="text-xs">Aksi Permohonan</DropdownMenuLabel>
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
                    v-if="permohonan.data.length > 0"
                    class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-border/70 text-xs text-muted-foreground"
                >
                    <div>
                        Menampilkan
                        <strong class="font-semibold text-foreground">{{ permohonan.from || 0 }}</strong>
                        sampai
                        <strong class="font-semibold text-foreground">{{ permohonan.to || 0 }}</strong>
                        dari
                        <strong class="font-semibold text-foreground">{{ permohonan.total }}</strong>
                        data
                    </div>

                    <div class="flex items-center gap-1.5">
                        <template v-for="(link, i) in permohonan.links" :key="i">
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
