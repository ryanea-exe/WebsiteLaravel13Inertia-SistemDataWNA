<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    AtSign,
    Camera,
    Check,
    Clock,
    Eye,
    EyeOff,
    KeyRound,
    Loader2,
    Mail,
    RotateCcw,
    Save,
    Shield,
    ShieldCheck,
    Trash2,
    User as UserIcon,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

defineOptions({
    layout: () => ({
        breadcrumbs: [
            {
                title: 'Profil Pengguna',
                href: '/settings/profile',
            },
        ],
    }),
});

interface ProfilePageProps {
    user?: User;
    status?: string;
    mustVerifyEmail?: boolean;
}

const props = defineProps<ProfilePageProps>();

const page = usePage();
const { getInitials } = useInitials();

// Current authenticated user data
const currentUser = computed<User>(() => {
    return props.user || (page.props.auth as { user: User }).user;
});

const flashSuccess = computed(() => (page.props.flash as { success?: string })?.success);
const flashError = computed(() => (page.props.flash as { error?: string })?.error);

// Password visibility state
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Photo management state
const photoPreview = ref<string | null>(currentUser.value.foto_url || null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm({
    nama: currentUser.value.nama || currentUser.value.name || '',
    username: currentUser.value.username || '',
    email: currentUser.value.email || '',
    foto: null as File | null,
    remove_foto: false,
    current_password: '',
    password: '',
    password_confirmation: '',
});

function handlePhotoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    form.foto = file;
    form.remove_foto = false;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function handleRemovePhoto() {
    form.foto = null;
    form.remove_foto = true;
    photoPreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function handleReset() {
    form.reset();
    form.clearErrors();
    form.nama = currentUser.value.nama || currentUser.value.name || '';
    form.username = currentUser.value.username || '';
    form.email = currentUser.value.email || '';
    form.foto = null;
    form.remove_foto = false;
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';
    photoPreview.value = currentUser.value.foto_url || null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function handleSubmit() {
    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post('/settings/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.current_password = '';
            form.password = '';
            form.password_confirmation = '';
            form.foto = null;
            form.remove_foto = false;
            showCurrentPassword.value = false;
            showNewPassword.value = false;
            showPasswordConfirmation.value = false;
        },
    });
}

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Head title="Profil Pengguna" />

    <div class="flex flex-col gap-6 p-4 md:p-6 w-full">
        <!-- Header Section -->
        <PageHeader
            title="Profil Pengguna"
            description="Kelola informasi identitas akun, foto profil resmi, dan pengaturan keamanan kata sandi Anda."
        >
            <template #actions>
                <div class="inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-foreground shadow-xs">
                    <Shield v-if="currentUser.role === 'admin'" class="size-3.5 text-indigo-600 dark:text-indigo-400" />
                    <ShieldCheck v-else class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Peran:</span>
                    <span :class="currentUser.role === 'admin' ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'font-semibold text-emerald-600 dark:text-emerald-400'">
                        {{ currentUser.role === 'admin' ? 'Administrator' : 'Petugas' }}
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
                <Check class="size-3.5 font-bold text-emerald-800 dark:text-emerald-200" />
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
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Kolom Kiri (7 Kolom): Informasi Profil & Keamanan -->
                <div class="lg:col-span-7 flex flex-col gap-6">
                    <!-- Kartu 1: Informasi Profil Akun -->
                    <Card class="border-border/80 shadow-xs">
                        <CardHeader class="pb-4 border-b border-border/60">
                            <div class="flex items-center gap-2 text-primary">
                                <UserIcon class="size-5" />
                                <CardTitle class="text-base font-semibold text-foreground">
                                    Informasi Profil Pengguna
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-muted-foreground">
                                Nama dan username digunakan untuk identifikasi akun saat bertugas dan mengakses aplikasi.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4 pt-5">
                            <!-- Nama Lengkap -->
                            <div class="space-y-1.5">
                                <Label for="nama" class="text-xs font-semibold">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                    <Input
                                        id="nama"
                                        v-model="form.nama"
                                        type="text"
                                        placeholder="Masukkan nama lengkap Anda"
                                        class="pl-9 h-9 text-sm"
                                        required
                                        autocomplete="name"
                                    />
                                </div>
                                <InputError :message="form.errors.nama" />
                            </div>

                            <!-- Username -->
                            <div class="space-y-1.5">
                                <Label for="username" class="text-xs font-semibold">
                                    Username <span class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <AtSign class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                    <Input
                                        id="username"
                                        v-model="form.username"
                                        type="text"
                                        placeholder="contoh_username"
                                        class="pl-9 h-9 text-sm"
                                        required
                                        autocomplete="username"
                                    />
                                </div>
                                <p class="text-[11px] text-muted-foreground">
                                    Hanya boleh berisi huruf, angka, titik, tanda hubung, atau garis bawah.
                                </p>
                                <InputError :message="form.errors.username" />
                            </div>

                            <!-- Alamat Email -->
                            <div class="space-y-1.5">
                                <Label for="email" class="text-xs font-semibold">
                                    Alamat Email <span class="text-xs font-normal text-muted-foreground">(Opsional)</span>
                                </Label>
                                <div class="relative">
                                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="nama@imigrasi.go.id"
                                        class="pl-9 h-9 text-sm"
                                        autocomplete="email"
                                    />
                                </div>
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Hak Akses Peran (Read-only) -->
                            <div class="space-y-1.5 pt-1">
                                <Label class="text-xs font-semibold text-muted-foreground">
                                    Peran Hak Akses (Role)
                                </Label>
                                <div class="flex items-center gap-2.5 rounded-lg border border-border/70 bg-muted/30 px-3.5 py-2.5">
                                    <Shield v-if="currentUser.role === 'admin'" class="size-4 text-indigo-600 dark:text-indigo-400" />
                                    <ShieldCheck v-else class="size-4 text-emerald-600 dark:text-emerald-400" />
                                    <div class="flex-1">
                                        <div class="text-xs font-semibold text-foreground">
                                            {{ currentUser.role === 'admin' ? 'Administrator Sistem' : 'Petugas Keimigrasian' }}
                                        </div>
                                        <div class="text-[11px] text-muted-foreground">
                                            Hak akses bersifat permanen dan hanya dapat diubah oleh Administrator melalui modul Data User.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Kartu 2: Keamanan & Perubahan Kata Sandi -->
                    <Card class="border-border/80 shadow-xs">
                        <CardHeader class="pb-4 border-b border-border/60">
                            <div class="flex items-center gap-2 text-primary">
                                <KeyRound class="size-5" />
                                <CardTitle class="text-base font-semibold text-foreground">
                                    Keamanan & Kata Sandi
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-muted-foreground">
                                Kosongkan seluruh kolom kata sandi di bawah jika Anda tidak ingin melakukan pergantian kata sandi akun.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4 pt-5">
                            <!-- Kata Sandi Saat Ini -->
                            <div class="space-y-1.5">
                                <Label for="current_password" class="text-xs font-semibold">
                                    Kata Sandi Saat Ini
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="current_password"
                                        v-model="form.current_password"
                                        :type="showCurrentPassword ? 'text' : 'password'"
                                        placeholder="Masukkan kata sandi saat ini"
                                        class="pr-10 h-9 text-sm"
                                        autocomplete="current-password"
                                    />
                                    <button
                                        type="button"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground cursor-pointer"
                                        @click="showCurrentPassword = !showCurrentPassword"
                                        tabindex="-1"
                                    >
                                        <EyeOff v-if="showCurrentPassword" class="size-4" />
                                        <Eye v-else class="size-4" />
                                    </button>
                                </div>
                                <p class="text-[11px] text-muted-foreground">
                                    Wajib diisi sebagai verifikasi otentikasi jika Anda ingin membuat kata sandi baru.
                                </p>
                                <InputError :message="form.errors.current_password" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <!-- Kata Sandi Baru -->
                                <div class="space-y-1.5">
                                    <Label for="password" class="text-xs font-semibold">
                                        Kata Sandi Baru
                                    </Label>
                                    <div class="relative">
                                        <Input
                                            id="password"
                                            v-model="form.password"
                                            :type="showNewPassword ? 'text' : 'password'"
                                            placeholder="Minimal 8 karakter"
                                            class="pr-10 h-9 text-sm"
                                            autocomplete="new-password"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground cursor-pointer"
                                            @click="showNewPassword = !showNewPassword"
                                            tabindex="-1"
                                        >
                                            <EyeOff v-if="showNewPassword" class="size-4" />
                                            <Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                    <InputError :message="form.errors.password" />
                                </div>

                                <!-- Konfirmasi Kata Sandi Baru -->
                                <div class="space-y-1.5">
                                    <Label for="password_confirmation" class="text-xs font-semibold">
                                        Konfirmasi Kata Sandi Baru
                                    </Label>
                                    <div class="relative">
                                        <Input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            :type="showPasswordConfirmation ? 'text' : 'password'"
                                            placeholder="Ulangi kata sandi baru"
                                            class="pr-10 h-9 text-sm"
                                            autocomplete="new-password"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground cursor-pointer"
                                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                                            tabindex="-1"
                                        >
                                            <EyeOff v-if="showPasswordConfirmation" class="size-4" />
                                            <Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                    <InputError :message="form.errors.password_confirmation" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Kolom Kanan (5 Kolom): Foto Profil & Ringkasan -->
                <div class="lg:col-span-5 flex flex-col gap-6">
                    <!-- Kartu 3: Foto Profil -->
                    <Card class="border-border/80 shadow-xs">
                        <CardHeader class="pb-4 border-b border-border/60">
                            <div class="flex items-center gap-2 text-primary">
                                <Camera class="size-5" />
                                <CardTitle class="text-base font-semibold text-foreground">
                                    Foto Profil Resmi
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-muted-foreground">
                                Foto profil akan disajikan pada menu akun header atas, sidebar navigasi, dan log penugasan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="pt-5 space-y-4">
                            <div class="flex flex-col items-center sm:flex-row sm:items-start gap-4 p-4 rounded-xl border border-border/70 bg-muted/20">
                                <!-- Preview Box -->
                                <div class="relative size-24 shrink-0 overflow-hidden rounded-2xl border-2 border-border/80 bg-background flex items-center justify-center shadow-xs">
                                    <img
                                        v-if="photoPreview"
                                        :src="photoPreview"
                                        :alt="form.nama || form.username"
                                        class="size-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="size-full flex flex-col items-center justify-center bg-primary/10 text-primary font-bold text-2xl select-none"
                                    >
                                        {{ getInitials(form.nama || form.username) }}
                                    </div>
                                </div>

                                <!-- Upload Controls & Info -->
                                <div class="flex-1 space-y-2 text-center sm:text-left">
                                    <div>
                                        <div class="text-xs font-semibold text-foreground">
                                            Berkas Foto Akun
                                        </div>
                                        <p class="text-[11px] text-muted-foreground leading-relaxed">
                                            Format yang didukung: <strong>JPG, JPEG, PNG, WEBP</strong> (Maksimal <strong>2 MB</strong>).
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                                        <input
                                            ref="fileInputRef"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp,image/jpg"
                                            class="hidden"
                                            @change="handlePhotoChange"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            class="h-8 text-xs gap-1.5 cursor-pointer"
                                            @click="fileInputRef?.click()"
                                        >
                                            <Camera class="size-3.5" />
                                            <span>{{ photoPreview ? 'Ganti Foto' : 'Pilih Foto' }}</span>
                                        </Button>
                                        <Button
                                            v-if="photoPreview"
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 text-xs gap-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer"
                                            @click="handleRemovePhoto"
                                        >
                                            <Trash2 class="size-3.5" />
                                            <span>Hapus</span>
                                        </Button>
                                    </div>
                                    <InputError :message="form.errors.foto" />
                                </div>
                            </div>

                            <div class="rounded-lg border border-border/60 bg-background/50 p-3 text-[11px] text-muted-foreground leading-relaxed">
                                Foto profil disimpan secara privat dan terlindungi dengan enkripsi akses. Berkas hanya disajikan kepada sesi pengguna yang terotentikasi.
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Kartu 4: Metadata & Ringkasan Akun -->
                    <Card class="border-border/80 shadow-xs">
                        <CardHeader class="pb-3 border-b border-border/60">
                            <div class="flex items-center gap-2 text-primary">
                                <Clock class="size-4" />
                                <CardTitle class="text-xs font-semibold text-foreground">
                                    Informasi Aktivitas Akun
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-3 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between py-1 border-b border-border/40">
                                <span class="text-muted-foreground">ID Pengguna</span>
                                <span class="font-mono font-medium text-foreground">#{{ currentUser.id }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-border/40">
                                <span class="text-muted-foreground">Terakhir Masuk</span>
                                <span class="font-medium text-foreground">{{ formatDate(currentUser.terakhir_login) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-muted-foreground">Akun Terdaftar Sejak</span>
                                <span class="font-medium text-foreground">{{ formatDate(currentUser.created_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Tombol Aksi Simpan & Pulihkan -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 rounded-xl border border-border/80 bg-card p-4 shadow-xs">
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
                    <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Profil' }}</span>
                </Button>
            </div>
        </form>
    </div>
</template>
