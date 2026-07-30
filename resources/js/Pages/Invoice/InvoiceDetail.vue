<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps<{ invoice: any; paymentChannels: any[] }>();
const page = usePage();
const user = (page.props.auth as any)?.user;
const isAdmin = user?.role === 'admin';
const downloading = ref(false);
const showProof = ref(false);
const showConfirm = ref(false);
const isProcessing = ref(false);

function onDownload() {
    downloading.value = true;
    const base = isAdmin ? '/admin' : '/client';
    window.open(`${base}/invoices/${props.invoice.id}/download`, '_blank');
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

function doConfirmPaid() {
    isProcessing.value = true;
    router.post(`/admin/invoices/${props.invoice.id}/confirm-paid`, {}, {
        preserveScroll: true,
        onFinish: () => { showConfirm.value = false; isProcessing.value = false; },
    });
}

const backUrl = isAdmin ? '/admin/billing' : '/client/invoices';
const backLabel = isAdmin ? 'Kembali' : 'Kembali ke Invoice';
</script>

<template>
    <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
        <!-- Back -->
        <Link :href="backUrl" class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-700 mb-4 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            {{ backLabel }}
        </Link>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-mono text-emerald-600 dark:text-emerald-400">{{ invoice.invoice_number || invoice.id?.substring(0, 8) }}</p>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ invoice.tenant_name || invoice.name }}</h1>
                        <p class="text-sm text-neutral-500 font-mono">{{ invoice.domain }}.e-koperasi.com</p>
                        <p v-if="invoice.plan_name" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-1">{{ invoice.plan_name }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
                        :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30'">
                        {{ invoice.status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 text-sm">
                    <div><p class="text-xs text-neutral-400">Tanggal</p><p class="font-medium mt-0.5">{{ invoice.created_at }}</p></div>
                    <div v-if="invoice.due_date"><p class="text-xs text-neutral-400">Jatuh Tempo</p><p class="font-medium mt-0.5">{{ invoice.due_date }}</p></div>
                    <div v-if="invoice.paid_at"><p class="text-xs text-neutral-400">Dibayar</p><p class="font-medium mt-0.5 text-emerald-600">{{ invoice.paid_at }}</p></div>
                    <div v-if="isAdmin && invoice.client_name"><p class="text-xs text-neutral-400">Client</p><p class="font-medium mt-0.5">{{ invoice.client_name }}</p></div>
                </div>
                <div v-if="isAdmin && invoice.client_email" class="mt-2 text-xs text-neutral-400">Email: {{ invoice.client_email }}</div>
            </div>

            <!-- Items Table -->
            <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                <h2 class="text-sm font-semibold mb-3">Rincian Tagihan</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-800">
                                <th class="text-left pb-2 font-medium text-neutral-500 text-xs uppercase">Paket</th>
                                <th class="text-center pb-2 font-medium text-neutral-500 text-xs uppercase w-20">Resort</th>
                                <th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-28">Harga</th>
                                <th class="text-right pb-2 font-medium text-neutral-500 text-xs uppercase w-28">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            <tr>
                                <td class="py-3 pr-4 text-neutral-700 dark:text-neutral-300 font-medium">{{ invoice.plan_name || "Langganan" }}</td>
                                <td class="py-3 text-center">{{ invoice.resort_count }}</td>
                                <td class="py-3 text-right font-mono">Rp{{ Number(invoice.price_per_resort).toLocaleString('id-ID') }}</td>
                                <td class="py-3 text-right font-mono font-medium">Rp{{ Number(invoice.resort_count * invoice.price_per_resort).toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr v-if="invoice.months > 1" class="text-xs text-neutral-400">
                                <td colspan="3" class="py-1 text-right italic pr-4">Periode {{ invoice.months }} bulan</td>
                                <td class="py-1 text-right font-mono">×{{ invoice.months }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t-2 border-neutral-200 dark:border-neutral-800">
                            <tr v-if="Number(invoice.discount_amount) > 0">
                                <td colspan="3" class="pt-2 text-right text-sm text-red-600">Diskon</td>
                                <td class="pt-2 text-right font-mono text-sm text-red-600">-Rp{{ Number(invoice.discount_amount).toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr><td colspan="3" class="pt-2 text-right text-base font-bold text-neutral-900 dark:text-white">Total</td><td class="pt-2 text-right font-bold text-base text-primary-700 font-mono">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Client: Payment (pending only) -->
            <div v-if="!isAdmin && invoice.status === 'pending'" class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                <h2 class="text-sm font-semibold mb-3">Pembayaran</h2>
                <p class="text-xs text-neutral-400 mb-4">Lanjutkan pembayaran untuk invoice ini</p>
                <div class="flex gap-2">
                    <Link :href="`/client/invoices/${invoice.id}/payment`" class="px-5 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 cursor-pointer min-h-[44px] inline-flex items-center">
                        Bayar Sekarang
                    </Link>
                    <button @click="uploadProof" class="px-5 py-2.5 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer min-h-[44px]">Upload Bukti Transfer</button>
                </div>
            </div>

            <!-- Payment Transactions History -->
            <div v-if="invoice.transactions?.length" class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                <h2 class="text-sm font-semibold mb-3">Riwayat Pembayaran</h2>
                <div class="space-y-2">
                    <div v-for="txn in invoice.transactions" :key="txn.id"
                        class="flex items-center justify-between p-3 rounded-lg bg-neutral-50 dark:bg-neutral-800/30 border border-neutral-100 dark:border-neutral-800"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                :class="txn.status === 'success' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' :
                                    txn.status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' :
                                    'bg-red-100 dark:bg-red-900/30 text-red-600'"
                            >
                                {{ txn.status === 'success' ? '✓' : txn.status === 'pending' ? '⏳' : '✗' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-neutral-900 dark:text-white">
                                    {{ txn.channel_name || (txn.payment_type === 'manual' ? 'Transfer Manual' : 'Pembayaran') }}
                                </p>
                                <p class="text-[10px] text-neutral-400">
                                    {{ txn.status === 'success' ? txn.paid_at : txn.status === 'pending' ? 'Menunggu' : txn.expiry || 'Gagal' }}
                                </p>
                            </div>
                        </div>
                        <Link :href="'/client/payments/' + txn.id" class="text-xs text-emerald-600 hover:underline shrink-0 ml-3">Detail</Link>
                    </div>
                </div>
            </div>

            <!-- Proof -->
            <div v-if="invoice.payment_proof" class="px-6 sm:px-8 py-4 border-b border-neutral-200 dark:border-neutral-800">
                <p class="text-sm font-medium mb-2">Bukti Pembayaran</p>
                <button @click="showProof = true" class="text-sm text-primary-600 hover:underline cursor-pointer">Lihat bukti</button>
            </div>

            <!-- Admin: Confirm Paid -->
            <div v-if="isAdmin && invoice.status === 'pending'" class="px-6 sm:px-8 py-4 border-b border-neutral-200 dark:border-neutral-800">
                <button @click="showConfirm = true" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 cursor-pointer min-h-[44px]">Konfirmasi Dibayar</button>
            </div>

            <!-- Download -->
            <div class="px-6 sm:px-8 py-4 flex justify-end">
                <button @click="onDownload" :disabled="downloading"
                    class="px-5 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer flex items-center gap-2 min-h-[44px]">
                    <svg v-if="downloading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    {{ downloading ? 'Mengunduh...' : 'Download PDF' }}
                </button>
            </div>
        </div>

        <!-- Proof Modal -->
        <Teleport to="body">
            <div v-if="showProof" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showProof = false">
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-xl w-full overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b">
                        <h3 class="text-sm font-semibold">Bukti Pembayaran</h3>
                        <button @click="showProof = false" class="p-1 text-neutral-400 hover:text-neutral-600">&times;</button>
                    </div>
                    <div class="p-4">
                        <img v-if="invoice.payment_proof?.match(/\.(png|jpe?g|webp)$/i)" :src="invoice.payment_proof" class="w-full h-auto rounded-lg" alt="Bukti" />
                        <div v-else class="text-center py-8 text-neutral-400">
                            <p class="text-sm">File PDF — <a :href="invoice.payment_proof" target="_blank" class="text-primary-600 hover:underline">Buka di tab baru</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Confirm Dialog (admin only) -->
        <Teleport to="body">
            <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showConfirm = false">
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Konfirmasi Pembayaran</h3>
                    <p class="text-sm text-neutral-500 mt-2">Pastikan pembayaran dari <strong>{{ invoice.tenant_name || invoice.name }}</strong> sudah diterima. Invoice akan ditandai lunas dan tenant akan diaktifkan.</p>
                    <div class="flex gap-3 mt-6 justify-end">
                        <button @click="showConfirm = false" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm font-medium text-neutral-600 hover:bg-neutral-50 cursor-pointer">Batal</button>
                        <button @click="doConfirmPaid" :disabled="isProcessing" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 cursor-pointer">{{ isProcessing ? 'Memproses...' : 'Ya, Konfirmasi' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
