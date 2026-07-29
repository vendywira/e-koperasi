<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{ invoice: any; paymentChannels: any[] }>();
const downloading = ref(false);
const selectedChannel = ref('');
const paying = ref(false);
const showProof = ref(false);

function onDownload() {
    downloading.value = true;
    window.open(`/client/invoices/${props.invoice.id}/download`, '_blank');
    setTimeout(() => { downloading.value = false; }, 2000);
}

function uploadProof() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/png,image/jpeg,image/jpg,application/pdf';
    input.onchange = () => {
        if (!input.files?.length) return;
        const form = new FormData();
        form.append('payment_proof', input.files[0]);
        router.post(`/client/invoices/${props.invoice.id}/upload-proof`, form, { preserveScroll: true });
    };
    input.click();
}

function payNow() {
    if (!selectedChannel.value || paying.value) return;
    paying.value = true;
    router.post('/client/payment/duitku', {
        invoice_id: props.invoice.id,
        payment_method: selectedChannel.value,
    }, {
        preserveScroll: true,
        onFinish: () => { paying.value = false; },
    });
}
</script>

<template>
    <ClientLayout title="Detail Invoice">
        <Head :title="'Invoice - ' + (invoice.invoice_number || '')" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <!-- Back -->
            <Link href="/client/invoices" class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali ke Invoice
            </Link>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-mono text-neutral-400">{{ invoice.invoice_number || invoice.id?.substring(0, 8) }}</p>
                            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ invoice.name }}</h1>
                            <p class="text-sm text-neutral-500 font-mono">{{ invoice.domain }}.e-koperasi.com</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30'">
                            {{ invoice.status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 text-sm">
                        <div><p class="text-xs text-neutral-400">Tanggal</p><p class="font-medium mt-0.5">{{ invoice.created_at }}</p></div>
                        <div v-if="invoice.due_date"><p class="text-xs text-neutral-400">Jatuh Tempo</p><p class="font-medium mt-0.5">{{ invoice.due_date }}</p></div>
                        <div v-if="invoice.paid_at"><p class="text-xs text-neutral-400">Dibayar</p><p class="font-medium mt-0.5 text-emerald-600">{{ invoice.paid_at }}</p></div>
                        <div><p class="text-xs text-neutral-400">{{ invoice.months }} bulan</p><p class="font-medium mt-0.5">{{ invoice.resort_count }} resort</p></div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                    <h2 class="text-sm font-semibold mb-3">Rincian Tagihan</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-neutral-800">
                                    <th class="text-left pb-2 font-medium text-neutral-500 text-xs uppercase">Item</th>
                                    <th class="text-center pb-2 font-medium text-neutral-500 text-xs uppercase w-16">Qty</th>
                                    <th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-28">Harga</th>
                                    <th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-24">Diskon</th>
                                    <th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-28">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                <tr v-if="invoice.items?.length" v-for="item in invoice.items" :key="item.id">
                                    <td class="py-3 pr-4 text-neutral-700 dark:text-neutral-300">{{ item.description }}</td>
                                    <td class="py-3 text-center">{{ item.quantity }}</td>
                                    <td class="py-3 text-right font-mono">Rp{{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                                    <td class="py-3 text-right font-mono text-red-500">-Rp{{ Number(item.discount_amount).toLocaleString('id-ID') }}</td>
                                    <td class="py-3 text-right font-mono font-medium">Rp{{ Number(item.total_amount).toLocaleString('id-ID') }}</td>
                                </tr>
                                <tr v-if="!invoice.items?.length">
                                    <td colspan="5" class="py-4 text-center text-xs text-neutral-400 italic">
                                        Langganan: {{ invoice.resort_count }} resort × Rp{{ Number(invoice.price_per_resort).toLocaleString('id-ID') }} × {{ invoice.months }} bulan
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t-2 border-neutral-200 dark:border-neutral-800">
                                <tr v-if="invoice.subtotal"><td colspan="4" class="pt-3 text-right text-sm text-neutral-500">Subtotal</td><td class="pt-3 text-right font-mono text-sm">Rp{{ Number(invoice.subtotal).toLocaleString('id-ID') }}</td></tr>
                                <tr v-if="Number(invoice.discount_amount) > 0"><td colspan="4" class="pt-1 text-right text-sm text-red-600">Diskon</td><td class="pt-1 text-right font-mono text-sm text-red-600">-Rp{{ Number(invoice.discount_amount).toLocaleString('id-ID') }}</td></tr>
                                <tr><td colspan="4" class="pt-2 text-right text-base font-bold text-neutral-900 dark:text-white">Total</td><td class="pt-2 text-right font-bold text-base text-primary-700 font-mono">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Payment Section (pending only) -->
                <div v-if="invoice.status === 'pending'" class="p-6 sm:p-8 space-y-4">
                    <h2 class="text-sm font-semibold">Pembayaran</h2>
                    <div v-if="paymentChannels.length" class="space-y-2">
                        <p class="text-xs text-neutral-400">Pilih metode bayar:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button v-for="ch in paymentChannels" :key="ch.id" @click="selectedChannel = ch.code"
                                class="px-3 py-2.5 border-2 rounded-lg text-xs text-left transition cursor-pointer min-h-[44px]"
                                :class="selectedChannel === ch.code ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 hover:border-primary-300'">
                                {{ ch.name }}
                            </button>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button @click="payNow" :disabled="!selectedChannel || paying" class="px-5 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer min-h-[44px]">{{ paying ? '...' : 'Bayar Sekarang' }}</button>
                            <button @click="uploadProof" class="px-5 py-2.5 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer min-h-[44px]">Upload Bukti Transfer</button>
                        </div>
                    </div>
                </div>

                <!-- Proof -->
                <div v-if="invoice.payment_proof" class="px-6 sm:px-8 py-4 border-b border-neutral-200 dark:border-neutral-800">
                    <p class="text-sm font-medium mb-2">Bukti Pembayaran</p>
                    <button @click="showProof = true" class="text-sm text-primary-600 hover:underline cursor-pointer">Lihat bukti</button>
                </div>

                <!-- Download -->
                <div class="px-6 sm:px-8 pb-6 sm:pb-8 flex justify-end">
                    <button @click="onDownload" :disabled="downloading"
                        class="px-5 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer flex items-center gap-2">
                        <svg v-if="downloading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        {{ downloading ? 'Mengunduh...' : 'Download PDF' }}
                    </button>
                </div>
            </div>
        </div>
            <Teleport to="body">
            <div v-if="showProof" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showProof = false">
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-xl w-full overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b">
                        <h3 class="text-sm font-semibold">Bukti Pembayaran</h3>
                        <button @click="showProof = false" class="p-1 text-neutral-400 hover:text-neutral-600">&times;</button>
                    </div>
                    <div class="p-4">
                        <img v-if="invoice.payment_proof.match(/\.(png|jpe?g|webp)$/i)" :src="invoice.payment_proof" class="w-full h-auto rounded-lg" alt="Bukti" />
                        <div v-else class="text-center py-8 text-neutral-400">
                            <p class="text-sm">File PDF — <a :href="invoice.payment_proof" target="_blank" class="text-primary-600 hover:underline">Buka di tab baru</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </ClientLayout>
</template>