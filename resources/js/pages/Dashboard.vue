<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    FileCheck,
    Info,
    Shield,
    UserCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import ChartCard from '@/components/ChartCard.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { User } from '@/types';

defineOptions({
    layout: () => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard().url,
            },
        ],
    }),
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as User | undefined);
const isAdmin = computed(() => currentUser.value?.role === 'admin');
</script>

<template>
    <Head title="Dashboard Utama" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Header Halaman -->
        <PageHeader
            title="Dashboard Operasional"
            description="Ringkasan pemantauan data WNA, pendaftaran ABG & affidavit, serta peringatan masa berlaku izin tinggal."
        >
            <template #actions>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-foreground shadow-xs">
                    <Shield class="size-3.5" :class="isAdmin ? 'text-indigo-600' : 'text-emerald-600'" />
                    <span>Mode Akses:</span>
                    <span
                        class="font-semibold"
                        :class="isAdmin ? 'text-indigo-600 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400'"
                    >
                        {{ isAdmin ? 'Administrator Sistem' : 'Petugas Operasional' }}
                    </span>
                </div>
            </template>
        </PageHeader>

        <!-- Baris Kartu Statistik (Reusables dengan Label Transparan) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Total Data WNA"
                :icon="Users"
                placeholder-label="Siap dihubungkan"
                description="Basis data Warga Negara Asing pemegang izin tinggal ITAS, ITAP, dan VOA."
                variant="primary"
            />
            <StatCard
                title="Pendaftaran ABG"
                :icon="UserCheck"
                placeholder-label="Siap dihubungkan"
                description="Registrasi Anak Berkewarganegaraan Ganda sesuai ketentuan perundang-undangan."
                variant="default"
            />
            <StatCard
                title="Pendaftaran Affidavit"
                :icon="FileCheck"
                placeholder-label="Siap dihubungkan"
                description="Penerbitan surat keimigrasian fasilitas affidavit kewarganegaraan ganda."
                variant="default"
            />
            <StatCard
                title="Early Warning Izin Tinggal"
                :icon="AlertTriangle"
                placeholder-label="Siap dihubungkan"
                description="Pemantauan WNA dengan masa berlaku izin tinggal berakhir dalam 3 bulan."
                variant="warning"
            />
        </div>

        <!-- Baris Grafik Visualisasi (Container Reusable) -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Tren Pendaftaran WNA Bulanan"
                description="Visualisasi perkembangan jumlah WNA terdaftar sepanjang tahun berjalan."
                placeholder-note="Grafik tren bulanan akan dikalkulasi otomatis dari database setelah modul Data WNA (Phase 4) dan Dashboard (Phase 8) terhubung."
            />
            <ChartCard
                title="Distribusi Jenis Izin Tinggal"
                description="Persebaran kategori izin tinggal aktif berdasarkan ITAS, ITAP, dan VOA."
                placeholder-note="Diagram lingkaran distribusi izin tinggal akan dirender dinamis dari query database agregat pada implementasi Phase 8."
            />
        </div>

        <!-- Card Informasi Panduan Hak Akses & Status Modul -->
        <Card class="border-border/80 bg-muted/20 shadow-xs">
            <CardContent class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <Info class="size-4" />
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-semibold text-foreground">
                            Status Pengembangan & Panduan Otorisasi
                        </h4>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            {{
                                isAdmin
                                    ? 'Sebagai Administrator, Anda memiliki hak kelola penuh untuk seluruh modul, termasuk permohonan, laporan izin tinggal, penerbitan, realisasi PNBP, dan data user.'
                                    : 'Sebagai Petugas Operasional, akses Anda difokuskan pada pemantauan data WNA, pendaftaran ABG, pendaftaran affidavit, dan early warning secara read-only.'
                            }}
                            Menu navigasi dengan penanda <span class="font-medium text-foreground">"Segera"</span> akan diaktifkan secara bertahap pada fase berikutnya.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
