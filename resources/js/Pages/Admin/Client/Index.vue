<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    clients: any;
}>();

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AdminLayout title="Daftar Client">
        <Head title="Client - CMS Admin" />

        <div class="p-4 sm:p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Daftar Client / Koperasi</h2>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Koperasi</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Email</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Paket</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Bergabung</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white">{{ client.name }}</td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400">{{ client.email }}</td>
                                <td class="px-5 py-3 capitalize text-neutral-700 dark:text-neutral-300">{{ client.subscription?.plan ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span
                                        v-if="client.subscription"
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="client.subscription.is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'"
                                    >
                                        {{ client.subscription.is_active ? 'Aktif' : client.subscription.status }}
                                    </span>
                                    <span v-else class="text-xs text-neutral-400 dark:text-neutral-500">Belum ada</span>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400 text-xs">{{ formatDate(client.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <Link
                                        :href="'/admin/clients/' + client.id"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors"
                                    >
                                        Kelola
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="clients.total > clients.per_page" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-center text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">Menampilkan {{ clients.from }}–{{ clients.to }} dari {{ clients.total }}</span>
                    <div class="flex gap-2">
                        <Link
                            v-if="clients.prev_page_url"
                            :href="clients.prev_page_url"
                            class="px-3 py-1 rounded text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Sebelumnya
                        </Link>
                        <Link
                            v-if="clients.next_page_url"
                            :href="clients.next_page_url"
                            class="px-3 py-1 rounded text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Selanjutnya
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
