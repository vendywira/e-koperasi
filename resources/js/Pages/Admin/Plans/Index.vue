<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps<{ plans: any[] }>();
const deleting = ref<string | null>(null);

function destroy(id: string, name: string) {
    if (!confirm(`Hapus paket "${name}"?`)) return;
    deleting.value = id;
    router.delete(`/admin/plans/${id}`, { preserveScroll: true, onFinish: () => { deleting.value = null; } });
}
</script>

<template>
    <AdminLayout title="Paket Subscription">
        <Head title="Paket - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Paket Langganan</h2>
                <Link href="/admin/plans/create" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">+ Tambah Paket</Link>
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="plan in plans" :key="plan.id" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ plan.name }}</h3>
                            <p class="text-xs text-neutral-500">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-neutral-100 dark:bg-neutral-800">{{ plan.type }}</span>
                                <span v-if="plan.type === 'enterprise'" class="ml-1 text-neutral-400">Unlimited resort</span>
                                <span v-else-if="plan.max_resorts > 0" class="ml-1 text-neutral-400">{{ plan.max_resorts }} resort</span>
                            </p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="plan.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500'">{{ plan.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <p class="text-2xl font-bold text-primary-600 mb-4">
                        <template v-if="plan.type === 'trial'">Gratis <span class="text-sm font-normal text-neutral-500">{{ plan.trial_days }} hari</span></template>
                        <template v-else-if="plan.type === 'enterprise'">Rp{{ Number(plan.pricing_config?.price || plan.price_per_month).toLocaleString('id-ID') }} <span class="text-sm font-normal text-neutral-500">one-time</span></template>
                        <template v-else>Rp{{ Number(plan.pricing_config?.price_per_resort || 0).toLocaleString('id-ID') }} <span class="text-sm font-normal text-neutral-500">/resort/bln</span></template>
                    </p>
                    <ul v-if="plan.features?.length" class="space-y-1.5 mb-4">
                        <li v-for="f in plan.features" :key="f.id" class="flex items-start gap-1.5 text-xs text-neutral-600 dark:text-neutral-400">
                            <svg class="w-3.5 h-3.5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ f.feature_text }}
                        </li>
                    </ul>
                    <div class="flex gap-2 pt-3 border-t border-neutral-100 dark:border-neutral-800">
                        <Link :href="`/admin/plans/${plan.id}/edit`" class="flex-1 text-center px-3 py-1.5 text-xs font-medium rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition cursor-pointer">Edit</Link>
                        <button @click="destroy(plan.id, plan.name)" :disabled="deleting === plan.id" class="flex-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition cursor-pointer">{{ deleting === plan.id ? '...' : 'Hapus' }}</button>
                    </div>
                </div>
            </div>
            <div v-if="plans.length === 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center mt-6"><p class="text-neutral-500">Belum ada paket. Tambah paket baru untuk mulai menjual subscription.</p></div>
        </div>
    </AdminLayout>
</template>