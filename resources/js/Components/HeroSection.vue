<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSiteConfig } from '@/composables/useSiteConfig';
import { useImageOptimization } from '@/composables/useImageOptimization';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { ArrowRight, PlayCircle, Sparkles, Smartphone, ShieldCheck, Zap, TrendingDown } from 'lucide-vue-next';
import HeroCarousel from '@/Components/HeroCarousel.vue';

const { hero } = useSiteConfig();
const { toWebP, toWebPSmall } = useImageOptimization();
const reveal = useScrollReveal({ staggerMs: 80, threshold: 0.05, rootMargin: '0px 0px -20px 0px' });

const heroScreenshot = '/images/app-screenshots/homepage.png';
const chartScreenshot = '/images/app-screenshots/chart.png';
const badge = computed(() => hero.value?.badge ?? 'Dipercaya 50+ Koperasi · 10K+ Anggota');
const headline = computed(() => hero.value?.headline ?? 'Simpanan & Pinjaman Koperasi Lebih Cerdas');
const subheadline = computed(() => hero.value?.subheadline ?? '');
const primaryCta = computed(() => hero.value?.primary_cta ?? { label: 'Coba Demo Gratis', href: '/demo' });
const secondaryCta = computed(() => hero.value?.secondary_cta ?? { label: 'Lihat Produk', href: '/#produk' });

// Featured app screens for the 3D carousel
const carouselSlides = [
    { src: '/images/app-screenshots/webp/homepage.webp',         srcSmall: '/images/app-screenshots/webp/homepage-sm.webp',         alt: 'e-Koperasi App - Dashboard utama' },
    { src: '/images/app-screenshots/webp/budget-dashboard.webp',  srcSmall: '/images/app-screenshots/webp/budget-dashboard-sm.webp',  alt: 'e-Koperasi App - Budget dashboard' },
    { src: '/images/app-screenshots/webp/chart.webp',             srcSmall: '/images/app-screenshots/webp/chart-sm.webp',             alt: 'e-Koperasi App - Grafik performa' },
    { src: '/images/app-screenshots/webp/transaction.webp',       srcSmall: '/images/app-screenshots/webp/transaction-sm.webp',       alt: 'e-Koperasi App - Transaksi' },
    { src: '/images/app-screenshots/webp/list-angsuran.webp',     srcSmall: '/images/app-screenshots/webp/list-angsuran-sm.webp',     alt: 'e-Koperasi App - Daftar angsuran' },
];
</script>

<template>
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 to-white dark:from-primary-900/20 dark:to-neutral-950">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-200/30 dark:bg-primary-800/20 rounded-full blur-3xl" />
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-primary-100/40 dark:bg-primary-900/20 rounded-full blur-3xl" />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20 lg:pt-20 lg:pb-32 relative">
            <div class="text-center max-w-4xl mx-auto">
                <span :ref="(el: any) => reveal.setItemRef(0, el)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 text-xs font-semibold">
                    <Sparkles class="h-3.5 w-3.5" />
                    {{ badge }}
                </span>

                <h1 :ref="(el: any) => reveal.setItemRef(1, el)" class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-neutral-900 dark:text-white leading-tight">
                    {{ headline }}
                </h1>

                <p :ref="(el: any) => reveal.setItemRef(2, el)" class="mt-6 text-lg sm:text-xl text-neutral-600 dark:text-neutral-300 leading-relaxed max-w-3xl mx-auto">
                    {{ subheadline }}
                </p>

                <div :ref="(el: any) => reveal.setItemRef(3, el)" class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        :href="primaryCta.href"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-primary-600 text-white font-semibold hover:bg-primary-700 transition shadow-sm cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2"
                    >
                        {{ primaryCta.label }}
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <Link
                        :href="secondaryCta.href"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-white text-neutral-700 font-semibold border border-neutral-200 hover:border-neutral-300 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-neutral-800 dark:text-neutral-200 dark:border-neutral-700 dark:hover:border-neutral-600"
                    >
                        <PlayCircle class="h-4 w-4" />
                        {{ secondaryCta.label }}
                    </Link>
                </div>

                <p :ref="(el: any) => reveal.setItemRef(4, el)" class="mt-6 text-sm text-neutral-500 dark:text-neutral-400 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Tanpa kartu kredit · Setup 5 menit · AI Risk Scoring</span>
                </p>
            </div>

            <!-- Trust badges -->
            <div :ref="(el: any) => reveal.setItemRef(5, el)" class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/80 dark:bg-neutral-800/80 border border-neutral-100 dark:border-neutral-700 text-sm">
                    <ShieldCheck class="h-4 w-4 text-emerald-500" />
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Enkripsi AES-256</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/80 dark:bg-neutral-800/80 border border-neutral-100 dark:border-neutral-700 text-sm">
                    <Zap class="h-4 w-4 text-amber-500" />
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Real-time Dashboard</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/80 dark:bg-neutral-800/80 border border-neutral-100 dark:border-neutral-700 text-sm">
                    <TrendingDown class="h-4 w-4 text-primary-500" />
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">NPL Turun 40%</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/80 dark:bg-neutral-800/80 border border-neutral-100 dark:border-neutral-700 text-sm">
                    <Smartphone class="h-4 w-4 text-violet-500" />
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">iOS & Android</span>
                </div>
            </div>

            <!-- 3D Carousel of app screenshots -->
            <div :ref="(el: any) => reveal.setItemRef(6, el)" class="mt-8 lg:mt-12 relative pb-32">
                <!-- Glow effect behind carousel -->
                <div class="hero-glow-pulse absolute inset-0 mx-8 lg:mx-16 bg-primary-500/20 blur-3xl rounded-full" />

                <div class="relative flex justify-center">
                    <HeroCarousel :slides="carouselSlides" />
                </div>

                <div class="mt-4 text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white dark:bg-neutral-800 shadow-md border border-neutral-100 dark:border-neutral-700">
                        <Smartphone class="h-4 w-4 text-primary-600" aria-hidden="true" />
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Tersedia di iOS & Android — Pantau dari mana saja</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
