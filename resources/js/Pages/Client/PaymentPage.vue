<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    mockMode?: boolean;
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
const simulating = ref(false);
const error = ref('');
const copied = ref(false);
const openAccordions = ref<string[]>(['va']);

const typeMeta: Record<string, { label: string; icon: string; color: string; badge: string }> = {
    va: {
        label: 'Virtual Account',
        icon: 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z',
        color: 'bg-blue-600',
        badge: 'VA',
    },
    qris: {
        label: 'QRIS',
        icon: 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z',
        color: 'bg-purple-600',
        badge: 'QR',
    },
    ewallet: {
        label: 'E-Wallet',
        icon: 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3',
        color: 'bg-amber-600',
        badge: 'EW',
    },
    retail: {
        label: 'Retail',
        icon: 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z',
        color: 'bg-neutral-600',
        badge: 'RT',
    },
};

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

const totalGroups = computed(() => Object.keys(nonEmptyGroups.value).length);

function toggleAccordion(type: string) {
    const idx = openAccordions.value.indexOf(type);
    if (idx >= 0) {
        openAccordions.value.splice(idx, 1);
    } else {
        openAccordions.value.push(type);
    }
}

function findSelectedChannel() {
    for (const ch of Object.values(props.groupedChannels).flat()) {
        if (ch.code === selectedCode.value) return ch;
    }
    return null;
}

async function copyToClipboard(text: string) {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        // fallback
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    }
}

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

const countdownParts = computed(() => {
    const totalSec = Math.floor(remaining.value / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    return {
        hours: String(h).padStart(2, '0'),
        minutes: String(m).padStart(2, '0'),
        seconds: String(s).padStart(2, '0'),
        totalSec,
    };
});

const countdownUrgent = computed(() => remaining.value < 300000);

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
            // silent retry
        }
    }, 15_000);
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

async function simulatePayment(targetStatus: 'success' | 'failed') {
    if (!transactionId.value) return;
    simulating.value = true;
    try {
        await axios.post('/client/payment/simulate-callback', {
            transaction_id: transactionId.value,
            status: targetStatus,
        });
        if (targetStatus === 'success') {
            step.value = 'success';
        } else {
            step.value = 'select';
            selectedCode.value = '';
        }
        stopPolling();
    } catch {
        // silent
    } finally {
        simulating.value = false;
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

// checkmark animation for success
const showCheckmark = ref(false);
</script>

<template>
    <ClientLayout :title="'Pembayaran — ' + invoice.invoice_number">
        <Head :title="'Pembayaran — ' + invoice.invoice_number" />

        <div class="min-h-[calc(100vh-8rem)] bg-neutral-50 dark:bg-neutral-950">
            <!-- Top Bar -->
            <div class="sticky top-0 z-10 bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
                <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
                    <Link
                        :href="step === 'select' ? '/client/invoices/' + invoice.id : undefined"
                        :class="step === 'select' ? '' : 'pointer-events-none opacity-50'"
                        class="flex items-center gap-2 text-sm text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        Kembali
                    </Link>
                    <h1 class="text-sm font-semibold text-neutral-900 dark:text-white">Pembayaran</h1>
                    <div class="w-20" />
                </div>
            </div>

            <div class="max-w-3xl mx-auto px-4 py-6 sm:py-8">
                <!-- Step Indicator -->
                <div class="mb-6 sm:mb-8">
                    <div class="flex items-center gap-2">
                        <div v-for="(s, i) in ['Pilih Metode', 'Bayar', 'Selesai']" :key="i" class="flex items-center gap-2 flex-1">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 shrink-0"
                                :class="(step === 'select' && i === 0) || (step === 'payment' && i === 1) || (step === 'success' && i === 2)
                                    ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 dark:ring-emerald-900/30'
                                    : (step === 'payment' && i < 1) || (step === 'success' && i < 2) || (step === 'expired' && i < 1)
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-400 dark:text-neutral-500'"
                            >
                                <svg v-if="(step === 'payment' && i < 1) || (step === 'success' && i < 2) || (step === 'expired' && i < 1)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                            <span
                                class="text-xs font-medium hidden sm:block"
                                :class="(step === 'select' && i === 0) || (step === 'payment' && i === 1) || (step === 'success' && i === 2)
                                    ? 'text-emerald-700 dark:text-emerald-400'
                                    : 'text-neutral-400 dark:text-neutral-500'"
                            >
                                {{ s }}
                            </span>
                            <div v-if="i < 2" class="flex-1 h-px bg-neutral-200 dark:bg-neutral-700 ml-1" />
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading && !paymentData && step !== 'payment'"
                    class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-12 text-center"
                >
                    <svg class="w-10 h-10 animate-spin mx-auto text-emerald-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <p class="mt-4 text-sm text-neutral-500 font-medium">Menyiapkan pembayaran...</p>
                </div>

                <!-- ============================================================ -->
                <!-- STEP 1: SELECT METHOD                                         -->
                <!-- ============================================================ -->
                <div v-if="step === 'select' && !(loading && !paymentData)" class="space-y-5">
                    <!-- Invoice Summary Card -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm px-5 py-4">
                        <div class="flex items-center">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0 ring-1 ring-emerald-100 dark:ring-emerald-800/50">
                                    <svg class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[13px] font-semibold text-neutral-900 dark:text-white leading-tight truncate max-w-[200px] sm:max-w-none">{{ invoice.name }}</p>
                                    <p class="text-[11px] text-neutral-400 font-mono mt-0.5">{{ invoice.invoice_number }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0 ml-4 pl-4 border-l border-neutral-100 dark:border-neutral-800">
                                <p class="text-[11px] text-neutral-400 font-medium uppercase tracking-wide">Tagihan</p>
                                <p class="text-base font-bold text-neutral-900 dark:text-white font-mono mt-0.5">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden divide-y divide-neutral-100 dark:divide-neutral-800">
                        <div class="px-5 py-4">
                            <h2 class="text-[15px] font-bold text-neutral-900 dark:text-white">Pilih Metode Pembayaran</h2>
                            <p class="text-[13px] text-neutral-400 mt-0.5">Biaya layanan ditambahkan ke total</p>
                        </div>

                        <div v-for="(channels, type) in nonEmptyGroups" :key="type">
                            <!-- Group Header -->
                            <button
                                @click="toggleAccordion(type)"
                                class="w-full flex items-center justify-between px-5 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors cursor-pointer group"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-[26px] h-[26px] rounded flex items-center justify-center text-[10px] font-bold text-white uppercase shrink-0"
                                        :class="typeMeta[type]?.color || 'bg-neutral-500'"
                                    >
                                        {{ typeMeta[type]?.badge || type[0] }}
                                    </span>
                                    <span class="text-[13px] font-semibold text-neutral-900 dark:text-white">{{ typeMeta[type]?.label || type }}</span>
                                    <span class="text-[11px] text-neutral-400">{{ channels.length }} metode</span>
                                </div>
                                <svg
                                    class="w-4 h-4 text-neutral-300 dark:text-neutral-600 transition-transform duration-200 group-hover:text-neutral-400"
                                    :class="openAccordions.includes(type) ? 'rotate-180' : ''"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Channel List -->
                            <Transition
                                enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="max-h-0 opacity-0"
                                enter-to-class="max-h-[600px] opacity-100"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-from-class="max-h-[600px] opacity-100"
                                leave-to-class="max-h-0 opacity-0"
                            >
                                <div v-if="openAccordions.includes(type)" class="bg-neutral-50/80 dark:bg-neutral-800/20 border-t border-neutral-100 dark:border-neutral-800/50">
                                    <div class="pb-2 px-2 space-y-px">
                                        <button
                                            v-for="ch in channels"
                                            :key="ch.code"
                                            @click="selectedCode = ch.code"
                                            class="w-full flex items-center gap-3 px-3 py-3 rounded-lg text-left transition-all cursor-pointer group/ch"
                                            :class="selectedCode === ch.code
                                                ? 'bg-white dark:bg-neutral-800 shadow-[0_1px_3px_0_rgba(0,0,0,0.04)] ring-1 ring-emerald-200 dark:ring-emerald-700'
                                                : 'hover:bg-white dark:hover:bg-neutral-800/50 hover:shadow-[0_1px_2px_0_rgba(0,0,0,0.02)]'"
                                        >
                                            <!-- Radio -->
                                            <div
                                                class="w-[18px] h-[18px] rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                :class="selectedCode === ch.code ? 'border-emerald-500' : 'border-neutral-300 dark:border-neutral-600 group-hover/ch:border-neutral-400 dark:group-hover/ch:border-neutral-500'"
                                            >
                                                <div v-if="selectedCode === ch.code" class="w-[10px] h-[10px] rounded-full bg-emerald-500" />
                                            </div>

                                            <!-- Icon -->
                                            <div v-if="ch.icon_url" class="w-[34px] h-[34px] rounded-lg bg-white dark:bg-neutral-700/50 flex items-center justify-center overflow-hidden shrink-0 ring-1 ring-neutral-100 dark:ring-neutral-700 p-1">
                                                <img :src="ch.icon_url" :alt="ch.name" class="w-full h-full object-contain" />
                                            </div>
                                            <div v-else class="w-[34px] h-[34px] rounded-lg bg-neutral-100 dark:bg-neutral-700/50 flex items-center justify-center shrink-0 ring-1 ring-neutral-100 dark:ring-neutral-700">
                                                <span class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400">{{ ch.name.slice(0, 2).toUpperCase() }}</span>
                                            </div>

                                            <!-- Info -->
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[13px] font-medium text-neutral-900 dark:text-white leading-tight">{{ ch.name }}</p>
                                                <p class="text-[11px] text-neutral-400 mt-0.5">Fee Rp{{ Number(ch.calculated_fee).toLocaleString('id-ID') }}</p>
                                            </div>

                                            <!-- Total -->
                                            <div class="text-right shrink-0 ml-2">
                                                <p class="text-[13px] font-semibold text-neutral-900 dark:text-white font-mono leading-tight">Rp{{ Number(ch.total_amount).toLocaleString('id-ID') }}</p>
                                                <p class="text-[10px] text-neutral-400 mt-0.5">Total</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Error -->
                        <div v-if="error" class="px-5 pb-4">
                            <div class="flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-3.5 py-2.5">
                                <svg class="w-[14px] h-[14px] text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                <p class="text-[12px] text-red-700 dark:text-red-300 leading-snug">{{ error }}</p>
                            </div>
                        </div>

                        <!-- Sticky CTA -->
                        <div class="px-5 py-3.5 bg-neutral-50 dark:bg-neutral-800/30 border-t border-neutral-100 dark:border-neutral-800">
                            <button
                                @click="initiatePayment"
                                :disabled="!selectedCode || loading"
                                class="w-full h-[44px] bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg text-[13px] font-bold transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-2"
                            >
                                <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                                {{ loading ? 'Memproses...' : !selectedCode ? 'Pilih Metode Pembayaran' : `Bayar Rp${Number(findSelectedChannel()?.total_amount ?? invoice.total_amount).toLocaleString('id-ID')}` }}
                            </button>
                        </div>
                    </div>

                    <!-- Security Note -->
                    <div class="flex items-center gap-2.5 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-4 py-3">
                        <svg class="w-[14px] h-[14px] text-neutral-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        <p class="text-[11px] text-neutral-400 leading-normal">Pembayaran diproses melalui <span class="font-medium text-neutral-500">Duitku</span> — gateway pembayaran terpercaya dan tersertifikasi.</p>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- STEP 2: PAYMENT INSTRUCTIONS                                  -->
                <!-- ============================================================ -->
                <div v-if="step === 'payment' && paymentData" class="space-y-5">
                    <!-- Invoice Summary -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm px-5 py-3.5">
                        <div class="flex items-center">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-[30px] h-[30px] rounded bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[12px] font-medium text-neutral-900 dark:text-white leading-tight">{{ invoice.invoice_number }}</p>
                                    <p class="text-[11px] text-neutral-400">{{ paymentData.channel_name }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                <p class="text-[10px] text-neutral-400 font-medium uppercase tracking-wide">Total</p>
                                <p class="text-base font-bold text-emerald-700 dark:text-emerald-400 font-mono leading-tight">Rp{{ Number(paymentData.total_amount).toLocaleString('id-ID') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Detail Card -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                        <!-- Status + Countdown -->
                        <div class="border-b border-neutral-100 dark:border-neutral-800">
                            <div class="flex items-center gap-2.5 px-5 py-3 bg-amber-50/70 dark:bg-amber-900/10">
                                <svg class="w-[14px] h-[14px] text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-[12px] font-medium text-amber-700 dark:text-amber-300">Menunggu Pembayaran</p>
                                <span class="text-[11px] text-amber-500 ml-auto font-mono font-medium tabular-nums">{{ countdownParts.hours }}:{{ countdownParts.minutes }}:{{ countdownParts.seconds }}</span>
                            </div>
                            <div class="h-1 bg-amber-100 dark:bg-amber-900/30">
                                <div
                                    class="h-full transition-[width] duration-1000 ease-linear rounded-r"
                                    :class="countdownUrgent ? 'bg-red-500' : 'bg-amber-500'"
                                    :style="{ width: (remaining / (24 * 60 * 60 * 1000)) * 100 + '%' }"
                                />
                            </div>
                        </div>

                        <!-- Virtual Account -->
                        <div v-if="paymentData.va_number" class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800">
                            <p class="text-[11px] text-neutral-400 font-medium uppercase tracking-wide mb-1.5">Nomor Virtual Account</p>
                            <p class="text-[22px] font-bold font-mono tracking-[0.12em] text-neutral-900 dark:text-white select-all leading-tight">{{ paymentData.va_number }}</p>
                            <p class="text-[12px] text-neutral-400 mt-1">{{ paymentData.channel_name }}</p>
                            <button
                                @click="copyToClipboard(paymentData.va_number)"
                                class="mt-2.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-medium transition-all active:scale-[0.97] cursor-pointer"
                            >
                                <svg v-if="copied" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                <svg v-else class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" /></svg>
                                {{ copied ? 'Tersalin' : 'Salin' }}
                            </button>
                        </div>

                        <!-- QRIS -->
                        <div v-if="paymentData.qr_url" class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 text-center">
                            <p class="text-[11px] text-neutral-400 font-medium uppercase tracking-wide mb-3">Scan QR Code</p>
                            <div class="w-44 h-44 mx-auto bg-white dark:bg-neutral-700 rounded-lg p-2.5 shadow-sm border border-neutral-100 dark:border-neutral-700">
                                <img :src="paymentData.qr_url" alt="QR Code" class="w-full h-full object-contain" />
                            </div>
                            <p class="text-[12px] text-neutral-500 mt-2.5">{{ paymentData.channel_name }}</p>
                        </div>

                        <!-- Redirect URL -->
                        <div v-if="paymentData.redirect_url && !paymentData.va_number && !paymentData.qr_url" class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 text-center">
                            <p class="text-[11px] text-neutral-400 font-medium uppercase tracking-wide mb-2.5">Pembayaran via {{ paymentData.channel_name }}</p>
                            <a :href="paymentData.redirect_url" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-[12px] font-semibold hover:bg-emerald-700 transition-all active:scale-[0.97]">
                                Lanjutkan Pembayaran
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                            </a>
                        </div>

                        <!-- Instructions -->
                        <div class="px-5 py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div class="flex items-start gap-2">
                                <svg class="w-[14px] h-[14px] text-neutral-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                                <p class="text-[12px] text-neutral-500 leading-relaxed">{{ paymentData.instructions }}</p>
                            </div>
                        </div>

                        <!-- Amount Breakdown -->
                        <div class="px-5 py-3 border-b border-neutral-100 dark:border-neutral-800">
                            <div class="flex justify-between text-[12px] py-1">
                                <span class="text-neutral-400">Jumlah Tagihan</span>
                                <span class="font-medium text-neutral-900 dark:text-white font-mono tabular-nums">Rp{{ Number(paymentData.base_amount).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-[12px] py-1">
                                <span class="text-neutral-400">Biaya Layanan ({{ paymentData.channel_name }})</span>
                                <span class="font-medium text-neutral-900 dark:text-white font-mono tabular-nums">Rp{{ Number(paymentData.fee_amount).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-[13px] font-semibold py-1 mt-1.5 pt-2.5 border-t border-neutral-100 dark:border-neutral-800">
                                <span class="text-neutral-900 dark:text-white">Total</span>
                                <span class="text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">Rp{{ Number(paymentData.total_amount).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="px-5 py-3 flex gap-2">
                            <button @click="changeMethod" :disabled="loading" class="flex-1 h-[38px] border border-neutral-200 dark:border-neutral-700 rounded-lg text-[12px] font-medium text-neutral-500 hover:bg-neutral-50 dark:hover:bg-neutral-800 disabled:opacity-50 transition-all cursor-pointer">Ganti Metode</button>
                            <button @click="checkStatus" class="flex-1 h-[38px] bg-neutral-100 dark:bg-neutral-800 rounded-lg text-[12px] font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-all cursor-pointer">Cek Status</button>
                        </div>

                        <!-- Mock Simulation -->
                        <div v-if="mockMode && step === 'payment'" class="border-t border-dashed border-neutral-200 dark:border-neutral-800">
                            <div class="px-5 py-3">
                                <p class="text-[10px] text-neutral-400 uppercase tracking-wide font-medium mb-2">Simulasi (Mock Mode)</p>
                                <div class="flex gap-2">
                                    <button @click="simulatePayment('success')" :disabled="simulating" class="flex-1 h-[34px] bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded text-[11px] font-semibold hover:bg-emerald-200 dark:hover:bg-emerald-900/40 transition-all disabled:opacity-50 cursor-pointer">✓ Simulasi Sukses</button>
                                    <button @click="simulatePayment('failed')" :disabled="simulating" class="flex-1 h-[34px] bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded text-[11px] font-semibold hover:bg-red-200 dark:hover:bg-red-900/40 transition-all disabled:opacity-50 cursor-pointer">✗ Simulasi Gagal</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUCCESS -->
                <div v-if="step === 'success'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 sm:p-10 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center ring-8 ring-emerald-50 dark:ring-emerald-900/10">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                    <h2 class="mt-5 text-xl font-bold text-neutral-900 dark:text-white">Pembayaran Berhasil</h2>
                    <p class="mt-1.5 text-[13px] text-neutral-400 max-w-xs mx-auto leading-relaxed">Pembayaran invoice <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ invoice.invoice_number }}</span> telah diterima dan sedang diproses.</p>
                    <div class="mt-7 flex flex-col sm:flex-row gap-2.5 justify-center">
                        <Link :href="'/client/invoices/' + invoice.id" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-[13px] font-bold hover:bg-emerald-700 transition-all active:scale-[0.97]">Lihat Invoice</Link>
                        <Link href="/client/dashboard" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 rounded-lg text-[13px] font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-all">Kembali ke Dashboard</Link>
                    </div>
                </div>

                <!-- EXPIRED -->
                <div v-if="step === 'expired'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 sm:p-10 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center ring-8 ring-red-50 dark:ring-red-900/10">
                        <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <h2 class="mt-5 text-xl font-bold text-neutral-900 dark:text-white">Pembayaran Kadaluarsa</h2>
                    <p class="mt-1.5 text-[13px] text-neutral-400 max-w-xs mx-auto leading-relaxed">Waktu pembayaran telah habis. Silakan lakukan pembayaran ulang dengan metode pembayaran yang tersedia.</p>
                    <div class="mt-7 flex flex-col sm:flex-row gap-2.5 justify-center">
                        <button @click="step = 'select'; selectedCode = '';" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-[13px] font-bold hover:bg-emerald-700 transition-all active:scale-[0.97] cursor-pointer">Bayar Ulang</button>
                        <Link href="/client/dashboard" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 rounded-lg text-[13px] font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-all">Kembali ke Dashboard</Link>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
