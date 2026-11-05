<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    FileText,
    ImagePlus,
    Loader2,
    RotateCcw,
    Save,
    Settings as SettingsIcon,
    Shield,
    Star,
    Trash2,
} from '@lucide/vue';
import { ref } from 'vue';
import FlashAlert from '@/components/FlashAlert.vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface AvailableLogo {
    slot: number;
    path: string;
    url: string;
    is_primary: boolean;
}

interface AppSettingsData {
    app_name: string;
    instansi_name: string | null;
    app_description: string | null;
    app_icon: string | null;
    app_icon_url: string | null;
    primary_logo: number;
    logo_1: string | null;
    logo_1_url: string | null;
    logo_2: string | null;
    logo_2_url: string | null;
    logo_3: string | null;
    logo_3_url: string | null;
    available_logos: AvailableLogo[];
}

const props = defineProps<{
    settings: AppSettingsData;
    can: {
        update: boolean;
    };
}>();

const slots = [1, 2, 3] as const;
type SlotNumber = (typeof slots)[number];

const previews = ref<Record<SlotNumber, string | null>>({
    1: props.settings.logo_1_url || props.settings.app_icon_url || null,
    2: props.settings.logo_2_url || null,
    3: props.settings.logo_3_url || null,
});

const fileInputs: Record<SlotNumber, HTMLInputElement | null> = {
    1: null,
    2: null,
    3: null,
};

function setFileInputRef(slot: SlotNumber, el: any) {
    if (el) {
        fileInputs[slot] = el as HTMLInputElement;
    }
}

function triggerFileInput(slot: SlotNumber) {
    fileInputs[slot]?.click();
}

const form = useForm({
    app_name: props.settings.app_name || 'Sistem Data WNA Kanim Ponorogo',
    instansi_name: props.settings.instansi_name || '',
    app_description: props.settings.app_description || '',
    primary_logo: props.settings.primary_logo || 1,
    app_logo_1: null as File | null,
    remove_logo_1: false,
    app_logo_2: null as File | null,
    remove_logo_2: false,
    app_logo_3: null as File | null,
    remove_logo_3: false,
    // Backwards compatibility with single icon
    app_icon: null as File | null,
    remove_icon: false,
});

function handleFileChange(slot: SlotNumber, event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    if (slot === 1) {
        form.app_logo_1 = file;
        form.remove_logo_1 = false;
        form.app_icon = file;
        form.remove_icon = false;
    } else if (slot === 2) {
        form.app_logo_2 = file;
        form.remove_logo_2 = false;
    } else if (slot === 3) {
        form.app_logo_3 = file;
        form.remove_logo_3 = false;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        previews.value[slot] = e.target?.result as string;
        if (!previews.value[form.primary_logo as SlotNumber]) {
            form.primary_logo = slot;
        }
    };
    reader.readAsDataURL(file);
}

function handleRemoveLogo(slot: SlotNumber) {
    if (slot === 1) {
        form.app_logo_1 = null;
        form.remove_logo_1 = true;
        form.app_icon = null;
        form.remove_icon = true;
    } else if (slot === 2) {
        form.app_logo_2 = null;
        form.remove_logo_2 = true;
    } else if (slot === 3) {
        form.app_logo_3 = null;
        form.remove_logo_3 = true;
    }

    previews.value[slot] = null;
    if (fileInputs[slot]) {
        fileInputs[slot]!.value = '';
    }

    if (form.primary_logo === slot) {
        const nextSlot = slots.find((s) => s !== slot && previews.value[s] !== null);
        if (nextSlot) {
            form.primary_logo = nextSlot;
        }
    }
}

function handleReset() {
    form.reset();
    form.app_name = props.settings.app_name || 'Sistem Data WNA Kanim Ponorogo';
    form.instansi_name = props.settings.instansi_name || '';
    form.app_description = props.settings.app_description || '';
    form.primary_logo = props.settings.primary_logo || 1;
    form.app_logo_1 = null;
    form.remove_logo_1 = false;
    form.app_logo_2 = null;
    form.remove_logo_2 = false;
    form.app_logo_3 = null;
    form.remove_logo_3 = false;
    form.app_icon = null;
    form.remove_icon = false;

    previews.value[1] = props.settings.logo_1_url || props.settings.app_icon_url || null;
    previews.value[2] = props.settings.logo_2_url || null;
    previews.value[3] = props.settings.logo_3_url || null;

    slots.forEach((s) => {
        if (fileInputs[s]) {
            fileInputs[s]!.value = '';
        }
    });
}

function handleSubmit() {
    form.post('/pengaturan', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.app_logo_1 = null;
            form.remove_logo_1 = false;
            form.app_logo_2 = null;
            form.remove_logo_2 = false;
            form.app_logo_3 = null;
            form.remove_logo_3 = false;
            form.app_icon = null;
            form.remove_icon = false;
        },
    });
}
</script>

<template>
    <Head title="Pengaturan" />

    <div class="flex flex-col gap-6 p-4 md:p-6 w-full">
        <!-- Header Section -->
        <PageHeader
            title="Pengaturan Aplikasi"
            description="Konfigurasikan identitas visual, nama aplikasi keimigrasian, serta kelola 3 slot logo resmi instansi."
        >
            <template #actions>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-foreground shadow-xs">
                    <Shield class="size-3.5 text-indigo-600 dark:text-indigo-400" />
                    <span>Peran:</span>
                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                        Administrator Sistem
                    </span>
                </div>
            </template>
        </PageHeader>

        <!-- Flash Alert Feedback Message with Dismiss button and 10s auto-dismiss -->
        <FlashAlert />

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Kolom Kiri (5 Kolom): Identitas dan Branding Sistem -->
                <div class="lg:col-span-5 flex flex-col">
                    <Card class="border-border/80 shadow-xs h-full flex flex-col">
                        <CardHeader class="pb-3 border-b border-border/60">
                            <div class="flex items-center gap-2 text-primary">
                                <SettingsIcon class="size-4.5" />
                                <CardTitle class="text-sm font-semibold text-foreground">
                                    Identitas dan Branding Sistem
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-muted-foreground">
                                Nama dan identitas utama sistem yang ditampilkan pada navigasi sidebar, form login, serta dokumen laporan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4 pt-4 flex-1">
                            <!-- Nama Aplikasi -->
                            <div class="space-y-1.5">
                                <Label for="app_name" class="text-xs font-semibold">
                                    Nama Aplikasi <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="app_name"
                                    v-model="form.app_name"
                                    type="text"
                                    placeholder="Contoh: Sistem Data WNA Kanim Ponorogo"
                                    class="h-9 text-sm"
                                    required
                                />
                                <InputError :message="form.errors.app_name" />
                            </div>

                            <!-- Nama Instansi / Satuan Kerja -->
                            <div class="space-y-1.5">
                                <Label for="instansi_name" class="text-xs font-semibold">
                                    Nama Instansi / Satuan Kerja
                                </Label>
                                <div class="relative">
                                    <Building2 class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                    <Input
                                        id="instansi_name"
                                        v-model="form.instansi_name"
                                        type="text"
                                        placeholder="Contoh: Kantor Imigrasi Kelas II Non TPI Ponorogo"
                                        class="pl-9 h-9 text-sm"
                                    />
                                </div>
                                <InputError :message="form.errors.instansi_name" />
                            </div>

                            <!-- Deskripsi Singkat -->
                            <div class="space-y-1.5">
                                <Label for="app_description" class="text-xs font-semibold">
                                    Deskripsi Singkat / Subtitle
                                </Label>
                                <div class="relative">
                                    <FileText class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                    <Input
                                        id="app_description"
                                        v-model="form.app_description"
                                        type="text"
                                        placeholder="Contoh: Sistem Informasi Data Warga Negara Asing"
                                        class="pl-9 h-9 text-sm"
                                    />
                                </div>
                                <InputError :message="form.errors.app_description" />
                            </div>

                            <div class="rounded-lg border border-border/60 bg-muted/30 p-3 text-[11px] text-muted-foreground leading-relaxed mt-2">
                                <div class="font-semibold text-foreground mb-0.5 flex items-center gap-1.5">
                                    <CheckCircle2 class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span>Sinkronisasi Otomatis</span>
                                </div>
                                Perubahan identitas instansi akan langsung diterapkan ke seluruh halaman, dokumen laporan cetak, dan judul tab peramban.
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Kolom Kanan (7 Kolom): Pengaturan 3 Logo Aplikasi & Penentuan Logo Utama (Compact) -->
                <div class="lg:col-span-7 flex flex-col">
                    <Card class="border-border/80 shadow-xs h-full flex flex-col">
                        <CardHeader class="pb-3 border-b border-border/60">
                            <div class="flex items-center gap-2 text-primary">
                                <ImagePlus class="size-4.5" />
                                <CardTitle class="text-sm font-semibold text-foreground">
                                    Kelola Logo Aplikasi (3 Slot Logo)
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-muted-foreground">
                                Unggah hingga 3 berkas logo resmi. Logo utama akan tampil sebagai favicon peramban dan ikon sidebar.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="pt-4 flex-1 flex flex-col justify-between gap-3">
                            <!-- Compact List of 3 Logo Slots -->
                            <div class="space-y-2.5">
                                <div
                                    v-for="slot in slots"
                                    :key="slot"
                                    class="rounded-lg border p-2.5 transition-colors"
                                    :class="form.primary_logo === slot ? 'border-amber-400/80 bg-amber-500/[0.03] dark:border-amber-600/60' : 'border-border/70 bg-card hover:bg-muted/15'"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <!-- Left: Preview & Slot Label -->
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="relative size-12 shrink-0 overflow-hidden rounded-lg border border-border/80 bg-background flex items-center justify-center shadow-2xs">
                                                <img
                                                    v-if="previews[slot]"
                                                    :src="previews[slot]!"
                                                    :alt="`Logo ${slot}`"
                                                    class="size-full object-contain p-0.5 rounded-lg"
                                                />
                                                <div v-else class="flex flex-col items-center justify-center text-muted-foreground/40 text-center">
                                                    <ImagePlus class="size-4.5" />
                                                    <span class="text-[8px] mt-0.5">Kosong</span>
                                                </div>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-flex size-5 items-center justify-center rounded-md bg-primary/10 text-[10px] font-bold text-primary">
                                                        {{ slot }}
                                                    </span>
                                                    <span class="text-xs font-semibold text-foreground">Logo {{ slot }}</span>
                                                    <span v-if="slot === 1" class="text-[10px] text-muted-foreground font-normal">(Slot 1)</span>
                                                </div>
                                                <div class="text-[11px] text-muted-foreground mt-0.5">
                                                    <span v-if="previews[slot]" class="text-emerald-600 dark:text-emerald-400 font-medium">✓ Berkas aktif</span>
                                                    <span v-else class="text-muted-foreground/60">Belum diunggah</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right: Primary Badge/Radio & Buttons -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            <!-- Option to Set as Primary Logo -->
                                            <label
                                                :for="previews[slot] ? `primary_logo_${slot}` : undefined"
                                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-medium transition-all select-none"
                                                :class="[
                                                    form.primary_logo === slot ? 'border-amber-300 bg-amber-50 text-amber-900 shadow-2xs dark:border-amber-700 dark:bg-amber-950/70 dark:text-amber-200' : 'border-border/70 bg-background text-muted-foreground',
                                                    !previews[slot] ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer hover:bg-muted/60'
                                                ]"
                                                :title="!previews[slot] ? 'Unggah logo terlebih dahulu untuk menjadikannya Logo Utama' : (form.primary_logo === slot ? 'Logo ini aktif sebagai Logo Utama' : 'Klik untuk jadikan Logo Utama')"
                                            >
                                                <input
                                                    :id="`primary_logo_${slot}`"
                                                    v-model.number="form.primary_logo"
                                                    type="radio"
                                                    :value="slot"
                                                    name="primary_logo"
                                                    :disabled="!previews[slot]"
                                                    class="size-3 text-amber-600 focus:ring-amber-500 disabled:cursor-not-allowed"
                                                />
                                                <span class="inline-flex items-center gap-1">
                                                    <Star v-if="form.primary_logo === slot" class="size-3 fill-amber-500 text-amber-500" />
                                                    <span>{{ form.primary_logo === slot ? 'Logo Utama' : 'Utamakan' }}</span>
                                                </span>
                                            </label>

                                            <!-- File upload trigger -->
                                            <input
                                                :ref="(el) => setFileInputRef(slot, el)"
                                                type="file"
                                                accept="image/png,image/jpeg,image/webp"
                                                class="hidden"
                                                @change="handleFileChange(slot, $event)"
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-7 text-xs px-2.5 gap-1 cursor-pointer"
                                                @click="triggerFileInput(slot)"
                                            >
                                                <ImagePlus class="size-3" />
                                                <span>{{ previews[slot] ? 'Ganti' : 'Pilih' }}</span>
                                            </Button>

                                            <!-- Remove button -->
                                            <Button
                                                v-if="previews[slot]"
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 px-2 text-xs gap-1 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer"
                                                title="Hapus Logo"
                                                @click="handleRemoveLogo(slot)"
                                            >
                                                <Trash2 class="size-3" />
                                                <span>Hapus</span>
                                            </Button>
                                        </div>
                                    </div>
                                    <InputError :message="(form.errors as any)[`app_logo_${slot}`]" class="mt-1" />
                                </div>
                            </div>

                            <!-- Compact Helper Notice -->
                            <div class="rounded-lg border border-border/60 bg-muted/20 px-3 py-2 text-[11px] text-muted-foreground leading-relaxed">
                                Format didukung: <strong>PNG, JPG, WEBP</strong> (maks. 1 MB). Logo utama akan menjadi ikon web tab browser & sidebar. Pada halaman login, semua logo yang diisi akan tampil berjejer ke samping.
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Tombol Aksi Simpan & Pulihkan (Tanpa Card / Border) -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="gap-1.5 text-xs h-9 px-4 cursor-pointer"
                    :disabled="form.processing"
                    @click="handleReset"
                >
                    <RotateCcw class="size-3.5" />
                    <span>Batal / Pulihkan</span>
                </Button>
                <Button
                    type="submit"
                    size="sm"
                    class="gap-1.5 text-xs shadow-xs h-9 px-5 cursor-pointer"
                    :disabled="form.processing"
                >
                    <Loader2 v-if="form.processing" class="size-3.5 animate-spin" />
                    <Save v-else class="size-3.5" />
                    <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                </Button>
            </div>
        </form>
    </div>
</template>
