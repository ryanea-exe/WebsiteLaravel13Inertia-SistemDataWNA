<script setup lang="ts">
import {
    Calendar,
    CheckCircle2,
    Clock,
    FileSpreadsheet,
    FileText,
    Layers,
    Tag,
    XCircle,
} from '@lucide/vue';
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
import type { LaporanIzinTinggalItem } from '@/types/laporan-izin-tinggal';

const props = defineProps<{
    open: boolean;
    laporan: LaporanIzinTinggalItem | null;
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
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-w-xl">
            <DialogHeader>
                <div class="flex items-center gap-2">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <FileSpreadsheet class="size-5" />
                    </div>
                    <div>
                        <DialogTitle class="text-base font-semibold">
                            Rincian Rekapitulasi Izin Tinggal
                        </DialogTitle>
                        <DialogDescription class="text-xs text-muted-foreground">
                            Detail rincian permohonan yang disetujui dan ditolak per layanan keimigrasian.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="laporan" class="space-y-4 py-2">
                <!-- Banner Kategori & Layanan -->
                <div class="rounded-xl border border-border/80 bg-muted/30 p-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary uppercase tracking-wider">
                                <Layers class="size-3" />
                                {{ laporan.kategori }}
                            </span>
                            <h3 class="text-base font-bold text-foreground mt-0.5">
                                {{ laporan.layanan }}
                            </h3>
                        </div>
                        <span class="rounded-md bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-900 dark:bg-indigo-950/60 dark:text-indigo-300 tabular-nums">
                            Total: {{ laporan.total.toLocaleString('id-ID') }}
                        </span>
                    </div>
                </div>

                <!-- Tabel Breakdown Gender & Keputusan -->
                <div class="rounded-xl border border-border overflow-hidden">
                    <table class="w-full text-xs">
                        <thead class="bg-muted/60 text-muted-foreground font-semibold border-b border-border">
                            <tr>
                                <th class="text-left px-3 py-2">Status / Keputusan</th>
                                <th class="text-right px-3 py-2">Laki-laki (L)</th>
                                <th class="text-right px-3 py-2">Perempuan (P)</th>
                                <th class="text-right px-3 py-2 font-bold text-foreground">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr class="hover:bg-muted/20">
                                <td class="px-3 py-2.5 font-medium flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400">
                                    <CheckCircle2 class="size-3.5 shrink-0" />
                                    <span>Disetujui</span>
                                </td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ laporan.disetujui_l.toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ laporan.disetujui_p.toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums font-bold text-emerald-800 dark:text-emerald-300">
                                    {{ laporan.total_disetujui.toLocaleString('id-ID') }}
                                </td>
                            </tr>
                            <tr class="hover:bg-muted/20">
                                <td class="px-3 py-2.5 font-medium flex items-center gap-1.5 text-rose-700 dark:text-rose-400">
                                    <XCircle class="size-3.5 shrink-0" />
                                    <span>Ditolak</span>
                                </td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ laporan.ditolak_l.toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ laporan.ditolak_p.toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums font-bold text-rose-800 dark:text-rose-300">
                                    {{ laporan.total_ditolak.toLocaleString('id-ID') }}
                                </td>
                            </tr>
                            <tr class="bg-muted/40 font-bold">
                                <td class="px-3 py-2.5 text-foreground">Akumulasi Total</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">
                                    {{ (laporan.disetujui_l + laporan.ditolak_l).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-3 py-2.5 text-right tabular-nums">
                                    {{ (laporan.disetujui_p + laporan.ditolak_p).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-sm text-primary">
                                    {{ laporan.total.toLocaleString('id-ID') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Keterangan Tambahan -->
                <div class="rounded-lg border border-border/80 bg-muted/20 p-3 space-y-1 text-xs">
                    <span class="font-semibold text-muted-foreground flex items-center gap-1.5">
                        <FileText class="size-3.5" />
                        <span>Keterangan / Catatan Operasional:</span>
                    </span>
                    <p class="text-foreground leading-relaxed whitespace-pre-wrap">
                        {{ laporan.keterangan || 'Tidak ada catatan tambahan untuk laporan ini.' }}
                    </p>
                </div>

                <!-- Timestamps -->
                <div class="flex flex-wrap items-center justify-between text-[11px] text-muted-foreground border-t border-border/60 pt-2.5">
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        Dibuat: {{ formatIndoDate(laporan.created_at) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <Clock class="size-3" />
                        Terakhir diubah: {{ formatIndoDate(laporan.updated_at) }}
                    </span>
                </div>
            </div>

            <DialogFooter class="pt-2">
                <DialogClose as-child>
                    <Button variant="outline" size="sm">
                        Tutup
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
