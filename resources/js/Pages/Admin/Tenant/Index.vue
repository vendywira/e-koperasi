<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
  tenants: any;
  filters: { search: string | null };
  stats: {
    total: number;
    active: number;
    suspended: number;
    trialing: number;
    expiring_soon: number;
    expired: number;
    total_revenue: number;
  };
}>();

const search = ref(props.filters.search ?? '');

watch(search, (val) => {
  router.get('/admin/tenants', { search: val || undefined }, {
    preserveState: true,
    replace: true,
  });
});

function destroy(tenant: any) {
  if (!confirm(`Hapus tenant "${tenant.name}"? DB & storage akan ikut terhapus.`)) return;
  router.delete(`/admin/tenants/${tenant.id}`);
}

function toggleSuspend(tenant: any) {
  const action = tenant.status === 'suspended' ? 'aktifkan' : 'suspend';
  if (!confirm(`${action} tenant "${tenant.name}"?`)) return;
  router.post(`/admin/tenants/${tenant.id}/toggle-suspend`, {}, { preserveScroll: true });
}

function extend(tenant: any) {
  const days = prompt(`Perpanjang "${tenant.name}" — berapa hari?`, '30');
  if (!days || isNaN(Number(days)) || Number(days) < 1) return;
  router.post(`/admin/tenants/${tenant.id}/extend`, { extend_days: Number(days) }, { preserveScroll: true });
}

const statusBadge = (s: string) => {
  const map: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    suspended: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    trialing: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    expired: 'bg-neutral-200 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  };
  return map[s] ?? '';
};

function daysLeft(endsAt: string | null): number | null {
  if (!endsAt) return null;
  const diff = new Date(endsAt).getTime() - Date.now();
  return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}
</script>

<template>
  <AdminLayout>
    <Head title="KSU Tenants" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">KSU Tenants</h1>
          <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Kelola tenant aplikasi KSU &amp; subscription</p>
        </div>
        <Link href="/admin/tenants/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
          + Tambah Tenant
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
          <p class="text-xs text-neutral-500">Total</p>
          <p class="text-2xl font-bold mt-0.5">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-emerald-200 dark:border-emerald-800 p-4">
          <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Aktif</p>
          <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-0.5">{{ stats.active }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-amber-200 dark:border-amber-800 p-4">
          <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Trial</p>
          <p class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-0.5">{{ stats.trialing }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-orange-200 dark:border-orange-800 p-4">
          <p class="text-xs text-orange-600 dark:text-orange-400 font-medium">Akan Expired</p>
          <p class="text-2xl font-bold text-orange-700 dark:text-orange-300 mt-0.5">{{ stats.expiring_soon }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-red-200 dark:border-red-800 p-4">
          <p class="text-xs text-red-600 dark:text-red-400 font-medium">Suspend</p>
          <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-0.5">{{ stats.suspended }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
          <p class="text-xs text-neutral-500">Expired</p>
          <p class="text-2xl font-bold mt-0.5">{{ stats.expired }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 col-span-2 lg:col-span-1">
          <p class="text-xs text-neutral-500">Revenue</p>
          <p class="text-xl font-bold mt-0.5 text-primary-700">Rp {{ stats.total_revenue.toLocaleString('id-ID') }}</p>
        </div>
      </div>

      <div class="mb-4">
        <input v-model="search" type="text" placeholder="Cari tenant..." class="w-full sm:w-80 px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" />
      </div>

      <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/50">
              <th class="text-left px-4 py-3 font-medium text-neutral-500">Tenant</th>
              <th class="text-left px-4 py-3 font-medium text-neutral-500">Domain</th>
              <th class="text-center px-4 py-3 font-medium text-neutral-500">Status</th>
              <th class="text-center px-4 py-3 font-medium text-neutral-500">Resort</th>
              <th class="text-center px-4 py-3 font-medium text-neutral-500">Plan</th>
              <th class="text-center px-4 py-3 font-medium text-neutral-500">Sisa</th>
              <th class="text-center px-4 py-3 font-medium text-neutral-500">Pembayaran</th>
              <th class="text-right px-4 py-3 font-medium text-neutral-500">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
            <tr v-for="t in tenants.data" :key="t.id"
              class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors"
              :class="t.status === 'suspended' ? 'opacity-60' : ''">
              <td class="px-4 py-3">
                <Link :href="`/admin/tenants/${t.id}`" class="font-medium text-primary-600 hover:underline">{{ t.name }}</Link>
              </td>
              <td class="px-4 py-3 text-neutral-600 font-mono text-xs">{{ t.domain }}</td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(t.status)">{{ t.status }}</span>
              </td>
              <td class="px-4 py-3 text-center text-neutral-600">{{ t.resort_count ?? '?' }} / {{ t.subscription?.max_resorts ?? '-' }}</td>
              <td class="px-4 py-3 text-center text-xs capitalize">{{ t.subscription?.plan }}<br><span class="text-neutral-400">Rp{{ Number(t.subscription?.price_per_resort ?? 0).toLocaleString('id-ID') }}/r</span></td>
              <td class="px-4 py-3 text-center">
                <span v-if="t.subscription?.ends_at"
                  :class="daysLeft(t.subscription.ends_at) !== null && daysLeft(t.subscription.ends_at)! <= 7 ? 'text-red-600 font-bold' : daysLeft(t.subscription.ends_at)! <= 30 ? 'text-amber-600' : 'text-neutral-500'"
                  class="text-xs">
                  {{ daysLeft(t.subscription.ends_at) }} hr
                </span>
                <span v-else class="text-xs text-neutral-400">&infin;</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span v-if="t.subscription?.last_payment_status" class="text-xs"
                  :class="t.subscription.last_payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600'">
                  {{ t.subscription.last_payment_status }}
                </span>
                <span v-else class="text-xs text-neutral-400">-</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button @click="extend(t)" class="px-2 py-1 text-xs rounded border border-primary-300 text-primary-700 hover:bg-primary-50 dark:border-primary-700 dark:text-primary-400">Perpanjang</button>
                  <button @click="toggleSuspend(t)" class="px-2 py-1 text-xs rounded border"
                    :class="t.status === 'suspended'
                      ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50'
                      : 'border-red-300 text-red-700 hover:bg-red-50'">
                    {{ t.status === 'suspended' ? 'Aktifkan' : 'Suspend' }}
                  </button>
                  <Link :href="`/admin/tenants/${t.id}`" class="px-2 py-1 text-xs rounded border border-neutral-300 text-neutral-600 hover:bg-neutral-50">Detail</Link>
                </div>
              </td>
            </tr>
            <tr v-if="tenants.data.length === 0">
              <td colspan="8" class="px-4 py-12 text-center text-neutral-400">Belum ada tenant.</td>
            </tr>
          </tbody>
        </table>
        <div v-if="tenants.links?.length > 3" class="flex items-center justify-between px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
          <div class="text-xs text-neutral-500">{{ tenants.from }}–{{ tenants.to }} dari {{ tenants.total }}</div>
          <div class="flex gap-1">
            <Link v-for="link in tenants.links" :key="link.label" :href="link.url || '#'" class="px-3 py-1.5 text-xs rounded-lg transition-colors"
              :class="link.active ? 'bg-primary-600 text-white' : 'text-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-800'"
              v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
