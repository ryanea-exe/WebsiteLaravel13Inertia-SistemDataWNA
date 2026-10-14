<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Bell, ChevronsUpDown } from '@lucide/vue';
import { computed } from 'vue';
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
import { useInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, User } from '@/types';

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
const { getInitials } = useInitials();

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
    } else if (path.startsWith('/early-warning')) {
        items.push({ title: 'Early Warning', href: '/early-warning' });
    } else if (path.startsWith('/permohonan')) {
        items.push({ title: 'Permohonan Keimigrasian', href: '/permohonan' });
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
    if (path.startsWith('/early-warning')) return 'Early Warning';
    if (path.startsWith('/permohonan')) return 'Permohonan Keimigrasian';

    return 'Dashboard';
});

const displayName = computed(() => currentUser.value?.nama || currentUser.value?.name || currentUser.value?.username || 'Pengguna');
const avatarUrl = computed(() => currentUser.value?.foto || currentUser.value?.avatar || '');
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

        <!-- Sisi Kanan: Notifikasi (Placeholder Jelas) & Dropdown Profil -->
        <div class="flex items-center gap-2">
            <!-- Dropdown Notifikasi Placeholder Transparan -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative size-9 text-muted-foreground hover:text-foreground focus-visible:ring-2"
                        aria-label="Pusat Notifikasi"
                    >
                        <Bell class="size-4.5" />
                        <span class="absolute top-2 right-2 size-2 rounded-full bg-primary/70"></span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-80 p-4 text-left shadow-lg">
                    <div class="flex items-center justify-between border-b border-border/60 pb-2">
                        <div class="flex items-center gap-1.5">
                            <Bell class="size-4 text-primary" />
                            <h4 class="text-sm font-semibold text-foreground">Pusat Pemberitahuan</h4>
                        </div>
                        <span class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                            Placeholder
                        </span>
                    </div>
                    <div class="py-6 text-center text-xs text-muted-foreground">
                        <p class="font-medium text-foreground/80">Belum ada notifikasi baru.</p>
                        <p class="mt-1 text-[11px] text-muted-foreground/80">
                            Fitur peringatan dini & notifikasi otomatis izin tinggal akan aktif pada fase berikutnya.
                        </p>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>

            <Separator orientation="vertical" class="hidden h-5 sm:block bg-border/80" />

            <!-- Dropdown User Profile Header -->
            <DropdownMenu v-if="currentUser">
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="ghost"
                        class="flex h-9.5 items-center gap-2 px-1.5 py-1 text-left hover:bg-accent/60 sm:px-2"
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
