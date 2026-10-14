<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    label?: string;
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-1.5">
        <SidebarGroupLabel v-if="label" class="text-[11px] font-semibold tracking-wider text-muted-foreground/80 uppercase">
            {{ label }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- Enabled Item -->
                <SidebarMenuButton
                    v-if="!item.disabled"
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                >
                    <Link :href="item.href" class="flex items-center gap-2">
                        <component :is="item.icon" v-if="item.icon" class="size-4 shrink-0" />
                        <span class="truncate font-medium">{{ item.title }}</span>
                        <span
                            v-if="item.badge"
                            class="ml-auto inline-flex items-center rounded-full bg-primary/10 px-1.5 py-0.2 text-[10px] font-semibold text-primary"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </SidebarMenuButton>

                <!-- Disabled Item with "Segera Tersedia" badge -->
                <SidebarMenuButton
                    v-else
                    :tooltip="`${item.title} (Segera tersedia)`"
                    aria-disabled="true"
                    class="cursor-not-allowed opacity-60 hover:bg-transparent hover:text-inherit"
                >
                    <div class="flex w-full items-center gap-2">
                        <component :is="item.icon" v-if="item.icon" class="size-4 shrink-0 text-muted-foreground/60" />
                        <span class="truncate text-muted-foreground">{{ item.title }}</span>
                        <span
                            class="ml-auto rounded-sm border border-border/80 bg-muted/60 px-1.5 py-0.5 text-[9px] font-medium text-muted-foreground group-data-[collapsible=icon]:hidden"
                        >
                            {{ item.badge || 'Segera' }}
                        </span>
                    </div>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
