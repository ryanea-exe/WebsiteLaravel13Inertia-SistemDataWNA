<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    ChevronDown,
    Clock,
    MoreHorizontal,
    Pencil,
    Plus,
    RefreshCw,
    Search,
    Shield,
    ShieldCheck,
    Trash2,
    User as UserIcon,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { cleanBrowserUrl } from '@/lib/cleanUrl';
import DataUserDeleteDialog from '@/components/data-user/DataUserDeleteDialog.vue';
import DataUserFormModal from '@/components/data-user/DataUserFormModal.vue';
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
import type {
    DataUserPageProps,
    RoleOption,
    UserItem,
} from '@/types/data-user';

const props = defineProps<DataUserPageProps>();

const page = usePage();

// Local filter states
const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const sortBy = ref(props.filters.sort_by || '');
const sortOrder = ref<'asc' | 'desc'>((props.filters.sort_order as 'asc' | 'desc') || 'asc');
const perPage = ref(props.filters.per_page || 50);

// Modal states
const isFormModalOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const activeUser = ref<UserItem | null>(null);

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/data-user',
        {
            search: search.value || undefined,
            role: selectedRole.value || undefined,
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

watch(selectedRole, () => {
    applyFilters();
});

watch(perPage, () => {
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
    selectedRole.value = '';
    sortBy.value = '';
    sortOrder.value = 'asc';
    perPage.value = 50;
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return !!search.value || !!selectedRole.value || !!sortBy.value || perPage.value !== 50;
});

function openCreateModal() {
    activeUser.value = null;
    isFormModalOpen.value = true;
}

function openEditModal(item: UserItem) {
    activeUser.value = item;
    isFormModalOpen.value = true;
}

function openDeleteDialog(item: UserItem) {
    activeUser.value = item;
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

function formatIndoDateTime(dateTimeStr?: string | null): string {
    if (!dateTimeStr) return 'Belum pernah';
    try {
        const date = new Date(dateTimeStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    } catch {
        return dateTimeStr;
    }
}
</script>

<template>
    <Head title="Data User — Manajemen Pengguna" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <!-- Header Section -->
        <PageHeader
            title="Data User"
            description="Kelola akun pengguna, peran hak akses, dan kredensial sistem secara terpusat."
        >
            <template #actions>
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        v-if="can.create"
                        size="sm"
                        class="gap-1.5 shadow-xs"
                        @click="openCreateModal"
                    >
                        <Plus class="size-4" />
                        <span>Tambah User</span>
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
                <div class="relative md:col-span-7">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama lengkap, username, atau email..."
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

                <!-- Filter Peran / Role -->
                <div class="relative md:col-span-4">
                    <select
                        v-model="selectedRole"
                        class="h-9 w-full appearance-none rounded-md border border-input bg-transparent pl-3 pr-8 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer dark:bg-card"
                    >
                        <option value="">Semua Peran (Role)</option>
                        <option v-for="item in roleOptions" :key="item.value" :value="item.value">
                            {{ item.label }}
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

        <!-- Tabel Data User -->
        <div class="rounded-xl border border-border/80 bg-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border/80 bg-muted/40 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th scope="col" class="px-4 py-3 w-12 text-center">No</th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('nama')">
                                <div class="flex items-center gap-1.5">
                                    <span>Nama User</span>
                                    <span v-if="sortBy === 'nama'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('username')">
                                <div class="flex items-center gap-1.5">
                                    <span>Username</span>
                                    <span v-if="sortBy === 'username'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('role')">
                                <div class="flex items-center gap-1.5">
                                    <span>Role</span>
                                    <span v-if="sortBy === 'role'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('terakhir_login')">
                                <div class="flex items-center gap-1.5">
                                    <span>Terakhir Login</span>
                                    <span v-if="sortBy === 'terakhir_login'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="handleSort('created_at')">
                                <div class="flex items-center gap-1.5">
                                    <span>Tanggal Dibuat</span>
                                    <span v-if="sortBy === 'created_at'" class="text-foreground">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <!-- Empty State -->
                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                    <UserIcon class="size-6" />
                                </div>
                                <h3 class="text-sm font-semibold text-foreground">Tidak Ada Data Pengguna</h3>
                                <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                                    {{ hasActiveFilters ? 'Tidak ada data pengguna yang cocok dengan kriteria pencarian atau filter yang dipilih.' : 'Belum ada pengguna tambahan yang terdaftar di dalam sistem.' }}
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
                                <Button
                                    v-else-if="can.create"
                                    size="sm"
                                    class="mt-4 gap-1.5"
                                    @click="openCreateModal"
                                >
                                    <Plus class="size-4" />
                                    <span>Tambah Pengguna Baru</span>
                                </Button>
                            </td>
                        </tr>

                        <!-- Data Rows -->
                        <tr
                            v-for="(item, idx) in users.data"
                            :key="item.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                {{ ((users.current_page - 1) * users.per_page) + idx + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative size-9 shrink-0 overflow-hidden rounded-full border border-border/80 bg-muted flex items-center justify-center">
                                        <img
                                            v-if="item.foto_url"
                                            :src="item.foto_url"
                                            :alt="item.nama"
                                            class="size-full object-cover"
                                        />
                                        <UserIcon v-else class="size-4 text-muted-foreground/70" />
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-foreground truncate max-w-[200px]">
                                                {{ item.nama }}
                                            </span>
                                            <span
                                                v-if="item.id === authUserId"
                                                class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium bg-primary/10 text-primary border border-primary/20"
                                            >
                                                Anda
                                            </span>
                                        </div>
                                        <span class="text-xs text-muted-foreground truncate max-w-[200px]">
                                            {{ item.email || '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-foreground font-medium">
                                @{{ item.username }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="item.role === 'admin'"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800"
                                >
                                    <ShieldCheck class="size-3.5" />
                                    <span>Administrator</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700"
                                >
                                    <Shield class="size-3.5" />
                                    <span>Petugas</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <div class="flex items-center gap-1.5">
                                    <Clock class="size-3 text-muted-foreground/70 shrink-0" />
                                    <span>{{ formatIndoDateTime(item.terakhir_login) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <div class="flex items-center gap-1.5">
                                    <Calendar class="size-3 text-muted-foreground/70 shrink-0" />
                                    <span>{{ formatIndoDate(item.created_at) }}</span>
                                </div>
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
                                            <DropdownMenuItem class="cursor-pointer" @click="openEditModal(item)">
                                                <Pencil class="mr-2 size-4" />
                                                <span>Ubah Data</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="item.id !== authUserId"
                                                class="cursor-pointer text-red-600 focus:bg-red-50 focus:text-red-700 dark:focus:bg-red-950/30"
                                                @click="openDeleteDialog(item)"
                                            >
                                                <Trash2 class="mr-2 size-4" />
                                                <span>Hapus Data</span>
                                            </DropdownMenuItem>
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
                        <strong class="font-medium text-foreground">{{ users.from || 0 }}</strong>
                        sampai
                        <strong class="font-medium text-foreground">{{ users.to || 0 }}</strong>
                        dari
                        <strong class="font-medium text-foreground">{{ users.total }}</strong>
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
                <div v-if="users.links && users.links.length > 3" class="flex items-center gap-1">
                    <template v-for="(link, index) in users.links" :key="index">
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
        <DataUserFormModal
            v-model:open="isFormModalOpen"
            :user="activeUser"
            :role-options="roleOptions"
            :auth-user-id="authUserId"
        />

        <DataUserDeleteDialog
            v-model:open="isDeleteDialogOpen"
            :user="activeUser"
        />
    </div>
</template>
