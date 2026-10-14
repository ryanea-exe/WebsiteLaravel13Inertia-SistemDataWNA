<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Printer, Shield, User } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { WnaItem } from '@/types/wna';

const props = defineProps<{
    wna: WnaItem;
    printedAt: string;
}>();

function printCard() {
    window.print();
}

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
    <Head :title="`Kartu WNA - ${wna.nama}`" />

    <div class="min-h-screen bg-muted/40 p-4 md:p-8 flex flex-col items-center justify-center font-sans print:p-0 print:bg-white">
        <!-- Action Header (Hidden when printing) -->
        <div class="w-full max-w-xl mb-6 flex items-center justify-between print:hidden">
            <Button variant="outline" size="sm" as-child class="gap-1.5 shadow-xs">
                <Link href="/wna">
                    <ArrowLeft class="size-4" />
                    <span>Kembali ke Data WNA</span>
                </Link>
            </Button>

            <Button
                size="sm"
                class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs"
                @click="printCard"
            >
                <Printer class="size-4" />
                <span>Cetak Kartu</span>
            </Button>
        </div>

        <!-- ID Card Wrapper (CR80 standard proportions: approx 85.6mm x 54mm scaled up for crisp display) -->
        <div
            id="wna-card-container"
            class="relative w-full max-w-md overflow-hidden rounded-2xl border-2 border-border/80 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl print:shadow-none print:border print:border-slate-400 print:rounded-xl"
            style="-webkit-print-color-adjust: exact; print-color-adjust: exact;"
        >
            <!-- Card Header Ribbon -->
            <div class="relative flex items-center justify-between border-b border-white/15 bg-white/10 px-5 py-3 backdrop-blur-xs">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-500/30 border border-white/20 text-indigo-300">
                        <Shield class="size-4.5" />
                    </div>
                    <div>
                        <h1 class="text-xs font-black uppercase tracking-widest text-indigo-200">
                            KARTU DATA WNA
                        </h1>
                        <p class="text-[9px] font-medium tracking-wider text-slate-300">
                            SISTEM INFORMASI DATA KEIMIGRASIAN
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1 rounded-full border border-white/20 bg-indigo-600/40 px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">
                    <span>{{ wna.jenis_izin_tinggal }}</span>
                </div>
            </div>

            <!-- Card Body Content -->
            <div class="p-5">
                <div class="flex gap-4">
                    <!-- Photo Frame with Chip Aesthetic -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="relative size-28 shrink-0 overflow-hidden rounded-xl border-2 border-indigo-400/40 bg-slate-800 shadow-inner">
                            <img
                                v-if="wna.foto_url"
                                :src="wna.foto_url"
                                :alt="wna.nama"
                                class="size-full object-cover"
                            />
                            <div v-else class="flex size-full items-center justify-center text-slate-400">
                                <User class="size-12" />
                            </div>
                        </div>

                        <!-- Chip Emblem Graphic -->
                        <div class="flex h-4 w-9 rounded-sm border border-amber-300/60 bg-gradient-to-r from-amber-400/40 via-amber-200/50 to-amber-400/40 opacity-80" />
                    </div>

                    <!-- WNA Details Grid -->
                    <div class="flex-1 space-y-2 min-w-0">
                        <div>
                            <span class="text-[9px] uppercase tracking-wider text-slate-400">Nama Lengkap</span>
                            <h2 class="text-base font-bold text-white truncate leading-tight">
                                {{ wna.nama }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[9px] uppercase tracking-wider text-slate-400">No. Register</span>
                                <p class="font-mono font-semibold text-indigo-200 truncate">
                                    {{ wna.no_register }}
                                </p>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase tracking-wider text-slate-400">No. Paspor</span>
                                <p class="font-mono font-semibold text-indigo-200 truncate">
                                    {{ wna.no_paspor }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[9px] uppercase tracking-wider text-slate-400">Kewarganegaraan</span>
                                <p class="font-semibold text-slate-100 truncate">
                                    {{ wna.kewarganegaraan }}
                                </p>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase tracking-wider text-slate-400">Jenis Kelamin</span>
                                <p class="font-semibold text-slate-100">
                                    {{ wna.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <span class="text-[9px] uppercase tracking-wider text-slate-400">Masa Berlaku Izin Tinggal</span>
                            <p class="text-xs font-bold text-amber-300">
                                {{ formatIndoDate(wna.masa_berlaku) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Bar inside card -->
                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-[8px] text-slate-400">
                    <div class="flex items-center gap-1">
                        <CheckCircle2 class="size-3 text-emerald-400 shrink-0" />
                        <span>Data Terverifikasi Sistem</span>
                    </div>
                    <div>
                        <span>Tgl Cetak: {{ printedAt }}</span>
                    </div>
                </div>
            </div>

            <!-- Mandatory Disclaimer Bar -->
            <div class="bg-black/40 px-5 py-2 text-center text-[8px] tracking-wide text-slate-400 border-t border-white/5">
                Kartu Ringkasan Data Internal — Bukan Dokumen Identitas Resmi Pemerintah
            </div>
        </div>

        <!-- Print Instructions (Hidden on print) -->
        <p class="mt-4 text-xs text-muted-foreground print:hidden">
            Tips: Gunakan opsi cetak browser dengan orientasi portrait atau kartu identitas, dan centang "Background graphics" untuk hasil optimal.
        </p>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: auto;
        margin: 15mm;
    }
    body {
        background: transparent !important;
    }
}
</style>
