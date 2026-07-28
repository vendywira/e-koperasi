<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    invoice: any;
    subscription: any;
}>();
</script>

<template>
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-5 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Ringkasan Tagihan</h3>
            <span class="text-xs text-neutral-400">Status</span>
        </div>

        <div v-if="subscription" class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-neutral-500">Paket</span>
                <span class="font-medium">{{ subscription.plan || '-' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-neutral-500">Resort</span>
                <span class="font-medium">{{ subscription.max_resorts }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-neutral-500">Berakhir</span>
                <span class="font-medium">{{ subscription.ends_at || '-' }}</span>
            </div>
        </div>

        <div v-if="invoice" class="mt-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
            <div class="flex justify-between text-sm font-semibold">
                <span>Tagihan Terbaru</span>
                <span class="text-primary-600">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</span>
            </div>
            <p class="text-xs text-neutral-400 mt-1">{{ invoice.invoice_number }} — Jatuh tempo {{ invoice.due_date || '-' }}</p>
            <Link :href="'/client/invoices'" class="mt-2 inline-block text-xs text-primary-600 hover:underline">Lihat Invoice →</Link>
        </div>

        <div v-else-if="!subscription" class="text-sm text-neutral-400 text-center py-2">
            Belum ada tagihan.
        </div>
    </div>
</template>