<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

defineProps<{
    payments: any[];
}>();
</script>

<template>
    <ClientLayout title="Riwayat Pembayaran">
        <Head title="Pembayaran - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Riwayat Pembayaran</h2>
            </div>

            <div v-if="payments.length > 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">#</th>
                                <th class="text-left px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Invoice</th>
                                <th class="text-left px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-right px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Jumlah</th>
                                <th class="text-center px-5 sm:px-6 py-3.5 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="(payment, idx) in payments" :key="payment.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 sm:px-6 py-3.5 text-neutral-500 dark:text-neutral-400">{{ idx + 1 }}</td>
                                <td class="px-5 sm:px-6 py-3.5">
                                    <Link :href="'/client/payments/' + payment.id" class="text-emerald-600 dark:text-emerald-400 hover:underline font-mono text-xs">
                                        {{ payment.receipt_number }}
                                    </Link>
                                </td>
                                <td class="px-5 sm:px-6 py-3.5 text-neutral-700 dark:text-neutral-300">{{ payment.paid_at ?? payment.created_at }}</td>
                                <td class="px-5 sm:px-6 py-3.5 text-right font-medium text-neutral-900 dark:text-white font-mono">
                                    Rp{{ Number(payment.amount).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-5 sm:px-6 py-3.5 text-center">
                                    <span
                                        class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="payment.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'"
                                    >
                                        {{ payment.status === 'paid' ? 'Lunas' : payment.status === 'pending' ? 'Pending' : 'Gagal' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center">
                <p class="text-3xl mb-3">💳</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Belum ada riwayat pembayaran.</p>
            </div>
        </div>
    </ClientLayout>
</template>
