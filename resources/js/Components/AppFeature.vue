<script setup lang="ts">
import { ref } from 'vue';
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

const activeFeature = ref(0);
const { toWebP, toWebPSmall } = useImageOptimization();
const reveal = useScrollReveal({ staggerMs: 80, threshold: 0.05 });

const features = [
    {
        icon: CreditCard,
        title: 'Cek Simpanan & Pinjaman',
        desc: 'Anggota bisa melihat saldo simpanan pokok, wajib, sukarela, dan status pinjaman kapan saja — tanpa perlu ke kantor.',
        screenshot: '/images/app-screenshots/homepage.png',
    },
    {
        icon: Brain,
        title: 'AI Risk Scoring & Rekomendasi',
        desc: 'Sistem AI menganalisis histori pembayaran dan memberikan rekomendasi approve, review, atau reject pinjaman secara objektif.',
        screenshot: '/images/app-screenshots/AI-recomendation-approve.png',
    },
    {
        icon: BarChart3,
        title: 'Dashboard & Laporan',
        desc: 'Pantau grafik performa keuangan, profit, dan budget real-time dariHP.',
        screenshot: '/images/app-screenshots/chart.png',
    },
    {
        icon: TrendingDown,
        title: 'Jatuh Tempo & Penagihan',
        desc: 'Lihat daftar jatuh tempo, status pembayaran, dan route optimization untuk penagih.',
        screenshot: '/images/app-screenshots/jatuh-tempo.png',
    },
    {
        icon: ListOrdered,
        title: 'List Angsuran & Transaksi',
        desc: 'Kelola daftar angsuran, riwayat transaksi, dan pencatatan harian dalam satu layar.',
        screenshot: '/images/app-screenshots/list-angsuran.png',
    },
    {
        icon: FileText,
        title: 'Slip Gaji Digital',
        desc: 'Download slip gaji langsung dari HP. Lihat rincian gaji pokok, tunjangan, potongan kasbon, dan insentif.',
        screenshot: '/images/app-screenshots/pay-slip-dashboard.png',
    },
];

function setActive(i: number) {
    activeFeature.value = i;
}
</script>

<template>
    <section id="aplikasi" class="py-20 lg:py-28 bg-white dark:bg-neutral-950 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div :ref="(el: any) => reveal.setItemRef(0, el)" class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 text-xs font-semibold mb-4">
                    <Smartphone class="h-3.5 w-3.5" />
                    Mobile App
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    Koperasi di Saku Anggota
                </h2>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    Aplikasi mobile untuk anggota koperasi. Cek simpanan, ajukan pinjaman, dan absensi — semua dari HP.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Phone Mockup with real screenshot -->
                <div :ref="(el: any) => reveal.setItemRef(1, el)" class="flex justify-center order-2 lg:order-1">
                    <div class="relative">
                        <!-- Glow behind phone -->
                        <div class="absolute inset-0 bg-primary-500/15 blur-3xl rounded-full" />
                        
                        <!-- Phone frame -->
                        <div class="relative w-[260px] h-[540px] bg-neutral-900 dark:bg-neutral-800 rounded-[2.5rem] p-[10px] shadow-2xl border-2 border-neutral-200 dark:border-neutral-700">
                            <div class="w-full h-full rounded-[2rem] overflow-hidden bg-white relative">
                                <picture>
                                    <source
                                        :srcset="`${toWebPSmall(features[activeFeature].screenshot)} 640w, ${toWebP(features[activeFeature].screenshot)} 1280w`"
                                        sizes="(max-width: 768px) 220px, 260px"
                                        type="image/webp"
                                    />
                                    <img
                                        :src="toWebP(features[activeFeature].screenshot)"
                                        :alt="features[activeFeature].title"
                                        class="w-full h-full object-cover object-top transition-opacity duration-300"
                                        loading="lazy"
                                        width="260"
                                        height="540"
                                    />
                                </picture>
                            </div>
                        </div>

                        <!-- Floating badges -->
                        <div class="absolute -top-3 -right-6 bg-white dark:bg-neutral-800 rounded-xl shadow-lg px-3 py-2 border border-neutral-100 dark:border-neutral-700 flex items-center gap-2">
                            <ShieldCheck class="h-4 w-4 text-emerald-500" />
                            <span class="text-xs font-semibold text-neutral-900 dark:text-white">Enkripsi E2E</span>
                        </div>
                        <div class="absolute -bottom-3 -left-6 bg-white dark:bg-neutral-800 rounded-xl shadow-lg px-3 py-2 border border-neutral-100 dark:border-neutral-700 flex items-center gap-2">
                            <Zap class="h-4 w-4 text-amber-500" />
                            <span class="text-xs font-semibold text-neutral-900 dark:text-white">Real-time</span>
                        </div>
                    </div>
                </div>

                <!-- Feature List -->
                <div :ref="(el: any) => reveal.setItemRef(2, el)" class="order-1 lg:order-2">
                    <div class="space-y-3">
                        <div
                            v-for="(feature, i) in features"
                            :key="feature.title"
                            @click="setActive(i)"
                            :class="[
                                'p-5 rounded-xl border-2 cursor-pointer transition-all duration-200',
                                activeFeature === i
                                    ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20 shadow-lg'
                                    : 'border-transparent bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700',
                            ]"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'h-11 w-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors',
                                        activeFeature === i ? 'bg-primary-600' : 'bg-neutral-200 dark:bg-neutral-700',
                                    ]"
                                >
                                    <component
                                        :is="feature.icon"
                                        :class="['h-5 w-5 transition-colors', activeFeature === i ? 'text-white' : 'text-neutral-500 dark:text-neutral-400']"
                                    />
                                </div>
                                <div>
                                    <h4
                                        :class="[
                                            'text-base font-bold transition-colors',
                                            activeFeature === i ? 'text-primary-700 dark:text-primary-300' : 'text-neutral-900 dark:text-white',
                                        ]"
                                    >
                                        {{ feature.title }}
                                    </h4>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-1 leading-relaxed">
                                        {{ feature.desc }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a
                            href="/demo"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-primary-600 text-white font-semibold hover:bg-primary-700 transition text-sm"
                        >
                            Coba Demo Sekarang
                            <ArrowRight class="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
