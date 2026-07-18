<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    subscription: any;
    pendingRequest: any;
    userTenants: any[];
    recentPayments: any[];
    ticketStats?: {
        total: number;
        pending: number;
        in_progress: number;
        solved: number;
        close: number;
    };
}>();

const planLabel = computed(() => {
    const labels: Record<string, string> = {
        starter: 'Starter',
        premium: 'Premium',
        enterprise: 'Enterprise',
    };
    return labels[props.subscription?.plan] || props.subscription?.plan || '-';
});

const priceLabel = computed(() => {
    const prices: Record<string, string> = {
        starter: 'Rp499.000 / bln',
        premium: 'Rp1.500.000 / bln',
        enterprise: 'Custom',
    };
    return prices[props.subscription?.plan] || '-';
});

const statusColor = computed(() => {
    if (!props.subscription) return 'bg-neutral-400';
    return props.subscription.is_active ? 'bg-emerald-500' : 'bg-red-500';
});

const statusLabel = computed(() => {
    if (!props.subscription) return 'Belum Aktif';
    return props.subscription.is_active ? 'Aktif' : 'Tidak Aktif';
});
</script>

<template>
    <ClientLayout title="Beranda">
        <Head title="Dashboard - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl">
            <!-- Welcome -->
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">
                    Selamat datang, {{ $page.props.auth?.user?.name || 'Client' }}
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Ringkasan langganan koperasi Anda.</p>
            </div>

            <!-- Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Status Langganan</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span :class="statusColor" class="w-3 h-3 rounded-full inline-block"></span>
                                <span class="text-lg font-bold text-neutral-900 dark:text-white">{{ statusLabel }}</span>
                            </div>
                        </div>
                        <span class="text-3xl opacity-10">📋</span>
                    </div>

                    <div v-if="subscription" class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-500 dark:text-neutral-400">Sisa Masa Aktif</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ subscription.days_remaining }} hari</span>
                            </div>
                            <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="subscription.is_active ? 'bg-emerald-500' : 'bg-red-400'"
                                    :style="{ width: Math.min(100, subscription.usage_percent || 0) + '%' }"
                                />
                            </div>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">
                                {{ subscription.started_at ?? '-' }} — {{ subscription.ends_at ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <div v-else>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Belum ada langganan aktif. Hubungi admin.</p>
                    </div>
                </div>

                <!-- Package Card -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Paket</p>
                            <h3 class="text-xl font-bold text-neutral-900 dark:text-white mt-1">{{ planLabel }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ priceLabel }}</p>
                        </div>
                        <span class="text-3xl opacity-10">📦</span>
                    </div>
                    <Link
                        href="/client/subscription"
                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors"
                    >
                        Lihat Detail Paket
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Ticket Stats -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Ticket Saya</h3>
                    <Link href="/tickets" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Lihat Semua</Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ ticketStats?.total || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Total</p>
                    </div>
                    <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ ticketStats?.pending || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Pending</p>
                    </div>
                    <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ ticketStats?.in_progress || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Diproses</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ ticketStats?.solved || 0 }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Selesai</p>
                    </div>
                </div>
            </div>

            <!-- KSU Tenants -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">Tenant KSU Saya</h3>
                </div>
                <div v-if="userTenants.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Nama</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Domain</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="t in userTenants" :key="t.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-medium">{{ t.tenant_name }}</td>
                                <td class="px-5 py-3 text-neutral-500 font-mono text-xs">{{ t.tenant_domain }}.e-koperasi.com</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="t.status === 'active' ? 'bg-emerald-100 text-emerald-700' : t.status === 'trialing' ? 'bg-amber-100 text-amber-700' : t.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-neutral-100 text-neutral-500'">
                                        {{ t.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-5 text-center text-sm text-neutral-400">
                    Belum memiliki tenant KSU.
                    <Link href="/client/request-tenant" class="text-primary-600 hover:underline ml-1">Ajukan tenant baru</Link>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">Riwayat Pembayaran</h3>
                    <Link
                        v-if="recentPayments.length > 0"
                        href="/client/payments"
                        class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline"
                    >
                        Lihat Semua
                    </Link>
                </div>

                <div v-if="recentPayments.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Invoice</th>
                                <th class="text-right px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Jumlah</th>
                                <th class="text-center px-5 sm:px-6 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="payment in recentPayments" :key="payment.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 sm:px-6 py-3 text-neutral-700 dark:text-neutral-300">{{ payment.paid_at ?? payment.created_at }}</td>
                                <td class="px-5 sm:px-6 py-3">
                                    <Link :href="'/client/payments/' + payment.id" class="text-emerald-600 dark:text-emerald-400 hover:underline font-mono text-xs">
                                        {{ payment.receipt_number }}
                                    </Link>
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-right font-medium text-neutral-900 dark:text-white">
                                    Rp{{ Number(payment.amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-center">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="payment.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'"
                                    >
                                        {{ payment.status === 'paid' ? 'Lunas' : payment.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 sm:px-6 py-8 text-center text-sm text-neutral-400 dark:text-neutral-500">
                    <p class="text-2xl mb-2">💳</p>
                    <p>Belum ada pembayaran tercatat.</p>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
