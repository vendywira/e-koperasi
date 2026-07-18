<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { generate as invoiceGenerate, confirmPaid as invoiceConfirmPaid } from '@/routes/admin/invoice';
import { reject as tenantRequestReject } from '@/routes/admin/tenant-request';
import { show as tenantShow } from '@/routes/admin/tenant';

const props = defineProps<{
    items: any[];
    stats: { pending_requests: number; active_tenants: number; pending_tenants: number; pending_invoices: number; total_tenants: number; };
}>();

const activeTab = ref('all');

const tabs = [
    { key: 'all', label: 'Semua', count: props.items.length },
    { key: 'request', label: 'Request', count: props.stats.pending_requests },
    { key: 'tenant', label: 'Tenant Aktif', count: props.stats.active_tenants },
    { key: 'pending-tenant', label: 'Tenant Pending', count: props.stats.pending_tenants },
    { key: 'invoice', label: 'Invoice Pending', count: props.stats.pending_invoices },
];

const filtered = computed(() => {
    if (activeTab.value === 'all') return props.items;
    if (activeTab.value === 'pending-tenant') {
        return props.items.filter((i: any) => i.type === 'tenant' && i.status === 'pending');
    }
    return props.items.filter((i: any) => i.type === activeTab.value);
});

const badge = (item: any) => {
    if (item.type === 'tenant') {
        const m: Record<string, string> = { active: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', suspended: 'bg-red-50 text-red-700 ring-1 ring-red-200', trialing: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200', pending: 'bg-neutral-50 text-neutral-600 ring-1 ring-neutral-200' };
        return m[item.status] ?? '';
    }
    return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
};

function approveRequest(id: string, name: string) {
    if (!confirm(`Generate invoice untuk "${name}"?`)) return;
    router.post(invoiceGenerate(id).url, {}, { preserveScroll: true });
}
function rejectRequest(id: string, name: string) {
    if (!confirm(`Tolak "${name}"?`)) return;
    router.post(tenantRequestReject(id).url, {}, { preserveScroll: true });
}
function confirmPaid(id: string, name: string) {
    if (!confirm(`Konfirmasi pembayaran "${name}"?`)) return;
    router.post(invoiceConfirmPaid(id).url, {}, { preserveScroll: true });
}

const typeLabel: Record<string, string> = { tenant: 'Tenant', invoice: 'Invoice' };
</script>

<template>
    <AdminLayout>
        <Head title="Tenant" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white tracking-tight">Tenant</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Kelola permintaan, tenant aktif, dan pembayaran</p>
                </div>
                <Link href="/admin/tenants/create" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Tenant
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-8">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                    <p class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Total</p>
                    <p class="text-3xl font-semibold text-neutral-900 dark:text-white mt-1">{{ stats.total_tenants }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-emerald-100 dark:border-emerald-900/30 p-5">
                    <p class="text-xs font-medium text-emerald-500 uppercase tracking-wide">Aktif</p>
                    <p class="text-3xl font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ stats.active_tenants }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-amber-100 dark:border-amber-900/30 p-5">
                    <p class="text-xs font-medium text-amber-500 uppercase tracking-wide">Request</p>
                    <p class="text-3xl font-semibold text-amber-600 dark:text-amber-400 mt-1">{{ stats.pending_requests }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Pending</p>
                    <p class="text-3xl font-semibold text-neutral-600 dark:text-neutral-400 mt-1">{{ stats.pending_tenants }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-amber-100 dark:border-amber-900/30 p-5">
                    <p class="text-xs font-medium text-amber-500 uppercase tracking-wide">Invoice</p>
                    <p class="text-3xl font-semibold text-amber-600 dark:text-amber-400 mt-1">{{ stats.pending_invoices }}</p>
                </div>
            </div>

            <!-- Tabs (scrollable on mobile) -->
            <div class="overflow-x-auto -mx-4 px-4 mb-6">
                <div class="flex gap-1 p-1 bg-neutral-100 dark:bg-neutral-800 rounded-xl w-max min-w-full">
                    <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all whitespace-nowrap"
                        :class="activeTab === tab.key ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700'">
                        {{ tab.label }}
                        <span class="ml-1.5 text-xs opacity-60">({{ tab.count }})</span>
                    </button>
                </div>
            </div>

            <!-- Table (scrollable on mobile) -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800">
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table style="min-width: 640px; width: 100%;">
                    <thead>
                        <tr class="border-b border-neutral-100 dark:border-neutral-800">
                            <th class="text-left px-5 py-4 text-xs font-medium text-neutral-400 uppercase tracking-wider">Nama</th>
                            <th class="text-left px-5 py-4 text-xs font-medium text-neutral-400 uppercase tracking-wider">Domain</th>
                            <th class="text-left px-5 py-4 text-xs font-medium text-neutral-400 uppercase tracking-wider">Client</th>
                            <th class="text-center px-5 py-4 text-xs font-medium text-neutral-400 uppercase tracking-wider">Tipe</th>
                            <th class="text-center px-5 py-4 text-xs font-medium text-neutral-400 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-4 text-xs font-medium text-neutral-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-50 dark:divide-neutral-800/50">
                        <tr v-for="item in filtered" :key="item.id + item.type"
                            class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ item.name }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-mono text-neutral-500">{{ item.domain }}.e-koperasi.com</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-neutral-700 dark:text-neutral-300">{{ item.user_name || '-' }}</div>
                                <div v-if="item.user_email" class="text-xs text-neutral-400">{{ item.user_email }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-xs text-neutral-400 font-medium">{{ typeLabel[item.type] || item.type }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium" :class="badge(item)">{{ item.status }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <template v-if="item.type === 'tenant' && item.status === 'pending'">
                                    <button @click="approveRequest(item.id, item.name)" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">Setujui</button>
                                    <button @click="rejectRequest(item.id, item.name)" class="ml-2 px-3 py-1.5 text-xs font-medium rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Tolak</button>
                                </template>
                                <div v-else-if="item.type === 'invoice' && item.status === 'pending'">
                                    <button @click="confirmPaid(item.id, item.name)" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">Konfirmasi</button>
                                </div>
                                <div v-else-if="item.type === 'tenant'">
                                    <Link :href="tenantShow(item.id).url" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Detail &rarr;</Link>
                                </div>
                                <span v-else class="text-xs text-neutral-300">&mdash;</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <div v-if="filtered.length === 0" class="px-5 py-12 text-center text-sm text-neutral-400">Tidak ada data.</div>
            </div>
        </div>
    </AdminLayout>
</template>
