<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { email } from '@/routes/password';

const form = useForm({
    email: '',
});

const page = usePage();
const status = computed(() => (page.props as any).status as string | null);

function submit() {
    form.post(email().url);
}
</script>

<template>
    <Head title="Lupa Password — CMS Admin" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-neutral-50 via-white to-primary-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-neutral-950 px-4">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50" />
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-primary-50 dark:bg-primary-900/10 rounded-full blur-3xl opacity-50" />
        </div>

        <div class="relative w-full max-w-md">
            <!-- Card -->
            <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-neutral-800 p-8">
                <!-- Logo -->
                <div class="flex items-center justify-center gap-3 mb-8">
                    <img src="/images/logo-only-white.png" alt="e-Koperasi" class="h-11 w-11" />
                    <div>
                        <h1 class="text-lg font-bold text-neutral-900 dark:text-white">e-Koperasi</h1>
                        <p class="text-[11px] text-neutral-400 dark:text-neutral-500 -mt-0.5">CMS Admin Panel</p>
                    </div>
                </div>

                <h2 class="text-center text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-6">
                    Masukkan email Anda untuk menerima link reset password
                </h2>

                <!-- Success Status -->
                <div
                    v-if="status"
                    class="mb-6 px-4 py-3 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 text-sm text-primary-700 dark:text-primary-400"
                >
                    {{ status }}
                </div>

                <!-- Error -->
                <div
                    v-if="form.errors.email"
                    class="mb-6 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-400"
                >
                    {{ form.errors.email }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                            Email
                        </label>
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

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-all duration-150 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                    >
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            Mengirim...
                        </span>
                        <span v-else>Kirim Link Reset</span>
                    </button>
                </form>
            </div>

            <!-- Back to login -->
            <p class="text-center mt-6 text-sm text-neutral-500 dark:text-neutral-400">
                <Link href="/login" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">← Kembali ke login</Link>
            </p>
        </div>
    </div>
</template>
