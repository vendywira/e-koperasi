<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const googleUrl = '/auth/google';

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Daftar - e-Koperasi" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary-50 via-white to-emerald-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-primary-950 px-4">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50" />
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-primary-50 dark:bg-primary-900/10 rounded-full blur-3xl opacity-50" />
        </div>

        <div class="relative w-full max-w-md">
            <Link href="/" class="flex items-center justify-center gap-3 mb-8 group">
                <img src="/images/logo-only-white.png" alt="e-Koperasi" class="h-11 w-11" />
                <div>
                    <h1 class="text-xl font-bold text-neutral-900 dark:text-white">e-Koperasi</h1>
                    <p class="text-[11px] text-neutral-400 dark:text-neutral-500 -mt-0.5">Daftar Akun</p>
                </div>
            </Link>

            <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xl dark:shadow-neutral-950/80 border border-neutral-200 dark:border-neutral-800 p-8">
                <div v-if="form.errors.email" class="mb-6 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-400">{{ form.errors.email }}</div>

                <!-- Google Register -->
                <a :href="googleUrl"
                    class="w-full flex items-center justify-center gap-3 px-4 py-2.5 mb-4 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Daftar dengan Google
                </a>

                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-neutral-300 dark:border-neutral-700" /></div>
                    <div class="relative flex justify-center text-xs"><span class="bg-white dark:bg-neutral-900 px-2 text-neutral-500">atau</span></div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nama</label>
                        <input id="name" v-model="form.name" type="text" required autofocus
                            class="w-full px-4 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none text-neutral-900 dark:text-white" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Email</label>
                        <input id="email" v-model="form.email" type="email" required
                            class="w-full px-4 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none text-neutral-900 dark:text-white" />
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Password</label>
                        <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required minlength="8"
                            class="w-full px-4 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none text-neutral-900 dark:text-white" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Konfirmasi Password</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" required
                            class="w-full px-4 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none text-neutral-900 dark:text-white" />
                    </div>
                    <div class="flex items-center">
                        <input id="showPass" type="checkbox" v-model="showPassword" class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500" />
                        <label for="showPass" class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Tampilkan password</label>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-all disabled:opacity-50">
                        {{ form.processing ? 'Mendaftar...' : 'Daftar' }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-neutral-500">
                Sudah punya akun? <Link href="/login" class="text-primary-600 hover:underline font-medium">Masuk</Link>
            </p>
        </div>
    </div>
</template>
