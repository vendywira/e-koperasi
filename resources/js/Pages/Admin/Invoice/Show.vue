<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps<{ invoice: any }>();
const downloading = ref(false);
const showConfirm = ref(false);
const showProof = ref(false);
const isProcessing = ref(false);

function onDownload() {
    downloading.value = true;
    window.open(`/admin/invoices/${props.invoice.id}/download`, '_blank');
    setTimeout(() => { downloading.value = false; }, 2000);
}

function doConfirmPaid() {
    isProcessing.value = true;
    router.post(`/admin/invoices/${props.invoice.id}/confirm-paid`, {}, {
        preserveScroll: true,
        onFinish: () => { showConfirm.value = false; isProcessing.value = false; },
    });
}
</script>

<template>
    <AdminLayout title="Detail Invoice">
        <Head :title="'Invoice - ' + (invoice.invoice_number || '')" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <Link href="/admin/billing" class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-700 mb-4 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg> Kembali
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
                        <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap" :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">{{ invoice.status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 text-sm">
                        <div><p class="text-xs text-neutral-400">Tanggal</p><p class="font-medium mt-0.5">{{ invoice.created_at }}</p></div>
                        <div v-if="invoice.due_date"><p class="text-xs text-neutral-400">Jatuh Tempo</p><p class="font-medium mt-0.5">{{ invoice.due_date }}</p></div>
                        <div v-if="invoice.paid_at"><p class="text-xs text-neutral-400">Dibayar</p><p class="font-medium mt-0.5 text-emerald-600">{{ invoice.paid_at }}</p></div>
                        <div v-if="invoice.confirmed_by"><p class="text-xs text-neutral-400">Konfirmasi</p><p class="font-medium mt-0.5">{{ invoice.confirmed_by }}</p></div>
                    </div>
                    <div class="mt-3 text-xs text-neutral-400">Client: {{ invoice.client_name }} ({{ invoice.client_email }})</div>
                </div>

                <!-- Items -->
                <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                    <h2 class="text-sm font-semibold mb-3">Rincian Tagihan</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="border-b border-neutral-200 dark:border-neutral-800"><th class="text-left pb-2 font-medium text-neutral-500 text-xs uppercase">Item</th><th class="text-center pb-2 font-medium text-neutral-500 text-xs uppercase w-16">Qty</th><th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-28">Harga</th><th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-24">Diskon</th><th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-28">Total</th></tr></thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                <tr v-if="invoice.items?.length" v-for="item in invoice.items" :key="item.id">
                                    <td class="py-3 pr-4 text-neutral-700 dark:text-neutral-300">{{ item.description }}</td>
                                    <td class="py-3 text-center">{{ item.quantity }}</td>
                                    <td class="py-3 text-right font-mono">Rp{{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                                    <td class="py-3 text-right font-mono text-red-500">-Rp{{ Number(item.discount_amount).toLocaleString('id-ID') }}</td>
                                    <td class="py-3 text-right font-mono font-medium">Rp{{ Number(item.total_amount).toLocaleString('id-ID') }}</td>
                                </tr>
                                <tr v-if="!invoice.items?.length"><td colspan="5" class="py-4 text-center text-xs text-neutral-400 italic">{{ invoice.resort_count }} resort × Rp{{ Number(invoice.price_per_resort).toLocaleString('id-ID') }} × {{ invoice.months }} bulan</td></tr>
                            </tbody>
                            <tfoot class="border-t-2 border-neutral-200 dark:border-neutral-800">
                                <tr v-if="invoice.subtotal"><td colspan="4" class="pt-3 text-right text-sm text-neutral-500">Subtotal</td><td class="pt-3 text-right font-mono text-sm">Rp{{ Number(invoice.subtotal).toLocaleString('id-ID') }}</td></tr>
                                <tr v-if="Number(invoice.discount_amount) > 0"><td colspan="4" class="pt-1 text-right text-sm text-red-600">Diskon</td><td class="pt-1 text-right font-mono text-sm text-red-600">-Rp{{ Number(invoice.discount_amount).toLocaleString('id-ID') }}</td></tr>
                                <tr><td colspan="4" class="pt-3 text-right text-base font-bold text-neutral-900 dark:text-white">Total</td><td class="pt-3 text-right font-bold text-base text-primary-700 font-mono">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div v-if="invoice.payment_proof" class="px-6 sm:px-8 py-4 border-b border-neutral-200 dark:border-neutral-800">
                    <p class="text-sm font-medium mb-2">Bukti Pembayaran</p>
                    <button @click="showProof = true" class="text-sm text-primary-600 hover:underline cursor-pointer">Lihat bukti →</button>
                </div>

                <div class="px-6 sm:px-8 py-4 flex gap-3">
                    <button v-if="invoice.status === 'pending'" @click="showConfirm = true" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 cursor-pointer min-h-[44px]">Konfirmasi Dibayar</button>
                    <button @click="onDownload" :disabled="downloading" class="px-5 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer min-h-[44px]">{{ downloading ? 'Mengunduh...' : 'Download PDF' }}</button>
                </div>
            </div>
        </div>

                <!-- Proof Modal -->
        <Teleport to="body">
            <div v-if="showProof" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showProof = false">
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-xl w-full overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Bukti Pembayaran</h3>
                        <button @click="showProof = false" class="p-1 text-neutral-400 hover:text-neutral-600 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-4">
                        <img v-if="invoice.payment_proof.match(/\.(png|jpe?g|webp)$/i)" :src="invoice.payment_proof" class="w-full h-auto rounded-lg" alt="Bukti pembayaran" />
                        <div v-else class="flex flex-col items-center py-8 text-neutral-400">
                            <svg class="w-12 h-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <p class="text-sm">File PDF — <a :href="invoice.payment_proof" target="_blank" class="text-primary-600 hover:underline">Buka di tab baru</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
<!-- Confirm Dialog -->
        <ConfirmDialog
            :show="showConfirm"
            title="Konfirmasi Pembayaran"
            :message="'Pastikan pembayaran dari ' + invoice.name + ' sudah diterima. Invoice akan ditandai lunas dan tenant akan diaktifkan.'"
            confirmText="Ya, Konfirmasi"
            cancelText="Batal"
            variant="primary"
            :loading="isProcessing"
            @confirm="doConfirmPaid"
            @cancel="showConfirm = false"
        />
    </AdminLayout>
</template>