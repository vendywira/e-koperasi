<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        total_clients: number;
        active_subscriptions: number;
        expired_subscriptions: number;
        total_revenue: string;
        total_revenue_raw: number;
    };
    recentPayments: any[];
    planDistribution: Record<string, number>;
    monthlyRevenue: any[];
    ticketStats?: {
        total: number;
        pending: number;
        in_progress: number;
        solved: number;
        close: number;
    };
}>();

const planLabels: Record<string, string> = {
    starter: 'Starter',
    premium: 'Premium',
    enterprise: 'Enterprise',
};

const formatDate = (dt: string | null) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AdminLayout title="Dashboard Admin">
        <Head title="Dashboard Admin - e-Koperasi CMS" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Welcome -->
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Dashboard Admin</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Ringkasan client, subscription, dan pendapatan.</p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Total Client</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.total_clients }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Subs Aktif</p>
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.active_subscriptions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Subs Expired</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.expired_subscriptions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Total Pendapatan</p>
                            <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.total_revenue }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row: Plan Distribution + Recent Payments -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Plan Distribution -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                    <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Distribusi Paket</h3>
                    <div v-if="Object.keys(planDistribution).length > 0" class="space-y-3">
                        <div v-for="(total, plan) in planDistribution" :key="plan" class="flex items-center gap-3">
                            <span class="text-sm font-medium capitalize text-neutral-700 dark:text-neutral-300 w-24">{{ planLabels[plan] || plan }}</span>
                            <div class="flex-1 bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 overflow-hidden">
                                <div
                                    class="h-full rounded-full"
                                    :class="plan === 'premium' ? 'bg-primary-500' : plan === 'starter' ? 'bg-amber-500' : 'bg-violet-500'"
                                    :style="{ width: (total / Math.max(...Object.values(planDistribution)) * 100) + '%' }"
                                />
                            </div>
                            <span class="text-sm font-semibold text-neutral-900 dark:text-white w-8 text-right">{{ total }}</span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-neutral-400 dark:text-neutral-500">Belum ada data paket.</p>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                        <h3 class="font-semibold text-neutral-900 dark:text-white">Pembayaran Terbaru</h3>
                        <Link
                            v-if="recentPayments.length > 0"
                            href="/admin/clients"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                        >
                            Lihat Semua
                        </Link>
                    </div>
                    <div v-if="recentPayments.length > 0" class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <div v-for="payment in recentPayments" :key="payment.id" class="px-5 sm:px-6 py-3 flex items-center justify-between hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ payment.subscription?.user?.name || 'Unknown' }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ payment.receipt_number }} · {{ formatDate(payment.paid_at) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-neutral-900 dark:text-white ml-4">
                                Rp{{ Number(payment.amount).toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="px-5 sm:px-6 py-6 text-center text-sm text-neutral-400 dark:text-neutral-500">
                        Belum ada pembayaran.
                    </div>
                </div>
            </div>

            <!-- Ticket Stats -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Ticket Support</h3>
                    <Link href="/admin/tickets" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Kelola Ticket</Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <p class="text-xl font-bold text-neutral-900 dark:text-white">{{ ticketStats?.total || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Total</p>
                    </div>
                    <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ ticketStats?.pending || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Pending</p>
                    </div>
                    <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ ticketStats?.in_progress || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Diproses</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ ticketStats?.solved || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Selesai</p>
                    </div>
                    <div class="text-center p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                        <p class="text-xl font-bold text-neutral-600 dark:text-neutral-400">{{ ticketStats?.close || 0 }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Closed</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Aksi Cepat</h3>
                <div class="flex flex-wrap gap-3">
                    <Link
                        href="/admin/clients"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        Kelola Client
                    </Link>
                    <Link
                        href="/admin/cms"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        Edit CMS
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
