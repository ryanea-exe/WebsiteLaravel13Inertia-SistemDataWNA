<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Building2,
    FileText,
    ImagePlus,
    Loader2,
    RotateCcw,
    Save,
    Settings as SettingsIcon,
    Shield,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface AppSettingsData {
    app_name: string;
    instansi_name: string | null;
    app_description: string | null;
    app_icon: string | null;
    app_icon_url: string | null;
}

const props = defineProps<{
    settings: AppSettingsData;
    can: {
        update: boolean;
    };
}>();

const page = usePage();
const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success);
const flashError = computed(() => (page.props.flash as { error?: string })?.error);

const iconPreview = ref<string | null>(props.settings.app_icon_url || null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm({
    app_name: props.settings.app_name || 'Sistem Data WNA',
    instansi_name: props.settings.instansi_name || '',
    app_description: props.settings.app_description || '',
    app_icon: null as File | null,
    remove_icon: false,
});

function handleIconChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    form.app_icon = file;
    form.remove_icon = false;

    const reader = new FileReader();
    reader.onload = (e) => {
        iconPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function handleRemoveIcon() {
    form.app_icon = null;
    form.remove_icon = true;
    iconPreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function handleReset() {
    form.reset();
    form.app_name = props.settings.app_name || 'Sistem Data WNA';
    form.instansi_name = props.settings.instansi_name || '';
    form.app_description = props.settings.app_description || '';
    form.app_icon = null;
    form.remove_icon = false;
    iconPreview.value = props.settings.app_icon_url || null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function handleSubmit() {
    form.post('/pengaturan', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.app_icon = null;
            form.remove_icon = false;
        },
    });
}
</script>

<template>
    <Head title="Pengaturan Aplikasi" />

    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
        <!-- Header Section -->
        <PageHeader
            title="Pengaturan Aplikasi"
            description="Konfigurasikan identitas visual, nama aplikasi keimigrasian, dan logo resmi instansi."
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

        <!-- Flash Feedback Messages -->
        <div
            v-if="flashSuccess"
            class="flex items-center gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-xs dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <div class="flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-200/80 dark:bg-emerald-900">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200">✓</span>
            </div>
            <p class="font-medium">{{ flashSuccess }}</p>
        </div>

        <div
            v-if="flashError"
            class="flex items-center gap-2.5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-xs dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
            <AlertCircle class="size-5 shrink-0 text-red-600 dark:text-red-400" />
            <p class="font-medium">{{ flashError }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Card 1: Identitas Aplikasi -->
            <Card class="border-border/80 shadow-xs">
                <CardHeader class="pb-4 border-b border-border/60">
                    <div class="flex items-center gap-2 text-primary">
                        <SettingsIcon class="size-5" />
                        <CardTitle class="text-base font-semibold text-foreground">
                            Identitas dan Branding Sistem
                        </CardTitle>
                    </div>
                    <CardDescription class="text-xs text-muted-foreground">
                        Nama dan identitas ini akan ditampilkan pada antarmuka pengguna, navigasi sidebar, halaman login, serta tab peramban.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4 pt-5">
                    <!-- Nama Aplikasi -->
                    <div class="space-y-1.5">
                        <Label for="app_name" class="text-xs font-semibold">
                            Nama Aplikasi <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="app_name"
                            v-model="form.app_name"
                            type="text"
                            placeholder="Contoh: Sistem Data WNA"
                            class="h-9 text-sm"
                            required
                        />
                        <p class="text-[11px] text-muted-foreground">
                            Nama utama sistem yang terlihat di bagian kiri atas sidebar dan judul halaman login.
                        </p>
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
                                placeholder="Contoh: Kantor Imigrasi Kelas I"
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
                                placeholder="Contoh: Layanan Pengelolaan Data Warga Negara Asing"
                                class="pl-9 h-9 text-sm"
                            />
                        </div>
                        <InputError :message="form.errors.app_description" />
                    </div>
                </CardContent>
            </Card>

            <!-- Card 2: Ikon / Logo Aplikasi -->
            <Card class="border-border/80 shadow-xs">
                <CardHeader class="pb-4 border-b border-border/60">
                    <div class="flex items-center gap-2 text-primary">
                        <ImagePlus class="size-5" />
                        <CardTitle class="text-base font-semibold text-foreground">
                            Ikon & Logo Aplikasi
                        </CardTitle>
                    </div>
                    <CardDescription class="text-xs text-muted-foreground">
                        Unggah berkas gambar logo resmi yang akan menghiasi header sidebar dan form login aplikasi.
                    </CardDescription>
                </CardHeader>
                <CardContent class="pt-5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 p-4 rounded-xl border border-border/70 bg-muted/20">
                        <!-- Preview Box -->
                        <div class="relative size-20 shrink-0 overflow-hidden rounded-xl border border-border/80 bg-background flex items-center justify-center shadow-xs">
                            <img
                                v-if="iconPreview"
                                :src="iconPreview"
                                alt="Pratinjau Logo"
                                class="size-full object-contain p-1 rounded-xl"
                            />
                            <div v-else class="flex flex-col items-center justify-center text-muted-foreground/60 text-center p-2">
                                <ImagePlus class="size-8" />
                                <span class="text-[9px] mt-1">Default Logo</span>
                            </div>
                        </div>

                        <!-- Upload Actions & Specs -->
                        <div class="flex-1 space-y-2">
                            <div>
                                <div class="text-xs font-semibold text-foreground">
                                    Unggah Berkas Ikon
                                </div>
                                <p class="text-[11px] text-muted-foreground leading-relaxed">
                                    Format didukung: <strong>PNG, JPG, JPEG, WEBP</strong>. Ukuran maksimum <strong>1 MB</strong>. Disarankan gambar persegi (rasio 1:1) transparan.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="hidden"
                                    @change="handleIconChange"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 text-xs gap-1.5"
                                    @click="fileInputRef?.click()"
                                >
                                    <ImagePlus class="size-3.5" />
                                    <span>{{ iconPreview ? 'Ganti Ikon' : 'Pilih Berkas Ikon' }}</span>
                                </Button>
                                <Button
                                    v-if="iconPreview"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 text-xs gap-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40"
                                    @click="handleRemoveIcon"
                                >
                                    <Trash2 class="size-3.5" />
                                    <span>Hapus Ikon</span>
                                </Button>
                            </div>
                            <InputError :message="form.errors.app_icon" />
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 p-5 pt-3 border-t border-border/60">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="gap-1.5 text-xs"
                        :disabled="form.processing"
                        @click="handleReset"
                    >
                        <RotateCcw class="size-3.5" />
                        <span>Batal / Pulihkan</span>
                    </Button>
                    <Button
                        type="submit"
                        size="sm"
                        class="gap-1.5 text-xs shadow-xs"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="size-3.5 animate-spin" />
                        <Save v-else class="size-3.5" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
