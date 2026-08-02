<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import PlanPicker from '@/Components/PlanPicker.vue';
import { ref, computed } from 'vue';

const props = defineProps<{ subscriptions: any[]; plans?: any[]; billingCycles?: any[]; orderTenants?: any[] }>();

const cycleDiscount = (slug: string) => props.billingCycles?.find(c => c.slug === slug)?.discount_percent || 0;

const activeModal = ref<string | null>(null); // 'upgrade' | 'order' | 'cycle' | 'cancel' | 'resume' | null
const targetSub = ref<any>(null);

const upgradeForm = useForm({ subscription_id: '', plan_id: '', max_resorts: 1, billing_cycle: 'monthly' });
const orderForm = useForm({ plan_id: '', tenant_id: '', resort_qty: 1, billing_cycle: 'monthly' });
const cancelForm = useForm({ subscription_id: '' });
const resumeForm = useForm({ subscription_id: '' });

const orderTenant = ref<any>(null);

const tenantWithoutSub = computed(() => {
    const subTenantIds = new Set(props.subscriptions.map(s => s.tenant_id).filter(Boolean));
    // userTenants not passed; derive from subscriptions only. Order flow needs a tenant without sub.
    // For now fall back: if no subscriptions, use null (client must request tenant first).
    return null;
});

const cycleLabels: Record<string, string> = { monthly: 'Bulanan', quarterly: '3 Bulan', semiannual: '6 Bulan', yearly: '12 Bulan' };

function preselectPlan() {
    const plans = upgradePlans.value;
    if (!plans.length) return;
    // 1. plan aktif terakhir (dari subscription plan_id)
    const active = plans.find(p => p.id === targetSub.value?.plan_id);
    // 2. is_default
    const def = plans.find(p => p.is_default);
    // 3. Business (type business)
    const biz = plans.find(p => p.type === 'business');
    const target = active || def || biz || plans[0];
    onPlanSelectUpgrade(target);
}

function openModal(type: string, sub: any) {
    activeModal.value = type;
    targetSub.value = sub;
    if (type === 'upgrade') {
        upgradeForm.reset();
        upgradeForm.subscription_id = sub.id;
        upgradeForm.max_resorts = sub.max_resorts || 1;
        upgradeForm.billing_cycle = sub.billing_cycle || 'monthly';
        preselectPlan();
    } else if (type === 'order') {
        orderForm.reset();
        orderTenant.value = sub;
        orderForm.tenant_id = sub.tenant_id;
    } else if (type === 'cancel') {
        cancelForm.subscription_id = sub.id;
    } else if (type === 'resume') {
        resumeForm.subscription_id = sub.id;
    }
}

const upgradePlans = computed(() => targetSub.value?.available_plans ?? props.plans ?? []);
const orderPlans = computed(() => orderTenant.value?.available_plans ?? props.plans ?? []);

// One-time (enterprise) atau trial → resort & siklus gak berlaku (fix)
const isFixedPlan = computed(() => {
    const plan = selectedPlan.value;
    if (!plan) return false;
    return plan.type === 'trial' || plan.pricing_config?.has_cycle === false;
});

const selectedPlan = computed(() => {
    const id = activeModal.value === 'upgrade' ? upgradeForm.plan_id : orderForm.plan_id;
    if (!id) return null;
    const plans = activeModal.value === 'upgrade' ? upgradePlans.value : orderPlans.value;
    return plans.find(p => p.id === id);
});

const priceSimulation = computed(() => {
    const plan = selectedPlan.value;
    if (!plan) return null;
    const cycleSlug = activeModal.value === 'upgrade' ? upgradeForm.billing_cycle : orderForm.billing_cycle;
    const cycle = props.billingCycles?.find(c => c.slug === cycleSlug);
    const resort = activeModal.value === 'upgrade' ? upgradeForm.max_resorts : orderForm.resort_qty;
    if (!resort || resort < 1) return null;

    const cfg = plan.pricing_config || {};
    if (cfg.has_cycle === false) {
        const price = Number(cfg.price || 0);
        const discountPct = Number(cfg.discount_percent || 0);
        const subtotal = price;
        const discount = subtotal * discountPct / 100;
        return { ppu: price, months: 0, subtotal, discountPct, discount, total: Math.max(0, subtotal - discount), oneTime: true };
    }

    const ppu = cfg.price_per_resort || plan.price_per_month / Math.max(1, plan.max_resorts);
    const months = cycle?.months ?? 1;
    const subtotal = resort * ppu * months;
    const discountPct = cycle?.discount_percent ?? 0;
    const discount = subtotal * discountPct / 100;
    const total = Math.max(0, subtotal - discount);

    return { ppu, months, subtotal, discountPct, discount, total, oneTime: false };
});

function onPlanSelectUpgrade(plan: any) {
    upgradeForm.plan_id = plan?.id || '';
    if (plan) {
        // Upgrade: harga dari plan (backend derive), client cuma set resort count.
        // Default resort = resort yang sudah dipakai sebelumnya (dari targetSub), min 1.
        upgradeForm.max_resorts = targetSub.value?.max_resorts && targetSub.value.max_resorts >= 1
            ? targetSub.value.max_resorts
            : 1;
    }
}

function onPlanSelectOrder(plan: any) {
    orderForm.plan_id = plan?.id || '';
    if (plan) {
        // Order = tenant baru, default resort 1 (minimal)
        orderForm.resort_qty = 1;
    }
}
function closeModal() {
    activeModal.value = null;
    targetSub.value = null;
}

function submitUpgrade() {
    upgradeForm.post('/client/subscription/upgrade', { preserveScroll: true, onSuccess: closeModal });
}
function submitOrder() {
    orderForm.post('/client/subscription/order', { preserveScroll: true, onSuccess: closeModal });
}

const regenerating = ref(false);
function regenerateInvoice(sub: any) {
    regenerating.value = true;
    const form = useForm({ subscription_id: sub.id });
    form.post('/client/subscription/regenerate-invoice', {
        preserveScroll: true,
        onFinish: () => { regenerating.value = false; },
    });
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

const cycleMonths = (slug: string) => {
    const map: Record<string, number> = { monthly: 1, quarterly: 3, semiannual: 6, yearly: 12 };
    return map[slug] || 1;
};

// Periode: aktif → dari data; pending → perkiraan dari sekarang + siklus
function periodLabel(sub: any): string {
    if (sub.is_active && sub.started_at && sub.ends_at) {
        return `${sub.started_at} — ${sub.ends_at}`;
    }
    const end = new Date(Date.now() + cycleMonths(sub.billing_cycle) * 30 * 86400000);
    const endStr = end.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    return sub.status === 'pending'
        ? `Sekarang — ${endStr} (perkiraan)`
        : `${sub.started_at || '-'} — ${sub.ends_at || '-'}`;
}
</script>

<template>
    <ClientLayout title="Langganan KSU">
        <Head title="Langganan - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl space-y-6">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Langganan KSU</h2>

            <div v-if="subscriptions.length === 0 && !orderTenants?.length" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center">
                <p class="text-3xl mb-3">🏢</p>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Belum Punya Tenant</h3>
                <p class="text-sm text-neutral-500 mt-1 max-w-sm mx-auto">Ajukan tenant koperasi dulu untuk mulai berlangganan. Tim kami siapkan infrastrukturnya.</p>
                <Link href="/client/request-tenant" class="mt-4 inline-block px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">
                    Ajukan Tenant Baru
                </Link>
            </div>

            <!-- Order: tenant tanpa subscription -->
            <div v-if="orderTenants?.length" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5 sm:p-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pesan Langganan</h3>
                <p class="text-sm text-neutral-500 mt-1">Pilih tenant yang belum punya langganan untuk mulai berlangganan.</p>
                <div class="mt-4 space-y-2">
                    <div v-for="t in orderTenants" :key="t.tenant_id"
                        class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 transition"
                    >
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ t.tenant_name }}</p>
                            <p class="text-xs text-neutral-500 font-mono">{{ t.tenant_domain }}.e-koperasi.com</p>
                        </div>
                        <button @click="openModal('order', t)" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">Pilih Paket</button>
                    </div>
                </div>
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
                            :class="sub.is_grace ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : sub.is_trialing ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400' : sub.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500'">
                            {{ sub.is_grace ? 'Masa Tenggang' : sub.is_trialing ? 'Trial' : sub.status === 'active' ? 'Aktif' : sub.status }}
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
                        <p class="text-xs text-neutral-400 mt-1">{{ periodLabel(sub) }}</p>
                    </div>

                    <!-- Pending invoice banner -->
                    <div v-if="sub.pending_invoice" class="mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">⚠️ Tagihan Belum Dibayar</p>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                            {{ sub.pending_invoice.invoice_number }} · Rp{{ Number(sub.pending_invoice.total_amount).toLocaleString('id-ID') }}
                        </p>
                        <Link :href="`/client/invoices/${sub.pending_invoice.id}/payment`" class="mt-1.5 inline-block px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition cursor-pointer">
                            Bayar Sekarang →
                        </Link>
                    </div>

                    <!-- Pending sub without invoice → regenerate -->
                    <div v-else-if="sub.status === 'pending'" class="mt-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800">
                        <p class="text-xs font-semibold text-blue-800 dark:text-blue-200">⏳ Menunggu Pembayaran</p>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Langganan belum aktif. Buat tagihan untuk melanjutkan pembayaran.</p>
                        <button @click="regenerateInvoice(sub)" :disabled="regenerating" class="mt-1.5 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition cursor-pointer disabled:opacity-50">
                            {{ regenerating ? 'Memproses...' : 'Buat Tagihan & Bayar' }}
                        </button>
                    </div>

                    <!-- Trial banner -->
                    <div v-if="sub.is_trialing" class="mt-4 p-3 rounded-lg bg-sky-50 dark:bg-sky-900/10 border border-sky-200 dark:border-sky-800">
                        <p class="text-xs font-semibold text-sky-800 dark:text-sky-200">✨ Masa Trial — Berakhir {{ sub.trial_ends_at }}</p>
                        <p class="text-xs text-sky-700 dark:text-sky-300 mt-1">Perpanjang ke paket Business sebelum trial habis untuk lanjut.</p>
                        <button @click="openModal('upgrade', sub)" class="mt-1.5 inline-block px-4 py-2 bg-sky-600 text-white rounded-lg text-xs font-semibold hover:bg-sky-700 transition cursor-pointer">Perpanjang Sekarang</button>
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
                        <button v-if="sub.is_active" @click="openModal('cancel', sub)" class="px-4 py-2 border border-red-200 dark:border-red-900/50 text-red-600 rounded-lg text-sm hover:bg-red-50 dark:hover:bg-red-900/20 transition cursor-pointer">Berhenti</button>
                        <button v-if="sub.status === 'cancelled' && daysLeft(sub.ends_at) !== null && (daysLeft(sub.ends_at))! > 0" @click="openModal('resume', sub)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition cursor-pointer">Aktifkan</button>
                    </div>
                </div>
            </div>

            <!-- Modal: Ganti Paket -->
            <Teleport to="body">
                <div v-if="activeModal === 'upgrade' && targetSub" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 overflow-y-auto" @click.self="closeModal">
                    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-2xl w-full p-4 sm:p-6 space-y-4 my-auto max-h-[88vh] overflow-y-auto">
                        <div class="flex items-center justify-between"><h3 class="text-lg font-bold">Ganti Paket — {{ targetSub.tenant_name }}</h3><button @click="closeModal" class="p-1 text-neutral-400 hover:text-neutral-600 cursor-pointer">&times;</button></div>
                        <p class="text-sm text-neutral-500">Pilih paket baru. Upgrade → bayar prorata. Downgrade → kredit bulan depan.</p>
                        <PlanPicker
                            :plans="upgradePlans"
                            :selected-plan-id="upgradeForm.plan_id || null"
                            @select="onPlanSelectUpgrade"
                        />
                        <form @submit.prevent="submitUpgrade" class="space-y-3 pt-2">
                            <div v-if="!isFixedPlan" class="grid grid-cols-2 gap-3">
                                <div><label class="text-sm font-medium">Jumlah Resort</label><input v-model.number="upgradeForm.max_resorts" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                                <div>
                                    <label class="text-sm font-medium">Siklus Tagihan</label>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button v-for="c in ['monthly','quarterly','semiannual','yearly']" :key="c" type="button" @click="upgradeForm.billing_cycle = c"
                                            class="px-2 py-1.5 border-2 rounded-lg text-xs text-left transition cursor-pointer"
                                            :class="upgradeForm.billing_cycle === c ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-neutral-200 dark:border-neutral-700 hover:border-emerald-300'">
                                            <p class="font-semibold">{{ cycleLabels[c] }}</p>
                                            <p v-if="cycleDiscount(c) > 0" class="text-[10px] text-emerald-600 font-medium">-{{ cycleDiscount(c) }}%</p>
                                            <p v-else class="text-[10px] text-neutral-400">Harga normal</p>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-neutral-500">{{ selectedPlan?.type === 'trial' ? `Gratis ${selectedPlan.trial_days} hari, 1 resort` : 'One-time · Unlimited resort' }}</p>
                            <div v-if="priceSimulation" class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200 dark:border-neutral-700 space-y-2">
                                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Simulasi Tagihan</p>
                                <div v-if="!priceSimulation.oneTime" class="flex justify-between text-sm"><span class="text-neutral-500">Harga/Resort</span><span class="font-medium">Rp{{ Number(priceSimulation.ppu).toLocaleString('id-ID') }}</span></div>
                                <div class="flex justify-between text-sm"><span class="text-neutral-500">{{ priceSimulation.oneTime ? 'Harga Normal' : `${upgradeForm.max_resorts} resort × ${priceSimulation.months} bulan` }}</span><span class="font-medium">Rp{{ Number(priceSimulation.subtotal).toLocaleString('id-ID') }}</span></div>
                                <div v-if="priceSimulation.discount > 0" class="flex justify-between text-sm text-emerald-600 dark:text-emerald-400"><span>Diskon ({{ priceSimulation.discountPct }}%)</span><span>-Rp{{ Number(priceSimulation.discount).toLocaleString('id-ID') }}</span></div>
                                <div class="flex justify-between text-sm font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700"><span>Total</span><span>Rp{{ Number(priceSimulation.total).toLocaleString('id-ID') }}</span></div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="submit" :disabled="upgradeForm.processing" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer">{{ upgradeForm.processing ? 'Memproses...' : 'Simpan' }}</button>
                                <p class="text-[11px] text-neutral-400 text-center w-full">Paket baru aktif setelah invoice prorata dibayar.</p>
                                <button type="button" @click="closeModal" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal: Order (tenant tanpa sub) -->
                <Teleport to="body">
                    <div v-if="activeModal === 'order' && orderTenant" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 overflow-y-auto" @click.self="closeModal">
                        <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-2xl w-full p-4 sm:p-6 space-y-4 my-auto max-h-[88vh] overflow-y-auto">
                            <div class="flex items-center justify-between"><h3 class="text-lg font-bold">Pilih Paket — {{ orderTenant.tenant_name }}</h3><button @click="closeModal" class="p-1 text-neutral-400 hover:text-neutral-600 cursor-pointer">&times;</button></div>
                            <PlanPicker
                                :plans="orderPlans"
                                :selected-plan-id="orderForm.plan_id || null"
                                @select="onPlanSelectOrder"
                            />
                            <form @submit.prevent="submitOrder" class="space-y-3 pt-2">
                                <div v-if="!isFixedPlan" class="grid grid-cols-2 gap-3">
                                    <div><label class="text-sm font-medium">Jumlah Resort</label><input v-model.number="orderForm.resort_qty" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                                    <div>
                                        <label class="text-sm font-medium">Siklus</label>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <button v-for="c in ['monthly','quarterly','semiannual','yearly']" :key="c" type="button" @click="orderForm.billing_cycle = c"
                                                class="px-2 py-1.5 border-2 rounded-lg text-xs text-left transition cursor-pointer"
                                                :class="orderForm.billing_cycle === c ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-neutral-200 dark:border-neutral-700 hover:border-emerald-300'">
                                                <p class="font-semibold">{{ cycleLabels[c] }}</p>
                                                <p v-if="cycleDiscount(c) > 0" class="text-[10px] text-emerald-600 font-medium">-{{ cycleDiscount(c) }}%</p>
                                                <p v-else class="text-[10px] text-neutral-400">Harga normal</p>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-neutral-500">{{ selectedPlan?.type === 'trial' ? `Gratis ${selectedPlan.trial_days} hari, 1 resort` : 'One-time · Unlimited resort' }}</p>
                                <div v-if="priceSimulation" class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200 dark:border-neutral-700 space-y-2">
                                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Simulasi Tagihan</p>
                                    <div v-if="!priceSimulation.oneTime" class="flex justify-between text-sm"><span class="text-neutral-500">Harga/Resort</span><span class="font-medium">Rp{{ Number(priceSimulation.ppu).toLocaleString('id-ID') }}</span></div>
                                    <div class="flex justify-between text-sm"><span class="text-neutral-500">{{ priceSimulation.oneTime ? 'Harga Normal' : `${orderForm.resort_qty} resort × ${priceSimulation.months} bulan` }}</span><span class="font-medium">Rp{{ Number(priceSimulation.subtotal).toLocaleString('id-ID') }}</span></div>
                                    <div v-if="priceSimulation.discount > 0" class="flex justify-between text-sm text-emerald-600 dark:text-emerald-400"><span>Diskon ({{ priceSimulation.discountPct }}%)</span><span>-Rp{{ Number(priceSimulation.discount).toLocaleString('id-ID') }}</span></div>
                                    <div class="flex justify-between text-sm font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700"><span>Total</span><span>Rp{{ Number(priceSimulation.total).toLocaleString('id-ID') }}</span></div>
                                </div>
                                <div class="flex gap-2 pt-2">
                                    <button type="submit" :disabled="orderForm.processing" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer">{{ orderForm.processing ? 'Memproses...' : 'Pesan Paket' }}</button>
                                    <button type="button" @click="closeModal" class="px-4 py-2 border border-neutral-200 rounded-lg text-sm hover:bg-neutral-50 cursor-pointer">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Teleport>

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