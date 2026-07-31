<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import InvoiceDetailContent from '@/Components/InvoiceDetailContent.vue';

defineProps<{ invoice: any; paymentChannels: any[] }>();
</script>

<template>
    <ClientLayout title="Detail Invoice">
        <Head :title="'Invoice - ' + (invoice.invoice_number || '')" />
        <InvoiceDetailContent :invoice :paymentChannels />
    </ClientLayout>
</template>
