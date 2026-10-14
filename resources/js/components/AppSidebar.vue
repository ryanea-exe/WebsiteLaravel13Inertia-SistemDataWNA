<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Award,
    Calendar,
    Clock,
    FileCheck,
    FileSpreadsheet,
    FileText,
    LayoutGrid,
    Receipt,
    Settings,
    ShieldCheck,
    UserCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { useClock } from '@/composables/useClock';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem, User } from '@/types';

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as User | undefined);
const isAdmin = computed(() => currentUser.value?.role === 'admin');

const dashboardUrl = computed(() => dashboard().url);
const profileUrl = computed(() => {
    try {
        return editProfile().url;
    } catch {
        return '/settings/profile';
    }
});

const { isMounted, timeFormatted, dateFormatted } = useClock();

// Group 1: Modul Utama (Tersedia untuk Admin & User/Petugas)
const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
        disabled: false,
    },
    {
        title: 'Data WNA',
        href: '/wna',
        icon: Users,
        disabled: false,
    },
    {
        title: 'Pendaftaran ABG',
        href: '/abg',
        icon: UserCheck,
        disabled: false,
    },
    {
        title: 'Pendaftaran Affidavit',
        href: '/affidavit',
        icon: FileCheck,
        disabled: false,
    },
    {
        title: 'Early Warning',
        href: '/early-warning',
        icon: AlertTriangle,
        disabled: false,
    },
]);

// Group 2: Layanan & Rekap (Khusus Admin)
const serviceNavItems = computed<NavItem[]>(() => [
    {
        title: 'Permohonan',
        href: '/permohonan',
        icon: FileText,
        disabled: false,
    },
    {
        title: 'Laporan Izin Tinggal',
        href: '/laporan-izin-tinggal',
        icon: FileSpreadsheet,
        disabled: false,
    },
    {
        title: 'Penerbitan',
        href: '/penerbitan',
        icon: Award,
        disabled: false,
    },
    {
        title: 'Realisasi PNBP',
        href: '/realisasi-pnbp',
        icon: Receipt,
        disabled: false,
    },
]);

// Group 3: Administrasi Sistem (Khusus Admin)
const adminNavItems = computed<NavItem[]>(() => [
    {
        title: 'Data User',
        href: '#',
        icon: ShieldCheck,
        disabled: true,
        badge: 'Segera',
    },
    {
        title: 'Pengaturan',
        href: profileUrl.value,
        icon: Settings,
        disabled: false,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r border-sidebar-border/80">
        <SidebarHeader class="border-b border-sidebar-border/60 pb-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="transition-colors hover:bg-sidebar-accent">
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="space-y-1 py-2">
            <!-- Navigasi Utama (Admin & Petugas) -->
            <NavMain label="Operasional Data" :items="mainNavItems" />

            <!-- Navigasi Layanan & Rekapitulasi (Khusus Admin) -->
            <template v-if="isAdmin">
                <SidebarSeparator class="mx-3 my-2" />
                <NavMain label="Layanan & Rekap" :items="serviceNavItems" />

                <SidebarSeparator class="mx-3 my-2" />
                <NavMain label="Administrasi Sistem" :items="adminNavItems" />
            </template>
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/60 pt-2">
            <!-- Widget Jam & Tanggal Real-time Asia/Jakarta -->
            <div class="px-2 py-1.5 group-data-[collapsible=icon]:hidden">
                <div class="rounded-lg border border-sidebar-border/70 bg-sidebar-accent/40 p-2.5 text-xs">
                    <div class="flex items-center gap-1.5 font-semibold text-foreground">
                        <Clock class="size-3.5 text-primary shrink-0" />
                        <span class="font-mono tabular-nums text-[12px] font-bold tracking-tight">
                            {{ isMounted ? timeFormatted : 'Memuat waktu...' }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] text-muted-foreground">
                        <Calendar class="size-3 text-muted-foreground/70 shrink-0" />
                        <span class="truncate">{{ isMounted ? dateFormatted : 'Zona Asia/Jakarta' }}</span>
                    </div>
                </div>
            </div>

            <!-- Profil Pengguna & Dropdown -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
