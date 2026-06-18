<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { submit as loginSubmit } from '@/routes/login';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post(loginSubmit().url, {
        onFinish: () => {
            form.reset('password');
        },
    });
}
</script>

<template>
    <Head title="Login - e-Koperasi" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary-50 via-white to-emerald-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-primary-950 px-4">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50" />
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-primary-50 dark:bg-primary-900/10 rounded-full blur-3xl opacity-50" />
        </div>

        <div class="relative w-full max-w-md">
            <!-- Logo -->
            <Link href="/" class="flex items-center justify-center gap-3 mb-8 group">
                <img src="/images/logo-only-white.png" alt="e-Koperasi" class="h-11 w-11" />
                <div>
                    <h1 class="text-xl font-bold text-neutral-900 dark:text-white">e-Koperasi</h1>
                    <p class="text-[11px] text-neutral-400 dark:text-neutral-500 -mt-0.5">Portal Masuk</p>
                </div>
            </Link>

            <!-- Card -->
            <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xl dark:shadow-neutral-950/80 border border-neutral-200 dark:border-neutral-800 p-8">

                <!-- Error -->
                <div
                    v-if="form.errors.email"
                    class="mb-6 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-400"
                >
                    {{ form.errors.email }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full px-4 py-2.5 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 transition-colors"
                            :class="form.errors.email ? 'border-red-300 dark:border-red-700' : ''"
                            placeholder="admin@e-koperasi.com"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Password</label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-2.5 pr-10 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 transition-colors"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 text-primary-600 bg-neutral-100 dark:bg-neutral-800 border-neutral-300 dark:border-neutral-600 rounded focus:ring-primary-500"
                            />
                            <label for="remember" class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">Ingat saya</label>
                        </div>
                        <Link href="/forgot-password" class="text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium">
                            Lupa password?
                        </Link>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-all duration-150 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                    >
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            Masuk...
                        </span>
                        <span v-else>Masuk</span>
                    </button>
                </form>
            </div>

            <!-- Back to site -->
            <p class="text-center mt-6 text-sm text-neutral-500 dark:text-neutral-400">
                <a href="/" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">← Kembali ke website</a>
            </p>
        </div>
    </div>
</template>
