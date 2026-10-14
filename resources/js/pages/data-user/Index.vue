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
import DataUserDeleteDialog from '@/components/data-user/DataUserDeleteDialog.vue';
import DataUserFormModal from '@/components/data-user/DataUserFormModal.vue';
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
import type {
    DataUserPageProps,
    RoleOption,
    UserItem,
} from '@/types/data-user';

const props = defineProps<DataUserPageProps>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success);
const flashError = computed(() => (page.props.flash as { error?: string })?.error);

// Local filter states
const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref<'asc' | 'desc'>(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 10);

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
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    perPage.value = 10;
    applyFilters();
}

const hasActiveFilters = computed(() => {
    return !!search.value || !!selectedRole.value || sortBy.value !== 'created_at' || sortOrder.value !== 'desc' || perPage.value !== 10;
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
                <div class="relative lg:col-span-2">
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
                <div>
                    <select
                        v-model="selectedRole"
                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs sm:text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        <option value="">Semua Peran (Role)</option>
                        <option v-for="item in roleOptions" :key="item.value" :value="item.value">
                            {{ item.label }}
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
                    <span>per halaman (Total: <strong class="text-foreground font-semibold">{{ users.total }}</strong> pengguna)</span>
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

        <!-- Tabel Data User -->
        <div class="rounded-xl border border-border/70 bg-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-xs font-semibold text-muted-foreground">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('nama')">
                                <div class="flex items-center gap-1.5">
                                    <span>Pengguna</span>
                                    <ChevronDown v-if="sortBy === 'nama'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('username')">
                                <div class="flex items-center gap-1.5">
                                    <span>Username</span>
                                    <ChevronDown v-if="sortBy === 'username'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('role')">
                                <div class="flex items-center gap-1.5">
                                    <span>Peran (Role)</span>
                                    <ChevronDown v-if="sortBy === 'role'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('terakhir_login')">
                                <div class="flex items-center gap-1.5">
                                    <span>Terakhir Login</span>
                                    <ChevronDown v-if="sortBy === 'terakhir_login'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 cursor-pointer hover:text-foreground select-none" @click="handleSort('created_at')">
                                <div class="flex items-center gap-1.5">
                                    <span>Tanggal Dibuat</span>
                                    <ChevronDown v-if="sortBy === 'created_at'" :class="['size-3.5 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" />
                                </div>
                            </th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <!-- Empty State -->
                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                                        <UserIcon class="size-6 text-muted-foreground/60" />
                                    </div>
                                    <div class="text-sm font-semibold text-foreground">Tidak Ada Data Pengguna</div>
                                    <p class="text-xs text-muted-foreground max-w-sm">
                                        {{ hasActiveFilters ? 'Tidak ada data pengguna yang cocok dengan kriteria pencarian atau filter yang dipilih.' : 'Belum ada pengguna tambahan yang terdaftar di dalam sistem.' }}
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
                                    <Button
                                        v-else-if="can.create"
                                        size="sm"
                                        class="mt-2 text-xs gap-1.5"
                                        @click="openCreateModal"
                                    >
                                        <Plus class="size-3.5" />
                                        <span>Tambah Pengguna Baru</span>
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <!-- Data Rows -->
                        <tr
                            v-for="(item, idx) in users.data"
                            :key="item.id"
                            class="hover:bg-muted/30 transition-colors"
                        >
                            <td class="py-3 px-4 text-center text-xs text-muted-foreground">
                                {{ ((users.current_page - 1) * users.per_page) + idx + 1 }}
                            </td>
                            <td class="py-3 px-4">
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
                            <td class="py-3 px-4 font-mono text-xs text-foreground font-medium">
                                @{{ item.username }}
                            </td>
                            <td class="py-3 px-4">
                                <span
                                    v-if="item.role === 'admin'"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800"
                                >
                                    <ShieldCheck class="size-3.5" />
                                    <span>Administrator</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700"
                                >
                                    <Shield class="size-3.5" />
                                    <span>Petugas User</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">
                                <div class="flex items-center gap-1.5">
                                    <Clock class="size-3 text-muted-foreground/70 shrink-0" />
                                    <span>{{ formatIndoDateTime(item.terakhir_login) }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">
                                <div class="flex items-center gap-1.5">
                                    <Calendar class="size-3 text-muted-foreground/70 shrink-0" />
                                    <span>{{ formatIndoDate(item.created_at) }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-8 text-muted-foreground hover:text-foreground"
                                        title="Ubah Data Pengguna"
                                        @click="openEditModal(item)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>

                                    <Button
                                        v-if="item.id !== authUserId"
                                        variant="ghost"
                                        size="icon"
                                        class="size-8 text-muted-foreground hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40"
                                        title="Hapus Pengguna"
                                        @click="openDeleteDialog(item)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                    <span
                                        v-else
                                        class="inline-flex size-8 items-center justify-center text-muted-foreground/40"
                                        title="Anda tidak dapat menghapus akun Anda sendiri"
                                    >
                                        <Trash2 class="size-4 opacity-30 cursor-not-allowed" />
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                v-if="users.data.length > 0"
                class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-border/70 text-xs text-muted-foreground"
            >
                <div>
                    Menampilkan
                    <strong class="font-semibold text-foreground">{{ users.from || 0 }}</strong>
                    sampai
                    <strong class="font-semibold text-foreground">{{ users.to || 0 }}</strong>
                    dari
                    <strong class="font-semibold text-foreground">{{ users.total }}</strong>
                    pengguna
                </div>

                <div class="flex items-center gap-1.5">
                    <template v-for="(link, i) in users.links" :key="i">
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
