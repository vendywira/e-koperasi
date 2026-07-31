<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InvoiceDetailContent from '@/Components/InvoiceDetailContent.vue';

defineProps<{ invoice: any; paymentChannels: any[] }>();
</script>

<template>
    <AdminLayout title="Detail Invoice">
        <Head :title="'Invoice - ' + (invoice.invoice_number || '')" />
        <InvoiceDetailContent :invoice :paymentChannels />
    </AdminLayout>
</template>
