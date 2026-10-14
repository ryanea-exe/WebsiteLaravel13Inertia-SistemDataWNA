<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Globe,
    IdCard,
    MapPin,
    Phone,
    User,
    Users,
} from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { AbgItem } from '@/types/abg';

defineProps<{
    abg: AbgItem;
}>();

function formatIndoDate(dateStr?: string | null): string {
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
    <Head :title="`Detail Pendaftaran ABG - ${abg.nama}`" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
        <PageHeader
            :title="`Detail Pendaftaran ABG: ${abg.nama}`"
            description="Informasi rinci biodata identitas, nomor register, paspor, dan data orang tua Anak Berkewarganegaraan Ganda."
        >
            <template #actions>
                <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                    <Link href="/abg">
                        <ArrowLeft class="size-4" />
                        <span>Kembali ke Daftar</span>
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
                        v-if="abg.foto_url"
                        :src="abg.foto_url"
                        :alt="abg.nama"
                        class="size-full object-cover"
                    />
                    <div v-else class="flex size-full items-center justify-center bg-secondary text-muted-foreground">
                        <User class="size-10" />
                    </div>
                </div>

                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-foreground truncate">
                            {{ abg.nama }}
                        </h2>
                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                            ABG
                        </span>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        {{ abg.kewarganegaraan }} &bull; {{ abg.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </p>

                    <div class="flex flex-wrap gap-2 pt-1 text-xs">
                        <span class="rounded-md bg-muted px-2.5 py-1 font-mono font-medium text-foreground border border-border">
                            No. Register: {{ abg.no_register }}
                        </span>
                        <span class="rounded-md bg-muted px-2.5 py-1 font-mono font-medium text-foreground border border-border">
                            No. Paspor: {{ abg.no_paspor }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Kelahiran & Kewarganegaraan -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-border/70 p-4 space-y-1.5 bg-muted/20">
                    <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                        <Calendar class="size-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Tempat / Tanggal Lahir</span>
                    </div>
                    <p class="text-base font-semibold text-foreground">
                        {{ abg.tempat_lahir || '-' }}, {{ formatIndoDate(abg.tanggal_lahir) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border/70 p-4 space-y-1.5 bg-muted/20">
                    <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                        <Globe class="size-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Kewarganegaraan Ganda</span>
                    </div>
                    <p class="text-base font-semibold text-foreground truncate">
                        {{ abg.kewarganegaraan }}
                    </p>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="space-y-3 pt-2">
                <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                    <Users class="size-4 text-emerald-600" />
                    <span>Data Orang Tua / Wali</span>
                </h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-border/80 p-4 space-y-2 bg-card">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Ayah Kandung</span>
                        <p class="text-base font-bold text-foreground">{{ abg.nama_ayah || '-' }}</p>
                        <div class="text-xs text-muted-foreground flex items-center gap-1.5">
                            <Globe class="size-3.5" />
                            <span>Kewarganegaraan: {{ abg.kewarganegaraan_ayah || '-' }}</span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border/80 p-4 space-y-2 bg-card">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Ibu Kandung</span>
                        <p class="text-base font-bold text-foreground">{{ abg.nama_ibu || '-' }}</p>
                        <div class="text-xs text-muted-foreground flex items-center gap-1.5">
                            <Globe class="size-3.5" />
                            <span>Kewarganegaraan: {{ abg.kewarganegaraan_ibu || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak & Domisili -->
            <div class="space-y-3 pt-2">
                <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                    <MapPin class="size-4 text-emerald-600" />
                    <span>Kontak & Domisili</span>
                </h3>

                <div class="rounded-lg border border-border/80 p-4 space-y-3 bg-card text-sm">
                    <div class="flex items-center gap-3">
                        <Phone class="size-4 text-muted-foreground shrink-0" />
                        <span class="text-muted-foreground w-28">No. Telepon:</span>
                        <span class="font-medium text-foreground">{{ abg.nomor_telepon || '-' }}</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <MapPin class="size-4 text-muted-foreground shrink-0 mt-0.5" />
                        <span class="text-muted-foreground w-28">Alamat Domisili:</span>
                        <span class="font-medium text-foreground leading-relaxed flex-1">{{ abg.alamat || '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
