<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Calendar,
    CreditCard,
    FileText,
    Globe,
    IdCard,
    MapPin,
    Phone,
    Shield,
    User,
} from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { WnaItem } from '@/types/wna';

defineProps<{
    wna: WnaItem;
}>();

function formatIndoDate(dateStr?: string): string {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Head :title="`Detail WNA - ${wna.nama}`" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
        <PageHeader
            :title="`Detail WNA: ${wna.nama}`"
            description="Informasi rinci biodata identitas, nomor register, paspor, dan izin tinggal keimigrasian WNA."
        >
            <template #actions>
                <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                    <Link href="/wna">
                        <ArrowLeft class="size-4" />
                        <span>Kembali ke Daftar</span>
                    </Link>
                </Button>

                <Button size="sm" as-child class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs">
                    <Link :href="`/wna/${wna.id}/card`">
                        <CreditCard class="size-4" />
                        <span>Cetak Kartu WNA</span>
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <!-- Main Detail Card -->
        <div class="rounded-xl border border-border/80 bg-card p-6 shadow-2xs space-y-6">
            <!-- Profil Singkat dengan Foto -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 pb-6 border-b border-border/60">
                <div class="size-24 shrink-0 overflow-hidden rounded-xl border-2 border-border bg-muted shadow-sm">
                    <img
                        v-if="wna.foto_url"
                        :src="wna.foto_url"
                        :alt="wna.nama"
                        class="size-full object-cover"
                    />
                    <div v-else class="flex size-full items-center justify-center bg-secondary text-muted-foreground">
                        <User class="size-10" />
                    </div>
                </div>

                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-foreground truncate">
                            {{ wna.nama }}
                        </h2>
                        <span
                            class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold uppercase"
                            :class="{
                                'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300': wna.jenis_izin_tinggal === 'ITAS',
                                'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300': wna.jenis_izin_tinggal === 'ITAP',
                                'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': wna.jenis_izin_tinggal === 'VOA',
                            }"
                        >
                            {{ wna.jenis_izin_tinggal }}
                        </span>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        {{ wna.kewarganegaraan }} &bull; {{ wna.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </p>

                    <div class="flex flex-wrap gap-2 pt-1 text-xs font-mono">
                        <span class="rounded-md border border-border/70 bg-muted/40 px-2.5 py-1 text-foreground">
                            No. Register: <strong>{{ wna.no_register }}</strong>
                        </span>
                        <span class="rounded-md border border-border/70 bg-muted/40 px-2.5 py-1 text-foreground">
                            No. Paspor: <strong>{{ wna.no_paspor }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Grid Rincian Informasi -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Kolom Kiri: Izin Tinggal & Masa Berlaku -->
                <div class="rounded-lg border border-border/70 p-4 space-y-4 bg-muted/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                        <Calendar class="size-4 text-indigo-600 dark:text-indigo-400" />
                        <span>Izin Tinggal & Masa Berlaku</span>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-1 border-b border-border/40">
                            <span class="text-muted-foreground">Jenis Izin Tinggal</span>
                            <span class="font-semibold text-foreground">{{ wna.jenis_izin_tinggal }}</span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-border/40">
                            <span class="text-muted-foreground">Izin Tinggal Ke</span>
                            <span class="font-medium text-foreground">{{ wna.izin_tinggal_ke || '1 (Pertama)' }}</span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-border/40">
                            <span class="text-muted-foreground">Tanggal Berlaku Hingga</span>
                            <span class="font-semibold text-foreground">{{ formatIndoDate(wna.masa_berlaku) }}</span>
                        </div>

                        <div class="flex justify-between py-1 items-center">
                            <span class="text-muted-foreground">Status Urgensi</span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="{
                                    'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300': wna.urgensi === 'merah' || wna.urgensi === 'kedaluwarsa',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': wna.urgensi === 'kuning',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300': wna.urgensi === 'biru',
                                }"
                            >
                                <AlertCircle class="size-3.5" />
                                <span>{{ wna.sisa_hari < 0 ? 'Sudah Kedaluwarsa' : `${wna.sisa_hari} Hari Lagi` }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Penjamin & Alamat -->
                <div class="rounded-lg border border-border/70 p-4 space-y-4 bg-muted/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                        <Shield class="size-4 text-indigo-600 dark:text-indigo-400" />
                        <span>Data Penjamin & Domisili</span>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-1 border-b border-border/40">
                            <span class="text-muted-foreground">Nama Penjamin</span>
                            <span class="font-medium text-foreground">{{ wna.penjamin || '-' }}</span>
                        </div>

                        <div class="flex justify-between py-1 border-b border-border/40">
                            <span class="text-muted-foreground">Nomor Telepon</span>
                            <span class="font-medium text-foreground">{{ wna.nomor_telepon || '-' }}</span>
                        </div>

                        <div class="py-1">
                            <span class="text-muted-foreground block text-xs">Alamat Domisili Penjamin</span>
                            <p class="font-medium text-foreground mt-0.5 text-xs leading-relaxed">
                                {{ wna.alamat_penjamin || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
