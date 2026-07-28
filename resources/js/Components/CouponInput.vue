<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{ planId?: string }>();
const code = ref('');
const result = ref<{ valid: boolean; discount_type?: string; discount_value?: number; message?: string } | null>(null);
const validating = ref(false);

function apply() {
    if (!code.value.trim()) return;
    validating.value = true;
    result.value = null;
    router.post('/client/coupon/validate', { code: code.value, plan_id: props.planId || '' }, {
        preserveScroll: true,
        onSuccess: (page: any) => {
            result.value = page.props?.couponResult || { valid: false, message: 'Kode tidak valid' };
            validating.value = false;
        },
        onError: (errors: any) => {
            result.value = { valid: false, message: errors.coupon || 'Kode tidak ditemukan' };
            validating.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-2">
        <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Kode Promo</label>
        <div class="flex gap-2">
            <input v-model="code" type="text" placeholder="Masukkan kode promo"
                class="flex-1 px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700"
                @keyup.enter="apply" />
            <button @click="apply" :disabled="validating || !code.trim()"
                class="px-4 py-2 border border-primary-600 text-primary-600 rounded-lg text-sm font-medium hover:bg-primary-50 dark:hover:bg-primary-900/20 transition disabled:opacity-50 cursor-pointer">
                {{ validating ? '...' : 'Pakai' }}
            </button>
        </div>
        <p v-if="result?.valid" class="text-xs text-emerald-600">✅ Diskon {{ result.discount_type === 'percentage' ? result.discount_value + '%' : 'Rp' + Number(result.discount_value).toLocaleString('id-ID') }}</p>
        <p v-else-if="result" class="text-xs text-red-500">{{ result.message }}</p>
    </div>
</template>