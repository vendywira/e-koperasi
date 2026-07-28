<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{ channels: any[] }>();

function toggle(id: string) { router.post(`/admin/payment-channels/${id}/toggle`, {}, { preserveScroll: true }); }
function sync() { router.post('/admin/payment-channels/sync', {}, { preserveScroll: true }); }
</script>

<template>
    <AdminLayout title="Payment Channels">
        <Head title="Payment Channels - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Metode Pembayaran</h2>
                <button @click="sync" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">Sync dari Duitku</button>
            </div>
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <div v-if="channels.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Kode</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Nama</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Tipe</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Status</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="ch in channels" :key="ch.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono text-xs">{{ ch.code }}</td>
                                <td class="px-5 py-3">{{ ch.name }}</td>
                                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-neutral-100 dark:bg-neutral-800">{{ ch.type }}</span></td>
                                <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="ch.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-500'">{{ ch.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-3 text-center"><button @click="toggle(ch.id)" class="text-xs text-primary-600 hover:underline cursor-pointer">{{ ch.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-8 text-center text-sm text-neutral-400">Belum ada payment channel. Klik "Sync dari Duitku".</div>
            </div>
        </div>
    </AdminLayout>
</template>