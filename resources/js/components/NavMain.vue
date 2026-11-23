<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    label?: string;
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();

function hasActiveSubItem(item: NavItem): boolean {
    if (!item.subItems || item.subItems.length === 0) return false;
    return item.subItems.some((sub) => sub.href && isCurrentUrl(sub.href));
}
</script>

<template>
    <SidebarGroup class="px-2 py-0.5">
        <SidebarGroupLabel v-if="label" class="text-[11px] font-semibold tracking-wider text-muted-foreground/80 uppercase">
            {{ label }}
        </SidebarGroupLabel>
        <SidebarMenu class="gap-0.5">
            <template v-for="item in items" :key="item.title">
                <!-- Item with Submenus (Collapsible) -->
                <Collapsible
                    v-if="item.subItems && item.subItems.length > 0"
                    as-child
                    :default-open="hasActiveSubItem(item)"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton
                                :tooltip="item.title"
                                :is-active="hasActiveSubItem(item)"
                                class="h-8 py-1 px-2 transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground cursor-pointer"
                            >
                                <component :is="item.icon" v-if="item.icon" class="size-4 shrink-0" />
                                <span class="truncate font-medium">{{ item.title }}</span>
                                <ChevronRight class="ml-auto size-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub class="gap-0.5 py-0.5">
                                <SidebarMenuSubItem v-for="sub in item.subItems" :key="sub.title">
                                    <SidebarMenuSubButton as-child :is-active="sub.href ? isCurrentUrl(sub.href) : false" class="h-7 py-0.5">
                                        <Link :href="sub.href || '#'">
                                            <span>{{ sub.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <!-- Enabled Item -->
                <SidebarMenuItem v-else-if="!item.disabled">
                    <SidebarMenuButton
                        as-child
                        :is-active="item.href ? isCurrentUrl(item.href) : false"
                        :tooltip="item.title"
                        class="h-8 py-1 px-2 transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                    >
                        <Link :href="item.href || '#'" class="flex items-center gap-2">
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
                </SidebarMenuItem>

                <!-- Disabled Item with "Segera Tersedia" badge -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        :tooltip="`${item.title} (Segera tersedia)`"
                        aria-disabled="true"
                        class="h-8 py-1 px-2 cursor-not-allowed opacity-60 hover:bg-transparent hover:text-inherit"
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
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
