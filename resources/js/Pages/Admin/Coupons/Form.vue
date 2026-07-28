<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{ coupon: any; plans?: any }>();
const isEdit = !!props.coupon;

const form = useForm({
    code: props.coupon?.code || '',
    discount_type: props.coupon?.discount_type || 'percentage',
    discount_value: props.coupon?.discount_value || 0,
    max_uses: props.coupon?.max_uses || null,
    valid_from: props.coupon?.valid_from || null,
    valid_until: props.coupon?.valid_until || null,
    plan_ids: props.coupon?.plan_ids || [],
    is_active: props.coupon?.is_active ?? true,
});

function submit() {
    isEdit ? form.put(`/admin/coupons/${props.coupon.id}`) : form.post('/admin/coupons');
}
</script>

<template>
    <AdminLayout :title="isEdit ? 'Edit Kupon' : 'Tambah Kupon'">
        <Head :title="(isEdit ? 'Edit' : 'Tambah') + ' Kupon'" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/admin/coupons" class="text-neutral-500 hover:text-neutral-700"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg></Link>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ isEdit ? 'Edit Kupon' : 'Tambah Kupon Baru' }}</h2>
            </div>
            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium mb-1">Kode Kupon</label><input v-model="form.code" type="text" required class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700 uppercase" /><p v-if="form.errors.code" class="text-xs text-red-500 mt-1">{{ form.errors.code }}</p></div>
                    <div><label class="block text-sm font-medium mb-1">Tipe Diskon</label><select v-model="form.discount_type" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700"><option value="percentage">Persen (%)</option><option value="fixed">Nominal (Rp)</option></select></div>
                    <div><label class="block text-sm font-medium mb-1">Nilai Diskon</label><input v-model="form.discount_value" type="number" min="0" step="0.01" required class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div><label class="block text-sm font-medium mb-1">Maks Pemakaian (kosong = unlimited)</label><input v-model="form.max_uses" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div><label class="block text-sm font-medium mb-1">Berlaku Dari</label><input v-model="form.valid_from" type="date" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                    <div><label class="block text-sm font-medium mb-1">Berlaku Sampai</label><input v-model="form.valid_until" type="date" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                </div>
                <div v-if="plans?.length"><label class="block text-sm font-medium mb-2">Khusus Paket (kosong = semua paket)</label>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="p in plans" :key="p.id" class="flex items-center gap-1.5 text-sm cursor-pointer"><input type="checkbox" :value="p.id" v-model="form.plan_ids" class="rounded border-neutral-300" />{{ p.name }}</label>
                    </div>
                </div>
                <div class="flex items-center gap-2"><input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-neutral-300 dark:border-neutral-600" /><label for="is_active" class="text-sm">Aktif</label></div>
                <div class="flex gap-3 pt-3">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition disabled:opacity-50 cursor-pointer">{{ isEdit ? 'Simpan' : 'Buat Kupon' }}</button>
                    <Link href="/admin/coupons" class="px-6 py-2.5 border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 transition cursor-pointer">Batal</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>