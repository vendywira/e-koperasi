<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{ invoices: any }>();

function confirmPaid(id: string, name: string) {
    if (!confirm(`Konfirmasi pembayaran untuk "${name}"?`)) return;
    router.post(`/admin/invoices/${id}/confirm-paid`, {}, { preserveScroll: true });
}

const statusBadge = (s: string) => {
    const map: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-800',
        paid: 'bg-emerald-100 text-emerald-800',
        cancelled: 'bg-neutral-100 text-neutral-500',
    };
    return map[s] ?? '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Invoice" />
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold mb-1">Invoice</h1>
            <p class="text-sm text-neutral-500 mb-6">Tagihan subscription tenant KSU</p>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-neutral-50">
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Tenant</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Client</th>
                            <th class="text-center px-4 py-3 font-medium text-neutral-500">Resort</th>
                            <th class="text-right px-4 py-3 font-medium text-neutral-500">Total</th>
                            <th class="text-center px-4 py-3 font-medium text-neutral-500">Status</th>
                            <th class="text-right px-4 py-3 font-medium text-neutral-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-neutral-50">
                            <td class="px-4 py-3 font-medium">{{ inv.name }}</td>
                            <td class="px-4 py-3 text-xs text-neutral-500">{{ inv.user?.email }}</td>
                            <td class="px-4 py-3 text-center">{{ inv.resort_count }} x Rp {{ Number(inv.price_per_resort).toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-right font-medium">Rp {{ Number(inv.total_amount).toLocaleString('id-ID') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(inv.status)">{{ inv.status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button v-if="inv.status === 'pending'" @click="confirmPaid(inv.id, inv.name)"
                                    class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                                    Konfirmasi Bayar
                                </button>
                                <span v-else class="text-xs text-neutral-400">{{ inv.paid_at ? new Date(inv.paid_at).toLocaleDateString('id-ID') : '-' }}</span>
                            </td>
                        </tr>
                        <tr v-if="invoices.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-neutral-400">Belum ada invoice.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
