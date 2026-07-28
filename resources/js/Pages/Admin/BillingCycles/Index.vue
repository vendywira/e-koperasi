<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps<{ cycles: any[] }>();
const editing = ref<string | null>(null);

const form = useForm({ name: '', slug: '', months: 1, discount_percent: 0, sort_order: 0, is_active: true });

function createOrUpdate(id?: string) {
    if (id) {
        form.put(`/admin/billing-cycles/${id}`, { preserveScroll: true, onSuccess: () => { editing.value = null; form.reset(); } });
    } else {
        form.post('/admin/billing-cycles', { preserveScroll: true, onSuccess: () => { form.reset(); } });
    }
}
function startEdit(c: any) {
    editing.value = c.id;
    form.name = c.name; form.slug = c.slug; form.months = c.months;
    form.discount_percent = c.discount_percent; form.sort_order = c.sort_order; form.is_active = c.is_active;
}
function cancelEdit() { editing.value = null; form.reset(); }
function destroy(id: string) { if (!confirm('Hapus siklus ini?')) return; router.delete(`/admin/billing-cycles/${id}`, { preserveScroll: true }); }
</script>

<template>
    <AdminLayout title="Siklus Tagihan">
        <Head title="Siklus Tagihan - e-Koperasi Admin" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Siklus Tagihan</h2>
                <button @click="editing = null; form.reset()" class="px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer min-h-[44px]">+ Tambah</button>
            </div>

            <!-- Form Add/Edit -->
            <div v-if="editing !== null || !editing" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 sm:p-5 mb-6">
                <h3 class="text-sm font-semibold mb-3">{{ editing ? 'Edit Siklus' : 'Tambah Siklus Baru' }}</h3>
                <form @submit.prevent="createOrUpdate(editing || undefined)" class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
                    <div class="col-span-2 sm:col-span-1"><label class="text-xs font-medium mb-1 block">Nama</label><input v-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div class="col-span-2 sm:col-span-1"><label class="text-xs font-medium mb-1 block">Slug</label><input v-model="form.slug" required class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 font-mono" /></div>
                    <div><label class="text-xs font-medium mb-1 block">Bulan</label><input v-model.number="form.months" type="number" min="1" required class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div><label class="text-xs font-medium mb-1 block">Diskon %</label><input v-model.number="form.discount_percent" type="number" min="0" max="100" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div><label class="text-xs font-medium mb-1 block">Urutan</label><input v-model.number="form.sort_order" type="number" min="0" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div class="flex gap-2 col-span-2 sm:col-span-1">
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer min-h-[44px]">{{ form.processing ? '...' : editing ? 'Simpan' : 'Tambah' }}</button>
                        <button v-if="editing" type="button" @click="cancelEdit" class="px-4 py-2.5 border-2 border-neutral-300 dark:border-neutral-600 rounded-lg text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer min-h-[44px]">Batal</button>
                    </div>
                </form>
            </div>

            <!-- Table (horizontal scroll on mobile) -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr><th class="text-left px-4 py-3 font-medium text-neutral-500 text-xs uppercase whitespace-nowrap">Nama</th><th class="text-left px-4 py-3 font-medium text-neutral-500 text-xs uppercase whitespace-nowrap">Slug</th><th class="text-center px-4 py-3 font-medium text-neutral-500 text-xs uppercase whitespace-nowrap">Bulan</th><th class="text-center px-4 py-3 font-medium text-neutral-500 text-xs uppercase whitespace-nowrap">Diskon</th><th class="text-center px-4 py-3 font-medium text-neutral-500 text-xs uppercase whitespace-nowrap">Status</th><th class="text-center px-4 py-3 font-medium text-neutral-500 text-xs uppercase whitespace-nowrap">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="c in cycles" :key="c.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-4 py-3 font-medium whitespace-nowrap">{{ c.name }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-neutral-500 whitespace-nowrap">{{ c.slug }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">{{ c.months }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap"><span class="text-emerald-600 font-medium">{{ c.discount_percent }}%</span></td>
                                <td class="px-4 py-3 text-center whitespace-nowrap"><span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="c.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-500'">{{ c.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-4 py-3 text-center whitespace-nowrap"><div class="flex justify-center gap-2"><button @click="startEdit(c)" class="text-xs text-primary-600 hover:underline cursor-pointer">Edit</button><button @click="destroy(c.id)" class="text-xs text-red-500 hover:underline cursor-pointer">Hapus</button></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>