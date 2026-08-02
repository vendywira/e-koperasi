<script setup lang="ts">
import { Check, Shield, Zap, Crown } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    plans: any[];
    selectedPlanId?: string | null;
    showFeatures?: boolean;
}>();

const emit = defineEmits<{ (e: 'select', plan: any): void }>();

const tiers = computed(() => {
    return props.plans.map((p) => {
        const cfg = p.pricing_config || {};
        const isOneTime = cfg.has_cycle === false;
        let price = '', period = '', tagline = '', priceOriginal = '', unlimited = false, discountPct = 0;

        if (p.type === 'trial') {
            price = 'Gratis';
            period = `${p.trial_days} hari`;
            tagline = p.description || 'Coba gratis sebelum berlangganan';
        } else if (isOneTime) {
            const flat = Number(cfg.price || 0);
            discountPct = Number(cfg.discount_percent || 0);
            price = `Rp${Math.round(flat * (1 - discountPct / 100)).toLocaleString('id-ID')}`;
            priceOriginal = discountPct > 0 ? `Rp${flat.toLocaleString('id-ID')}` : '';
            period = 'one-time';
            unlimited = !!cfg.unlimited;
            tagline = p.description || (unlimited ? 'Unlimited resort' : 'One-time payment');
        } else {
            const ppu = cfg.price_per_resort || p.price_per_month / Math.max(1, p.max_resorts);
            price = `Rp${ppu.toLocaleString('id-ID')}`;
            period = 'resort/bln';
            tagline = p.description || `${p.max_resorts} resort`;
        }

        const iconMap: Record<string, any> = { trial: Zap, business: Shield, enterprise: Crown };
        const icon = iconMap[p.type] || Shield;

        return {
            id: p.id,
            name: p.name,
            price,
            priceOriginal,
            discountPct,
            period,
            tagline,
            features: p.features,
            type: p.type,
            highlight: !!p.is_popular,
            unlimited,
            icon,
        };
    });
});

function getBadgeStyle(tier: { type: string }) {
    if (tier.type === 'trial') return 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400';
    if (tier.type === 'enterprise') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
}

function getIconBg(tier: { type: string }) {
    if (tier.type === 'trial') return 'bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400';
    if (tier.type === 'enterprise') return 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400';
    return 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400';
}
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <button
            v-for="tier in tiers"
            :key="tier.id"
            type="button"
            @click="emit('select', props.plans.find(p => p.id === tier.id))"
            class="relative p-4 sm:p-6 min-w-0 rounded-2xl border-2 text-left transition-all duration-200 cursor-pointer group"
            :class="selectedPlanId === tier.id
                ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/10 shadow-lg shadow-emerald-500/10'
                : 'border-neutral-200 dark:border-neutral-700 hover:border-emerald-300 dark:hover:border-emerald-600 hover:shadow-md'"
        >
            <!-- Popular Badge -->
            <div v-if="tier.highlight" class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold whitespace-nowrap shadow-sm">
                POPULER
            </div>

            <!-- Header -->
            <div class="flex items-center gap-2.5 mb-3">
                <div :class="getIconBg(tier)" class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0">
                    <component :is="tier.icon" class="w-4 h-4" />
                </div>
                <div>
                    <span :class="getBadgeStyle(tier)" class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide">
                        {{ tier.type === 'trial' ? 'TRIAL' : tier.type === 'enterprise' ? 'ENTERPRISE' : 'BISNIS' }}
                    </span>
                </div>
            </div>

            <!-- Name & Tagline -->
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white break-words">{{ tier.name }}</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed break-words">{{ tier.tagline }}</p>

            <!-- Price Section -->
            <div class="mt-4 mb-4">
                <p v-if="tier.priceOriginal" class="text-sm text-neutral-400 line-through mb-0.5 break-words">{{ tier.priceOriginal }}</p>
                <div class="flex items-baseline gap-1 flex-wrap">
                    <span class="text-2xl font-bold text-neutral-900 dark:text-white break-words">{{ tier.price }}</span>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">/ {{ tier.period }}</span>
                </div>
                <p v-if="tier.discountPct > 0" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">
                    Hemat {{ tier.discountPct }}%
                </p>
                <p v-if="tier.unlimited" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                    <svg class="w-3 h-3 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.375a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.375a2.25 2.25 0 002.25 2.25z" /></svg>
                    Unlimited Resort
                </p>
            </div>

            <!-- Features -->
            <ul v-if="showFeatures && tier.features?.length" class="space-y-2 mb-4">
                <li v-for="f in tier.features" :key="f.id || f.feature_text || f" class="flex items-start gap-2 text-xs text-neutral-600 dark:text-neutral-300">
                    <Check class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" />
                    <span class="break-words min-w-0">{{ f.feature_text || f.name || f }}</span>
                </li>
            </ul>

            <!-- Action Button -->
            <div class="mt-auto pt-4 border-t border-neutral-100 dark:border-neutral-800">
                <div class="w-full py-2.5 rounded-lg text-center text-sm font-semibold transition-colors"
                    :class="selectedPlanId === tier.id
                        ? 'bg-emerald-600 text-white'
                        : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 group-hover:bg-emerald-600/10 dark:group-hover:bg-emerald-900/20'"
                >
                    {{ selectedPlanId === tier.id ? 'Terpilih' : 'Pilih Paket' }}
                </div>
            </div>
        </button>
    </div>
</template>
