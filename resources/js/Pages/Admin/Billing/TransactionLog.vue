<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{ transactions: any }>();
</script>

<template>
    <AdminLayout title="Log Transaksi">
        <Head title="Log Transaksi - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/admin/billing" class="text-neutral-500 hover:text-neutral-700"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg></Link>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Log Transaksi Pembayaran</h2>
            </div>
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">ID</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Invoice</th><th class="text-right px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Amount</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Channel</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Ref</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Status</th><th class="text-right px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Dibuat</th></tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="t in transactions?.data" :key="t.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono text-xs">{{ t.id?.substring(0, 8) }}...</td>
                                <td class="px-5 py-3">{{ t.invoice?.invoice_number || '-' }}</td>
                                <td class="px-5 py-3 text-right font-mono">Rp{{ Number(t.amount).toLocaleString('id-ID') }}</td>
                                <td class="px-5 py-3">{{ t.channel_name || '-' }}</td>
                                <td class="px-5 py-3 font-mono text-xs">{{ t.duitku_ref || '-' }}</td>
                                <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="t.status === 'success' ? 'bg-emerald-100 text-emerald-700' : t.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">{{ t.status }}</span></td>
                                <td class="px-5 py-3 text-right text-xs text-neutral-500">{{ t.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>