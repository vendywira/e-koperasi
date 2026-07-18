<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps<{ clients: any[] }>();

const form = ref({
  name: '',
  domain: '',
  client_id: '',
  max_resorts: 5,
  price_per_resort: 100000,
  plan: 'monthly' as 'monthly' | 'yearly',
});

const errors = ref<Record<string, string>>({});
const searchRef = ref<HTMLElement | null>(null);
const loading = ref(false);
const domainTaken = ref(false);
const clientSearch = ref('');
const showClientDropdown = ref(false);
const selectedClientName = ref('');

const filteredClients = computed(() => {
  const q = clientSearch.value.toLowerCase();
  return props.clients.filter(c =>
    !q || c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
  );
});

function onClientInput(e: Event) {
  const val = (e.target as HTMLInputElement).value;
  clientSearch.value = val;
  showClientDropdown.value = true;
  // Jika user mengetik ulang, reset pilihan
  if (form.value.client_id) {
    form.value.client_id = '';
    selectedClientName.value = '';
  }
}

function selectClient(c: any) {
  form.value.client_id = c.id;
  selectedClientName.value = `${c.name} (${c.email})`;
  clientSearch.value = `${c.name} (${c.email})`;
  showClientDropdown.value = false;
}

function clearClient() {
  form.value.client_id = '';
  selectedClientName.value = '';
  clientSearch.value = '';
}

function onClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement;
  if (!target.closest('.client-search-wrap')) {
    showClientDropdown.value = false;
  }
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

const domainError = ref('');

function sanitizeDomain(e: Event) {
  const input = e.target as HTMLInputElement;
  const raw = input.value;

  // Filter: hapus karakter ilegal, biarkan a-z, 0-9, -
  const filtered = raw.replace(/[^a-z0-9-]/g, '');
  // Cegah strip beruntun, strip di awal/akhir
  const cleaned = filtered.replace(/^-+|-+(?=-)|-+$/g, '');

  if (raw !== filtered) {
    domainError.value = '⚠️ Hanya huruf kecil, angka, dan strip (-) yang diizinkan';
    setTimeout(() => { if (domainError.value) domainError.value = ''; }, 3000);
  } else {
    domainError.value = '';
  }

  form.value.domain = cleaned;
  showClientDropdown.value = false;
  checkDomain();
}

async function checkDomain() {
  if (!form.value.domain || form.value.domain.length < 3) {
    domainTaken.value = false;
    return;
  }
  try {
    const res = await fetch(`/admin/tenants/check-domain?domain=${encodeURIComponent(form.value.domain)}`);
    const data = await res.json();
    domainTaken.value = data.taken;
  } catch {
    domainTaken.value = false;
  }
}

function submit() {
  if (domainTaken.value) return;
  if (!form.value.client_id) {
    errors.value = { client_id: 'Pilih client terlebih dahulu.' };
    return;
  }
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
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Buat tenant KSU baru dan mapping ke client</p>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Client</label>
          <div class="relative client-search-wrap">
            <input :value="selectedClientName || clientSearch" @input="onClientInput" @focus="showClientDropdown = true"
              type="text"
              class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500"
              :placeholder="selectedClientName ? 'Klik untuk ganti client...' : 'Ketik nama atau email client...'" />
            <div v-if="showClientDropdown && filteredClients.length > 0"
              class="absolute z-10 mt-1 w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <button v-for="c in filteredClients" :key="c.id" type="button" @click="selectClient(c)"
                class="w-full text-left px-4 py-2.5 text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                :class="form.client_id === c.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700' : 'text-neutral-700 dark:text-neutral-300'">
                {{ c.name }}<span class="text-neutral-400 ml-2 text-xs">{{ c.email }}</span>
              </button>
            </div>
          </div>
          <p v-if="errors.client_id" class="text-xs text-red-500 mt-1">{{ errors.client_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nama Koperasi</label>
          <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Koperasi Sejahtera" />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Domain (subdomain)</label>
          <div class="flex items-center gap-2">
            <input :value="form.domain" @input="sanitizeDomain" type="text" required pattern="[a-z0-9]+(-[a-z0-9]+)*" title="Hanya huruf kecil, angka, dan tanda strip (-)" class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500 font-mono" :class="domainTaken ? 'border-red-500' : ''" placeholder="koperasi-a" />
            <span class="text-sm text-neutral-400 font-mono">.e-koperasi.com</span>
          </div>
          <p class="text-xs text-neutral-400 mt-1">Akses: https://<span class="font-mono">{{ form.domain || 'domain' }}</span>.e-koperasi.com</p>
          <p class="text-xs text-amber-600 mt-1.5 font-medium">⚠️ Domain tidak bisa diubah setelah tenant dibuat.</p>
          <p v-if="domainError" class="text-xs text-amber-600 mt-1">{{ domainError }}</p>
          <p v-if="domainTaken" class="text-xs text-red-500 mt-1">Domain sudah digunakan.</p>
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
          <button type="submit" :disabled="loading || domainTaken" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition-colors">
            {{ loading ? 'Membuat...' : 'Buat Tenant' }}
          </button>
          <a href="/admin/tenants" class="px-6 py-2.5 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white">Batal</a>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
