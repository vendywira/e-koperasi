<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = ref({
  name: '',
  domain: '',
  max_resorts: 5,
  price_per_resort: 100000,
  plan: 'monthly' as 'monthly' | 'yearly',
});

const errors = ref<Record<string, string>>({});
const loading = ref(false);

function submit() {
  loading.value = true;
  errors.value = {};
  router.post('/admin/tenants', form.value, {
    onError: (errs) => { errors.value = errs; loading.value = false; },
    onSuccess: () => { loading.value = false; },
  });
}
</script>

<template>
  <AdminLayout>
    <Head title="Tambah Tenant" />
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Tambah Tenant Baru</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Buat tenant KSU baru dengan database sendiri</p>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nama Koperasi</label>
          <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Koperasi Sejahtera" />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Domain (subdomain)</label>
          <div class="flex items-center gap-2">
            <input v-model="form.domain" type="text" required class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500 font-mono" placeholder="koperasi-a" />
            <span class="text-sm text-neutral-400 font-mono">.ksu.app</span>
          </div>
          <p class="text-xs text-neutral-400 mt-1">Akses: https://<span class="font-mono">{{ form.domain || 'domain' }}</span>.ksu.app</p>
          <p v-if="errors.domain" class="text-xs text-red-500 mt-1">{{ errors.domain }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Maks Resort</label>
            <input v-model.number="form.max_resorts" type="number" min="1" required class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Harga/Resort (Rp)</label>
            <input v-model.number="form.price_per_resort" type="number" min="0" required class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Paket</label>
          <select v-model="form.plan" class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500">
            <option value="monthly">Bulanan</option>
            <option value="yearly">Tahunan</option>
          </select>
        </div>

        <div class="flex items-center gap-3 pt-3 border-t border-neutral-200 dark:border-neutral-800">
          <button type="submit" :disabled="loading" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition-colors">
            {{ loading ? 'Membuat...' : 'Buat Tenant' }}
          </button>
          <a href="/admin/tenants" class="px-6 py-2.5 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Batal</a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
