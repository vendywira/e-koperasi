<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{ invoice: any }>();
const downloading = ref(false);

function onDownload() {
    downloading.value = true;
    const a = document.createElement('a');
    a.href = `/client/invoices/${props.invoice.id}/download`;
    a.target = '_blank';
    a.click();
    setTimeout(() => { downloading.value = false; }, 2000);
}
</script>

<template>
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-neutral-200 dark:border-neutral-800 pb-4">
            <div>
                <p class="text-xs font-mono text-neutral-400">{{ invoice.invoice_number || invoice.id?.substring(0, 8) }}</p>
                <h3 class="text-xl font-bold text-neutral-900 dark:text-white mt-1">{{ invoice.name }}</h3>
                <p class="text-sm text-neutral-500 font-mono">{{ invoice.domain }}.e-koperasi.com</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
                :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                    invoice.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                    'bg-neutral-100 text-neutral-500'">
                {{ invoice.status === 'paid' ? 'LUNAS' : invoice.status === 'pending' ? 'BELUM DIBAYAR' : invoice.status.toUpperCase() }}
            </span>
        </div>

        <!-- Invoice Info -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Tanggal</p>
                <p class="font-medium mt-0.5">{{ invoice.created_at }}</p>
            </div>
            <div v-if="invoice.due_date">
                <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Jatuh Tempo</p>
                <p class="font-medium mt-0.5">{{ invoice.due_date }}</p>
            </div>
            <div v-if="invoice.paid_at">
                <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Dibayar</p>
                <p class="font-medium mt-0.5 text-emerald-600">{{ invoice.paid_at }}</p>
            </div>
        </div>

        <!-- Items table -->
        <div class="overflow-x-auto border border-neutral-200 dark:border-neutral-800 rounded-lg">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-medium text-neutral-500 text-xs uppercase">Item</th>
                        <th class="text-center px-4 py-2.5 font-medium text-neutral-500 text-xs uppercase w-16">Qty</th>
                        <th class="text-right px-4 py-2.5 font-medium text-neutral-500 text-xs uppercase w-28">Harga</th>
                        <th class="text-right px-4 py-2.5 font-medium text-neutral-500 text-xs uppercase w-24">Diskon</th>
                        <th class="text-right px-4 py-2.5 font-medium text-neutral-500 text-xs uppercase w-28">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                    <tr v-if="invoice.items?.length" v-for="item in invoice.items" :key="item.id || item.description">
                        <td class="px-4 py-3 text-neutral-700 dark:text-neutral-300">{{ item.description }}</td>
                        <td class="px-4 py-3 text-center">{{ item.quantity }}</td>
                        <td class="px-4 py-3 text-right font-mono">Rp{{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-red-500">-Rp{{ Number(item.discount_amount).toLocaleString('id-ID') }}</td>
                        <td class="px-4 py-3 text-right font-mono font-medium">Rp{{ Number(item.total_amount).toLocaleString('id-ID') }}</td>
                    </tr>
                    <!-- Fallback row for invoices without items -->
                    <tr v-if="!invoice.items?.length">
                        <td colspan="5" class="px-4 py-4 text-center text-xs text-neutral-400 italic">
                            Langganan: {{ invoice.resort_count }} resort × Rp{{ Number(invoice.price_per_resort).toLocaleString('id-ID') }} × {{ invoice.months }} bulan
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-neutral-50 dark:bg-neutral-800/50 border-t-2 border-neutral-200 dark:border-neutral-800">
                    <tr v-if="invoice.subtotal">
                        <td colspan="4" class="px-4 py-2.5 text-right text-sm text-neutral-500">Subtotal</td>
                        <td class="px-4 py-2.5 text-right font-mono text-sm">Rp{{ Number(invoice.subtotal).toLocaleString('id-ID') }}</td>
                    </tr>
                    <tr v-if="Number(invoice.discount_amount) > 0">
                        <td colspan="4" class="px-4 py-2.5 text-right text-sm text-red-600">Diskon</td>
                        <td class="px-4 py-2.5 text-right font-mono text-sm text-red-600">-Rp{{ Number(invoice.discount_amount).toLocaleString('id-ID') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-base font-bold text-neutral-900 dark:text-white">Total</td>
                        <td class="px-4 py-3 text-right font-bold text-base text-primary-700 font-mono">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Download button -->
        <div class="flex justify-end pt-2">
            <button @click="onDownload" :disabled="downloading"
                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition cursor-pointer flex items-center gap-2">
                <svg v-if="downloading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                {{ downloading ? 'Mengunduh...' : 'Download PDF' }}
            </button>
        </div>
    </div>
</template>