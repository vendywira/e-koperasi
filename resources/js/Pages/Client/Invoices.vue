<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { ref } from 'vue';

const props = defineProps<{ invoices: any[] }>();

const uploadingId = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

function uploadProof(id: string) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/png,image/jpeg,image/jpg,application/pdf';
    input.onchange = () => {
        if (!input.files?.length) return;
        const form = new FormData();
        form.append('payment_proof', input.files[0]);
        router.post(`/client/invoices/${id}/upload-proof`, form, {
            preserveScroll: true,
            onSuccess: () => { input.value = ''; },
        });
    };
    input.click();
}

const statusBadge = (s: string) => {
    const map: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        paid: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        cancelled: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    };
    return map[s] ?? '';
};
</script>

<template>
    <ClientLayout title="Invoice Saya">
        <Head title="Invoice - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white mb-6">Invoice Saya</h2>

            <div v-if="invoices.length === 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center">
                <p class="text-neutral-500">Belum ada invoice.</p>
                <Link href="/client/request-tenant" class="text-primary-600 hover:underline text-sm mt-2 inline-block">Ajukan tenant baru</Link>
            </div>

            <div v-for="inv in invoices" :key="inv.id" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-lg">{{ inv.name }}</h3>
                        <p class="text-sm text-neutral-500 font-mono">{{ inv.domain }}.e-koperasi.com</p>
                    </div>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium" :class="statusBadge(inv.status)">{{ inv.status }}</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm mb-4">
                    <div>
                        <p class="text-neutral-500">Resort</p>
                        <p class="font-medium">{{ inv.resort_count }} x Rp {{ Number(inv.price_per_resort).toLocaleString('id-ID') }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-500">Periode</p>
                        <p class="font-medium">{{ inv.months }} bulan</p>
                    </div>
                    <div>
                        <p class="text-neutral-500">Total</p>
                        <p class="font-bold text-lg text-primary-700">Rp {{ Number(inv.total_amount).toLocaleString('id-ID') }}</p>
                    </div>
                    <div>
                        <p class="text-neutral-500">Tanggal</p>
                        <p class="font-medium">{{ inv.created_at }}</p>
                    </div>
                </div>

                <!-- Payment proof upload -->
                <div v-if="inv.status === 'pending'" class="border-t pt-4 mt-2">
                    <p class="text-sm font-medium mb-2">Upload Bukti Pembayaran</p>
                    <button @click="uploadProof(inv.id)" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        {{ inv.payment_proof ? 'Ganti Bukti' : 'Upload Bukti' }}
                    </button>
                    <a v-if="inv.payment_proof" :href="inv.payment_proof" target="_blank" class="ml-3 text-sm text-primary-600 hover:underline">Lihat bukti</a>
                </div>

                <div v-if="inv.status === 'paid' && inv.paid_at" class="border-t pt-4 mt-2 text-sm text-emerald-600">
                    ✅ Dibayar pada {{ inv.paid_at }}
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
