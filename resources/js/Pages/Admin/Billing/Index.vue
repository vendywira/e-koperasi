<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{ invoices: any; stats: any }>();
</script>

<template>
    <AdminLayout title="Billing">
        <Head title="Billing - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Pendapatan Total</p>
                    <p class="text-2xl font-bold text-primary-600 mt-1">Rp{{ Number(stats.total_revenue).toLocaleString('id-ID') }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">MRR</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rp{{ Number(stats.mrr).toLocaleString('id-ID') }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-amber-200 dark:border-amber-900/50 p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Outstanding</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">Rp{{ Number(stats.pending_amount).toLocaleString('id-ID') }}</p>
                    <p class="text-xs text-neutral-400">{{ stats.pending_count }} invoice pending</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Lunas</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ stats.paid_count }}</p>
                    <p class="text-xs text-neutral-400">invoice</p>
                </div>
            </div>
            <div class="flex justify-end"><Link href="/admin/billing/transactions" class="text-sm text-primary-600 hover:underline cursor-pointer">Lihat Log Transaksi →</Link></div>
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Invoice</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Client</th><th class="text-right px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Jumlah</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Status</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="inv in invoices?.data" :key="inv.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono text-xs">{{ inv.invoice_number }}</td>
                                <td class="px-5 py-3">{{ inv.name }}</td>
                                <td class="px-5 py-3 text-right font-mono">Rp{{ Number(inv.total_amount).toLocaleString('id-ID') }}</td>
                                <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="inv.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : inv.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-neutral-100 text-neutral-500'">{{ inv.status }}</span></td>
                                <td class="px-5 py-3 text-center"><Link :href="`/admin/invoices/${inv.id}/download`" class="text-xs text-primary-600 hover:underline cursor-pointer">PDF</Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>