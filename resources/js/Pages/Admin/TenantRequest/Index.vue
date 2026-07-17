<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{ requests: any }>();

function approve(id: string, name: string) {
    if (!confirm(`Setujui permintaan tenant "${name}"? Tenant akan langsung aktif.`)) return;
    router.post(`/admin/tenant-requests/${id}/approve`, {}, { preserveScroll: true });
}

function reject(id: string, name: string) {
    if (!confirm(`Tolak permintaan tenant "${name}"?`)) return;
    router.post(`/admin/tenant-requests/${id}/reject`, {}, { preserveScroll: true });
}

const statusBadge = (s: string) => {
    const map: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[s] ?? '';
};
</script>

<template>
    <AdminLayout>
        <Head title="Permintaan Tenant" />
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-1">Permintaan Tenant</h1>
            <p class="text-sm text-neutral-500 mb-6">Client mengajukan pembuatan tenant KSU baru</p>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/50">
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Client</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Nama Koperasi</th>
                            <th class="text-center px-4 py-3 font-medium text-neutral-500">Domain</th>
                            <th class="text-center px-4 py-3 font-medium text-neutral-500">Resort</th>
                            <th class="text-center px-4 py-3 font-medium text-neutral-500">Status</th>
                            <th class="text-right px-4 py-3 font-medium text-neutral-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        <tr v-for="r in requests.data" :key="r.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ r.user?.name }}</p>
                                <p class="text-xs text-neutral-400">{{ r.user?.email }}</p>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ r.name }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs">{{ r.domain }}.ksu.app</td>
                            <td class="px-4 py-3 text-center">{{ r.max_resorts }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(r.status)">{{ r.status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div v-if="r.status === 'pending'" class="flex items-center justify-end gap-2">
                                    <button @click="approve(r.id, r.name)" class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Setujui</button>
                                    <button @click="reject(r.id, r.name)" class="px-3 py-1.5 text-xs rounded-lg border border-red-300 text-red-700 hover:bg-red-50">Tolak</button>
                                </div>
                                <span v-else class="text-xs text-neutral-400">{{ r.reviewed_at ? new Date(r.reviewed_at).toLocaleDateString('id-ID') : '-' }}</span>
                            </td>
                        </tr>
                        <tr v-if="requests.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-neutral-400">Belum ada permintaan tenant.</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="requests.links?.length > 3" class="flex items-center justify-between px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
                    <div class="text-xs text-neutral-500">{{ requests.from }}–{{ requests.to }} dari {{ requests.total }}</div>
                    <div class="flex gap-1">
                        <Link v-for="link in requests.links" :key="link.label" :href="link.url || '#'" class="px-3 py-1.5 text-xs rounded-lg transition-colors"
                            :class="link.active ? 'bg-primary-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
