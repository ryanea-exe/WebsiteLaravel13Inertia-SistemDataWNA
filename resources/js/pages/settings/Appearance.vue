<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Pengaturan Tampilan" />

    <h1 class="sr-only">Pengaturan Tampilan</h1>

    <div class="space-y-6 max-w-xl p-4 md:p-6">
        <Heading
            variant="small"
            title="Pengaturan Tampilan"
            description="Pengaturan tema terang dan gelap kini dapat diakses langsung melalui tombol toggle tema pada header aplikasi di samping ikon notifikasi."
        />
    </div>
</template>
