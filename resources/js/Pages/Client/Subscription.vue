<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    subscriptions: any[];
}>();

function daysLeft(endsAt: string | null): number | null {
    if (!endsAt) return null;
    const diff = new Date(endsAt).getTime() - Date.now();
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}

const statusBadge = (s: string) => {
    const map: Record<string, string> = {
        active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        expired: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        trialing: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    };
    return map[s] ?? '';
};
</script>

<template>
    <ClientLayout title="Langganan KSU">
        <Head title="Langganan - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-5xl space-y-6">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Langganan KSU Anda</h2>

            <div v-if="subscriptions.length === 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center">
                <p class="text-neutral-500 dark:text-neutral-400">Belum ada langganan KSU. Hubungi admin untuk mendaftar.</p>
            </div>

            <div v-for="sub in subscriptions" :key="sub.id" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-5 sm:p-6 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ sub.tenant_name }}</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 font-mono">{{ sub.tenant_domain }}.e-koperasi.com</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusBadge(sub.status)">
                            {{ sub.status }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="daysLeft(sub.ends_at) !== null && daysLeft(sub.ends_at)! <= 7 ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400'">
                            {{ daysLeft(sub.ends_at) ?? '&infin;' }} hari
                        </span>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Paket</p>
                            <p class="text-sm font-medium mt-1 capitalize">{{ sub.plan }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Resort</p>
                            <p class="text-sm font-medium mt-1">Maks. {{ sub.max_resorts }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Mulai</p>
                            <p class="text-sm font-medium mt-1">{{ sub.started_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Berakhir</p>
                            <p class="text-sm font-medium mt-1">{{ sub.ends_at ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-neutral-500">Masa berlaku</span>
                            <span class="font-medium">{{ sub.usage_percent ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all"
                                :class="sub.is_active ? 'bg-emerald-500' : 'bg-neutral-400'"
                                :style="{ width: Math.min(100, sub.usage_percent || 0) + '%' }" />
                        </div>
                    </div>

                    <!-- Features -->
                    <div v-if="sub.features?.length">
                        <h4 class="text-sm font-semibold mb-2">Fitur</h4>
                        <ul class="space-y-1">
                            <li v-for="(f, i) in sub.features" :key="i" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                {{ f }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Harga -->
                <div class="px-5 sm:px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <span class="text-sm text-neutral-500">Rp {{ Number(sub.price_per_resort).toLocaleString('id-ID') }} / resort</span>
                    <a :href="'https://' + sub.tenant_domain + '.e-koperasi.com'" target="_blank"
                        class="text-sm text-primary-600 hover:underline font-medium">
                        Buka Aplikasi &rarr;
                    </a>
                </div>
            </div>

            <Link href="/client/dashboard"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 hover:text-neutral-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Dashboard
            </Link>
        </div>
    </ClientLayout>
</template>
