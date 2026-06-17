<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { useSiteConfig } from '@/composables/useSiteConfig';
import { useImageOptimization } from '@/composables/useImageOptimization';
import { useScrollReveal } from '@/composables/useScrollReveal';
import {
    Smartphone,
    CreditCard,
    FileText,
    TrendingDown,
    BarChart3,
    ArrowRight,
    ShieldCheck,
    Zap,
    Brain,
    ListOrdered,
} from 'lucide-vue-next';

const { app_features } = useSiteConfig();

const activeFeature = ref(0);
const featureListRef = ref<HTMLElement | null>(null);
const showScrollIndicator = ref(true);
const prevFeatureIndex = ref(0);
const isTransitioning = ref(false);
const badgeSlide = ref(false);
const { toWebP, toWebPSmall } = useImageOptimization();
const reveal = useScrollReveal({ staggerMs: 80, threshold: 0.05 });

// Apply WebP optimization only for known app-screenshot paths
// CMS-uploaded images (storage URLs) are used as-is
function optUrl(path: string): string {
    if (!path) return '';
    if (path.startsWith('/images/app-screenshots/')) {
        return toWebP(path);
    }
    return path;
}

function optUrlSmall(path: string): string {
    if (!path) return '';
    if (path.startsWith('/images/app-screenshots/')) {
        return toWebPSmall(path);
    }
    return path;
}

// Icon map to resolve icon names from CMS config to lucide components
const iconMap: Record<string, any> = {
    'credit-card': CreditCard,
    'file-text': FileText,
    'trending-down': TrendingDown,
    'bar-chart': BarChart3,
    'brain': Brain,
    'list-ordered': ListOrdered,
    'smartphone': Smartphone,
    'shield': ShieldCheck,
    'zap': Zap,
};

const sectionBadge = computed(() => app_features.value?.badge ?? 'Mobile App');
const sectionTitle = computed(() => app_features.value?.title ?? 'Koperasi di Saku Anggota');
const sectionSubtitle = computed(() => app_features.value?.subtitle ?? '');
const ctaLabel = computed(() => app_features.value?.cta_label ?? 'Coba Demo Sekarang');

interface AppFeatureItem {
    title: string;
    description: string;
    screenshot: string;
    icon: string;
}

const features = computed(() => {
    const items = app_features.value?.features;
    if (items && Array.isArray(items) && items.length > 0) {
        return items.map((f: AppFeatureItem, i: number) => ({
            icon: iconMap[f.icon] || (i === 0 ? CreditCard : i === 1 ? Brain : i === 2 ? BarChart3 : i === 3 ? TrendingDown : i === 4 ? ListOrdered : FileText),
            title: f.title || 'Fitur ' + (i + 1),
            desc: f.description || '',
            screenshot: f.screenshot || '/images/app-screenshots/homepage.png',
        }));
    }
    // Fallback defaults
    return [
        { icon: CreditCard,    title: 'Cek Tabungan & Pinjaman',            desc: 'Anggota bisa melihat saldo tabungan dari potongan pinjaman, status pinjaman, dan riwayat transaksi kapan saja — tanpa perlu ke kantor.', screenshot: '/images/app-screenshots/homepage.png' },
        { icon: Brain,         title: 'AI Risk Scoring & Rekomendasi',      desc: 'Sistem AI menganalisis histori pembayaran dan memberikan rekomendasi approve, review, atau reject pinjaman secara objektif.', screenshot: '/images/app-screenshots/AI-recomendation-approve.png' },
        { icon: BarChart3,     title: 'Dashboard & Laporan',                desc: 'Pantau grafik performa keuangan, profit, dan budget real-time dari HP.', screenshot: '/images/app-screenshots/chart.png' },
        { icon: TrendingDown,  title: 'Jatuh Tempo & Penagihan',            desc: 'Lihat daftar jatuh tempo, status pembayaran, dan route optimization untuk penagih.', screenshot: '/images/app-screenshots/jatuh-tempo.png' },
        { icon: ListOrdered,   title: 'List Angsuran & Transaksi',          desc: 'Kelola daftar angsuran, riwayat transaksi, dan pencatatan harian dalam satu layar.', screenshot: '/images/app-screenshots/list-angsuran.png' },
        { icon: FileText,      title: 'Slip Gaji Digital',                  desc: 'Download slip gaji langsung dari HP. Lihat rincian gaji pokok, tunjangan, potongan kasbon, dan insentif.', screenshot: '/images/app-screenshots/pay-slip-dashboard.png' },
    ];
});

const activeFeatureData = computed(() => features.value[activeFeature.value] || features.value[0]);

const glowColors = ['#059669', '#7c3aed', '#2563eb', '#d97706', '#dc2626', '#0891b2'];
const activeGlow = computed(() => glowColors[activeFeature.value % glowColors.length]);

function setActive(i: number) {
    if (i >= 0 && i < features.value.length && i !== activeFeature.value) {
        prevFeatureIndex.value = activeFeature.value;
        isTransitioning.value = true;
        badgeSlide.value = true;
        // Badges slide out → phone flips → badges slide in
        setTimeout(() => {
            activeFeature.value = i;
        }, 300);
        setTimeout(() => {
            isTransitioning.value = false;
            badgeSlide.value = false;
        }, 800);
        nextTick(() => {
            const container = featureListRef.value;
            if (!container) return;
            const cards = container.querySelectorAll('.feature-card');
            const card = cards[i] as HTMLElement | undefined;
            if (card) {
                const containerRect = container.getBoundingClientRect();
                const cardRect = card.getBoundingClientRect();
                const offset = cardRect.top - containerRect.top - containerRect.height / 2 + cardRect.height / 2;
                container.scrollBy({ top: offset, behavior: 'smooth' });
            }
        });
    }
}

function handleScroll(e: Event) {
    const el = e.target as HTMLElement;
    if (!el) { return; }
    showScrollIndicator.value = el.scrollHeight - el.scrollTop - el.clientHeight > 20;
}
</script>

<template>
    <section id="aplikasi" class="py-20 lg:py-28 bg-white dark:bg-neutral-950 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div :ref="(el: any) => reveal.setItemRef(0, el)" class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 text-xs font-semibold mb-4">
                    <Smartphone class="h-3.5 w-3.5" />
                    {{ sectionBadge }}
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    {{ sectionTitle }}
                </h2>
                <p v-if="sectionSubtitle" class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    {{ sectionSubtitle }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Phone Mockup with real screenshot -->
                <div :ref="(el: any) => reveal.setItemRef(1, el)" class="flex justify-center order-1 lg:order-1">
                    <div class="relative">
                        <!-- Phone frame with 3D card transition -->
                        <Transition name="phone-flip" mode="out-in">
                            <div :key="activeFeatureData.screenshot" class="relative w-[260px] h-[540px]" style="perspective: 1000px;">
                                <!-- Dynamic glow behind phone -->
                                <div class="absolute -inset-4 blur-3xl rounded-full transition-all duration-700 ease-out" :style="{ background: activeGlow + '30' }"></div>
                                <!-- Phone frame -->
                                <div class="relative w-full h-full bg-neutral-900 dark:bg-neutral-800 rounded-[2.5rem] p-[10px] shadow-2xl border-[3px] border-neutral-200 dark:border-neutral-700">
                                    <div class="w-full h-full rounded-[2rem] overflow-hidden bg-white">
                                        <picture>
                                            <source :srcset="`${optUrlSmall(activeFeatureData.screenshot)} 640w, ${optUrl(activeFeatureData.screenshot)} 1280w`" sizes="(max-width: 768px) 220px, 260px" type="image/webp" />
                                            <img :src="optUrl(activeFeatureData.screenshot)" :alt="activeFeatureData.title" class="w-full h-full object-cover object-top" loading="lazy" width="260" height="540" />
                                        </picture>
                                    </div>
                                </div>
                            </div>
                        </Transition>

                        <!-- Floating badges (slide-lock animation) -->
                        <div class="absolute -top-3 -right-6 z-30 bg-white dark:bg-neutral-800 rounded-xl shadow-lg px-3 py-2 border border-neutral-100 dark:border-neutral-700 flex items-center gap-2 transition-all duration-[400ms] ease-in-out" :class="badgeSlide ? 'translate-x-14' : 'translate-x-0'">
                            <ShieldCheck class="h-4 w-4 text-emerald-500" />
                            <span class="text-xs font-semibold text-neutral-900 dark:text-white">Enkripsi E2E</span>
                        </div>
                        <div class="absolute -bottom-3 -left-6 z-30 bg-white dark:bg-neutral-800 rounded-xl shadow-lg px-3 py-2 border border-neutral-100 dark:border-neutral-700 flex items-center gap-2 transition-all duration-[400ms] ease-in-out" :class="badgeSlide ? '-translate-x-14' : 'translate-x-0'">
                            <Zap class="h-4 w-4 text-amber-500" />
                            <span class="text-xs font-semibold text-neutral-900 dark:text-white">Real-time</span>
                        </div>
                    </div>
                </div>

                <!-- Feature List -->
                <div :ref="(el: any) => reveal.setItemRef(2, el)" class="order-2 lg:order-2">
                    <!-- Scrollable feature list -->
                    <div class="relative overflow-hidden">
                        <div ref="featureListRef" @scroll="handleScroll" class="space-y-3 max-h-[17rem] sm:max-h-[31rem] overflow-y-auto overflow-x-hidden overscroll-contain pr-1.5 scrollbar-feature">
                            <div v-for="(feature, i) in features" :key="feature.title" @click="setActive(i)" :class="['feature-card p-5 rounded-xl border-2 cursor-pointer transition-all duration-200', activeFeature === i ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20 shadow-lg' : 'border-transparent bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700']">
                                <div class="flex items-start gap-4">
                                    <div :class="['h-11 w-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors', activeFeature === i ? 'bg-primary-600' : 'bg-neutral-200 dark:bg-neutral-700']">
                                        <component :is="feature.icon" :class="['h-5 w-5 transition-colors', activeFeature === i ? 'text-white' : 'text-neutral-500 dark:text-neutral-400']" />
                                    </div>
                                    <div>
                                        <h4 :class="['text-base font-bold transition-colors', activeFeature === i ? 'text-primary-700 dark:text-primary-300' : 'text-neutral-900 dark:text-white']">{{ feature.title }}</h4>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-1 leading-relaxed">{{ feature.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="showScrollIndicator" class="pointer-events-none absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white dark:from-neutral-950 to-transparent"></div>

                        <Transition enter-active-class="transition-all duration-500 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-300 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                            <div v-if="showScrollIndicator" class="absolute bottom-1 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-0.5 animate-bounce">
                                <span class="text-[9px] font-medium text-neutral-400 dark:text-neutral-500 whitespace-nowrap">Scroll untuk lihat lebih</span>
                                <svg class="w-4 h-4 text-neutral-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </Transition>
                    </div>

                    <div class="mt-8">
                        <a href="/demo" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-primary-600 text-white font-semibold hover:bg-primary-700 transition text-sm">
                            {{ ctaLabel }}
                            <ArrowRight class="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<!-- Custom thin scrollbar for feature list -->
<style scoped>
.scrollbar-feature::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-feature::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-feature::-webkit-scrollbar-thumb {
    background: #d4d4d4;
    border-radius: 999px;
}
.dark .scrollbar-feature::-webkit-scrollbar-thumb {
    background: #404040;
}
.scrollbar-feature::-webkit-scrollbar-thumb:hover {
    background: #a3a3a3;
}
.dark .scrollbar-feature::-webkit-scrollbar-thumb:hover {
    background: #525252;
}

/* Slide-lock transitions for floating badges */
.door-right-enter-active {
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 20;
}
.door-right-leave-active {
    transition: all 0.25s cubic-bezier(0.5, 0, 0.75, 0);
    z-index: 5;
}
.door-right-enter-from {
    opacity: 0;
    transform: translateX(60px);
}
.door-right-enter-to {
    opacity: 1;
    transform: translateX(0);
}
.door-right-leave-to {
    opacity: 0;
    transform: translateX(-40px);
}

.door-left-enter-active {
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 20;
}
.door-left-leave-active {
    transition: all 0.25s cubic-bezier(0.5, 0, 0.75, 0);
    z-index: 5;
}
.door-left-enter-from {
    opacity: 0;
    transform: translateX(-60px);
}
.door-left-enter-to {
    opacity: 1;
    transform: translateX(0);
}
.door-left-leave-to {
    opacity: 0;
    transform: translateX(40px);
}

/* 3D Phone Flip transition — rotates the entire phone frame */
.phone-flip-enter-active {
    transition: all 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    z-index: 10;
}
.phone-flip-leave-active {
    transition: all 0.35s cubic-bezier(0.5, 0, 0.75, 0);
    z-index: 5;
}
.phone-flip-enter-from {
    opacity: 0;
    transform: scale(0.8) rotateY(-35deg) rotateX(8deg) translateX(40px) translateY(-10px);
    filter: blur(6px);
}
.phone-flip-leave-to {
    opacity: 0;
    transform: scale(0.85) rotateY(25deg) rotateX(-5deg) translateX(-30px) translateY(8px);
    filter: blur(3px);
}
.phone-flip-enter-to {
    opacity: 1;
    transform: scale(1) rotateY(0) rotateX(0) translateX(0) translateY(0);
    filter: blur(0);
}
.phone-flip-leave-from {
    opacity: 1;
    transform: scale(1) rotateY(0) rotateX(0) translateX(0) translateY(0);
    filter: blur(0);
}
</style>
