<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Masuk ke Sistem Data WNA',
        description: 'Masukkan username dan password Anda untuk masuk ke sistem',
    },
});

defineProps<{
    status?: string;
}>();

const page = usePage();
const appName = computed(() => (page.props.app_settings as any)?.app_name || (page.props.name as string) || 'Sistem Data WNA');

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head :title="`Masuk - ${appName}`" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="username">Username</Label>
                <Input
                    id="username"
                    type="text"
                    name="username"
                    v-model="form.username"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    placeholder="Masukkan username"
                />
                <InputError :message="form.errors.username" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="form.password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox
                        id="remember"
                        name="remember"
                        :checked="form.remember"
                        @update:checked="(val: boolean) => (form.remember = !!val)"
                        :tabindex="3"
                    />
                    <span>Ingat saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="form.processing"
                data-test="login-button"
            >
                <Spinner v-if="form.processing" />
                Masuk
            </Button>
        </div>
    </form>
</template>
