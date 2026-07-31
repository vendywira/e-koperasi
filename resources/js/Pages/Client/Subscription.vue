<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { ref } from 'vue';

const props = defineProps<{ subscriptions: any[]; plans?: any[] }>();

const activeModal = ref<string | null>(null); // 'upgrade' | 'cycle' | 'cancel' | 'resume' | null
const targetSub = ref<any>(null);

const upgradeForm = useForm({ subscription_id: '', max_resorts: 1, price_per_resort: 0 });
const cycleForm = useForm({ subscription_id: '', billing_cycle: 'monthly' });
const cancelForm = useForm({ subscription_id: '' });
const resumeForm = useForm({ subscription_id: '' });

const cycleLabels: Record<string, string> = { monthly: 'Bulanan', quarterly: '3 Bulan', semiannual: '6 Bulan', yearly: '12 Bulan' };
const cycleDiscounts: Record<string, number> = { monthly: 0, quarterly: 5, semiannual: 10, yearly: 20 };

function openModal(type: string, sub: any) {
    activeModal.value = type;
    targetSub.value = sub;
    if (type === 'upgrade') {
        upgradeForm.subscription_id = sub.id;
        upgradeForm.max_resorts = sub.max_resorts;
        upgradeForm.price_per_resort = sub.price_per_resort;
    } else if (type === 'cycle') {
        cycleForm.subscription_id = sub.id;
        cycleForm.billing_cycle = sub.billing_cycle || 'monthly';
    } else if (type === 'cancel') {
        cancelForm.subscription_id = sub.id;
    } else if (type === 'resume') {
        resumeForm.subscription_id = sub.id;
    }
}
function closeModal() {
    activeModal.value = null;
    targetSub.value = null;
}

function submitUpgrade() {
    upgradeForm.post('/client/subscription/upgrade', { preserveScroll: true, onSuccess: closeModal });
}
function submitCycle() {
    cycleForm.post('/client/subscription/change-cycle', { preserveScroll: true, onSuccess: closeModal });
}
function submitCancel() {
    if (!confirm('Yakin ingin berhenti berlangganan? Tenant tetap aktif sampai akhir periode.')) return;
    cancelForm.post('/client/subscription/cancel', { preserveScroll: true, onSuccess: closeModal });
}
function submitResume() {
    resumeForm.post('/client/subscription/resume', { preserveScroll: true, onSuccess: closeModal });
}

function daysLeft(endsAt: string | null): number | null {
    if (!endsAt) return null;
    return Math.max(0, Math.ceil((new Date(endsAt).getTime() - Date.now()) / (1000 * 60 * 60 * 24)));
}
</script>

<template>
    <ClientLayout title="Langganan KSU">
        <Head title="Langganan - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl space-y-6">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Langganan KSU</h2>

            <div v-if="subscriptions.length === 0" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center">
                <p class="text-neutral-500">Belum ada langganan. Ajukan tenant baru melalui menu Tenant.</p>
            </div>

            <div v-for="sub in subscriptions" :key="sub.id" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-5 sm:p-6 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ sub.tenant_name }}</h3>
                        <p class="text-sm text-neutral-500 font-mono">{{ sub.tenant_domain }}.e-koperasi.com</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="sub.is_grace ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : sub.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500'">
                            {{ sub.is_grace ? 'Masa Tenggang' : sub.status === 'active' ? 'Aktif' : sub.status }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
                            :class="daysLeft(sub.ends_at) !== null && (sub.ends_at) && daysLeft(sub.ends_at)! <= 7 ? 'bg-red-50 text-red-700' : 'bg-neutral-100 text-neutral-600'">
                            {{ daysLeft(sub.ends_at) ?? '∞' }} hari
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Resort</p>
                            <p class="font-medium mt-1">{{ sub.max_resorts }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Harga/Resort</p>
                            <p class="font-medium mt-1">Rp{{ Number(sub.price_per_resort).toLocaleString('id-ID') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Siklus</p>
                            <p class="font-medium mt-1">{{ cycleLabels[sub.billing_cycle] || sub.billing_cycle }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-neutral-400">Tagihan Berikut</p>
                            <p class="font-medium mt-1">{{ sub.next_bill_date || '-' }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-neutral-400">Masa berlaku</span>
                            <span class="font-medium">{{ sub.usage_percent ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all" :class="sub.is_active ? 'bg-emerald-500' : 'bg-neutral-400'" :style="{ width: Math.min(100, sub.usage_percent || 0) + '%' }" />
                        </div>
                        <p class="text-xs text-neutral-400 mt-1">{{ sub.started_at }} — {{ sub.ends_at }}</p>
                    </div>

                    <!-- Grace banner -->
                    <div v-if="sub.is_grace" class="mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">⚠️ Masa Tenggang — Sisa {{ sub.grace_days_remaining }} hari</p>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">Perpanjang sebelum <strong>{{ sub.grace_ends_at }}</strong> untuk menghindari penonaktifan.</p>
                        <Link href="/client/invoices" class="mt-1.5 inline-block text-xs font-semibold text-amber-700 dark:text-amber-300 underline cursor-pointer">Bayar Sekarang →</Link>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 pt-2">
                        <button @click="openModal('upgrade', sub)" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">Ganti Paket</button>
                        <button @click="openModal('cycle', sub)" class="px-4 py-2 border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 transition cursor-pointer">Ubah Siklus</button>
                        <button v-if="sub.is_active" @click="openModal('cancel', sub)" class="px-4 py-2 border border-red-200 dark:border-red-900/50 text-red-600 rounded-lg text-sm hover:bg-red-50 dark:hover:bg-red-900/20 transition cursor-pointer">Berhenti</button>
                        <button v-if="sub.status === 'cancelled' && daysLeft(sub.ends_at) !== null && (daysLeft(sub.ends_at))! > 0" @click="openModal('resume', sub)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition cursor-pointer">Aktifkan</button>
                    </div>
                </div>
            </div>

            <!-- Modal: Ganti Paket -->
            <Teleport to="body">
                <div v-if="activeModal === 'upgrade' && targetSub" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @click.self="closeModal">
                    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-md w-full p-6 space-y-4">
                        <div class="flex items-center justify-between"><h3 class="text-lg font-bold">Ganti Paket</h3><button @click="closeModal" class="p-1 text-neutral-400 hover:text-neutral-600 cursor-pointer">&times;</button></div>
                        <p class="text-sm text-neutral-500">Ubah jumlah resort. Upgrade → bayar prorata. Downgrade → kredit bulan depan.</p>
                        <form @submit.prevent="submitUpgrade" class="space-y-3">
                            <div><label class="text-sm font-medium">Jumlah Resort</label><input v-model="upgradeForm.max_resorts" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                            <div><label class="text-sm font-medium">Harga/Resort (Rp)</label><input v-model="upgradeForm.price_per_resort" type="number" min="0" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                            <div class="flex gap-2 pt-2">
                                <button type="submit" :disabled="upgradeForm.processing" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer">{{ upgradeForm.processing ? 'Memproses...' : 'Simpan' }}</button>
                                <button type="button" @click="closeModal" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal: Ubah Siklus -->
                <div v-if="activeModal === 'cycle' && targetSub" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @click.self="closeModal">
                    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-md w-full p-6 space-y-4">
                        <div class="flex items-center justify-between"><h3 class="text-lg font-bold">Ubah Siklus Tagihan</h3><button @click="closeModal" class="p-1 text-neutral-400 hover:text-neutral-600 cursor-pointer">&times;</button></div>
                        <p class="text-sm text-neutral-500">Pilih siklus baru. Berlaku untuk tagihan berikutnya.</p>
                        <form @submit.prevent="submitCycle" class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="c in ['monthly','quarterly','semiannual','yearly']" :key="c" type="button" @click="cycleForm.billing_cycle = c"
                                    class="px-3 py-3 border-2 rounded-lg text-sm text-left transition cursor-pointer"
                                    :class="cycleForm.billing_cycle === c ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700' : 'border-neutral-200 dark:border-neutral-700 hover:border-primary-300'">
                                    <p class="font-semibold">{{ cycleLabels[c] }}</p>
                                    <p v-if="cycleDiscounts[c] > 0" class="text-xs text-emerald-600 font-medium">Hemat {{ cycleDiscounts[c] }}%</p>
                                    <p v-else class="text-xs text-neutral-400">Harga normal</p>
                                </button>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="submit" :disabled="cycleForm.processing" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer">{{ cycleForm.processing ? 'Memproses...' : 'Simpan' }}</button>
                                <button type="button" @click="closeModal" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal: Berhenti -->
                <div v-if="activeModal === 'cancel' && targetSub" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @click.self="closeModal">
                    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-md w-full p-6 space-y-4">
                        <h3 class="text-lg font-bold text-red-600">Berhenti Berlangganan</h3>
                        <p class="text-sm text-neutral-600">Tenant <strong>{{ targetSub.tenant_name }}</strong> tetap aktif sampai <strong>{{ targetSub.ends_at }}</strong>. Setelah itu tenant akan dinonaktifkan dan data tetap tersimpan.</p>
                        <p class="text-xs text-neutral-400">Langganan bisa diaktifkan kembali kapan saja dengan memperpanjang.</p>
                        <form @submit.prevent="submitCancel" class="space-y-3 pt-2">
                            <div class="flex gap-2">
                                <button type="submit" :disabled="cancelForm.processing" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 cursor-pointer">{{ cancelForm.processing ? 'Memproses...' : 'Ya, Berhenti' }}</button>
                                <button type="button" @click="closeModal" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal: Aktifkan Kembali -->
                <div v-if="activeModal === 'resume' && targetSub" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @click.self="closeModal">
                    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-md w-full p-6 space-y-4">
                        <div class="flex items-center justify-between"><h3 class="text-lg font-bold text-blue-600">Aktifkan Kembali</h3><button @click="closeModal" class="p-1 text-neutral-400 hover:text-neutral-600 cursor-pointer">&times;</button></div>
                        <p class="text-sm text-neutral-600">Langganan <strong>{{ targetSub.tenant_name }}</strong> akan diaktifkan kembali. Tagihan akan berjalan normal sesuai siklus sebelumnya.</p>
                        <form @submit.prevent="submitResume" class="space-y-3 pt-2">
                            <div class="flex gap-2">
                                <button type="submit" :disabled="resumeForm.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 cursor-pointer">{{ resumeForm.processing ? 'Memproses...' : 'Aktifkan' }}</button>
                                <button type="button" @click="closeModal" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </ClientLayout>
</template>