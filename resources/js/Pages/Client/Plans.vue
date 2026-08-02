<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import PlanPicker from '@/Components/PlanPicker.vue';
import { ref, computed } from 'vue';

const props = defineProps<{
    plans: any[];
    billingCycles?: any[];
    orderTenants: any[];
    upgradeTenants: any[];
    hasAnyTenant: boolean;
}>();

const selectedTenantId = ref<string | null>(null);
const cycleDiscount = (slug: string) => props.billingCycles?.find(c => c.slug === slug)?.discount_percent || 0;
const activeMode = ref<'order' | 'upgrade' | null>(null);

const orderForm = useForm({ plan_id: '', tenant_id: '', resort_qty: 1, billing_cycle: 'monthly' });
const upgradeForm = useForm({ subscription_id: '', plan_id: '', max_resorts: 1, billing_cycle: 'monthly' });

const cycleLabels: Record<string, string> = { monthly: 'Bulanan', quarterly: '3 Bulan', semiannual: '6 Bulan', yearly: '12 Bulan' };

const activeTenant = computed(() => {
    if (activeMode.value === 'order') return props.orderTenants.find(t => t.tenant_id === selectedTenantId.value);
    if (activeMode.value === 'upgrade') return props.upgradeTenants.find(t => t.tenant_id === selectedTenantId.value);
    return null;
});

const activePlans = computed(() => {
    if (!activeTenant.value) return [];
    return activeTenant.value.available_plans ?? [];
});

function selectTenant(mode: 'order' | 'upgrade', tenantId: string) {
    activeMode.value = mode;
    selectedTenantId.value = tenantId;
    if (mode === 'order') {
        orderForm.reset();
        orderForm.tenant_id = tenantId;
    } else {
        const t = props.upgradeTenants.find(x => x.tenant_id === tenantId);
        upgradeForm.reset();
        upgradeForm.subscription_id = t?.subscription_id ?? '';
        upgradeForm.max_resorts = t?.max_resorts ?? 1;
        upgradeForm.billing_cycle = t?.billing_cycle ?? 'monthly';
    }
}

function onPlanSelect(plan: any) {
    if (activeMode.value === 'order') {
        orderForm.plan_id = plan?.id || '';
        if (plan) orderForm.resort_qty = plan.max_resorts || 1;
    } else {
        // Upgrade: harga dari plan (backend derive), client cuma set resort count
        upgradeForm.plan_id = plan?.id || '';
        if (plan) upgradeForm.max_resorts = plan.max_resorts || 1;
    }
}

function submitOrder() {
    orderForm.post('/client/subscription/order', { preserveScroll: true });
}
function submitUpgrade() {
    upgradeForm.post('/client/subscription/upgrade', { preserveScroll: true });
}
</script>

<template>
    <ClientLayout title="Pilih Paket">
        <Head title="Pilih Paket - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Pilih Paket Langganan</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Pilih paket untuk tenant koperasi Anda. Upgrade → bayar prorata. Downgrade → kredit bulan depan.</p>
            </div>

            <!-- No tenant -->
            <div v-if="!hasAnyTenant" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-8 text-center">
                <p class="text-3xl mb-3">🏢</p>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Belum Punya Tenant</h3>
                <p class="text-sm text-neutral-500 mt-1 max-w-sm mx-auto">Ajukan tenant koperasi dulu untuk mulai berlangganan. Tim kami siapkan infrastrukturnya.</p>
                <Link href="/client/request-tenant" class="mt-4 inline-block px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition cursor-pointer">
                    Ajukan Tenant Baru
                </Link>
            </div>

            <!-- Tenant selection -->
            <div v-else class="space-y-6">
                <!-- Order: tenant tanpa sub -->
                <div v-if="orderTenants.length" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-neutral-100 dark:border-neutral-800">
                        <h3 class="font-semibold text-neutral-900 dark:text-white">Mulai Berlangganan</h3>
                        <p class="text-xs text-neutral-500 mt-0.5">Tenant yang belum punya langganan</p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-3">
                        <button
                            v-for="t in orderTenants"
                            :key="t.tenant_id"
                            @click="selectTenant('order', t.tenant_id)"
                            class="w-full flex items-center justify-between p-3.5 rounded-lg border-2 text-left transition cursor-pointer"
                            :class="selectedTenantId === t.tenant_id && activeMode === 'order'
                                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                : 'border-neutral-200 dark:border-neutral-700 hover:border-primary-300'"
                        >
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ t.tenant_name }}</p>
                                <p class="text-xs text-neutral-500 font-mono">{{ t.tenant_domain }}.e-koperasi.com</p>
                            </div>
                            <span class="text-xs text-primary-600 font-medium">{{ selectedTenantId === t.tenant_id && activeMode === 'order' ? 'Dipilih' : 'Pilih' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Upgrade: tenant dgn sub -->
                <div v-if="upgradeTenants.length" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-neutral-100 dark:border-neutral-800">
                        <h3 class="font-semibold text-neutral-900 dark:text-white">Ganti / Upgrade Paket</h3>
                        <p class="text-xs text-neutral-500 mt-0.5">Tenant yang sudah berlangganan</p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-3">
                        <button
                            v-for="t in upgradeTenants"
                            :key="t.tenant_id"
                            @click="selectTenant('upgrade', t.tenant_id)"
                            class="w-full flex items-center justify-between p-3.5 rounded-lg border-2 text-left transition cursor-pointer"
                            :class="selectedTenantId === t.tenant_id && activeMode === 'upgrade'
                                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                : 'border-neutral-200 dark:border-neutral-700 hover:border-primary-300'"
                        >
                            <div>
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ t.tenant_name }}</p>
                                <p class="text-xs text-neutral-500">Saat ini: {{ t.current_plan }} · {{ t.max_resorts }} resort · {{ cycleLabels[t.billing_cycle] || t.billing_cycle }}</p>
                            </div>
                            <span class="text-xs text-primary-600 font-medium">{{ selectedTenantId === t.tenant_id && activeMode === 'upgrade' ? 'Dipilih' : 'Ganti' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Plan selection -->
                <div v-if="activeTenant" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-neutral-100 dark:border-neutral-800">
                        <h3 class="font-semibold text-neutral-900 dark:text-white">
                            {{ activeMode === 'order' ? 'Pilih Paket' : 'Ganti Paket' }} — {{ activeTenant.tenant_name }}
                        </h3>
                        <p class="text-xs text-neutral-500 mt-0.5">
                            {{ activeMode === 'order' ? 'Pilih paket untuk mulai berlangganan' : 'Paket Trial tidak tersedia jika sudah pernah dipakai' }}
                        </p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <PlanPicker :plans="activePlans" :selected-plan-id="activeMode === 'order' ? orderForm.plan_id : upgradeForm.plan_id" @select="onPlanSelect" />

                        <form v-if="activeMode === 'order'" @submit.prevent="submitOrder" class="space-y-3 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-medium">Jumlah Resort</label>
                                    <input v-model.number="orderForm.resort_qty" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                                </div>
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
                            <button type="submit" :disabled="!orderForm.plan_id || orderForm.processing" class="w-full px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer">
                                {{ orderForm.processing ? 'Memproses...' : 'Pesan Paket & Buat Tagihan' }}
                            </button>
                        </form>

                        <form v-else @submit.prevent="submitUpgrade" class="space-y-3 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-medium">Jumlah Resort</label>
                                    <input v-model.number="upgradeForm.max_resorts" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                                </div>
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
                            <button type="submit" :disabled="!upgradeForm.plan_id || upgradeForm.processing" class="w-full px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer">
                                {{ upgradeForm.processing ? 'Memproses...' : 'Simpan Perubahan Paket' }}
                            </button>
                            <p class="text-xs text-neutral-400 text-center">Paket baru aktif setelah invoice prorata dibayar.</p>
                        </form>
                    </div>
                </div>

                <!-- Add tenant -->
                <Link href="/client/request-tenant" class="block w-full p-4 rounded-xl border-2 border-dashed border-neutral-300 dark:border-neutral-700 text-center text-sm text-neutral-500 dark:text-neutral-400 hover:border-primary-400 hover:text-primary-600 transition cursor-pointer">
                    + Tambah Tenant Baru
                </Link>
            </div>
        </div>
    </ClientLayout>
</template>
