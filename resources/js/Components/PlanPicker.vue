<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    plans: any[];
    selectedPlanId?: string | null;
    showFeatures?: boolean;
}>();

const emit = defineEmits<{ (e: 'select', plan: any): void }>();

const tiers = computed(() => {
    return props.plans.map((p, i) => {
        const cfg = p.pricing_config || {};
        let price = '', period = '', tagline = '';
        if (p.type === 'trial') {
            price = 'Gratis';
            period = `${p.trial_days} hari`;
            tagline = p.description || 'Coba gratis';
        } else if (p.type === 'enterprise') {
            price = cfg.price ? `Rp${Number(cfg.price).toLocaleString('id-ID')}` : 'Custom';
            period = 'one-time';
            tagline = p.description || 'Server dikelola client. Unlimited.';
        } else {
            const ppu = cfg.price_per_resort || p.price_per_month / Math.max(1, p.max_resorts);
            price = `Rp${ppu.toLocaleString('id-ID')}`;
            period = 'resort/bln';
            tagline = p.description || `${p.max_resorts} resort`;
        }
        return { id: p.id, name: p.name, price, period, tagline, features: p.features, type: p.type, highlight: i === 1 };
    });
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <button
            v-for="tier in tiers"
            :key="tier.id"
            type="button"
            @click="emit('select', props.plans.find(p => p.id === tier.id))"
            class="relative p-5 rounded-xl border-2 text-left transition cursor-pointer"
            :class="selectedPlanId === tier.id
                ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/10'
                : 'border-neutral-200 dark:border-neutral-700 hover:border-emerald-300 dark:hover:border-emerald-600'"
        >
            <div v-if="tier.highlight" class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-semibold whitespace-nowrap">
                POPULER
            </div>
            <h3 class="font-bold text-neutral-900 dark:text-white">{{ tier.name }}</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ tier.tagline }}</p>
            <p class="text-xl font-bold text-neutral-900 dark:text-white mt-2">
                {{ tier.price }}
                <span class="text-xs font-normal text-neutral-500">/ {{ tier.period }}</span>
            </p>
            <ul v-if="showFeatures && tier.features?.length" class="mt-3 space-y-1.5">
                <li v-for="f in tier.features" :key="f.id || f.feature_text || f" class="flex items-start gap-1.5 text-xs text-neutral-600 dark:text-neutral-300">
                    <Check class="w-3.5 h-3.5 text-emerald-500 mt-0.5 flex-shrink-0" />
                    <span>{{ f.feature_text || f.name || f }}</span>
                </li>
            </ul>
        </button>
    </div>
</template>
