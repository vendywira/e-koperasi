<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Check, Sparkles, Building2, Crown } from 'lucide-vue-next';
import { computed } from 'vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { useSiteConfig } from '@/composables/useSiteConfig';

const reveal = useScrollReveal({ staggerMs: 80 });
const { pricing } = useSiteConfig();

const tiers = computed(() => Object.values(pricing.value.tiers || {}));

const tierIcons: Record<string, any> = {
    starter: Sparkles,
    premium: Crown,
    bisnis: Crown,
    enterprise: Building2,
};
</script>

<template>
    <section id="harga" class="py-20 lg:py-28 bg-white dark:bg-neutral-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div :ref="(el: any) => reveal.setItemRef(0, el)" class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    {{ pricing.value?.title || 'Harga Sederhana & Transparan' }}
                </h2>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    {{ pricing.value?.subtitle || 'Bayar bulanan. Tanpa biaya setup tersembunyi. Batalkan kapan saja.' }}
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto items-stretch">
                <div
                    v-for="(tier, i) in tiers"
                    :key="tier.name"
                    :ref="(el: any) => reveal.setItemRef(i + 1, el)"
                    :class="[
                        'relative rounded-xl p-8 border-2 flex flex-col transition',
                        tier.highlight
                            ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20 shadow-xl ring-1 ring-primary-600 md:scale-[1.02]'
                            : 'border-neutral-100 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:border-primary-200 dark:hover:border-primary-500',
                    ]"
                >
                    <div
                        v-if="tier.highlight"
                        class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-primary-600 text-white text-xs font-semibold shadow-md whitespace-nowrap"
                    >
                        PALING POPULER
                    </div>

                    <div class="flex items-center gap-2 mb-2">
                        <component
                            :is="tierIcons[tier.name.toLowerCase()] || Sparkles"
                            class="h-5 w-5 text-primary-600 dark:text-primary-400"
                            aria-hidden="true"
                        />
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ tier.name }}</h3>
                    </div>

                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4 min-h-[2.5rem]">
                        {{ tier.tagline }}
                    </p>

                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-4xl font-bold text-neutral-900 dark:text-white">{{ tier.price }}</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">/ {{ tier.period }}</span>
                    </div>

                    <ul class="mt-6 space-y-3 flex-1">
                        <li
                            v-for="feature in tier.features"
                            :key="feature"
                            class="flex items-start gap-2 text-sm text-neutral-700 dark:text-neutral-300"
                        >
                            <Check class="h-4 w-4 text-primary-600 mt-0.5 flex-shrink-0" />
                            {{ feature }}
                        </li>
                    </ul>

                    <Link
                        href="/demo#konsultasi"
                        :class="[
                            'mt-8 block w-full text-center px-4 py-3 rounded-md font-semibold transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 cursor-pointer',
                            tier.highlight
                                ? 'bg-primary-600 text-white hover:bg-primary-700'
                                : 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-neutral-600',
                        ]"
                    >
                        {{ tier.name === 'Enterprise' ? 'Hubungi Kami' : `Pilih ${tier.name}` }}
                    </Link>
                </div>
            </div>

            <p class="mt-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
                {{ pricing.value?.footer_note || 'Semua paket include: dashboard real-time, mobile app iOS/Android, backup otomatis, dan kepatuhan UU PDP.' }}
            </p>
        </div>
    </section>
</template>
