<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    clients: any;
    filters: { search: string | null };
}>();

const search = ref(props.filters?.search || '');

let debounceTimer: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/admin/clients', { search: val || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
});

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

            <!-- Search -->
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari client berdasarkan nama, email, atau telepon..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
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
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">KSU</th>
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
                                <td class="px-5 py-3 text-center">
                                    <span v-if="client.ksu_count > 0" class="inline-flex items-center gap-1 text-xs font-medium">
                                        <svg class="w-3.5 h-3.5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                        {{ client.ksu_active_count }}/{{ client.ksu_count }} aktif
                                    </span>
                                    <span v-else class="text-xs text-neutral-400">-</span>
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
                <div v-if="clients.total > clients.per_page" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex flex-col sm:flex-row justify-between items-center gap-2 text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">Menampilkan {{ clients.from }}–{{ clients.to }} dari {{ clients.total }}</span>
                    <div class="flex items-center gap-2">
                        <span v-for="link in clients.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded text-xs font-medium transition-colors"
                                :class="link.active
                                    ? 'bg-primary-600 text-white'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-500" v-html="link.label" />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
