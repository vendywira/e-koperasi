<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/client/login', {
        onError: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Login Client - e-Koperasi" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary-50 via-white to-emerald-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-primary-950 px-4">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 mb-8 group">
            <img src="/images/logo-only-white.png" alt="e-Koperasi" class="h-10 w-10 rounded-xl shadow-sm" />
            <div>
                <span class="text-xl font-bold text-neutral-900 dark:text-white">e-Koperasi</span>
                <span class="block text-xs text-neutral-500 dark:text-neutral-400 -mt-0.5">Client Portal</span>
            </div>
        </Link>

        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xl dark:shadow-neutral-950/80 border border-neutral-200 dark:border-neutral-800 p-8">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-1">Login Client</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">Masuk untuk melihat dashboard langganan koperasi Anda.</p>

                <!-- Error message -->
                <div v-if="form.errors.email" class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                            placeholder="koperasi@email.com"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                            placeholder="Password"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="rounded border-neutral-300 dark:border-neutral-600 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Ingat saya</span>
                        </label>
                        <Link href="/forgot-password" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                            Lupa password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 disabled:cursor-not-allowed transition-all shadow-sm hover:shadow-md active:scale-[0.98]"
                    >
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-neutral-500 dark:text-neutral-400">
                <Link href="/" class="text-primary-600 dark:text-primary-400 hover:underline">&larr; Kembali ke website</Link>
            </p>
        </div>
    </div>
</template>
