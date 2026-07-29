<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    invoice: {
        id: string;
        invoice_number: string;
        name: string;
        total_amount: number;
        status: string;
        domain: string;
    };
    groupedChannels: Record<string, {
        id: string;
        code: string;
        name: string;
        icon_url: string | null;
        type: string;
        fee_fixed: number;
        fee_percent: number;
        calculated_fee: number;
        total_amount: number;
    }[]>;
    existingTransaction: {
        id: string;
        status: string;
        expiry: string;
        channel_code: string;
    } | null;
}>();

type Step = 'select' | 'payment' | 'success' | 'expired';
const step = ref<Step>(
    props.existingTransaction && props.existingTransaction.status === 'pending' ? 'payment' : 'select'
);
const selectedCode = ref(props.existingTransaction?.channel_code ?? '');
const transactionId = ref(props.existingTransaction?.id ?? '');
const loading = ref(false);
const error = ref('');

const paymentData = ref<{
    va_number: string | null;
    qr_url: string | null;
    redirect_url: string | null;
    reference: string | null;
    expiry: string;
    base_amount: number;
    fee_amount: number;
    total_amount: number;
    channel_name: string;
    channel_type: string;
    instructions: string;
} | null>(null);

const groupLabels: Record<string, string> = {
    va: 'Virtual Account',
    qris: 'QRIS',
    ewallet: 'E-Wallet',
    retail: 'Retail',
};

const nonEmptyGroups = computed(() => {
    const entries = Object.entries(props.groupedChannels).filter(([, channels]) => channels.length > 0);
    return Object.fromEntries(entries);
});

// — Countdown —
const remaining = ref(0);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

function startCountdown(expiryIso: string) {
    if (countdownInterval) clearInterval(countdownInterval);
    updateRemaining(expiryIso);
    countdownInterval = setInterval(() => updateRemaining(expiryIso), 1000);
}

function updateRemaining(expiryIso: string) {
    const expiry = new Date(expiryIso).getTime();
    const now = Date.now();
    remaining.value = expiry > now ? expiry - now : 0;
    if (remaining.value <= 0) {
        step.value = 'expired';
        stopPolling();
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }
}

const countdownDisplay = computed(() => {
    const totalSec = Math.floor(remaining.value / 1000);
    const m = Math.floor(totalSec / 60);
    const s = totalSec % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
    stopPolling();
});

// — Polling —
let pollingInterval: ReturnType<typeof setInterval> | null = null;

function startPolling() {
    stopPolling();
    pollingInterval = setInterval(async () => {
        if (!transactionId.value) return;
        try {
            const res = await axios.get(`/client/payment/${transactionId.value}/status`);
            const status = res.data.status;
            if (status === 'success') {
                step.value = 'success';
                stopPolling();
            } else if (status === 'expired') {
                step.value = 'expired';
                stopPolling();
            } else if (status === 'pending' && res.data.expiry) {
                updateRemaining(res.data.expiry);
            }
        } catch {
            // silent retry next interval
        }
    }, 30_000);
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

async function initiatePayment() {
    if (!selectedCode.value) return;
    loading.value = true;
    error.value = '';
    try {
        const res = await axios.post('/client/payment/initiate', {
            invoice_id: props.invoice.id,
            payment_method: selectedCode.value,
        });
        paymentData.value = res.data;
        transactionId.value = res.data.transaction_id;
        step.value = 'payment';
        startCountdown(res.data.expiry);
        startPolling();
    } catch (e: any) {
        error.value = e.response?.data?.error ?? 'Gagal membuat pembayaran. Silakan coba lagi.';
    } finally {
        loading.value = false;
    }
}

async function changeMethod() {
    loading.value = true;
    error.value = '';
    try {
        step.value = 'select';
        selectedCode.value = '';
        stopPolling();
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    } finally {
        loading.value = false;
    }
}

async function checkStatus() {
    if (!transactionId.value) return;
    try {
        const res = await axios.get(`/client/payment/${transactionId.value}/status`);
        const status = res.data.status;
        if (status === 'success') {
            step.value = 'success';
            stopPolling();
        } else if (status === 'expired') {
            step.value = 'expired';
            stopPolling();
        }
    } catch {
        // silent
    }
}

onMounted(async () => {
    if (props.existingTransaction && props.existingTransaction.status === 'pending') {
        loading.value = true;
        try {
            const res = await axios.post('/client/payment/initiate', {
                invoice_id: props.invoice.id,
                payment_method: props.existingTransaction.channel_code,
            });
            paymentData.value = res.data;
            transactionId.value = res.data.transaction_id;
            startCountdown(res.data.expiry);
            startPolling();
        } catch {
            step.value = 'select';
        } finally {
            loading.value = false;
        }
    }
});
</script>

<template>
    <ClientLayout :title="'Pembayaran — ' + invoice.invoice_number">
        <Head :title="'Pembayaran — ' + invoice.invoice_number" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-2xl mx-auto space-y-6">
            <!-- Back -->
            <Link
                :href="'/client/invoices/' + invoice.id"
                class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Invoice
            </Link>

            <!-- Invoice Header -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-neutral-400">{{ invoice.name }}</p>
                            <h1 class="text-lg font-bold text-neutral-900 dark:text-white mt-0.5">{{ invoice.invoice_number }}</h1>
                        </div>
                        <span class="text-right">
                            <p class="text-xs text-neutral-400">Total Tagihan</p>
                            <p class="text-lg font-bold text-neutral-900 dark:text-white font-mono">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</p>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="loading && !paymentData" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center">
                <svg class="w-8 h-8 animate-spin mx-auto text-emerald-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <p class="mt-3 text-sm text-neutral-500">Memuat halaman pembayaran...</p>
            </div>

            <!-- STEP 1: Select Payment Method -->
            <div v-if="step === 'select' && !loading" class="space-y-4">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Pilih Metode Pembayaran</h2>
                        <p class="text-xs text-neutral-400 mb-4">Biaya layanan akan ditambahkan ke total pembayaran</p>

                        <div v-for="(channels, type) in nonEmptyGroups" :key="type" class="mb-5 last:mb-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                                {{ groupLabels[type as keyof typeof groupLabels] || type }}
                            </p>
                            <div class="space-y-2">
                                <button
                                    v-for="ch in channels"
                                    :key="ch.code"
                                    @click="selectedCode = ch.code"
                                    class="w-full flex items-center gap-3 p-3 sm:p-4 rounded-lg border-2 text-left transition-all cursor-pointer"
                                    :class="selectedCode === ch.code
                                        ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/10'
                                        : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600'"
                                >
                                    <div v-if="ch.icon_url" class="w-10 h-10 rounded-lg bg-white dark:bg-neutral-800 flex items-center justify-center overflow-hidden flex-shrink-0 border border-neutral-100 dark:border-neutral-700">
                                        <img :src="ch.icon_url" :alt="ch.name" class="w-8 h-8 object-contain" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ ch.name }}</p>
                                        <p class="text-xs text-neutral-400">
                                            Biaya layanan: Rp{{ Number(ch.calculated_fee).toLocaleString('id-ID') }}
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold text-neutral-900 dark:text-white font-mono">Rp{{ Number(ch.total_amount).toLocaleString('id-ID') }}</p>
                                        <p class="text-[10px] text-neutral-400">Total Bayar</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-if="error" class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
                        </div>

                        <!-- Submit -->
                        <button
                            @click="initiatePayment"
                            :disabled="!selectedCode || loading"
                            class="mt-5 w-full py-3 px-5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer min-h-[48px] flex items-center justify-center gap-2"
                        >
                            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ loading ? 'Memproses...' : selectedCode ? 'Bayar Sekarang' : 'Pilih Metode Bayar' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Payment Instructions -->
            <div v-if="step === 'payment' && paymentData" class="space-y-4">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 space-y-5">
                        <!-- Status Banner -->
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Menunggu Pembayaran</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">Selesaikan pembayaran sebelum waktu habis</p>
                            </div>
                        </div>

                        <!-- Countdown -->
                        <div class="text-center py-3">
                            <p class="text-xs text-neutral-400 mb-1">Sisa Waktu</p>
                            <p class="text-3xl font-bold font-mono" :class="remaining < 300000 ? 'text-red-600 dark:text-red-400' : 'text-neutral-900 dark:text-white'">
                                {{ countdownDisplay }}
                            </p>
                        </div>

                        <!-- Amount Breakdown -->
                        <div class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500">Jumlah Tagihan</span>
                                <span class="font-medium text-neutral-900 dark:text-white font-mono">Rp{{ Number(paymentData.base_amount).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500">Biaya Layanan ({{ paymentData.channel_name }})</span>
                                <span class="font-medium text-neutral-900 dark:text-white font-mono">Rp{{ Number(paymentData.fee_amount).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-semibold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <span class="text-neutral-900 dark:text-white">Total Bayar</span>
                                <span class="text-emerald-700 dark:text-emerald-400 font-mono">Rp{{ Number(paymentData.total_amount).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <!-- VA Number -->
                        <div v-if="paymentData.va_number" class="text-center">
                            <p class="text-xs text-neutral-400 mb-1">Nomor Virtual Account</p>
                            <p class="text-2xl sm:text-3xl font-bold font-mono tracking-wider text-neutral-900 dark:text-white select-all bg-neutral-50 dark:bg-neutral-800 py-3 px-4 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-600">
                                {{ paymentData.va_number }}
                            </p>
                            <p class="text-xs text-neutral-400 mt-1">{{ paymentData.channel_name }}</p>
                        </div>

                        <!-- QRIS -->
                        <div v-if="paymentData.qr_url" class="text-center">
                            <img :src="paymentData.qr_url" alt="QR Code" class="w-48 h-48 mx-auto rounded-lg" />
                            <p class="text-xs text-neutral-400 mt-1">{{ paymentData.channel_name }}</p>
                        </div>

                        <!-- Redirect URL fallback (e-wallet) -->
                        <div v-if="paymentData.redirect_url && !paymentData.va_number && !paymentData.qr_url" class="text-center">
                            <a
                                :href="paymentData.redirect_url"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors"
                            >
                                Buka Halaman Pembayaran
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </div>

                        <!-- Instructions -->
                        <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                                <p class="text-sm text-blue-800 dark:text-blue-200">{{ paymentData.instructions }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button
                                @click="changeMethod"
                                :disabled="loading"
                                class="flex-1 py-2.5 border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800 disabled:opacity-50 transition-colors cursor-pointer"
                            >
                                Ganti Metode
                            </button>
                            <button
                                @click="checkStatus"
                                class="flex-1 py-2.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer"
                            >
                                Cek Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success -->
            <div v-if="step === 'success'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Pembayaran Berhasil</h2>
                <p class="text-sm text-neutral-500">Pembayaran invoice {{ invoice.invoice_number }} telah diterima.</p>
                <Link
                    :href="'/client/invoices/' + invoice.id"
                    class="inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors"
                >
                    Lihat Invoice
                </Link>
            </div>

            <!-- Expired -->
            <div v-if="step === 'expired'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Pembayaran Kadaluarsa</h2>
                <p class="text-sm text-neutral-500">Waktu pembayaran telah habis. Silakan pilih metode pembayaran lagi.</p>
                <button
                    @click="step = 'select'; selectedCode = '';"
                    class="inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors cursor-pointer"
                >
                    Bayar Ulang
                </button>
            </div>
        </div>
    </ClientLayout>
</template>
