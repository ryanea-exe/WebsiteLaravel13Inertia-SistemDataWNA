<script setup lang="ts">
import { BarChart3 } from '@lucide/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
    title: string;
    description?: string;
    placeholderNote?: string;
};

withDefaults(defineProps<Props>(), {
    description: undefined,
    placeholderNote: 'Visualisasi akan aktif saat modul data terhubung pada fase berikutnya.',
});
</script>

<template>
    <Card class="flex flex-col border-border/80 shadow-xs">
        <CardHeader class="flex flex-row items-start justify-between space-y-0 p-5 pb-3">
            <div class="space-y-1">
                <CardTitle class="text-sm font-semibold tracking-tight text-foreground">
                    {{ title }}
                </CardTitle>
                <p v-if="description" class="text-xs text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <span class="rounded-sm border border-border/70 bg-muted/60 px-2 py-0.5 text-[10px] font-medium text-muted-foreground">
                Placeholder
            </span>
        </CardHeader>
        <CardContent class="flex flex-1 flex-col justify-between p-5 pt-2">
            <!-- Area Visual Placeholder Transparan -->
            <div
                class="flex min-h-[220px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-border/80 bg-muted/20 p-6 text-center"
            >
                <div class="flex size-11 items-center justify-center rounded-full bg-muted text-muted-foreground/70">
                    <BarChart3 class="size-5" />
                </div>
                <h4 class="mt-3 text-xs font-semibold text-foreground/80">
                    Area Visualisasi Grafik
                </h4>
                <p class="mt-1 max-w-sm text-[11px] text-muted-foreground leading-relaxed">
                    {{ placeholderNote }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
