<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import InvoiceCard from '@/Components/InvoiceCard.vue';

defineProps<{ invoices: any[]; paymentChannels: any[] }>();
</script>

<template>
    <ClientLayout title="Invoice Saya">
        <Head title="Invoice - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white mb-6">Invoice Saya</h2>

            <div v-if="invoices.length === 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center">
                <p class="text-3xl mb-3">📄</p>
                <p class="text-sm text-neutral-500">Belum ada invoice.</p>
                <Link href="/client/request-tenant" class="text-primary-600 hover:underline text-sm mt-2 inline-block">Ajukan tenant baru</Link>
            </div>

            <InvoiceCard v-for="inv in invoices" :key="inv.id" :invoice="inv" :channels="paymentChannels" />
        </div>
    </ClientLayout>
</template>