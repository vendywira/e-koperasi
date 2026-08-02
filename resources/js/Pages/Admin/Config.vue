<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { update as configUpdate } from '@/routes/admin/config';

const props = defineProps<{
    config: Record<string, any>;
    defaults: Record<string, any>;
}>();

const form = ref<Record<string, any>>({ ...props.config });
const saving = ref(false);
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);

const ENUMS: Record<string, { value: string; label: string }[]> = {
    provision_mode: [
        { value: 'auto', label: 'Auto (Otomatis)' },
        { value: 'manual', label: 'Manual (Butuh Approval Admin)' },
    ],
};

const labels: Record<string, string> = {
    provision_mode: 'Mode Provisioning',
};

const fields = computed(() => Object.keys(props.defaults).map((key) => ({
    key,
    label: labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
    options: ENUMS[key] || null,
    value: form.value[key],
})));

function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.value = { message, type };
    setTimeout(() => (toast.value = null), 3000);
}

function save() {
    saving.value = true;
    router.put(configUpdate().url, form.value, {
        preserveScroll: true, preserveState: true,
        onFinish: () => { saving.value = false; },
        onSuccess: () => showToast('Config berhasil disimpan.'),
        onError: () => showToast('Gagal menyimpan config.', 'error'),
    });
}
</script>

<template>
    <AdminLayout title="Config">
        <div class="p-6 max-w-2xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Config</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Parameter operational — flag &amp; pengaturan sistem.</p>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 space-y-4">
                <div v-for="field in fields" :key="field.key" class="flex items-center justify-between gap-4">
                    <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ field.label }}</label>
                    <select
                        v-if="field.options"
                        v-model="form[field.key]"
                        class="px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white"
                    >
                        <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <input
                        v-else
                        v-model="form[field.key]"
                        class="px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white"
                    />
                </div>

                <div v-if="toast" class="text-sm" :class="toast.type === 'success' ? 'text-emerald-600' : 'text-red-600'">{{ toast.message }}</div>

                <div class="pt-4 flex justify-end">
                    <button
                        @click="save"
                        :disabled="saving"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
