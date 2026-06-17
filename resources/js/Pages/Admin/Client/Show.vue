<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    client: any;
    planLabels: Record<string, { name: string; price: string }>;
}>();

const form = useForm({
    plan: props.client.subscription?.plan || 'starter',
    status: props.client.subscription?.status || 'active',
    started_at: props.client.subscription?.started_at ? props.client.subscription.started_at.split(' ')[0] : new Date().toISOString().split('T')[0],
    ends_at: props.client.subscription?.ends_at ? props.client.subscription.ends_at.split(' ')[0] : '',
    trial_ends_at: props.client.subscription?.trial_ends_at ? props.client.subscription.trial_ends_at.split(' ')[0] : '',
});

const paymentForm = useForm({
    amount: 0,
    status: 'paid',
    paid_at: new Date().toISOString().split('T')[0],
    notes: '',
});

function saveSubscription() {
    form.put('/admin/clients/' + props.client.id + '/subscription', {
        onSuccess: () => {
            router.reload({ only: ['client'] });
        },
    });
}

function addPayment() {
    if (!props.client.subscription) {
        alert('Client belum memiliki subscription. Buat subscription dulu.');
        return;
    }
    paymentForm.post('/admin/clients/' + props.client.subscription.id + '/payments', {
        onSuccess: () => {
            paymentForm.reset();
            router.reload({ only: ['client'] });
        },
    });
}

const formatDate = (dt: string | null) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AdminLayout :title="'Client: ' + client.name">
        <Head :title="client.name + ' - Client Detail'" />

        <div class="p-4 sm:p-6 space-y-6 max-w-5xl">
            <Link
                href="/admin/clients"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Daftar Client
            </Link>

            <!-- Client Info -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Info Card -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                    <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Informasi Koperasi</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Nama</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">{{ client.name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Email</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">{{ client.email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Telepon</dt>
                            <dd class="text-sm font-medium text-neutral-900 dark:text-white">{{ client.phone || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-neutral-500 dark:text-neutral-400">Bergabung</dt>
                            <dd class="text-sm text-neutral-900 dark:text-white">{{ formatDate(client.created_at) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Subscription Form -->
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                    <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Kelola Subscription</h3>
                    <form @submit.prevent="saveSubscription" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Paket</label>
                            <select v-model="form.plan" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm">
                                <option v-for="(label, key) in planLabels" :key="key" :value="key">{{ label.name }} ({{ label.price }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                            <select v-model="form.status" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm">
                                <option value="active">Aktif</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Dibatalkan</option>
                                <option value="trialing">Trial</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Mulai</label>
                                <input v-model="form.started_at" type="date" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Berakhir</label>
                                <input v-model="form.ends_at" type="date" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Subscription' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 class="font-semibold text-neutral-900 dark:text-white">Riwayat Pembayaran</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Invoice</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Tanggal</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Jumlah</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Status</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="payment in client.subscription?.payments ?? []" :key="payment.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                                <td class="px-5 py-3 font-mono text-xs text-primary-600 dark:text-primary-400">{{ payment.receipt_number }}</td>
                                <td class="px-5 py-3 text-neutral-700 dark:text-neutral-300">{{ formatDate(payment.paid_at) }}</td>
                                <td class="px-5 py-3 text-right font-medium text-neutral-900 dark:text-white">Rp{{ Number(payment.amount).toLocaleString('id-ID') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="payment.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700'"
                                    >{{ payment.status === 'paid' ? 'Lunas' : payment.status }}</span>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400 text-xs max-w-[150px] truncate">{{ payment.notes || '-' }}</td>
                            </tr>
                            <tr v-if="!client.subscription?.payments?.length">
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-neutral-400">Belum ada pembayaran.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Payment Form -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                <h3 class="font-semibold text-neutral-900 dark:text-white mb-4">Input Pembayaran Manual</h3>
                <form @submit.prevent="addPayment" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Jumlah (Rp)</label>
                        <input v-model.number="paymentForm.amount" type="number" min="0" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                        <select v-model="paymentForm.status" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm">
                            <option value="paid">Lunas</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Gagal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Tanggal Bayar</label>
                        <input v-model="paymentForm.paid_at" type="date" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                    </div>
                    <div class="flex items-end">
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="w-full py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-neutral-300 dark:disabled:bg-neutral-700 transition-colors"
                        >
                            {{ paymentForm.processing ? 'Menyimpan...' : 'Tambah Pembayaran' }}
                        </button>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Catatan</label>
                        <input v-model="paymentForm.notes" type="text" placeholder="Catatan pembayaran (opsional)" class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm" />
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
