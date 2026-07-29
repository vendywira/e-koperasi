<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    invoice: any;
    channels?: any[];
}>();

const showModal = ref(false);

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
const downloading = ref<string | null>(null);
function onDownload() { downloading.value = "loading"; setTimeout(() => { downloading.value = null; }, 3000); }
</script>

<template>
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 sm:p-5 shadow-sm mb-3">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-xs font-mono text-neutral-400">{{ invoice.invoice_number || invoice.id?.substring(0, 8) }}</p>
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
            <Link :href="`/client/invoices/${invoice.id}`"
                class="block w-full px-4 py-3 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition text-center cursor-pointer">
                Lihat Detail & Bayar
            </Link>
        </div>
        <div v-if="invoice.status === 'paid'" class="border-t border-neutral-100 dark:border-neutral-800 pt-3 text-xs text-emerald-600 flex items-center justify-between">
            <span>✅ Dibayar {{ invoice.paid_at }}</span>
            <a :href="`/client/invoices/${invoice.id}/download`" target="_blank" class="underline hover:text-emerald-700 cursor-pointer" @click="onDownload">Download PDF</a>
        </div>
    </div>
</template>