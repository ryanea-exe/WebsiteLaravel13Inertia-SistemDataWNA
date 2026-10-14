<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { Team, User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
    showRole?: boolean;
    team?: Team | null;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
    showRole: true,
    team: null,
});

const { getInitials } = useInitials();

const displayName = computed(() => props.user.nama || props.user.name || props.user.username);
const avatarUrl = computed(() => props.user.foto || props.user.avatar || '');
const roleLabel = computed(() => (props.user.role === 'admin' ? 'Administrator' : 'Petugas'));
</script>

<template>
    <Avatar class="size-8.5 shrink-0 overflow-hidden rounded-lg border border-border/60">
        <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="displayName" class="object-cover" />
        <AvatarFallback class="rounded-lg bg-primary/10 text-xs font-semibold text-primary">
            {{ getInitials(displayName) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left leading-tight">
        <div class="flex items-center gap-1.5">
            <span class="truncate text-sm font-semibold text-foreground">{{ displayName }}</span>
            <span
                v-if="showRole"
                class="inline-flex shrink-0 items-center rounded-sm px-1.5 py-0.2 text-[10px] font-semibold"
                :class="
                    user.role === 'admin'
                        ? 'bg-indigo-500/10 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300'
                        : 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300'
                "
            >
                {{ roleLabel }}
            </span>
        </div>
        <span class="truncate text-xs text-muted-foreground">@{{ user.username }}</span>
        <span v-if="team" class="truncate text-[11px] text-muted-foreground/80">{{ team.name }}</span>
        <span v-else-if="showEmail && user.email" class="truncate text-[11px] text-muted-foreground/80">
            {{ user.email }}
        </span>
    </div>
</template>
