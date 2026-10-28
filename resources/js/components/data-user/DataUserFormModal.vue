<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Camera, Eye, EyeOff, Loader2, ShieldCheck, Trash2, User as UserIcon, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { RoleOption, UserFormData, UserItem } from '@/types/data-user';

const props = defineProps<{
    open: boolean;
    user: UserItem | null;
    roleOptions: RoleOption[];
    authUserId?: number;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isEdit = computed(() => props.user !== null);
const isEditingSelf = computed(() => isEdit.value && props.authUserId !== undefined && props.user?.id === props.authUserId);

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const photoPreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm<UserFormData>({
    nama: '',
    username: '',
    email: '',
    role: 'user',
    password: '',
    password_confirmation: '',
    foto: null,
    remove_foto: false,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors();
            showPassword.value = false;
            showPasswordConfirmation.value = false;
            if (props.user) {
                form.nama = props.user.nama || '';
                form.username = props.user.username || '';
                form.email = props.user.email || '';
                form.role = props.user.role;
                form.password = '';
                form.password_confirmation = '';
                form.foto = null;
                form.remove_foto = false;
                photoPreview.value = props.user.foto_url || null;
            } else {
                form.reset();
                form.role = 'user';
                photoPreview.value = null;
            }
        }
    }
);

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

function handleSubmit() {
    if (isEdit.value && props.user) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/data-user/${props.user.id}`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
            },
        });
    } else {
        form.post('/data-user', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
                form.reset();
                photoPreview.value = null;
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-xl max-h-[90vh] flex flex-col p-0 gap-0 overflow-hidden">
            <DialogHeader class="p-6 pb-4 border-b border-border/60 shrink-0">
                <div class="flex items-center gap-2 text-primary">
                    <ShieldCheck class="size-5" />
                    <DialogTitle class="text-lg font-semibold text-foreground">
                        {{ isEdit ? 'Ubah Data Pengguna' : 'Tambah Pengguna Baru' }}
                    </DialogTitle>
                </div>
                <DialogDescription class="text-sm text-muted-foreground">
                    {{ isEdit ? 'Perbarui informasi profil, username, hak akses peran, atau kata sandi pengguna.' : 'Masukkan rincian identitas dan hak akses akun baru untuk mengakses sistem.' }}
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col flex-1 min-h-0 overflow-hidden" @submit.prevent="handleSubmit">
                <div class="flex-1 overflow-y-auto min-h-0 p-6 space-y-4">
                    <!-- Foto Profil Avatar -->
                    <div class="flex items-center gap-4 rounded-lg border border-border/60 bg-muted/30 p-3">
                        <div class="relative size-16 shrink-0 overflow-hidden rounded-full border border-border/80 bg-muted flex items-center justify-center">
                            <img
                                v-if="photoPreview"
                                :src="photoPreview"
                                alt="Pratinjau Foto"
                                class="size-full object-cover"
                            />
                            <UserIcon v-else class="size-8 text-muted-foreground/60" />
                        </div>

                        <div class="flex-1 space-y-1">
                            <div class="text-xs font-medium text-foreground">Foto Profil (Opsional)</div>
                            <div class="text-[11px] text-muted-foreground leading-tight">
                                Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="handlePhotoChange"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="h-7 text-xs"
                                    @click="fileInputRef?.click()"
                                >
                                    <Camera class="mr-1 size-3.5" />
                                    {{ photoPreview ? 'Ganti Foto' : 'Pilih Foto' }}
                                </Button>
                                <Button
                                    v-if="photoPreview"
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40"
                                    @click="handleRemovePhoto"
                                >
                                    <Trash2 class="mr-1 size-3.5" />
                                    Hapus
                                </Button>
                            </div>
                            <InputError :message="form.errors.foto" />
                        </div>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5">
                        <Label for="nama" class="text-xs font-semibold">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="nama"
                            v-model="form.nama"
                            type="text"
                            placeholder="Contoh: Budi Santoso"
                            class="h-9 text-sm"
                            required
                        />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <!-- Username & Role Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="username" class="text-xs font-semibold">
                                Username <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="username"
                                v-model="form.username"
                                type="text"
                                placeholder="Contoh: budi_santoso"
                                class="h-9 text-sm font-mono"
                                required
                            />
                            <InputError :message="form.errors.username" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="role" class="text-xs font-semibold">
                                Peran / Hak Akses <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="role"
                                v-model="form.role"
                                :disabled="isEditingSelf"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-60"
                                required
                            >
                                <option
                                    v-for="opt in roleOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                            <p v-if="isEditingSelf" class="text-[11px] text-amber-600 dark:text-amber-400">
                                Role akun sendiri terkunci demi keamanan anti-lockout.
                            </p>
                            <InputError :message="form.errors.role" />
                        </div>
                    </div>

                    <!-- Email (Opsional) -->
                    <div class="space-y-1.5">
                        <Label for="email" class="text-xs font-semibold">
                            Alamat Email (Opsional)
                        </Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Contoh: user@imigrasi.go.id"
                            class="h-9 text-sm"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password & Konfirmasi Password Grid -->
                    <div class="space-y-3">
                        <div class="text-xs font-medium text-foreground flex items-center justify-between">
                            <span>{{ isEdit ? 'Ubah Kata Sandi (Opsional)' : 'Kata Sandi Akun' }}</span>
                            <span v-if="isEdit" class="text-[11px] text-muted-foreground font-normal">
                                Kosongkan jika tidak ingin mengubah
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <Label for="password" class="text-xs font-semibold">
                                    Kata Sandi <span v-if="!isEdit" class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        :placeholder="isEdit ? '••••••••' : 'Minimal 8 karakter'"
                                        class="h-9 text-sm pr-9"
                                        :required="!isEdit"
                                    />
                                    <button
                                        type="button"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                        @click="showPassword = !showPassword"
                                    >
                                        <EyeOff v-if="showPassword" class="size-4" />
                                        <Eye v-else class="size-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="password_confirmation" class="text-xs font-semibold">
                                    Konfirmasi Kata Sandi <span v-if="!isEdit || form.password" class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        placeholder="Ulangi kata sandi"
                                        class="h-9 text-sm pr-9"
                                        :required="!isEdit || !!form.password"
                                    />
                                    <button
                                        type="button"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    >
                                        <EyeOff v-if="showPasswordConfirmation" class="size-4" />
                                        <Eye v-else class="size-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="p-6 py-3 border-t border-border/60 bg-muted/20 shrink-0 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" :disabled="form.processing">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-1.5 size-4 animate-spin" />
                        {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat Akun') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
