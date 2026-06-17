<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    payment: any;
    subscription: any;
}>();

const statusBadge = (status: string) => {
    const map: Record<string, { class: string; label: string }> = {
        paid: { class: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300', label: 'Lunas' },
        pending: { class: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300', label: 'Pending' },
        failed: { class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300', label: 'Gagal' },
    };
    return map[status] || { class: 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400', label: status };
};
</script>

<template>
    <ClientLayout title="Detail Pembayaran">
        <Head title="Detail Pembayaran - e-Koperasi Client" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-2xl space-y-6">
            <Link
                href="/client/payments"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Riwayat
            </Link>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Invoice Header -->
                <div class="p-6 sm:p-8 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Invoice</p>
                            <h2 class="text-xl font-bold text-neutral-900 dark:text-white mt-1 font-mono">{{ payment.receipt_number }}</h2>
                        </div>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="statusBadge(payment.status).class"
                        >
                            {{ statusBadge(payment.status).label }}
                        </span>
                    </div>
                </div>

                <!-- Detail -->
                <div class="p-6 sm:p-8 space-y-4">
                    <div class="grid grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Tanggal</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ payment.paid_at ?? payment.created_at ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Metode</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">
                                {{ payment.payment_method === 'manual_transfer' ? 'Transfer Manual' : payment.payment_method }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Paket</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1 capitalize">{{ subscription?.plan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Periode</p>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white mt-1">{{ payment.paid_at ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div v-if="payment.notes" class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1">Catatan</p>
                        <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ payment.notes }}</p>
                    </div>

                    <!-- Total -->
                    <div class="pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Total Pembayaran</span>
                            <span class="text-xl font-bold text-neutral-900 dark:text-white">Rp{{ Number(payment.amount).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
