<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{ existingRequest: any | null; plans: any[] }>();
const page = usePage();
const cycles = computed(() => (page.props as any).billing_cycles || []);

const form = useForm({
    name: '',
    domain: '',
    plan_id: props.plans?.[0]?.id || '',
    billing_cycle: cycles.value?.[0]?.slug || 'monthly',
    resort_qty: 1,
    notes: '',
    company_address: '',
    company_phone: '',
    company_email: '',
    logo: null as File | null,
});

const logoPreview = ref<string | null>(null);
const domainStatus = ref<'idle' | 'checking' | 'available' | 'taken'>('idle');
const domainSuggestions = ref<string[]>([]);
let domainTimer: ReturnType<typeof setTimeout> | null = null;

const selectedPlan = computed(() => props.plans?.find(p => p.id === form.plan_id));
const planType = computed(() => selectedPlan.value?.type);

const pricePreview = computed(() => {
    if (planType.value === 'enterprise' || planType.value === 'trial') return null;
    const plan = selectedPlan.value;
    if (!plan) return null;
    const qty = form.resort_qty || plan.max_resorts;
    const cfg = plan.pricing_config || {};
    const unitPrice = cfg.price_per_resort || plan.price_per_month / Math.max(1, qty);
    const cycle = cycles.value.find((c: any) => c.slug === form.billing_cycle);
    const months = cycle?.months || 1;
    const monthlyTotal = qty * unitPrice;
    const subtotal = monthlyTotal * months;
    const discountPct = cycle?.discount_percent || 0;
    const discount = subtotal * discountPct / 100;
    const total = subtotal - discount;
    return { qty, unitPrice: Math.round(unitPrice), monthlyTotal: Math.round(monthlyTotal), months, subtotal: Math.round(subtotal), discountPct, discount: Math.round(discount), total: Math.round(total) };
});

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = () => logoPreview.value = reader.result as string;
        reader.readAsDataURL(file);
    }
}

function checkDomain() {
    const d = form.domain?.trim().toLowerCase();
    form.errors.domain = ''; // clear previous validation error
    if (!d || d.length < 2) { domainStatus.value = 'idle'; return; }
    domainStatus.value = 'checking';
    if (domainTimer) clearTimeout(domainTimer);
    domainTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/client/request-tenant/check-domain?domain=${d}`);
            const data = await res.json();
            domainStatus.value = data.available ? 'available' : 'taken';
            domainSuggestions.value = data.suggestions || [];
        } catch { domainStatus.value = 'idle'; }
    }, 500);
}

function submit() {
    form.transform((data: any) => {
        // For trial/enterprise, remove unnecessary fields
        const plan = props.plans?.find(p => p.id === form.plan_id);
        if (plan?.type !== 'business') {
            delete data.resort_qty;
        }
        return data;
    });
    form.post('/client/request-tenant', { preserveScroll: true });
}
</script>

<template>
    <ClientLayout title="Kelola Tenant">
        <Head title="Ajukan Tenant - e-Koperasi" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white mb-2">Ajukan Tenant Baru</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">Pilih paket, isi detail tenant, dan kirim permintaan. Admin akan memproses dalam 1x24 jam.</p>

            <div v-if="existingRequest" class="mb-6 p-4 rounded-lg border bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800">
                <p class="text-sm font-medium">Permintaan sebelumnya: {{ existingRequest.name }}</p>
                <p class="text-xs text-amber-600 mt-1">Masih diproses, silakan tunggu.</p>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6">
                <!-- Error summary -->
                <!-- <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">Mohon perbaiki error berikut:</p>
                    <ul class="mt-1.5 space-y-0.5 list-disc list-inside">
                        <li v-for="(msg, field) in form.errors" :key="field" class="text-xs text-red-600 dark:text-red-400">{{ msg }}</li>
                    </ul>
                </div> -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Plan -->
                    <div v-if="plans.length">
                        <label class="block text-sm font-medium mb-2">Pilih Paket</label>
                        <p v-if="form.errors.plan_id" class="text-xs text-red-500 mb-1">{{ form.errors.plan_id }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <button v-for="p in plans" :key="p.id" type="button" @click="form.plan_id = p.id"
                                class="px-3 py-3 border-2 rounded-lg text-sm text-left transition cursor-pointer"
                                :class="form.plan_id === p.id ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700' : 'border-neutral-200 dark:border-neutral-700 hover:border-primary-300'">
                                <p class="font-semibold">{{ p.name }}</p>
                                <p class="text-xs text-neutral-400">{{ p.type }}</p>
                                <template v-if="p.type === 'trial'">
                                    <p class="text-sm font-bold mt-1 text-emerald-600">Gratis</p>
                                    <p class="text-[10px] text-neutral-400">{{ p.trial_days }} hari trial</p>
                                </template>
                                <template v-else-if="p.type === 'enterprise'">
                                    <p class="text-sm font-bold mt-1 text-purple-600">Rp{{ Number(p.pricing_config?.price || 0).toLocaleString('id-ID') }}</p>
                                    <p class="text-[10px] text-neutral-400">One-time, on-premise</p>
                                </template>
                                <template v-else-if="p.type === 'business' || !p.type">
                                    <p class="text-sm font-bold mt-1 text-primary-600">Rp{{ Number(p.pricing_config?.price_per_resort || p.price_per_month / Math.max(1, p.max_resorts)).toLocaleString('id-ID') }}<span class="text-[10px] font-normal text-neutral-400">/resort/bln</span></p>
                                    <p class="text-[10px] text-neutral-400">{{ p.max_resorts }} resort max</p>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Cycle -->
                    <div v-if="selectedPlan && selectedPlan.type !== 'enterprise' && selectedPlan.type !== 'trial'">
                        <label class="block text-sm font-medium mb-2">Jumlah Resort</label>
                        <input v-model.number="form.resort_qty" type="number" :min="1" :max="selectedPlan.max_resorts" class="w-full sm:w-32 px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" :class="form.errors.resort_qty ? 'border-red-500 ring-1 ring-red-500' : ''" />
                            <p v-if="form.errors.resort_qty" class="text-xs text-red-500 mt-1">{{ form.errors.resort_qty }}</p>
                        <p class="text-xs text-neutral-400 mt-1">Maksimal {{ selectedPlan.max_resorts }} resort</p>
                        
                        <label class="block text-sm font-medium mb-2 mt-4">Siklus Tagihan</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button v-for="c in cycles" :key="c.slug" type="button" @click="form.billing_cycle = c.slug"
                                class="px-3 py-3 border-2 rounded-lg text-sm text-left transition cursor-pointer"
                                :class="form.billing_cycle === c.slug ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700' : 'border-neutral-200 dark:border-neutral-700 hover:border-primary-300'">
                                <p class="font-semibold">{{ c.name }}</p>
                                <p v-if="c.discount_percent > 0" class="text-xs text-emerald-600 font-medium">Hemat {{ c.discount_percent }}%</p>
                                <p v-else class="text-xs text-neutral-400">Harga normal</p>
                            </button>
                        </div>
                    </div>

                    <div v-if="planType === 'enterprise'" class="rounded-lg bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-800 p-4">
                        <p class="text-sm font-semibold text-purple-800 dark:text-purple-300">Paket Enterprise (On-Premise)</p>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Server dikelola client. Bayar satu kali. Request fitur di-charge terpisah.</p>
                    </div>

                    <!-- Price Preview -->
                    <div v-if="pricePreview && planType !== 'enterprise' && planType !== 'trial'" class="rounded-lg bg-neutral-50 dark:bg-neutral-800/50 p-4 space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-neutral-500">{{ pricePreview.qty }} × Rp{{ pricePreview.unitPrice.toLocaleString('id-ID') }}</span><span>Rp{{ pricePreview.subtotal.toLocaleString('id-ID') }}</span></div>
                        <div v-if="pricePreview.discount > 0" class="flex justify-between"><span class="text-emerald-600">Diskon {{ pricePreview.discountPct }}%</span><span class="text-emerald-600">-Rp{{ pricePreview.discount.toLocaleString('id-ID') }}</span></div>
                        <div class="flex justify-between font-bold text-primary-700 pt-1 border-t border-neutral-200 dark:border-neutral-700">
                            <span>Total per {{ pricePreview.months }} bulan</span><span>Rp{{ pricePreview.total.toLocaleString('id-ID') }}</span>
                        </div>
                    </div>

                    <!-- Nama & Domain -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Nama Koperasi</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" :class="form.errors.name ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="Koperasi Anda" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Domain</label>
                            <div class="flex items-center gap-2">
                                <input v-model="form.domain" type="text" required class="flex-1 px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm font-mono focus:ring-2 focus:ring-primary-500" :class="(form.errors.domain || domainStatus === 'taken') ? 'border-red-500 ring-1 ring-red-500' : domainStatus === 'available' ? 'border-emerald-500 ring-1 ring-emerald-500' : ''" placeholder="koperasi-anda" @input="checkDomain" />
                                <span class="text-sm text-neutral-400 font-mono">.e-koperasi.com</span>
                            </div>
                            <div class="mt-1 space-y-1">
                                <p v-if="form.errors.domain" class="text-xs text-red-500">{{ form.errors.domain }}</p>
                                <span v-if="domainStatus === 'checking'" class="text-xs text-neutral-400">Memeriksa ketersediaan...</span>
                                <span v-else-if="domainStatus === 'available'" class="text-xs text-emerald-600">✅ Domain tersedia</span>
                                <span v-else-if="domainStatus === 'taken'" class="text-xs text-red-500">❌ Domain sudah dipakai</span>
                            </div>
                            <div v-if="domainStatus === 'taken' && domainSuggestions.length" class="mt-1 flex flex-wrap gap-1">
                                <span class="text-xs text-neutral-400">Rekomendasi domain lain:</span>
                                <button v-for="s in domainSuggestions" :key="s" type="button" @click="form.domain = s; checkDomain();"
                                    class="text-xs text-primary-600 hover:underline font-mono cursor-pointer">{{ s }}.e-koperasi.com</button>
                            </div>
                        </div>
                    </div>

                    <div><label class="block text-sm font-medium mb-1.5">Catatan (opsional)</label><textarea v-model="form.notes" rows="2" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" :class="form.errors.notes ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="Informasi tambahan..."></textarea>
                        <p v-if="form.errors.notes" class="text-xs text-red-500 mt-1">{{ form.errors.notes }}</p></div>

                    <!-- Profil Perusahaan -->
                    <div class="pt-4 border-t dark:border-neutral-700">
                        <h3 class="text-sm font-semibold mb-3">Profil Perusahaan</h3>
                        <div class="mb-3"><label class="block text-sm font-medium mb-1.5">Alamat</label><textarea v-model="form.company_address" rows="2" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Alamat..."></textarea>
                            <p v-if="form.errors.company_address" class="text-xs text-red-500 mt-1">{{ form.errors.company_address }}</p></div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div><label class="block text-sm font-medium mb-1.5">Telepon</label><input v-model="form.company_phone" type="text" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" :class="form.errors.company_phone ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="08123456789" />
                            <p v-if="form.errors.company_phone" class="text-xs text-red-500 mt-1">{{ form.errors.company_phone }}</p></div>
                            <div><label class="block text-sm font-medium mb-1.5">Email</label><input v-model="form.company_email" type="email" class="w-full px-4 py-2.5 rounded-lg border dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm focus:ring-2 focus:ring-primary-500" :class="form.errors.company_email ? 'border-red-500 ring-1 ring-red-500' : ''" placeholder="company@email.com" />
                            <p v-if="form.errors.company_email" class="text-xs text-red-500 mt-1">{{ form.errors.company_email }}</p></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Logo</label>
                            <input type="file" accept="image/jpeg,image/png" @change="onLogoChange" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                            <p v-if="form.errors.logo" class="text-xs text-red-500 mt-1">{{ form.errors.logo }}</p>
                            <div v-if="logoPreview" class="mt-2"><img :src="logoPreview" class="h-20 w-auto rounded border" alt="Preview" /></div>
                        </div>
                    </div>

                    <p v-if="form.errors.submit || form.errors.general" class="text-xs text-red-500 mb-2">{{ form.errors.submit || form.errors.general }}</p>
                    <button type="submit" :disabled="form.processing || existingRequest?.status === 'pending' || domainStatus === 'taken'"
                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium cursor-pointer">
                        {{ form.processing ? 'Mengirim...' : 'Ajukan Tenant' }}
                    </button>
                </form>
            </div>
        </div>
    </ClientLayout>
</template>