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
    Users,
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
import AbgDeleteDialog from '@/components/abg/AbgDeleteDialog.vue';
import AbgDetailModal from '@/components/abg/AbgDetailModal.vue';
import AbgFormModal from '@/components/abg/AbgFormModal.vue';
import type { AbgFilters, AbgItem, PaginatedAbg } from '@/types/abg';

const props = defineProps<{
    abg: PaginatedAbg;
    filters: AbgFilters;
    kewarganegaraanOptions: string[];
    genderOptions: { value: string; label: string }[];
    can: {
        create: boolean;
        printList: boolean;
        export?: boolean;
    };
}>();

const page = usePage();
const isFlashSuccessDismissed = ref(false);
const isFlashErrorDismissed = ref(false);

const flashSuccess = computed(() => (isFlashSuccessDismissed.value ? undefined : (page.props.flash as { success?: string })?.success));
const flashError = computed(() => (isFlashErrorDismissed.value ? undefined : (page.props.flash as { error?: string })?.error));

watch(
    () => page.props.flash,
    () => {
        isFlashSuccessDismissed.value = false;
        isFlashErrorDismissed.value = false;
    },
    { deep: true }
);

// Local filter states
const search = ref(props.filters.search || '');
const selectedJenisKelamin = ref(props.filters.jenis_kelamin || '');
const selectedKewarganegaraan = ref(props.filters.kewarganegaraan || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 10);

// Modal states
const isFormModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const activeAbg = ref<AbgItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/abg',
        {
            search: search.value || undefined,
            jenis_kelamin: selectedJenisKelamin.value || undefined,
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

watch([selectedJenisKelamin, selectedKewarganegaraan, perPage], () => {
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
    selectedJenisKelamin.value = '';
    selectedKewarganegaraan.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    perPage.value = 10;
    router.get('/abg', {}, { preserveState: true, replace: true });
}

const hasActiveFilters = computed(() => {
    return !!(search.value || selectedJenisKelamin.value || selectedKewarganegaraan.value);
});

const printListUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedJenisKelamin.value) params.set('jenis_kelamin', selectedJenisKelamin.value);
    if (selectedKewarganegaraan.value) params.set('kewarganegaraan', selectedKewarganegaraan.value);
    const query = params.toString();
    return query ? `/abg/print-list?${query}` : '/abg/print-list';
});

const exportCsvUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedJenisKelamin.value) params.set('jenis_kelamin', selectedJenisKelamin.value);
    if (selectedKewarganegaraan.value) params.set('kewarganegaraan', selectedKewarganegaraan.value);
    if (sortBy.value) params.set('sort_by', sortBy.value);
    if (sortOrder.value) params.set('sort_order', sortOrder.value);
    const query = params.toString();
    return query ? `/abg/export?${query}` : '/abg/export';
});

// Modal triggers
function openCreateModal() {
    activeAbg.value = null;
    isFormModalOpen.value = true;
}

function openEditModal(item: AbgItem) {
    activeAbg.value = item;
    isFormModalOpen.value = true;
}

function openDetailModal(item: AbgItem) {
    activeAbg.value = item;
    isDetailModalOpen.value = true;
}

function openDeleteModal(item: AbgItem) {
    activeAbg.value = item;
    isDeleteDialogOpen.value = true;
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
</script>

<template>
    <Head title="Pendaftaran ABG" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <!-- Header Halaman -->
        <PageHeader
            title="Pendaftaran Anak Berkewarganegaraan Ganda (ABG)"
            description="Pengelolaan arsip, data paspor, kewarganegaraan ganda, dan data orang tua anak (ABG)."
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
                    class="gap-1.5 shadow-xs bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500"
                    @click="openCreateModal"
                >
                    <Plus class="size-4" />
                    <span>Tambah Pendaftaran ABG</span>
                </Button>
            </template>
        </PageHeader>

        <!-- Flash Alert Feedback -->
        <div
            v-if="flashSuccess"
            class="flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-2xs dark:bg-emerald-950/40 dark:text-emerald-200"
        >
            <div class="flex items-center gap-2">
                <span class="size-2 rounded-full bg-emerald-500 shrink-0" />
                <span>{{ flashSuccess }}</span>
            </div>
            <button
                type="button"
                class="text-emerald-700 hover:text-emerald-900 dark:text-emerald-300"
                @click="isFlashSuccessDismissed = true"
            >
                <X class="size-4" />
            </button>
        </div>

        <div
            v-if="flashError"
            class="flex items-center justify-between rounded-lg border border-red-500/30 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-2xs dark:bg-red-950/40 dark:text-red-200"
        >
            <div class="flex items-center gap-2">
                <AlertCircle class="size-4 text-red-600 shrink-0" />
                <span>{{ flashError }}</span>
            </div>
            <button
                type="button"
                class="text-red-700 hover:text-red-900 dark:text-red-300"
                @click="isFlashErrorDismissed = true"
            >
                <X class="size-4" />
            </button>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
            <div class="grid gap-3 md:grid-cols-12">
                <!-- Search Input -->
                <div class="relative md:col-span-5">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Cari nama anak, no. paspor, no. register, nama ayah/ibu..."
                        class="pl-9 h-9"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                        @click="search = ''"
                    >
                        <X class="size-3.5" />
                    </button>
                </div>

                <!-- Filter Jenis Kelamin -->
                <div class="md:col-span-3">
                    <select
                        v-model="selectedJenisKelamin"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                    >
                        <option value="">Semua Jenis Kelamin</option>
                        <option
                            v-for="opt in genderOptions"
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
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                    >
                        <option value="">Semua Kewarganegaraan</option>
                        <option
                            v-for="country in kewarganegaraanOptions"
                            :key="country"
                            :value="country"
                        >
                            {{ country }}
                        </option>
                    </select>
                </div>

                <!-- Reset Filter -->
                <div class="md:col-span-1 flex items-center justify-end">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="h-9 w-full md:w-auto text-xs gap-1 text-muted-foreground hover:text-foreground"
                        :disabled="!hasActiveFilters"
                        @click="resetFilters"
                    >
                        <RefreshCw class="size-3" />
                        <span class="md:hidden">Reset Filter</span>
                    </Button>
                </div>
            </div>

            <!-- Active Filter Indicators -->
            <div
                v-if="hasActiveFilters"
                class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-border/60 text-xs text-muted-foreground"
            >
                <span class="font-medium flex items-center gap-1">
                    <Filter class="size-3" /> Filter Aktif:
                </span>
                <span
                    v-if="search"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5"
                >
                    Kata Kunci: "{{ search }}"
                    <button type="button" @click="search = ''"><X class="size-3" /></button>
                </span>
                <span
                    v-if="selectedJenisKelamin"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5"
                >
                    Jenis Kelamin: {{ selectedJenisKelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    <button type="button" @click="selectedJenisKelamin = ''"><X class="size-3" /></button>
                </span>
                <span
                    v-if="selectedKewarganegaraan"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5"
                >
                    Kewarganegaraan: {{ selectedKewarganegaraan }}
                    <button type="button" @click="selectedKewarganegaraan = ''"><X class="size-3" /></button>
                </span>
            </div>
        </div>

        <!-- Table Container Card -->
        <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border/80 bg-muted/50 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">Foto</th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('nama')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Nama Lengkap & No. Register</span>
                                    <span v-if="sortBy === 'nama'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('no_paspor')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>No. Paspor</span>
                                    <span v-if="sortBy === 'no_paspor'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center w-12">L/P</th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('kewarganegaraan')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Kewarganegaraan Ganda</span>
                                    <span v-if="sortBy === 'kewarganegaraan'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th class="px-4 py-3">Tempat, Tanggal Lahir</th>
                            <th class="px-4 py-3">Orang Tua (Ayah / Ibu)</th>
                            <th class="px-4 py-3">No. Telepon</th>
                            <th class="px-4 py-3 text-right w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <tr
                            v-for="item in abg.data"
                            :key="item.id"
                            class="hover:bg-muted/40 transition-colors group"
                        >
                            <!-- Foto -->
                            <td class="px-4 py-3 text-center">
                                <div class="size-9 mx-auto shrink-0 overflow-hidden rounded-full border border-border bg-muted flex items-center justify-center">
                                    <img
                                        v-if="item.foto_url"
                                        :src="item.foto_url"
                                        :alt="item.nama"
                                        class="size-full object-cover"
                                        loading="lazy"
                                    />
                                    <UserIcon v-else class="size-4 text-muted-foreground" />
                                </div>
                            </td>

                            <!-- Nama & No Register -->
                            <td class="px-4 py-3">
                                <div class="font-semibold text-foreground flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="hover:underline text-left"
                                        @click="openDetailModal(item)"
                                    >
                                        {{ item.nama }}
                                    </button>
                                </div>
                                <div class="text-xs font-mono text-muted-foreground mt-0.5">
                                    {{ item.no_register }}
                                </div>
                            </td>

                            <!-- No Paspor -->
                            <td class="px-4 py-3 font-mono text-xs font-medium text-foreground">
                                {{ item.no_paspor }}
                            </td>

                            <!-- Jenis Kelamin -->
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex size-6 items-center justify-center rounded-full text-xs font-bold"
                                    :class="item.jenis_kelamin === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300'"
                                >
                                    {{ item.jenis_kelamin }}
                                </span>
                            </td>

                            <!-- Kewarganegaraan Ganda -->
                            <td class="px-4 py-3 text-xs text-foreground font-medium max-w-[180px] truncate">
                                {{ item.kewarganegaraan }}
                            </td>

                            <!-- Tempat, Tanggal Lahir -->
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <div>{{ item.tempat_lahir || '-' }}</div>
                                <div class="text-[11px] font-mono text-muted-foreground">{{ formatIndoDate(item.tanggal_lahir) }}</div>
                            </td>

                            <!-- Orang Tua -->
                            <td class="px-4 py-3 text-xs text-muted-foreground max-w-[180px]">
                                <div class="truncate">A: {{ item.nama_ayah || '-' }}</div>
                                <div class="truncate">I: {{ item.nama_ibu || '-' }}</div>
                            </td>

                            <!-- No Telepon -->
                            <td class="px-4 py-3 text-xs font-mono text-muted-foreground">
                                {{ item.nomor_telepon || '-' }}
                            </td>

                            <!-- Aksi Menu -->
                            <td class="px-4 py-3 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="size-8 text-muted-foreground hover:text-foreground"
                                        >
                                            <MoreHorizontal class="size-4" />
                                            <span class="sr-only">Menu Aksi</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-44">
                                        <DropdownMenuLabel class="text-xs font-normal text-muted-foreground">
                                            Aksi Data
                                        </DropdownMenuLabel>
                                        <DropdownMenuItem class="gap-2 cursor-pointer" @click="openDetailModal(item)">
                                            <Eye class="size-4 text-muted-foreground" />
                                            <span>Lihat Detail</span>
                                        </DropdownMenuItem>

                                        <!-- Edit and Delete only for Admin -->
                                        <template v-if="can.create">
                                            <DropdownMenuItem class="gap-2 cursor-pointer" @click="openEditModal(item)">
                                                <Pencil class="size-4 text-emerald-600" />
                                                <span>Ubah Data</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="gap-2 text-destructive cursor-pointer focus:text-destructive"
                                                @click="openDeleteModal(item)"
                                            >
                                                <Trash2 class="size-4" />
                                                <span>Hapus Data</span>
                                            </DropdownMenuItem>
                                        </template>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="abg.data.length === 0">
                            <td colspan="9" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-2 text-muted-foreground">
                                    <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                                        <Users class="size-6 text-muted-foreground" />
                                    </div>
                                    <p class="text-sm font-semibold text-foreground">
                                        Tidak ada data Pendaftaran ABG
                                    </p>
                                    <p class="text-xs max-w-sm">
                                        {{ hasActiveFilters ? 'Tidak ada data yang sesuai dengan kriteria filter yang Anda terapkan.' : 'Belum ada arsip pendaftaran Anak Berkewarganegaraan Ganda yang tersimpan.' }}
                                    </p>
                                    <Button
                                        v-if="hasActiveFilters"
                                        variant="outline"
                                        size="sm"
                                        class="mt-2 text-xs"
                                        @click="resetFilters"
                                    >
                                        Reset Semua Filter
                                    </Button>
                                    <Button
                                        v-else-if="can.create"
                                        size="sm"
                                        class="mt-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white"
                                        @click="openCreateModal"
                                    >
                                        <Plus class="size-3.5 mr-1" />
                                        Tambah Pendaftaran ABG Pertama
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-border/80 px-4 py-3 bg-muted/20 text-xs text-muted-foreground">
                <div class="flex items-center gap-3">
                    <span>
                        Menampilkan
                        <strong class="font-medium text-foreground">{{ abg.from || 0 }}</strong>
                        sampai
                        <strong class="font-medium text-foreground">{{ abg.to || 0 }}</strong>
                        dari
                        <strong class="font-medium text-foreground">{{ abg.total }}</strong>
                        data
                    </span>

                    <div class="flex items-center gap-1.5">
                        <span>Per baris:</span>
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

                <!-- Navigation Page Links -->
                <div v-if="abg.last_page > 1" class="flex items-center gap-1">
                    <template v-for="(link, i) in abg.links" :key="i">
                        <span
                            v-if="link.url === null"
                            class="inline-flex h-8 px-2.5 items-center justify-center rounded-md text-xs text-muted-foreground/60 cursor-not-allowed"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-8 px-2.5 items-center justify-center rounded-md text-xs font-medium transition-colors"
                            :class="link.active ? 'bg-emerald-600 text-white dark:bg-emerald-600' : 'hover:bg-muted text-foreground'"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <AbgFormModal
            v-if="can.create"
            v-model:open="isFormModalOpen"
            :abg="activeAbg"
            :kewarganegaraan-options="kewarganegaraanOptions"
            :gender-options="genderOptions"
        />

        <AbgDetailModal
            v-model:open="isDetailModalOpen"
            :abg="activeAbg"
        />

        <AbgDeleteDialog
            v-if="can.create"
            v-model:open="isDeleteDialogOpen"
            :abg="activeAbg"
        />
    </div>
</template>
