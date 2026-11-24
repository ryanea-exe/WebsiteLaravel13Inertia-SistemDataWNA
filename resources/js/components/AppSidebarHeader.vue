<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Bell,
    CheckCircle2,
    ChevronsUpDown,
    Moon,
    Sun,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useInitials } from '@/composables/useInitials';
import type { AbgNotificationsPayload, BreadcrumbItem, NotificationsPayload, User } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as User | undefined);
const isAdmin = computed(() => currentUser.value?.role === 'admin');
const notifications = computed(() => page.props.notifications as NotificationsPayload | null | undefined);
const expiredCount = computed(() => notifications.value?.count ?? 0);
const expiredItems = computed(() => notifications.value?.items ?? []);
const displayedExpiredItems = computed(() => expiredItems.value.slice(0, 5));
const hasMoreExpired = computed(() => expiredCount.value > 5);

const notificationsAbg = computed(() => page.props.notifications_abg as AbgNotificationsPayload | null | undefined);
const abgExpiredCount = computed(() => notificationsAbg.value?.count ?? 0);
const abgExpiredItems = computed(() => notificationsAbg.value?.items ?? []);
const displayedAbgExpiredItems = computed(() => abgExpiredItems.value.slice(0, 5));
const hasMoreAbgExpired = computed(() => abgExpiredCount.value > 5);

const activeNotificationTab = ref<'wna' | 'abg'>('wna');
const totalExpiredCount = computed(() => expiredCount.value + abgExpiredCount.value);

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
}

const { getInitials } = useInitials();
const { resolvedAppearance, updateAppearance } = useAppearance();

const isDark = computed(() => resolvedAppearance.value === 'dark');

function toggleTheme(event?: MouseEvent) {
    updateAppearance(isDark.value ? 'light' : 'dark', event);
}

const activeBreadcrumbs = computed<BreadcrumbItem[]>(() => {
    if (props.breadcrumbs && props.breadcrumbs.length > 0) {
        return props.breadcrumbs;
    }

    const path = page.url.split('?')[0];
    const items: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

    if (path.startsWith('/wna')) {
        items.push({ title: 'Data WNA', href: '/wna' });
    } else if (path.startsWith('/abg')) {
        items.push({ title: 'Pendaftaran ABG', href: '/abg' });
    } else if (path.startsWith('/affidavit')) {
        items.push({ title: 'Pendaftaran Affidavit', href: '/affidavit' });
    } else if (path.startsWith('/early-warning/abg')) {
        items.push({ title: 'Early Warning', href: '/early-warning-wna' });
        items.push({ title: 'Data ABG', href: '/early-warning/abg' });
    } else if (path.startsWith('/early-warning-wna') || path.startsWith('/early-warning')) {
        items.push({ title: 'Early Warning', href: '/early-warning-wna' });
        items.push({ title: 'Data WNA', href: '/early-warning-wna' });
    } else if (path.startsWith('/permohonan')) {
        items.push({ title: 'Permohonan', href: '/permohonan' });
    } else if (path.startsWith('/laporan-izin-tinggal')) {
        items.push({ title: 'Laporan Izin Tinggal', href: '/laporan-izin-tinggal' });
    } else if (path.startsWith('/penerbitan')) {
        items.push({ title: 'Penerbitan', href: '/penerbitan' });
    } else if (path.startsWith('/realisasi-pnbp')) {
        items.push({ title: 'Realisasi PNBP', href: '/realisasi-pnbp' });
    } else if (path.startsWith('/data-user')) {
        items.push({ title: 'Data User', href: '/data-user' });
    } else if (path.startsWith('/pengaturan')) {
        items.push({ title: 'Pengaturan', href: '/pengaturan' });
    } else if (path.startsWith('/user-profile') || path.startsWith('/settings/profile') || path.startsWith('/profile')) {
        items.push({ title: 'User Profile', href: '/user-profile' });
    }

    return items;
});

const currentTitle = computed(() => {
    if (props.breadcrumbs && props.breadcrumbs.length > 0) {
        return props.breadcrumbs[props.breadcrumbs.length - 1].title;
    }

    const path = page.url.split('?')[0];
    if (path.startsWith('/wna')) return 'Data WNA';
    if (path.startsWith('/abg')) return 'Pendaftaran ABG';
    if (path.startsWith('/affidavit')) return 'Pendaftaran Affidavit';
    if (path.startsWith('/early-warning/abg')) return 'Early Warning Data ABG';
    if (path.startsWith('/early-warning')) return 'Early Warning Data WNA';
    if (path.startsWith('/permohonan')) return 'Permohonan';
    if (path.startsWith('/laporan-izin-tinggal')) return 'Laporan Izin Tinggal';
    if (path.startsWith('/penerbitan')) return 'Penerbitan';
    if (path.startsWith('/realisasi-pnbp')) return 'Realisasi PNBP';
    if (path.startsWith('/data-user')) return 'Data User';
    if (path.startsWith('/pengaturan')) return 'Pengaturan';
    if (path.startsWith('/user-profile') || path.startsWith('/settings/profile') || path.startsWith('/profile')) return 'User Profile';

    return 'Dashboard';
});

const displayName = computed(() => currentUser.value?.nama || currentUser.value?.name || currentUser.value?.username || 'Pengguna');
const avatarUrl = computed(() => currentUser.value?.foto_url || '');
const roleLabel = computed(() => (currentUser.value?.role === 'admin' ? 'Admin' : 'Petugas'));
</script>

<template>
    <header
        class="sticky top-0 z-10 flex h-16 shrink-0 items-center justify-between border-b border-border/70 bg-background/95 px-4 backdrop-blur-xs transition-[width,height] ease-linear md:px-6"
    >
        <!-- Sisi Kiri: Sidebar Toggle & Judul/Breadcrumbs Dinamis -->
        <div class="flex items-center gap-2.5 overflow-hidden">
            <SidebarTrigger class="-ml-1 text-muted-foreground hover:text-foreground" />
            <Separator orientation="vertical" class="h-4 bg-border/80" />

            <div class="flex items-center gap-2 truncate">
                <template v-if="activeBreadcrumbs && activeBreadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="activeBreadcrumbs" class="hidden sm:flex" />
                    <span class="truncate text-sm font-semibold text-foreground sm:hidden">
                        {{ currentTitle }}
                    </span>
                </template>
                <template v-else>
                    <span class="truncate text-sm font-semibold text-foreground">
                        {{ currentTitle }}
                    </span>
                </template>
            </div>
        </div>

        <!-- Sisi Kanan: Notifikasi, Toggle Tema, & Dropdown Profil -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            <!-- Dropdown Notifikasi Admin Tunggal dengan 2 Tab (WNA & ABG Kedaluwarsa) -->
            <DropdownMenu v-if="isAdmin">
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative size-9 text-muted-foreground hover:text-foreground focus-visible:ring-2 cursor-pointer"
                        aria-label="Notifikasi Peringatan Kedaluwarsa"
                        title="Notifikasi Peringatan Kedaluwarsa"
                    >
                        <Bell class="size-4.5" />
                        <span
                            v-if="totalExpiredCount > 0"
                            class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white shadow-xs"
                        >
                            {{ totalExpiredCount > 99 ? '99+' : totalExpiredCount }}
                        </span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-84 sm:w-96 p-0 text-left shadow-lg">
                    <!-- Tab Header -->
                    <div class="border-b border-border/60 bg-muted/40 p-2">
                        <div class="grid grid-cols-2 gap-1 rounded-lg bg-muted/80 p-1 text-xs">
                            <button
                                type="button"
                                class="flex items-center justify-center gap-1.5 rounded-md px-2.5 py-1.5 font-medium transition-colors cursor-pointer"
                                :class="activeNotificationTab === 'wna'
                                    ? 'bg-background text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'"
                                @click="activeNotificationTab = 'wna'"
                            >
                                <span>WNA Kedaluwarsa</span>
                                <span
                                    v-if="expiredCount > 0"
                                    class="rounded-full bg-red-100 px-1.5 py-0.2 text-[10px] font-bold text-red-700 dark:bg-red-950/80 dark:text-red-300"
                                >
                                    {{ expiredCount }}
                                </span>
                            </button>

                            <button
                                type="button"
                                class="flex items-center justify-center gap-1.5 rounded-md px-2.5 py-1.5 font-medium transition-colors cursor-pointer"
                                :class="activeNotificationTab === 'abg'
                                    ? 'bg-background text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'"
                                @click="activeNotificationTab = 'abg'"
                            >
                                <span>ABG Kedaluwarsa</span>
                                <span
                                    v-if="abgExpiredCount > 0"
                                    class="rounded-full bg-red-100 px-1.5 py-0.2 text-[10px] font-bold text-red-700 dark:bg-red-950/80 dark:text-red-300"
                                >
                                    {{ abgExpiredCount }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 1: WNA Content -->
                    <div v-show="activeNotificationTab === 'wna'">
                        <div v-if="expiredCount === 0" class="flex flex-col items-center justify-center px-4 py-8 text-center">
                            <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                                <CheckCircle2 class="size-5" />
                            </div>
                            <p class="mt-2 text-xs font-medium text-foreground">
                                Tidak Ada Izin Tinggal Kedaluwarsa
                            </p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Semua izin tinggal WNA masih dalam masa berlaku aktif.
                            </p>
                        </div>

                        <template v-else>
                            <div class="divide-y divide-border/50">
                                <Link
                                    v-for="item in displayedExpiredItems"
                                    :key="item.id"
                                    href="/early-warning-wna?kategori=kedaluwarsa"
                                    class="flex items-center gap-2.5 px-3.5 py-2 transition-colors hover:bg-muted/60 focus:bg-muted/80 focus:outline-hidden"
                                >
                                    <span class="size-1.5 shrink-0 rounded-full bg-red-600"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-semibold text-foreground">
                                            {{ item.nama }}
                                        </p>
                                        <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-muted-foreground truncate">
                                            <span class="font-mono">{{ item.no_paspor }}</span>
                                            <span>•</span>
                                            <span class="truncate">{{ item.kewarganegaraan }}</span>
                                            <span>•</span>
                                            <span class="shrink-0 font-medium text-red-600 dark:text-red-400">{{ formatDate(item.masa_berlaku) }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-900/60 dark:text-red-300">
                                        {{ item.keterangan }}
                                    </span>
                                </Link>

                                <div
                                    v-if="hasMoreExpired"
                                    class="flex items-center justify-center py-1.5 text-xs font-bold tracking-widest text-muted-foreground/70 bg-muted/15"
                                >
                                    ...
                                </div>
                            </div>

                            <div class="border-t border-border/60 bg-muted/20 p-2 text-center">
                                <Link
                                    href="/early-warning-wna?kategori=kedaluwarsa"
                                    class="flex items-center justify-center gap-1.5 py-1 text-xs font-medium text-primary hover:underline"
                                >
                                    <span>Lihat Selengkapnya</span>
                                    <ArrowRight class="size-3.5" />
                                </Link>
                            </div>
                        </template>
                    </div>

                    <!-- Tab 2: ABG Content -->
                    <div v-show="activeNotificationTab === 'abg'">
                        <div v-if="abgExpiredCount === 0" class="flex flex-col items-center justify-center px-4 py-8 text-center">
                            <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                                <CheckCircle2 class="size-5" />
                            </div>
                            <p class="mt-2 text-xs font-medium text-foreground">
                                Tidak Ada ABG Lewat 21 Tahun
                            </p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Semua data ABG belum melampaui batas usia 21 tahun.
                            </p>
                        </div>

                        <template v-else>
                            <div class="divide-y divide-border/50">
                                <Link
                                    v-for="item in displayedAbgExpiredItems"
                                    :key="item.id"
                                    href="/early-warning/abg?kategori=lewat_21"
                                    class="flex items-center gap-2.5 px-3.5 py-2 transition-colors hover:bg-muted/60 focus:bg-muted/80 focus:outline-hidden"
                                >
                                    <span class="size-1.5 shrink-0 rounded-full bg-red-600"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-semibold text-foreground">
                                            {{ item.nama }}
                                        </p>
                                        <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-muted-foreground truncate">
                                            <span class="font-mono">{{ item.no_register }}</span>
                                            <span>•</span>
                                            <span class="truncate">{{ item.kewarganegaraan }}</span>
                                            <span>•</span>
                                            <span class="shrink-0 font-medium text-red-600 dark:text-red-400">Umur: {{ item.umur_detail || (item.lewat_hari ? `${item.lewat_hari} hari` : '-') }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-900/60 dark:text-red-300">
                                        {{ item.keterangan }}
                                    </span>
                                </Link>

                                <div
                                    v-if="hasMoreAbgExpired"
                                    class="flex items-center justify-center py-1.5 text-xs font-bold tracking-widest text-muted-foreground/70 bg-muted/15"
                                >
                                    ...
                                </div>
                            </div>

                            <div class="border-t border-border/60 bg-muted/20 p-2 text-center">
                                <Link
                                    href="/early-warning/abg?kategori=lewat_21"
                                    class="flex items-center justify-center gap-1.5 py-1 text-xs font-medium text-primary hover:underline"
                                >
                                    <span>Lihat Selengkapnya</span>
                                    <ArrowRight class="size-3.5" />
                                </Link>
                            </div>
                        </template>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Kontrol Toggle Tema (Tepat di sebelah kanan ikon notifikasi) -->
            <Button
                variant="ghost"
                size="icon"
                class="size-9 text-muted-foreground hover:text-foreground focus-visible:ring-2 cursor-pointer transition-colors"
                :aria-label="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
                :title="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
                @click="toggleTheme"
                data-test="theme-toggle-button"
            >
                <Sun v-if="isDark" class="size-4.5 text-amber-500 transition-transform duration-200 hover:rotate-45" />
                <Moon v-else class="size-4.5 text-slate-700 transition-transform duration-200 hover:-rotate-12 dark:text-slate-200" />
                <span class="sr-only">
                    {{ isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap' }}
                </span>
            </Button>

            <Separator orientation="vertical" class="hidden h-5 sm:block bg-border/80" />

            <!-- Dropdown User Profile Header -->
            <DropdownMenu v-if="currentUser">
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="ghost"
                        class="flex h-9.5 items-center gap-2 px-1.5 py-1 text-left hover:bg-accent/60 sm:px-2 cursor-pointer"
                        aria-label="Menu Pengguna"
                    >
                        <Avatar class="size-7.5 shrink-0 overflow-hidden rounded-md border border-border/80">
                            <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="displayName" class="object-cover" />
                            <AvatarFallback class="rounded-md bg-primary/10 text-xs font-semibold text-primary">
                                {{ getInitials(displayName) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="hidden flex-col leading-tight sm:flex">
                            <span class="max-w-[120px] truncate text-xs font-semibold text-foreground">
                                {{ displayName }}
                            </span>
                            <span class="text-[10px] font-medium text-muted-foreground">
                                {{ roleLabel }}
                            </span>
                        </div>
                        <ChevronsUpDown class="hidden size-3.5 text-muted-foreground sm:block" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-64 rounded-xl shadow-lg" :side-offset="8">
                    <UserMenuContent :user="currentUser" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
