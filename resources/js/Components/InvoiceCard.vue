<script setup lang="ts">
defineProps<{
    invoice: any;
    channels?: any[];
}>();
</script>

<template>
    <Link :href="'/client/invoices/' + invoice.id"
        class="block bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 sm:p-5 shadow-sm mb-3 hover:shadow-md hover:border-neutral-300 dark:hover:border-neutral-700 transition-all cursor-pointer"
    >
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

        <div v-if="invoice.due_date && invoice.status === 'pending'" class="text-xs text-amber-600">
            ⏳ Jatuh tempo: {{ invoice.due_date }}
        </div>

        <div v-if="invoice.status === 'paid' && invoice.payment_type" class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 mt-2">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>{{ invoice.payment_method || invoice.payment_type }}</span>
            <span class="text-emerald-500">·</span>
            <span>{{ invoice.paid_at }}</span>
        </div>
    </Link>
</template>

<style scoped>
</style>
