<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{ transactions: any; filters: any }>();

function checkStatus(id: string) {
    router.post(`/admin/payment-transactions/${id}/check-status`, {}, { preserveScroll: true });
}

function filterBy(s: string) {
    router.get('/admin/payment-transactions', s ? { status: s } : {}, { preserveScroll: true, replace: true });
}
</script>

<template>
    <AdminLayout title="Payment Transactions">
        <Head title="Transaksi - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl">
            <div class="flex items-center gap-3 mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Log Transaksi Payment</h2>
            </div>
            <div class="flex gap-2 mb-4">
                <button @click="filterBy('')" class="px-3 py-1.5 text-xs rounded-lg font-medium transition cursor-pointer" :class="!filters?.status ? 'bg-primary-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600'">Semua</button>
                <button @click="filterBy('pending')" class="px-3 py-1.5 text-xs rounded-lg font-medium transition cursor-pointer" :class="filters?.status === 'pending' ? 'bg-amber-500 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600'">Pending</button>
                <button @click="filterBy('success')" class="px-3 py-1.5 text-xs rounded-lg font-medium transition cursor-pointer" :class="filters?.status === 'success' ? 'bg-emerald-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600'">Success</button>
                <button @click="filterBy('failed')" class="px-3 py-1.5 text-xs rounded-lg font-medium transition cursor-pointer" :class="filters?.status === 'failed' ? 'bg-red-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600'">Failed</button>
            </div>
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">ID</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Invoice</th><th class="text-right px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Amount</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Channel</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Status</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="t in transactions?.data" :key="t.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono text-xs">{{ t.id?.substring(0, 8) }}...</td>
                                <td class="px-5 py-3">{{ t.invoice?.invoice_number || '-' }}</td>
                                <td class="px-5 py-3 text-right font-mono">Rp{{ Number(t.amount).toLocaleString('id-ID') }}</td>
                                <td class="px-5 py-3">{{ t.channel_name || '-' }}</td>
                                <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="t.status === 'success' ? 'bg-emerald-100 text-emerald-700' : t.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">{{ t.status }}</span></td>
                                <td class="px-5 py-3 text-center"><button @click="checkStatus(t.id)" class="text-xs text-primary-600 hover:underline cursor-pointer">Cek Status</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>