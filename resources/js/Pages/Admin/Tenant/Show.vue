<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ tenant: any }>();

const editing = ref(false);
const extendDays = ref(30);
const showExtend = ref(false);
const dialog = ref<{ show: boolean; title: string; message: string; onConfirm: () => void }>({ show: false, title: '', message: '', onConfirm: () => {} });

const form = ref({
  name: props.tenant.name,
  domain: props.tenant.domain,
  db_name: props.tenant.db_name,
  status: props.tenant.status,
  max_resorts: props.tenant.subscription?.max_resorts ?? 5,
  price_per_resort: props.tenant.subscription?.price_per_resort ?? 100000,
  plan: props.tenant.subscription?.plan ?? 'monthly',
  subscription_status: props.tenant.subscription?.status ?? 'active',
});

const errors = ref<Record<string, string>>({});
const saving = ref(false);

function save() {
  saving.value = true;
  errors.value = {};
  router.put(`/admin/tenants/${props.tenant.id}`, form.value, {
    onError: (errs) => { errors.value = errs; saving.value = false; },
    onSuccess: () => { editing.value = false; saving.value = false; },
  });
}

function confirmAction(title: string, message: string, fn: () => void) {
  dialog.value = { show: true, title, message, onConfirm: fn };
}

function doExtend() {
  confirmAction(
    'Perpanjang Subscription',
    `Perpanjang ${extendDays.value} hari untuk "${props.tenant.name}"?`,
    () => {
      dialog.value.show = false;
      router.post(`/admin/tenants/${props.tenant.id}/extend`, { extend_days: extendDays.value }, { preserveScroll: true });
    }
  );
}

function toggleSuspend() {
  const action = props.tenant.status === 'suspended' ? 'Aktifkan' : 'Suspend';
  const message = props.tenant.status === 'suspended'
    ? `Aktifkan kembali tenant "${props.tenant.name}"?`
    : `Suspend tenant "${props.tenant.name}"? Semua akses akan diblokir.`;
  confirmAction(action, message, () => {
    dialog.value.show = false;
    router.post(`/admin/tenants/${props.tenant.id}/toggle-suspend`, {}, { preserveScroll: true });
  });
}

const statusBadge = (s: string) => {
  const map: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    suspended: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    expired: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    trialing: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
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
    <Head :title="tenant.name" />
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <Link href="/admin/tenants" class="text-sm text-primary-600 hover:underline">&larr; Kembali</Link>
      </div>

      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ tenant.name }}</h1>
          <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            {{ tenant.domain }}.e-koperasi.com
            <span class="inline-flex ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(tenant.status)">{{ tenant.status }}</span>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="toggleSuspend" class="px-3 py-2 text-sm font-medium rounded-lg border"
            :class="tenant.status === 'suspended'
              ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50'
              : 'border-red-300 text-red-700 hover:bg-red-50'">
            {{ tenant.status === 'suspended' ? 'Aktifkan' : 'Suspend' }}
          </button>
          <button v-if="!editing" @click="editing = true" class="px-4 py-2 text-sm font-medium text-primary-600 border border-primary-300 rounded-lg hover:bg-primary-50">
            Edit
          </button>
        </div>
      </div>

      <!-- Info Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5">
          <p class="text-xs text-neutral-500 uppercase tracking-wider mb-1">Domain</p>
          <p class="font-mono text-sm font-medium">{{ tenant.domain }}.e-koperasi.com</p>
          <p class="text-xs text-neutral-400 mt-0.5">DB: {{ tenant.db_name }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5">
          <p class="text-xs text-neutral-500 uppercase tracking-wider mb-1">Resort Usage</p>
          <p class="text-2xl font-bold">{{ tenant.resort_count ?? '?' }} <span class="text-sm font-normal text-neutral-400">/ {{ tenant.subscription?.max_resorts ?? '-' }}</span></p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5">
          <p class="text-xs text-neutral-500 uppercase tracking-wider mb-1">Langganan</p>
          <p class="text-sm font-medium capitalize">{{ tenant.subscription?.plan }} · {{ tenant.subscription?.status }}</p>
          <p class="text-xs text-neutral-400 mt-0.5">Mulai {{ tenant.subscription?.started_at ? new Date(tenant.subscription.started_at).toLocaleDateString('id-ID') : '-' }}</p>
        </div>
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5">
          <p class="text-xs text-neutral-500 uppercase tracking-wider mb-1">Sisa Hari</p>
          <p class="text-2xl font-bold" :class="daysLeft(tenant.subscription?.ends_at) !== null && daysLeft(tenant.subscription?.ends_at)! <= 7 ? 'text-red-600' : ''">
            {{ daysLeft(tenant.subscription?.ends_at) ?? '&infin;' }}
          </p>
          <p class="text-xs text-neutral-400 mt-0.5">Berakhir {{ tenant.subscription?.ends_at ? new Date(tenant.subscription.ends_at).toLocaleDateString('id-ID') : '-' }}</p>
        </div>
      </div>

      <!-- Extend / Perpanjang -->
      <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 mb-6">
        <h2 class="text-lg font-semibold mb-3">Perpanjang Subscription</h2>
        <div class="flex items-center gap-3">
          <input v-model.number="extendDays" type="number" min="1" max="365" class="w-24 px-3 py-2 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm text-center" />
          <span class="text-sm text-neutral-500">hari</span>
          <button @click="doExtend" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium">
            Perpanjang
          </button>
        </div>
        <p class="text-xs text-neutral-400 mt-2">Perpanjangan mengakumulasi dari tanggal berakhir saat ini.</p>
      </div>

      <!-- Edit Form -->
      <div v-if="editing" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Edit Tenant</h2>
        <form @submit.prevent="save" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Nama</label>
            <input v-model="form.name" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Domain (subdomain)</label>
            <input v-model="form.domain" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm font-mono focus:ring-2 focus:ring-primary-500" />
            <p class="text-xs text-neutral-400 mt-1">Akses: https://<span class="font-mono">{{ form.domain }}</span>.e-koperasi.com</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Database Name</label>
            <input v-model="form.db_name" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm font-mono focus:ring-2 focus:ring-primary-500" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Status Tenant</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="trialing">Trialing</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Status Subscription</label>
              <select v-model="form.subscription_status" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Max Resorts</label>
              <input v-model.number="form.max_resorts" type="number" min="1" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Harga/Resort</label>
              <input v-model.number="form.price_per_resort" type="number" min="0" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Plan</label>
              <select v-model="form.plan" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm">
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
          </div>
          <p v-if="errors.max_resorts" class="text-xs text-red-500">{{ errors.max_resorts }}</p>
          <div class="flex gap-3 pt-3">
            <button type="submit" :disabled="saving" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button type="button" @click="editing = false" class="px-6 py-2.5 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Batal</button>
          </div>
        </form>
      </div>

      <!-- Subscription Info -->
      <div v-if="tenant.subscription" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6">
        <h2 class="text-lg font-semibold mb-4">Detail Subscription</h2>
        <dl class="grid grid-cols-2 gap-4 text-sm">
          <div><dt class="text-neutral-500">Plan</dt><dd class="font-medium capitalize">{{ tenant.subscription.plan }}</dd></div>
          <div><dt class="text-neutral-500">Status</dt><dd class="font-medium">{{ tenant.subscription.status }}</dd></div>
          <div><dt class="text-neutral-500">Max Resorts</dt><dd class="font-medium">{{ tenant.subscription.max_resorts }}</dd></div>
          <div><dt class="text-neutral-500">Harga/Resort</dt><dd class="font-medium">Rp {{ Number(tenant.subscription.price_per_resort).toLocaleString('id-ID') }}</dd></div>
          <div><dt class="text-neutral-500">Mulai</dt><dd class="font-medium">{{ tenant.subscription.started_at ? new Date(tenant.subscription.started_at).toLocaleDateString('id-ID') : '-' }}</dd></div>
          <div><dt class="text-neutral-500">Berakhir</dt><dd class="font-medium">{{ tenant.subscription.ends_at ? new Date(tenant.subscription.ends_at).toLocaleDateString('id-ID') : '-' }}</dd></div>
        </dl>
      </div>

      <!-- Confirmation Dialog -->
      <Teleport to="body">
        <div v-if="dialog.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="dialog.show = false">
          <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6">
            <div class="flex items-start gap-3 mb-5">
              <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0 text-amber-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ dialog.title }}</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ dialog.message }}</p>
              </div>
            </div>
            <div class="flex gap-3 justify-end">
              <button @click="dialog.show = false" class="px-4 py-2 text-sm font-medium rounded-lg border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">Batal</button>
              <button @click="dialog.onConfirm" class="px-4 py-2 text-sm font-medium rounded-lg text-white transition-colors"
                :class="dialog.title === 'Suspend' ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'">Lanjutkan</button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
