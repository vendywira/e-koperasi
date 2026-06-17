<script setup lang="ts">
import { computed } from 'vue';
import { useSiteConfig } from '@/composables/useSiteConfig';
import { useImageOptimization } from '@/composables/useImageOptimization';
import { useScrollReveal } from '@/composables/useScrollReveal';
import {
    Landmark,
    Banknote,
    CalendarDays,
    CalendarClock,
    ShieldCheck,
    TrendingDown,
    Smartphone,
    Check,
    ArrowRight,
    Sparkles,
    Zap,
    Clock,
} from 'lucide-vue-next';

const { products: productsConfig } = useSiteConfig();
const { toWebP, toWebPSmall } = useImageOptimization();
const loanProducts = computed(() => productsConfig.value?.loan ?? []);
const productHighlights = computed(() => productsConfig.value?.highlights ?? []);
const youtubeUrl = computed(() => productsConfig.value?.youtube_url ?? '');

// Scroll-reveal setup for loan product cards
const loanReveal = useScrollReveal({ staggerMs: 100 });
const highlightReveal = useScrollReveal({ staggerMs: 60 });
</script>

<template>
    <section id="produk" class="py-16 lg:py-24 bg-gradient-to-b from-neutral-50 to-white dark:from-neutral-900 dark:to-neutral-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 text-xs font-semibold mb-4">
                    <Landmark class="h-3.5 w-3.5" />
                    Produk Keuangan Koperasi
                </span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 dark:text-white leading-tight">
                    Kelola Pinjaman <span class="text-primary-600">Lebih Cerdas</span> dengan AI
                </h2>
                <p class="mt-5 text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed">
                    Kelola pinjaman anggota dalam satu platform. Transparan, cepat, dan didukung AI risk scoring untuk keputusan yang lebih adil.
                </p>
            </div>

            <!-- Highlights Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                <div
                    v-for="(h, i) in productHighlights"
                    :key="h.label"
                    :ref="(el: any) => highlightReveal.setItemRef(i, el)"
                    class="flex items-center gap-3 bg-white dark:bg-neutral-800 rounded-xl px-5 py-4 border border-neutral-100 dark:border-neutral-700"
                >
                    <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center flex-shrink-0">
                        <component :is="h.icon === 'shield' ? ShieldCheck : h.icon === 'zap' ? Zap : h.icon === 'clock' ? Clock : Sparkles" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <p class="text-sm font-bold text-neutral-900 dark:text-white">{{ h.value }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ h.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Pinjaman Products -->
<!--
            <div>
                <div class="flex items-center gap-3 mb-8">
                    <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                        <Banknote class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-neutral-900 dark:text-white">Produk Pinjaman</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Solusi pembiayaan fleksibel dengan persetujuan cepat & adil</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        v-for="(product, i) in loanProducts"
                        :key="product.name"
                        :ref="(el: any) => loanReveal.setItemRef(i, el)"
                        class="group relative bg-white dark:bg-neutral-800 rounded-2xl overflow-hidden border border-neutral-100 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-500 hover:shadow-xl transition-all duration-300"
                    >
                        &lt;!&ndash; Top accent bar &ndash;&gt;
                        <div
                            :class="[
                                'h-1.5',
                                product.accent === 'primary' ? 'bg-primary-600' : product.accent === 'amber' ? 'bg-amber-500' : 'bg-violet-600'
                            ]"
                        />
                        &lt;!&ndash; Screenshot preview &ndash;&gt;
                        <div v-if="product.screenshot" class="relative h-36 bg-gradient-to-b from-neutral-50 to-neutral-100 dark:from-neutral-700 dark:to-neutral-800 flex items-center justify-center overflow-hidden">
                            <div class="w-16 h-32 bg-neutral-900 dark:bg-neutral-600 rounded-t-lg p-[2px] shadow-lg group-hover:shadow-primary-500/30 group-hover:shadow-xl transition-all duration-300 group-hover:scale-105 group-hover:-translate-y-1">
                                <div class="w-full h-full rounded-t-md overflow-hidden bg-white">
                                    <picture>
                                        <source
                                            :srcset="`${toWebPSmall(product.screenshot)} 640w, ${toWebP(product.screenshot)} 1280w`"
                                            sizes="160px"
                                            type="image/webp"
                                        />
                                        <img
                                            :src="toWebP(product.screenshot)"
                                            :alt="`Screenshot aplikasi ${product.name}`"
                                            class="w-full h-full object-cover object-top"
                                            loading="lazy"
                                            width="64"
                                            height="128"
                                        />
                                    </picture>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div
                                    :class="[
                                        'h-12 w-12 rounded-xl flex items-center justify-center',
                                        product.accent === 'primary' ? 'bg-primary-100 dark:bg-primary-900/40' : product.accent === 'amber' ? 'bg-amber-100 dark:bg-amber-900/40' : 'bg-violet-100 dark:bg-violet-900/40'
                                    ]"
                                >
                                    <component
                                        :is="product.icon === 'calendar-days' ? CalendarDays : product.icon === 'calendar-clock' ? CalendarClock : TrendingDown"
                                        :class="[
                                            'h-6 w-6',
                                            product.accent === 'primary' ? 'text-primary-600 dark:text-primary-400' : product.accent === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-violet-600 dark:text-violet-400'
                                        ]"
                                    />
                                </div>
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                        product.accent === 'primary' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400' : product.accent === 'amber' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400'
                                    ]"
                                >
                                    {{ product.badge }}
                                </span>
                            </div>
                            <h4 class="text-lg font-bold text-neutral-900 dark:text-white mb-1">{{ product.name }}</h4>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">{{ product.description }}</p>

                            <div class="flex items-center gap-4 mb-4 pb-4 border-b border-neutral-100 dark:border-neutral-700">
                                <div class="text-center">
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Bunga</p>
                                    <p class="text-sm font-bold text-neutral-900 dark:text-white">{{ product.rate }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Tenor</p>
                                    <p class="text-sm font-bold text-neutral-900 dark:text-white">{{ product.tenor }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Setuju</p>
                                    <p class="text-sm font-bold text-neutral-900 dark:text-white">{{ product.approval }}</p>
                                </div>
                            </div>

                            <ul class="space-y-1.5">
                                <li
                                    v-for="point in product.benefits"
                                    :key="point"
                                    class="flex items-start gap-2 text-xs text-neutral-600 dark:text-neutral-300"
                                >
                                    <Check class="h-3.5 w-3.5 text-primary-500 mt-0.5 flex-shrink-0" />
                                    {{ point }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
-->

            <!-- YouTube Trailer -->
            <div v-if="youtubeUrl" class="mt-12 text-center">
                <a :href="youtubeUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 px-6 py-3 rounded-full bg-red-600 hover:bg-red-700 text-white font-semibold transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 4-8 4z" />
                    </svg>
                    <span>Tonton Video Trailer</span>
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>

            <!-- Bottom CTA -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary-50 dark:bg-primary-900/30 border border-primary-100 dark:border-primary-800">
                    <Smartphone class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                    <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Semua produk tersedia langsung dari mobile app anggota</span>
                    <ArrowRight class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                </div>
            </div>
        </div>
    </section>
</template>
