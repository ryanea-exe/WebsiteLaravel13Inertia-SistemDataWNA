<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calendar, Eye, Globe, IdCard, MapPin, Phone, User, Users } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { AbgItem } from '@/types/abg';

defineProps<{
    open: boolean;
    abg: AbgItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
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
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-w-xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-foreground">
                    <IdCard class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <span>Detail Pendaftaran ABG</span>
                </DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Informasi lengkap biodata, paspor, dan orang tua Anak Berkewarganegaraan Ganda.
                </DialogDescription>
            </DialogHeader>

            <div v-if="abg" class="space-y-4 py-2">
                <!-- Header Card dengan Foto dan Nama -->
                <div class="flex items-start gap-4 rounded-xl border border-border/80 bg-muted/30 p-4">
                    <div class="size-20 shrink-0 overflow-hidden rounded-lg border border-border bg-muted">
                        <img
                            v-if="abg.foto_url"
                            :src="abg.foto_url"
                            :alt="abg.nama"
                            class="size-full object-cover"
                        />
                        <div v-else class="flex size-full items-center justify-center bg-secondary text-muted-foreground">
                            <User class="size-8" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-foreground truncate">
                                {{ abg.nama }}
                            </h2>
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                ABG
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-muted-foreground">
                            {{ abg.kewarganegaraan }} &bull; {{ abg.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-background px-2 py-0.5 font-mono text-muted-foreground border border-border/60">
                                Reg: {{ abg.no_register }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kelahiran & Identitas -->
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Tempat / Tanggal Lahir</span>
                        <p class="text-sm font-semibold text-foreground flex items-center gap-1.5">
                            <Calendar class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>{{ abg.tempat_lahir || '-' }}, {{ formatIndoDate(abg.tanggal_lahir) }}</span>
                        </p>
                    </div>

                    <div class="rounded-lg border border-border/70 p-3 space-y-1 bg-card">
                        <span class="text-xs font-medium text-muted-foreground">Kewarganegaraan Ganda</span>
                        <p class="text-sm font-semibold text-foreground flex items-center gap-1.5 truncate">
                            <Globe class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span class="truncate">{{ abg.kewarganegaraan }}</span>
                        </p>
                    </div>
                </div>

                <!-- Informasi Orang Tua -->
                <div class="rounded-lg border border-border/80 p-3.5 space-y-3 bg-muted/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                        <Users class="size-3.5 text-emerald-600" />
                        <span>Data Orang Tua</span>
                    </h3>

                    <div class="grid gap-3 sm:grid-cols-2 text-xs">
                        <div class="p-2.5 rounded-md bg-background border border-border/60 space-y-1">
                            <span class="text-muted-foreground font-medium">Ayah</span>
                            <p class="text-sm font-semibold text-foreground">{{ abg.nama_ayah || '-' }}</p>
                            <p class="text-muted-foreground">Kewarganegaraan: {{ abg.kewarganegaraan_ayah || '-' }}</p>
                        </div>
                        <div class="p-2.5 rounded-md bg-background border border-border/60 space-y-1">
                            <span class="text-muted-foreground font-medium">Ibu</span>
                            <p class="text-sm font-semibold text-foreground">{{ abg.nama_ibu || '-' }}</p>
                            <p class="text-muted-foreground">Kewarganegaraan: {{ abg.kewarganegaraan_ibu || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="rounded-lg border border-border/80 p-3.5 space-y-3 bg-muted/20">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Kontak & Domisili
                    </h3>
                    <div class="grid gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <Phone class="size-3.5 text-muted-foreground shrink-0" />
                            <span class="text-muted-foreground">No. Telepon:</span>
                            <span class="font-medium text-foreground">{{ abg.nomor_telepon || '-' }}</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <MapPin class="size-3.5 text-muted-foreground shrink-0 mt-0.5" />
                            <span class="text-muted-foreground">Alamat:</span>
                            <span class="font-medium text-foreground leading-relaxed">{{ abg.alamat || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-between items-center gap-2">
                <Link
                    v-if="abg"
                    :href="`/abg/${abg.id}`"
                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center gap-1"
                >
                    <Eye class="size-3.5" />
                    <span>Buka Halaman Lengkap</span>
                </Link>
                <div v-else />

                <DialogClose as-child>
                    <Button variant="outline">
                        Tutup
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
