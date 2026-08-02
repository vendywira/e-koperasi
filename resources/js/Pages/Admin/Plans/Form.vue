<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';

const props = defineProps<{ plan: any }>();
const isEdit = !!props.plan;

const form = useForm({
    name: props.plan?.name || '',
    description: props.plan?.description || '',
    type: props.plan?.type || 'business',
    pricing_config: props.plan?.pricing_config || defaultConfig('business'),
    max_resorts: props.plan?.max_resorts ?? 1,
    price_per_month: props.plan?.price_per_month || 0,
    trial_days: props.plan?.trial_days || 30,
    sort_order: props.plan?.sort_order || 0,
    is_active: props.plan?.is_active ?? true,
    is_default: props.plan?.is_default ?? false,
    is_popular: props.plan?.is_popular ?? false,
    features: props.plan?.features?.map((f: any) => ({ id: f.id || null, feature_text: f.feature_text || '' })) || [{ feature_text: '' }],
});

function defaultConfig(type: string) {
    switch (type) {
        case 'trial': return { price: 0, has_cycle: false };
        case 'enterprise': return { price: 20000000, has_cycle: false, unlimited: true, discount_percent: 20 };
        default: return { price_per_resort: 100000, has_cycle: true };
    }
}

const isOneTime = computed(() => form.pricing_config.has_cycle === false);

const pricingLabel = computed(() => {
    const cfg = form.pricing_config || {};
    if (form.type === 'trial') return `Gratis ${form.trial_days} hari, ${form.max_resorts} resort`;
    if (isOneTime.value) {
        const price = Number(cfg.price || 0);
        const pct = Number(cfg.discount_percent || 0);
        const final = Math.round(price * (1 - pct / 100));
        const original = pct > 0 ? `Rp${price.toLocaleString('id-ID')}` : '';
        return `${original ? original + ' → ' : ''}Rp${final.toLocaleString('id-ID')} (one-time${cfg.unlimited ? ', unlimited' : ''})`;
    }
    return `Rp${Number(cfg.price_per_resort || 100000).toLocaleString('id-ID')}/resort/bln`;
});

function onTypeChange() {
    form.pricing_config = defaultConfig(form.type);
    form.max_resorts = form.type === 'enterprise' ? 0 : 1;
    form.price_per_month = form.type === 'enterprise' ? 20000000 : (form.type === 'business' ? 100000 : 0);
    form.trial_days = form.type === 'trial' ? 30 : 0;
}

function toggleCycle() {
    const cfg = form.pricing_config || {};
    if (isOneTime.value) {
        // switch ke berlangganan (cycle-based)
        form.pricing_config = { price_per_resort: cfg.price ? Math.round(cfg.price / 12) : 100000, has_cycle: true };
    } else {
        // switch ke one-time
        form.pricing_config = { price: (cfg.price_per_resort || 100000) * 12, has_cycle: false, unlimited: true, discount_percent: 0 };
    }
}

function submit() {
    isEdit ? form.put(`/admin/plans/${props.plan.id}`) : form.post('/admin/plans');
}
function addFeature() { form.features.push({ id: null, feature_text: '' }); }
function removeFeature(i: number) { form.features.splice(i, 1); }
</script>

<template>
    <AdminLayout :title="isEdit ? 'Edit Paket' : 'Tambah Paket'">
        <Head :title="(isEdit ? 'Edit' : 'Tambah') + ' Paket'" />
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/admin/plans" class="text-neutral-500 hover:text-neutral-700"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg></Link>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ isEdit ? 'Edit Paket' : 'Tambah Paket Baru' }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1">Nama Paket</label><input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /><p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p></div>

                    <div><label class="block text-sm font-medium mb-1">Tipe Paket</label>
                        <select v-model="form.type" @change="onTypeChange" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700">
                            <option value="trial">Trial (gratis)</option>
                            <option value="business">Business (per resort)</option>
                            <option value="enterprise">Enterprise (on-premise)</option>
                        </select>
                    </div>

                    <div v-if="form.type !== 'trial'">
                        <label class="block text-sm font-medium mb-1">Model Penagihan</label>
                        <div class="flex gap-2">
                            <button type="button" @click="toggleCycle" class="px-3 py-2 border-2 rounded-lg text-sm transition cursor-pointer"
                                :class="!isOneTime ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 dark:border-neutral-700'">
                                Per Siklus
                            </button>
                            <button type="button" @click="toggleCycle" class="px-3 py-2 border-2 rounded-lg text-sm transition cursor-pointer"
                                :class="isOneTime ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 dark:border-neutral-700'">
                                One-Time
                            </button>
                        </div>
                    </div>

                    <div v-if="!isOneTime && form.type !== 'trial'">
                        <label class="block text-sm font-medium mb-1">Harga per Resort (Rp)</label>
                        <input v-model.number="form.pricing_config.price_per_resort" type="number" min="0" step="1000" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                    </div>

                    <div v-if="isOneTime">
                        <label class="block text-sm font-medium mb-1">Harga One-Time (Rp)</label>
                        <input v-model.number="form.pricing_config.price" type="number" min="0" step="100000" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                    </div>

                    <div v-if="isOneTime">
                        <label class="block text-sm font-medium mb-1">Diskon (%)</label>
                        <input v-model.number="form.pricing_config.discount_percent" type="number" min="0" max="100" step="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                        <p class="text-xs text-neutral-400 mt-1">Tampil sebagai harga coret. 0 = tanpa diskon.</p>
                    </div>

                    <div v-if="isOneTime">
                        <label class="block text-sm font-medium mb-1">Resort</label>
                        <div class="flex gap-2 items-center">
                            <button type="button" @click="form.pricing_config.unlimited = true" class="px-3 py-2 border-2 rounded-lg text-sm transition cursor-pointer"
                                :class="form.pricing_config.unlimited ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 dark:border-neutral-700'">
                                Unlimited
                            </button>
                            <button type="button" @click="form.pricing_config.unlimited = false" class="px-3 py-2 border-2 rounded-lg text-sm transition cursor-pointer"
                                :class="!form.pricing_config.unlimited ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 dark:border-neutral-700'">
                                Terbatas
                            </button>
                        </div>
                    </div>

                    <div v-if="!isOneTime && form.type !== 'trial'">
                        <label class="block text-sm font-medium mb-1">Maks Resort</label>
                        <input v-model="form.max_resorts" type="number" min="1" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                    </div>

                    <div v-if="form.type === 'trial'">
                        <label class="block text-sm font-medium mb-1">Lama Trial (hari)</label>
                        <input v-model="form.trial_days" type="number" min="1" max="90" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                    </div>

                    <div><label class="block text-sm font-medium mb-1">Urutan</label><input v-model="form.sort_order" type="number" min="0" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" /></div>
                </div>

                <div class="rounded-lg bg-neutral-50 dark:bg-neutral-800/50 p-3 text-sm flex items-center justify-between">
                    <span class="text-neutral-500">Ringkasan harga:</span>
                    <span class="font-semibold text-primary-700">{{ pricingLabel }}</span>
                </div>

                <div><label class="block text-sm font-medium mb-1">Deskripsi</label><textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700"></textarea></div>

                <div>
                    <div class="flex items-center justify-between mb-2"><label class="text-sm font-medium">Fitur</label><button type="button" @click="addFeature" class="text-xs text-primary-600 hover:underline cursor-pointer">+ Tambah fitur</button></div>
                    <div v-for="(f, i) in form.features" :key="i" class="flex gap-2 mb-2">
                        <input v-model="f.feature_text" type="text" placeholder="Nama fitur" class="flex-1 px-3 py-2 border rounded-lg text-sm dark:bg-neutral-800 dark:border-neutral-700" />
                        <button type="button" @click="removeFeature(i)" v-if="form.features.length > 1" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg cursor-pointer"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2"><input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-neutral-300 dark:border-neutral-600" /><label for="is_active" class="text-sm">Paket aktif</label></div>
                    <div class="flex items-center gap-2"><input v-model="form.is_default" type="checkbox" id="is_default" class="rounded border-neutral-300 dark:border-neutral-600" /><label for="is_default" class="text-sm">Paket default (preselect client)</label></div>
                    <div class="flex items-center gap-2"><input v-model="form.is_popular" type="checkbox" id="is_popular" class="rounded border-neutral-300 dark:border-neutral-600" /><label for="is_popular" class="text-sm">Paket populer (badge POPULER)</label></div>
                </div>

                <div class="flex gap-3 pt-3">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition disabled:opacity-50 cursor-pointer">{{ isEdit ? 'Simpan Perubahan' : 'Buat Paket' }}</button>
                    <Link href="/admin/plans" class="px-6 py-2.5 border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 transition cursor-pointer">Batal</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
