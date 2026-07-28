<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    invoice: any;
    channels?: any[];
}>();

const paying = ref(false);
const selectedChannel = ref('');

function uploadProof(id: string) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/png,image/jpeg,image/jpg,application/pdf';
    input.onchange = () => {
        if (!input.files?.length) return;
        const form = new FormData();
        form.append('payment_proof', input.files[0]);
        router.post(`/client/invoices/${id}/upload-proof`, form, { preserveScroll: true });
    };
    input.click();
}

function payNow(invoiceId: string) {
    if (!selectedChannel.value) return;
    paying.value = true;
    router.post('/client/payment/duitku', {
        invoice_id: invoiceId,
        payment_method: selectedChannel.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { paying.value = false; },
        onError: () => { paying.value = false; },
    });
}
</script>

<template>
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 sm:p-5 shadow-sm mb-3">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-xs font-mono text-neutral-400">{{ invoice.invoice_number }}</p>
                <h3 class="font-semibold text-neutral-900 dark:text-white">{{ invoice.name }}</h3>
                <p class="text-xs text-neutral-500 font-mono">{{ invoice.domain }}.e-koperasi.com</p>
            </div>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap"
                :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                    invoice.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                    'bg-neutral-100 text-neutral-500'">
                {{ invoice.status === 'paid' ? 'Lunas' : invoice.status === 'pending' ? 'Belum Dibayar' : invoice.status }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 text-xs mb-3">
            <div><p class="text-neutral-400">{{ invoice.resort_count }} × Rp{{ Number(invoice.price_per_resort).toLocaleString('id-ID') }}</p></div>
            <div><p class="text-neutral-400">{{ invoice.months }} bulan</p></div>
            <div class="text-right"><p class="font-bold text-primary-700">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</p></div>
        </div>

        <div v-if="invoice.due_date" class="text-xs text-amber-600 mb-2">
            ⏳ Jatuh tempo: {{ invoice.due_date }}
        </div>

        <div v-if="invoice.status === 'pending'" class="border-t border-neutral-100 dark:border-neutral-800 pt-3 space-y-3">
            <div v-if="channels && channels.length > 0" class="space-y-2">
                <p class="text-xs font-medium text-neutral-600 dark:text-neutral-400">Bayar Online:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                    <button v-for="ch in channels" :key="ch.id" @click="selectedChannel = ch.code"
                        class="px-2 py-2 text-[10px] border rounded-lg transition text-center cursor-pointer min-h-[44px]"
                        :class="selectedChannel === ch.code ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700' : 'border-neutral-200 dark:border-neutral-700 hover:border-primary-300'">
                        {{ ch.name }}
                    </button>
                </div>
                <button @click="payNow(invoice.id)" :disabled="!selectedChannel || paying"
                    class="w-full px-4 py-3 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition disabled:opacity-50 cursor-pointer min-h-[44px]">
                    {{ paying ? 'Memproses...' : 'Bayar Sekarang' }}
                </button>
            </div>

            <div class="space-y-2">
                <p class="text-xs text-neutral-400">Atau transfer manual:</p>
                <button @click="uploadProof(invoice.id)"
                    class="w-full px-4 py-3 border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 transition cursor-pointer min-h-[44px]">
                    {{ invoice.payment_proof ? 'Ganti Bukti Transfer' : 'Upload Bukti Transfer' }}
                </button>
                <a v-if="invoice.payment_proof" :href="invoice.payment_proof" target="_blank" class="block text-xs text-primary-600 hover:underline text-center">Lihat bukti</a>
            </div>
        </div>

        <div v-if="invoice.status === 'paid'" class="border-t border-neutral-100 dark:border-neutral-800 pt-3 text-xs text-emerald-600 flex items-center justify-between">
            <span>✅ Dibayar {{ invoice.paid_at }}</span>
            <Link :href="`/client/invoices/${invoice.id}/download`" class="underline hover:text-emerald-700">Download PDF</Link>
        </div>
    </div>
</template>