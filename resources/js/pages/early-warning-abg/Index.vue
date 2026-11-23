<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertOctagon,
    AlertTriangle,
    Calendar,
    ChevronDown,
    Clock,
    Eye,
    FileDown,
    Filter,
    MessageCircle,
    RefreshCw,
    Search,
    ShieldAlert,
    UserCheck,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { cleanBrowserUrl } from '@/lib/cleanUrl';
import PageHeader from '@/components/PageHeader.vue';
import FlashAlert from '@/components/FlashAlert.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AbgDetailModal from '@/components/abg/AbgDetailModal.vue';
import type { AbgItem } from '@/types/abg';
import type {
    AbgEarlyWarningItem,
    EarlyWarningAbgCounts,
    EarlyWarningAbgFilters,
    PaginatedAbgEarlyWarning,
} from '@/types/early-warning-abg';

const props = defineProps<{
    abg: PaginatedAbgEarlyWarning;
    counts: EarlyWarningAbgCounts;
    filters: EarlyWarningAbgFilters;
    kewarganegaraanOptions: string[];
    kabupatenOptions?: string[];
    can?: {
        printList?: boolean;
    };
}>();

// Filter states
const search = ref(props.filters.search || '');
const selectedKategori = ref(props.filters.kategori || 'semua');
const selectedKewarganegaraan = ref(props.filters.kewarganegaraan || '');
const sortBy = ref(props.filters.sort_by || 'tanggal_lahir');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'asc');
const perPage = ref(props.filters.per_page || 50);

// Modal state
const isDetailModalOpen = ref(false);
const activeAbg = ref<AbgItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/early-warning/abg',
        {
            search: search.value || undefined,
            kategori: selectedKategori.value === 'semua' ? undefined : selectedKategori.value,
            kewarganegaraan: selectedKewarganegaraan.value || undefined,
            sort_by: sortBy.value || undefined,
            sort_order: sortOrder.value,
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

watch([selectedKategori, selectedKewarganegaraan, perPage], () => {
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

function selectKategoriCard(kategori: string) {
    selectedKategori.value = kategori;
}

function resetFilters() {
    search.value = '';
    selectedKategori.value = 'semua';
    selectedKewarganegaraan.value = '';
    sortBy.value = 'tanggal_lahir';
    sortOrder.value = 'asc';
    perPage.value = 50;
    router.get('/early-warning/abg', {}, {
        preserveState: true,
        replace: true,
        onSuccess: () => {
            cleanBrowserUrl();
        },
    });
}

const printListUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (selectedKategori.value && selectedKategori.value !== 'semua') params.set('kategori', selectedKategori.value);
    if (selectedKewarganegaraan.value) params.set('kewarganegaraan', selectedKewarganegaraan.value);
    const queryString = params.toString();
    return `/early-warning/abg/print-list${queryString ? `?${queryString}` : ''}`;
});

const hasActiveFilters = computed(() => {
    return !!(
        search.value ||
        (selectedKategori.value && selectedKategori.value !== 'semua') ||
        selectedKewarganegaraan.value
    );
});

function openDetailModal(item: AbgEarlyWarningItem) {
    activeAbg.value = item as unknown as AbgItem;
    isDetailModalOpen.value = true;
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

function getUsia(item: AbgEarlyWarningItem): string {
    if (item.umur && item.umur !== '-') {
        return item.umur;
    }
    if (!item.tanggal_lahir) return '-';
    try {
        const today = new Date();
        const dob = new Date(item.tanggal_lahir);
        let years = today.getFullYear() - dob.getFullYear();
        const birthdayThisYear = new Date(dob);
        birthdayThisYear.setFullYear(dob.getFullYear() + years);
        if (today < birthdayThisYear) {
            years--;
            birthdayThisYear.setFullYear(dob.getFullYear() + years);
        }
        const diffTime = today.getTime() - birthdayThisYear.getTime();
        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        return `${years} tahun ${days} hari`;
    } catch {
        return '-';
    }
}
</script>

<template>
    <Head title="Early Warning Data ABG - Pengawasan Batas Usia Kewarganegaraan Ganda" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <FlashAlert />

        <!-- Header Halaman -->
        <PageHeader
            title="Early Warning Data ABG"
            description="Sistem peringatan dini batas usia Anak Berkewarganegaraan Ganda (ABG) menjelang usia 18 tahun, batas akhir usia 21 tahun, serta status kedaluwarsa."
        >
            <template #actions>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="can?.printList"
                        variant="outline"
                        size="sm"
                        as-child
                        class="gap-1.5 shadow-2xs cursor-pointer"
                    >
                        <a :href="printListUrl" target="_blank" rel="noopener noreferrer">
                            <FileDown class="size-4" />
                            <span>Ekspor PDF</span>
                        </a>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="gap-1.5 shadow-2xs cursor-pointer"
                    >
                        <Link href="/abg">
                            <Eye class="size-4" />
                            <span>Buka Data ABG</span>
                        </Link>
                    </Button>
                </div>
            </template>
        </PageHeader>

        <!-- Kartu Metrik Kategori Peringatan ABG -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Semua Kategori Early Warning ABG -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs cursor-pointer"
                :class="selectedKategori === 'semua'
                    ? 'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-500/20 dark:bg-indigo-950/40 dark:border-indigo-500'
                    : 'border-border/80 bg-card hover:border-indigo-300 dark:hover:border-indigo-700'"
                @click="selectKategoriCard('semua')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Total Early Warning</span>
                    <Clock class="size-4 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-foreground">{{ counts.semua }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Semua Kategori Peringatan</p>
                </div>
            </button>

            <!-- Card 2: Menjelang 18 Tahun (18 Th Kurang 6 Bulan s/d 18 Th) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs cursor-pointer"
                :class="selectedKategori === 'kurang_18'
                    ? 'border-amber-500 bg-amber-50/80 ring-2 ring-amber-500/20 dark:bg-amber-950/40 dark:border-amber-500'
                    : 'border-border/80 bg-card hover:border-amber-300 dark:hover:border-amber-800'"
                @click="selectKategoriCard('kurang_18')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Menjelang 18 Tahun</span>
                    <AlertTriangle class="size-4 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-amber-700 dark:text-amber-400">{{ counts.kurang_18 }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Usia 17,5 s/d 18 Tahun</p>
                </div>
            </button>

            <!-- Card 3: Menjelang 21 Tahun (21 Th Kurang 6 Bulan s/d 21 Th) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs cursor-pointer"
                :class="selectedKategori === 'kurang_21'
                    ? 'border-orange-500 bg-orange-50/80 ring-2 ring-orange-500/20 dark:bg-orange-950/40 dark:border-orange-500'
                    : 'border-border/80 bg-card hover:border-orange-300 dark:hover:border-orange-800'"
                @click="selectKategoriCard('kurang_21')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-orange-700 dark:text-orange-400 uppercase tracking-wider">Menjelang 21 Tahun</span>
                    <AlertOctagon class="size-4 text-orange-600 dark:text-orange-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-orange-700 dark:text-orange-400">{{ counts.kurang_21 }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Batas Akhir Penentuan Kewarganegaraan</p>
                </div>
            </button>

            <!-- Card 4: Lewat dari 21 Tahun (Kedaluwarsa) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs cursor-pointer"
                :class="selectedKategori === 'lewat_21'
                    ? 'border-rose-700 bg-rose-50/80 ring-2 ring-rose-500/20 dark:bg-rose-950/50 dark:border-rose-600'
                    : 'border-border/80 bg-card hover:border-rose-300 dark:hover:border-rose-900'"
                @click="selectKategoriCard('lewat_21')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-rose-800 dark:text-rose-400 uppercase tracking-wider">Kedaluwarsa</span>
                    <AlertCircle class="size-4 text-rose-700 dark:text-rose-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-rose-800 dark:text-rose-400">{{ counts.lewat_21 }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Telah Melewati Batas Usia 21 Tahun</p>
                </div>
            </button>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
            <div class="grid gap-3 md:grid-cols-12">
                <!-- Search Input -->
                <div class="relative md:col-span-6">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Cari nama ABG, no. register, kewarganegaraan..."
                        class="pl-9 h-9 text-sm"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground cursor-pointer"
                        @click="search = ''"
                    >
                        <X class="size-3.5" />
                    </button>
                </div>

                <!-- Filter Kategori Dropdown -->
                <div class="relative md:col-span-3">
                    <select
                        v-model="selectedKategori"
                        class="flex h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-10 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card cursor-pointer"
                    >
                        <option value="semua">Semua Kategori</option>
                        <option value="kurang_18">&lt; 18 Tahun (17,5 – 18 Th)</option>
                        <option value="kurang_21">&lt; 21 Tahun (20,5 – 21 Th)</option>
                        <option value="lewat_21">Lewat 21 Tahun (Kedaluwarsa)</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                </div>

                <!-- Filter Kewarganegaraan Dropdown -->
                <div class="relative md:col-span-3">
                    <select
                        v-model="selectedKewarganegaraan"
                        class="flex h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-10 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card cursor-pointer"
                    >
                        <option value="">Semua Kewarganegaraan</option>
                        <option v-for="opt in kewarganegaraanOptions" :key="opt" :value="opt">
                            {{ opt }}
                        </option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                </div>
            </div>

            <!-- Active Filter Badges & Reset -->
            <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 pt-1 border-t border-border/50 text-xs">
                <span class="text-muted-foreground font-medium flex items-center gap-1">
                    <Filter class="size-3" />
                    <span>Filter Aktif:</span>
                </span>

                <span
                    v-if="selectedKategori !== 'semua'"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-xs text-foreground"
                >
                    <span>Kategori: {{ selectedKategori === 'kurang_18' ? '< 18 Th' : selectedKategori === 'kurang_21' ? '< 21 Th' : 'Lewat 21 Th' }}</span>
                    <button type="button" class="cursor-pointer hover:text-destructive" @click="selectedKategori = 'semua'">
                        <X class="size-3" />
                    </button>
                </span>

                <span
                    v-if="selectedKewarganegaraan"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-xs text-foreground"
                >
                    <span>Negara: {{ selectedKewarganegaraan }}</span>
                    <button type="button" class="cursor-pointer hover:text-destructive" @click="selectedKewarganegaraan = ''">
                        <X class="size-3" />
                    </button>
                </span>

                <button
                    type="button"
                    class="inline-flex items-center gap-1 text-primary hover:underline font-medium cursor-pointer ml-auto"
                    @click="resetFilters"
                >
                    <RefreshCw class="size-3" />
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

        <!-- Table Container Card -->
        <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border/80 bg-muted/50 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('no_register')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>No. Register</span>
                                    <span v-if="sortBy === 'no_register'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('nama')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Nama Lengkap</span>
                                    <span v-if="sortBy === 'nama'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('tanggal_lahir')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>TTL & Usia</span>
                                    <span v-if="sortBy === 'tanggal_lahir'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('kewarganegaraan')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Kewarganegaraan</span>
                                    <span v-if="sortBy === 'kewarganegaraan'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th class="px-4 py-3">Batas Usia</th>
                            <th class="px-4 py-3">Status & Sisa Waktu</th>
                            <th class="px-4 py-3 text-right w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <tr
                            v-for="(item, index) in abg.data"
                            :key="item.id"
                            class="hover:bg-muted/40 transition-colors group"
                            :class="{
                                'bg-amber-50/20 dark:bg-amber-950/10': item.urgensi === 'kurang_18',
                                'bg-orange-50/20 dark:bg-orange-950/10': item.urgensi === 'kurang_21',
                                'bg-rose-50/20 dark:bg-rose-950/10': item.urgensi === 'lewat_21',
                            }"
                        >
                            <!-- No -->
                            <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                {{ (abg.current_page - 1) * abg.per_page + index + 1 }}
                            </td>

                            <!-- No. Register -->
                            <td class="px-4 py-3 text-xs font-mono font-medium text-foreground whitespace-nowrap">
                                {{ item.no_register }}
                            </td>

                            <!-- Nama Lengkap (dengan Foto & Jenis Kelamin) -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-9 shrink-0 overflow-hidden rounded-full border border-border bg-muted flex items-center justify-center">
                                        <img
                                            v-if="item.foto_url"
                                            :src="item.foto_url"
                                            :alt="item.nama"
                                            class="size-full object-cover"
                                            loading="lazy"
                                        />
                                        <UserCheck v-else class="size-4 text-muted-foreground" />
                                    </div>
                                    <div class="min-w-0">
                                        <button
                                            type="button"
                                            class="font-medium text-foreground hover:text-emerald-600 dark:hover:text-emerald-400 text-left transition-colors truncate block max-w-[200px] cursor-pointer"
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

                            <!-- TTL & Usia -->
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                <div class="font-medium text-foreground">
                                    {{ item.tempat_lahir ? `${item.tempat_lahir}, ` : '' }}{{ formatIndoDate(item.tanggal_lahir) }}
                                </div>
                                <div class="text-[11px] text-muted-foreground font-mono">
                                    Usia: {{ getUsia(item) }}
                                </div>
                            </td>

                            <!-- Kewarganegaraan -->
                            <td class="px-4 py-3 text-xs">
                                <div class="font-medium text-foreground truncate max-w-[180px]">
                                    {{ item.kewarganegaraan }}
                                </div>
                                <div v-if="item.kabupaten" class="text-[11px] text-muted-foreground">
                                    Kab. {{ item.kabupaten }}
                                </div>
                            </td>

                            <!-- Batas Usia (Milestone) -->
                            <td class="px-4 py-3 text-xs font-mono font-medium text-foreground whitespace-nowrap">
                                <div>{{ formatIndoDate(item.target_milestone) }}</div>
                                <div class="text-[10px] text-muted-foreground">
                                    {{ item.urgensi === 'kurang_18' ? 'Genap 18 Th' : 'Genap 21 Th' }}
                                </div>
                            </td>

                            <!-- Status Peringatan & Sisa Waktu (Atas-Bawah) -->
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                <div class="flex flex-col gap-1 items-start">
                                    <!-- Badge Kurang 18 -->
                                    <span
                                        v-if="item.urgensi === 'kurang_18'"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300"
                                    >
                                        <span class="size-1.5 rounded-full bg-amber-600" />
                                        <span>Menjelang 18 Tahun</span>
                                    </span>

                                    <!-- Badge Kurang 21 -->
                                    <span
                                        v-else-if="item.urgensi === 'kurang_21'"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-800 dark:bg-orange-950/60 dark:text-orange-300"
                                    >
                                        <span class="size-1.5 rounded-full bg-orange-600 animate-pulse" />
                                        <span>Menjelang 21 Tahun (Batas)</span>
                                    </span>

                                    <!-- Badge Lewat 21 / Kedaluwarsa -->
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/70 dark:text-rose-300"
                                    >
                                        <AlertCircle class="size-3" />
                                        <span>Lewat 21 Tahun (Kedaluwarsa)</span>
                                    </span>

                                    <!-- Sisa Waktu Label -->
                                    <span
                                        class="text-xs font-semibold"
                                        :class="{
                                            'text-amber-600 dark:text-amber-400': item.urgensi === 'kurang_18',
                                            'text-orange-600 dark:text-orange-400': item.urgensi === 'kurang_21',
                                            'text-rose-700 dark:text-rose-400': item.urgensi === 'lewat_21',
                                        }"
                                    >
                                        {{ item.sisa_waktu_label }}
                                    </span>
                                </div>
                            </td>

                            <!-- Aksi Menu -->
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol WA (Sementara Dinonaktifkan) -->
                                    <button
                                        type="button"
                                        disabled
                                        class="inline-flex size-8 items-center justify-center rounded-md bg-emerald-600/40 text-white cursor-not-allowed opacity-50 shrink-0"
                                        title="Fitur Hubungi via WhatsApp Sementara Dinonaktifkan"
                                    >
                                        <MessageCircle class="size-4" />
                                    </button>

                                    <!-- Tombol Lihat Detail ABG -->
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        class="size-8 cursor-pointer"
                                        title="Lihat Detail ABG"
                                        @click="openDetailModal(item)"
                                    >
                                        <Eye class="size-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="abg.data.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <UserCheck class="size-8 text-muted-foreground/50" />
                                    <p class="font-medium text-foreground">Tidak ada data ABG yang memenuhi kriteria early warning.</p>
                                    <p class="text-xs text-muted-foreground max-w-sm">
                                        Semua data ABG berada di luar rentang peringatan 18 tahun, 21 tahun, maupun status kedaluwarsa.
                                    </p>
                                    <Button
                                        v-if="hasActiveFilters"
                                        variant="outline"
                                        size="sm"
                                        class="mt-2 cursor-pointer"
                                        @click="resetFilters"
                                    >
                                        Reset Filter
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="abg.total > 0"
                class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-border/80 px-4 py-3 bg-muted/20 text-xs text-muted-foreground"
            >
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
                        <span>Baris per halaman:</span>
                        <select
                            v-model="perPage"
                            class="h-7 rounded border border-input bg-transparent px-2 text-xs shadow-2xs cursor-pointer"
                        >
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Page Links -->
                <div v-if="abg.links && abg.links.length > 3" class="flex items-center gap-1">
                    <template v-for="(link, index) in abg.links" :key="index">
                        <Component
                            :is="link.url ? Link : 'span'"
                            :href="link.url || undefined"
                            class="flex min-w-[32px] h-8 items-center justify-center rounded px-2 text-xs transition-colors cursor-pointer"
                            :class="{
                                'bg-neutral-900 font-semibold text-white dark:bg-white dark:text-neutral-900': link.active,
                                'text-muted-foreground hover:bg-muted hover:text-foreground': !link.active && link.url,
                                'text-muted-foreground/40 cursor-not-allowed': !link.url,
                            }"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Detail Modal ABG -->
        <AbgDetailModal
            v-model:open="isDetailModalOpen"
            :abg="activeAbg"
        />
    </div>
</template>

