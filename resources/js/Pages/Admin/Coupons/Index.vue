<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps<{ coupons: any; plans?: any }>();
const deleting = ref<string | null>(null);

function destroy(id: string) {
    if (!confirm('Hapus kupon ini?')) return;
    deleting.value = id;
    router.delete(`/admin/coupons/${id}`, { preserveScroll: true, onFinish: () => { deleting.value = null; } });
}
</script>

<template>
    <AdminLayout title="Kupon Promo">
        <Head title="Kupon - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Kupon Promo</h2>
                <Link href="/admin/coupons/create" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">+ Tambah Kupon</Link>
            </div>
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <div v-if="coupons?.data?.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Kode</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Diskon</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Pemakaian</th><th class="text-left px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Berlaku</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Status</th><th class="text-center px-5 py-3 font-medium text-neutral-500 text-xs uppercase">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="c in coupons.data" :key="c.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono font-bold text-primary-600">{{ c.code }}</td>
                                <td class="px-5 py-3">{{ c.discount_type === 'percentage' ? c.discount_value + '%' : 'Rp' + Number(c.discount_value).toLocaleString('id-ID') }}</td>
                                <td class="px-5 py-3">{{ c.used_count }} / {{ c.max_uses || '∞' }}</td>
                                <td class="px-5 py-3 text-xs whitespace-nowrap">{{ c.valid_from ? c.valid_from : '-' }} → {{ c.valid_until ? c.valid_until : '-' }}</td>
                                <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="c.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-500'">{{ c.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-3 text-center"><div class="flex justify-center gap-2"><Link :href="`/admin/coupons/${c.id}/edit`" class="text-xs text-primary-600 hover:underline cursor-pointer">Edit</Link><button @click="destroy(c.id)" :disabled="deleting === c.id" class="text-xs text-red-500 hover:underline cursor-pointer">{{ deleting === c.id ? '...' : 'Hapus' }}</button></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-8 text-center text-sm text-neutral-400">Belum ada kupon.</div>
            </div>
        </div>
    </AdminLayout>
</template>