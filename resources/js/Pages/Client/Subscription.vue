<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    subscription: any;
    planFeatures: string[];
}>();

const planLabel = computed(() => {
    const labels: Record<string, string> = { starter: 'Starter', premium: 'Premium', enterprise: 'Enterprise' };
    return labels[props.subscription?.plan] || '-';
});

const priceLabel = computed(() => {
    const prices: Record<string, string> = { starter: 'Rp499.000 / bln', premium: 'Rp1.500.000 / bln', enterprise: 'Custom' };
    return prices[props.subscription?.plan] || '-';
});

const isActive = computed(() => props.subscription?.is_active ?? false);
</script>

<template>
    <ClientLayout title="Detail Langganan">
        <Head title="Langganan - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl space-y-6">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Detail Langganan</h2>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-5 sm:p-6 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ planLabel }}</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ priceLabel }}</p>
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold"
                        :class="isActive ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'"
                    >
                        {{ isActive ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>

                <!-- Info -->
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Mulai</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.started_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Berakhir</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.ends_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Sisa Hari</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.days_remaining ?? '-' }} hari</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Terakhir Diperpanjang</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ subscription?.renewed_at ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div v-if="subscription">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-neutral-500 dark:text-neutral-400">Masa berlaku</span>
                            <span class="text-neutral-900 dark:text-white font-medium">{{ subscription.usage_percent ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="isActive ? 'bg-emerald-500' : 'bg-neutral-400'"
                                :style="{ width: Math.min(100, subscription.usage_percent || 0) + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div v-if="planFeatures.length > 0" class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Fitur Paket</h4>
                    <ul class="space-y-2">
                        <li v-for="(feature, idx) in planFeatures" :key="idx" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>
                </div>
            </div>

            <Link
                href="/client/dashboard"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Dashboard
            </Link>
        </div>
    </ClientLayout>
</template>
