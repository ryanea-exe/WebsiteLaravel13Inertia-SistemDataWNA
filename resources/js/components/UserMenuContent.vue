<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, User as UserIcon } from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-2 py-2 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="flex w-full cursor-pointer items-center gap-2 px-2 py-1.5 text-xs font-medium" :href="edit()" prefetch>
                <UserIcon class="size-3 text-muted-foreground" />
                <span>Edit Profile</span>
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link
                class="flex w-full cursor-pointer items-center gap-2 px-2 py-1.5 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10 focus:text-destructive"
                :href="logout()"
                method="post"
                as="button"
                @click="handleLogout"
                data-test="logout-button"
            >
                <LogOut class="size-3" />
                <span>Keluar (Logout)</span>
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
