<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { Card, CardContent } from '@/components/ui/card';

type Props = {
    title: string;
    icon: LucideIcon;
    value?: string | number;
    placeholderLabel?: string;
    description?: string;
    variant?: 'default' | 'warning' | 'primary' | 'success';
};

withDefaults(defineProps<Props>(), {
    value: undefined,
    placeholderLabel: 'Menunggu Query Phase 4',
    description: undefined,
    variant: 'default',
});
</script>

<template>
    <Card class="relative overflow-hidden border-border/80 shadow-xs transition-all hover:shadow-sm">
        <CardContent class="p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-xs font-medium text-muted-foreground">
                        {{ title }}
                    </p>
                    <div class="flex items-baseline gap-2">
                        <span v-if="value !== undefined" class="text-2xl font-bold tracking-tight text-foreground font-mono">
                            {{ value }}
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center rounded-sm border border-border/60 bg-muted/50 px-2 py-0.5 text-xs font-medium text-muted-foreground"
                        >
                            {{ placeholderLabel }}
                        </span>
                    </div>
                </div>

                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                    :class="{
                        'bg-primary/10 text-primary': variant === 'primary' || variant === 'default',
                        'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400': variant === 'warning',
                        'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400': variant === 'success',
                    }"
                >
                    <component :is="icon" class="size-5" />
                </div>
            </div>

            <p v-if="description" class="mt-3 text-[11px] text-muted-foreground/80 leading-normal">
                {{ description }}
            </p>
        </CardContent>
    </Card>
</template>
