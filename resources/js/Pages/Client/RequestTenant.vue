<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    existingRequest: any | null;
}>();

const form = useForm({
    name: '',
    domain: '',
    max_resorts: 1,
    notes: '',
});

function submit() {
    form.post('/client/request-tenant', {
        preserveScroll: true,
    });
}

const statusBadge = (s: string) => {
    const map: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[s] ?? '';
};
</script>

<template>
    <ClientLayout title="Ajukan Tenant">
        <Head title="Ajukan Tenant - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white mb-2">Ajukan Tenant Baru</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">Isi form di bawah untuk mengajukan pembuatan tenant KSU. Admin akan memproses dalam 1x24 jam.</p>

            <!-- Existing request status -->
            <div v-if="existingRequest" class="mb-6 p-4 rounded-lg border"
                :class="existingRequest.status === 'pending' ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800' : ''">
                <p class="text-sm font-medium">Permintaan sebelumnya:</p>
                <p class="text-sm mt-1">{{ existingRequest.name }} — <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(existingRequest.status)">{{ existingRequest.status }}</span></p>
                <p v-if="existingRequest.status === 'pending'" class="text-xs text-amber-600 mt-1">Masih diproses, silakan tunggu.</p>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Nama Koperasi</label>
                        <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Koperasi Anda" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Domain (subdomain)</label>
                        <div class="flex items-center gap-2">
                            <input v-model="form.domain" type="text" required class="flex-1 px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm font-mono focus:ring-2 focus:ring-primary-500" placeholder="koperasi-anda" />
                            <span class="text-sm text-neutral-400 font-mono">.ksu.app</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Jumlah Resort</label>
                        <input v-model.number="form.max_resorts" type="number" min="1" max="100" required class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Catatan (opsional)</label>
                        <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Informasi tambahan..."></textarea>
                    </div>
                    <button type="submit" :disabled="form.processing || existingRequest?.status === 'pending'"
                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">
                        {{ form.processing ? 'Mengirim...' : 'Ajukan Tenant' }}
                    </button>
                </form>
            </div>
        </div>
    </ClientLayout>
</template>
