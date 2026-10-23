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
    Filter,
    MessageCircle,
    RefreshCw,
    Search,
    ShieldAlert,
    User as UserIcon,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import WnaDetailModal from '@/components/wna/WnaDetailModal.vue';
import type { EarlyWarningCounts, EarlyWarningFilters, PaginatedWna, WnaItem } from '@/types/early-warning';

const props = defineProps<{
    wna: PaginatedWna;
    counts: EarlyWarningCounts;
    filters: EarlyWarningFilters;
    kewarganegaraanOptions: string[];
    jenisIzinTinggalOptions: { value: string; label: string }[];
}>();

// Filter states
const search = ref(props.filters.search || '');
const selectedKategori = ref(props.filters.kategori || 'semua');
const selectedJenisIzin = ref(props.filters.jenis_izin_tinggal || '');
const selectedKewarganegaraan = ref(props.filters.kewarganegaraan || '');
const sortBy = ref(props.filters.sort_by || 'masa_berlaku');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'asc');
const perPage = ref(props.filters.per_page || 10);

// Modal state
const isDetailModalOpen = ref(false);
const activeWna = ref<WnaItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/early-warning',
        {
            search: search.value || undefined,
            kategori: selectedKategori.value === 'semua' ? undefined : selectedKategori.value,
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

watch([selectedKategori, selectedJenisIzin, selectedKewarganegaraan, perPage], () => {
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
    selectedJenisIzin.value = '';
    selectedKewarganegaraan.value = '';
    sortBy.value = 'masa_berlaku';
    sortOrder.value = 'asc';
    perPage.value = 10;
    router.get('/early-warning', {}, { preserveState: true, replace: true });
}

const hasActiveFilters = computed(() => {
    return !!(
        search.value ||
        (selectedKategori.value && selectedKategori.value !== 'semua') ||
        selectedJenisIzin.value ||
        selectedKewarganegaraan.value
    );
});

function openDetailModal(item: WnaItem) {
    activeWna.value = item;
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

function formatSisaHari(days: number): string {
    if (days < 0) {
        return `Lewat ${Math.abs(days)} hari`;
    }
    if (days === 0) {
        return 'Hari ini';
    }
    return `${days} hari lagi`;
}
</script>

<template>
    <Head title="Early Warning Izin Tinggal WNA" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <!-- Header Halaman -->
        <PageHeader
            title="Early Warning Masa Berlaku Izin Tinggal WNA"
            description="Pemantauan preventif izin tinggal WNA yang akan berakhir dalam 3 bulan kalender ke depan serta daftar rekaman yang telah kedaluwarsa."
        >
            <template #actions>
                <Button
                    variant="outline"
                    size="sm"
                    class="gap-1.5 shadow-xs"
                    as-child
                >
                    <Link href="/wna">
                        <Eye class="size-4" />
                        <span>Buka Modul Data WNA</span>
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <!-- Category Stat Cards (Interactive) -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Card 1: Semua Peringatan Aktif (0-3 Bulan) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs"
                :class="selectedKategori === 'semua'
                    ? 'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-500/20 dark:bg-indigo-950/40 dark:border-indigo-500'
                    : 'border-border/80 bg-card hover:border-indigo-300 dark:hover:border-indigo-700'"
                @click="selectKategoriCard('semua')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Akan Berakhir</span>
                    <Clock class="size-4 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-foreground">{{ counts.semua }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">0 – 3 Bulan Kalender</p>
                </div>
            </button>

            <!-- Card 2: Sangat Mendesak (0-14 Hari / Merah) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs"
                :class="selectedKategori === 'merah'
                    ? 'border-red-600 bg-red-50/80 ring-2 ring-red-500/20 dark:bg-red-950/40 dark:border-red-500'
                    : 'border-border/80 bg-card hover:border-red-300 dark:hover:border-red-800'"
                @click="selectKategoriCard('merah')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wider">Sangat Mendesak</span>
                    <AlertOctagon class="size-4 text-red-600 dark:text-red-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-red-700 dark:text-red-400">{{ counts.merah }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Sisa 0 – 14 Hari</p>
                </div>
            </button>

            <!-- Card 3: Mendesak (15-30 Hari / Kuning) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs"
                :class="selectedKategori === 'kuning'
                    ? 'border-amber-500 bg-amber-50/80 ring-2 ring-amber-500/20 dark:bg-amber-950/40 dark:border-amber-500'
                    : 'border-border/80 bg-card hover:border-amber-300 dark:hover:border-amber-800'"
                @click="selectKategoriCard('kuning')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Mendesak</span>
                    <AlertTriangle class="size-4 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-amber-700 dark:text-amber-400">{{ counts.kuning }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Sisa 15 – 30 Hari</p>
                </div>
            </button>

            <!-- Card 4: Perhatian (>30 Hari - 3 Bulan / Biru) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs"
                :class="selectedKategori === 'biru'
                    ? 'border-blue-500 bg-blue-50/80 ring-2 ring-blue-500/20 dark:bg-blue-950/40 dark:border-blue-500'
                    : 'border-border/80 bg-card hover:border-blue-300 dark:hover:border-blue-800'"
                @click="selectKategoriCard('biru')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Perhatian</span>
                    <ShieldAlert class="size-4 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-blue-700 dark:text-blue-400">{{ counts.biru }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">> 30 Hari – 3 Bulan</p>
                </div>
            </button>

            <!-- Card 5: Kedaluwarsa (<0 Hari) -->
            <button
                type="button"
                class="flex flex-col justify-between rounded-xl border p-4 text-left transition-all shadow-2xs hover:shadow-xs"
                :class="selectedKategori === 'kedaluwarsa'
                    ? 'border-rose-700 bg-rose-50/80 ring-2 ring-rose-500/20 dark:bg-rose-950/50 dark:border-rose-600'
                    : 'border-border/80 bg-card hover:border-rose-300 dark:hover:border-rose-900'"
                @click="selectKategoriCard('kedaluwarsa')"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-rose-800 dark:text-rose-400 uppercase tracking-wider">Kedaluwarsa</span>
                    <AlertCircle class="size-4 text-rose-700 dark:text-rose-400" />
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold tracking-tight text-rose-800 dark:text-rose-400">{{ counts.kedaluwarsa }}</div>
                    <p class="mt-1 text-[11px] text-muted-foreground">Telah Lewat Masa Berlaku</p>
                </div>
            </button>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 shadow-2xs">
            <div class="grid gap-3 md:grid-cols-12">
                <!-- Search Input -->
                <div class="relative md:col-span-4">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Cari nama WNA, no. paspor, no. register..."
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

                <!-- Filter Kategori Dropdown -->
                <div class="md:col-span-3">
                    <select
                        v-model="selectedKategori"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                    >
                        <option value="semua">Semua Peringatan Aktif (0–3 Bulan)</option>
                        <option value="merah">Sangat Mendesak (0–14 Hari)</option>
                        <option value="kuning">Mendesak (15–30 Hari)</option>
                        <option value="biru">Perhatian (>30 Hari–3 Bulan)</option>
                        <option value="kedaluwarsa">Sudah Kedaluwarsa (&lt;0 Hari)</option>
                    </select>
                </div>

                <!-- Filter Jenis Izin Tinggal -->
                <div class="md:col-span-2">
                    <select
                        v-model="selectedJenisIzin"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                    >
                        <option value="">Semua Izin</option>
                        <option
                            v-for="opt in jenisIzinTinggalOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.value }}
                        </option>
                    </select>
                </div>

                <!-- Filter Kewarganegaraan -->
                <div class="md:col-span-2">
                    <select
                        v-model="selectedKewarganegaraan"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                    >
                        <option value="">Semua Negara</option>
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
                        <span class="md:hidden">Reset</span>
                    </Button>
                </div>
            </div>

            <!-- Active Filter Badges -->
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
                    Pencarian: "{{ search }}"
                    <button type="button" @click="search = ''"><X class="size-3" /></button>
                </span>
                <span
                    v-if="selectedKategori && selectedKategori !== 'semua'"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 capitalize"
                >
                    Kategori: {{ selectedKategori }}
                    <button type="button" @click="selectedKategori = 'semua'"><X class="size-3" /></button>
                </span>
                <span
                    v-if="selectedJenisIzin"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5"
                >
                    Izin: {{ selectedJenisIzin }}
                    <button type="button" @click="selectedJenisIzin = ''"><X class="size-3" /></button>
                </span>
                <span
                    v-if="selectedKewarganegaraan"
                    class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5"
                >
                    Negara: {{ selectedKewarganegaraan }}
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
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('kewarganegaraan')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Kewarganegaraan</span>
                                    <span v-if="sortBy === 'kewarganegaraan'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">Jenis Izin</th>
                            <th
                                class="px-4 py-3 cursor-pointer select-none hover:text-foreground transition-colors"
                                @click="handleSort('masa_berlaku')"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Masa Berlaku</span>
                                    <span v-if="sortBy === 'masa_berlaku'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </div>
                            </th>
                            <th class="px-4 py-3">Sisa Waktu</th>
                            <th class="px-4 py-3">Status Peringatan</th>
                            <th class="px-4 py-3 text-right w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <tr
                            v-for="item in wna.data"
                            :key="item.id"
                            class="hover:bg-muted/40 transition-colors group"
                            :class="{
                                'bg-red-50/20 dark:bg-red-950/10': item.urgensi === 'merah',
                                'bg-rose-50/20 dark:bg-rose-950/10': item.urgensi === 'kedaluwarsa',
                            }"
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

                            <!-- Nama & No. Register -->
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

                            <!-- No. Paspor -->
                            <td class="px-4 py-3 font-mono text-xs font-medium text-foreground">
                                {{ item.no_paspor }}
                            </td>

                            <!-- Kewarganegaraan -->
                            <td class="px-4 py-3 text-xs text-foreground font-medium max-w-[150px] truncate">
                                {{ item.kewarganegaraan }}
                            </td>

                            <!-- Jenis Izin Tinggal -->
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold font-mono"
                                    :class="{
                                        'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': item.jenis_izin_tinggal === 'ITAS',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300': item.jenis_izin_tinggal === 'ITAP',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': item.jenis_izin_tinggal === 'VOA',
                                        'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300': item.jenis_izin_tinggal === 'ITK',
                                    }"
                                >
                                    {{ item.jenis_izin_tinggal }}
                                </span>
                            </td>

                            <!-- Masa Berlaku -->
                            <td class="px-4 py-3 text-xs font-mono font-medium text-foreground">
                                {{ formatIndoDate(item.masa_berlaku) }}
                            </td>

                            <!-- Sisa Hari -->
                            <td class="px-4 py-3 text-xs font-semibold">
                                <span
                                    :class="{
                                        'text-red-600 dark:text-red-400': item.urgensi === 'merah',
                                        'text-amber-600 dark:text-amber-400': item.urgensi === 'kuning',
                                        'text-blue-600 dark:text-blue-400': item.urgensi === 'biru',
                                        'text-rose-700 dark:text-rose-400': item.urgensi === 'kedaluwarsa',
                                    }"
                                >
                                    {{ formatSisaHari(item.sisa_hari) }}
                                </span>
                            </td>

                            <!-- Status Urgensi Badge -->
                            <td class="px-4 py-3 text-xs">
                                <span
                                    v-if="item.urgensi === 'merah'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-950/60 dark:text-red-300"
                                >
                                    <span class="size-1.5 rounded-full bg-red-600 animate-pulse" />
                                    <span>Sangat Mendesak (0–14 Hari)</span>
                                </span>

                                <span
                                    v-else-if="item.urgensi === 'kuning'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300"
                                >
                                    <span class="size-1.5 rounded-full bg-amber-600" />
                                    <span>Mendesak (15–30 Hari)</span>
                                </span>

                                <span
                                    v-else-if="item.urgensi === 'biru'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300"
                                >
                                    <span class="size-1.5 rounded-full bg-blue-600" />
                                    <span>Perhatian (&gt; 30 Hari)</span>
                                </span>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/70 dark:text-rose-300"
                                >
                                    <AlertCircle class="size-3" />
                                    <span>Kedaluwarsa</span>
                                </span>
                            </td>

                            <!-- Aksi Menu -->
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        v-if="item.whatsapp_url"
                                        :href="item.whatsapp_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 shrink-0"
                                        title="Buka WhatsApp dengan pesan peringatan terisi"
                                    >
                                        <MessageCircle class="size-3.5" />
                                        <span>WhatsApp</span>
                                    </a>
                                    <button
                                        v-else
                                        type="button"
                                        disabled
                                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium bg-muted text-muted-foreground/50 cursor-not-allowed border border-border/40 shrink-0"
                                        title="Nomor telepon tidak tersedia atau tidak valid"
                                    >
                                        <MessageCircle class="size-3.5 opacity-50" />
                                        <span>WhatsApp</span>
                                    </button>

                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 gap-1 text-xs text-muted-foreground hover:text-foreground shrink-0"
                                        @click="openDetailModal(item)"
                                    >
                                        <Eye class="size-3.5" />
                                        <span>Detail</span>
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="wna.data.length === 0">
                            <td colspan="9" class="px-4 py-12 text-center text-muted-foreground">
                                <div class="mx-auto flex max-w-sm flex-col items-center justify-center gap-2">
                                    <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                                        <Clock class="size-6 text-muted-foreground" />
                                    </div>
                                    <p class="font-medium text-foreground">Tidak Ada Peringatan Izin Tinggal</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ hasActiveFilters ? 'Tidak ada data WNA yang cocok dengan kriteria filter saat ini.' : 'Tidak ada izin tinggal WNA yang akan berakhir pada periode pemantauan ini.' }}
                                    </p>
                                    <Button
                                        v-if="hasActiveFilters"
                                        variant="outline"
                                        size="sm"
                                        class="mt-2 text-xs"
                                        @click="resetFilters"
                                    >
                                        Bersihkan Filter
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="wna.total > 0"
                class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-border/80 px-4 py-3 text-xs text-muted-foreground bg-muted/20"
            >
                <div class="flex items-center gap-2">
                    <span>
                        Menampilkan
                        <strong class="font-medium text-foreground">{{ wna.from || 0 }}</strong>
                        sampai
                        <strong class="font-medium text-foreground">{{ wna.to || 0 }}</strong>
                        dari
                        <strong class="font-medium text-foreground">{{ wna.total }}</strong>
                        data peringatan
                    </span>

                    <span class="text-muted-foreground/50">&bull;</span>

                    <div class="flex items-center gap-1.5">
                        <span>Per baris:</span>
                        <select
                            v-model="perPage"
                            class="rounded border border-input bg-transparent px-2 py-0.5 text-xs text-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring dark:bg-card"
                        >
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Page Links -->
                <div class="flex items-center gap-1">
                    <template v-for="(link, idx) in wna.links" :key="idx">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 min-w-[32px] px-2 text-xs"
                            :class="link.active ? 'bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-500' : ''"
                            as-child
                        >
                            <Link :href="link.url" preserve-scroll preserve-state>
                                <span v-html="link.label" />
                            </Link>
                        </Button>
                        <span
                            v-else
                            class="px-2 py-1 text-xs text-muted-foreground/60 select-none"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Detail Modal Reuse -->
        <WnaDetailModal
            v-model:open="isDetailModalOpen"
            :wna="activeWna"
        />
    </div>
</template>
