<script setup lang="ts">
import {
    ShieldCheck,
    Zap,
    Headphones,
    TrendingDown,
    Users,
    Banknote,
    ArrowRight,
    Check,
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useScrollReveal } from '@/composables/useScrollReveal';

const reveal = useScrollReveal({ staggerMs: 80, threshold: 0.1 });

const reasons = [
    {
        icon: ShieldCheck,
        title: 'Kepatuhan UU PDP',
        desc: 'Enkripsi AES-256, audit trail, dan consent management sesuai regulasi Indonesia. Data Anda aman.',
        stat: 'AES-256',
        statLabel: 'Enkripsi',
    },
    {
        icon: Zap,
        title: 'AI Risk Scoring',
        desc: 'Sistem cerdas yang menganalisis histori pembayaran untuk approval pinjaman lebih objektif dan akurat.',
        stat: '0–100',
        statLabel: 'Skor Risiko',
    },
    {
        icon: TrendingDown,
        title: 'NPL Turun 40%',
        desc: 'Koperasi yang menggunakan e-Koperasi rata-rata mengalami penurunan pinjaman macet hingga 40%.',
        stat: '40%',
        statLabel: 'Penurunan NPL',
    },
    {
        icon: Headphones,
        title: 'Support WhatsApp',
        desc: 'Tim kami siap membantu via WhatsApp dalam 1x24 jam. Tanpa ticket system yang ribet.',
        stat: '1×24',
        statLabel: 'Jam Respon',
    },
    {
        icon: Users,
        title: 'Multi-Role Bertingkat',
        desc: '6 role berbeda — dari PDL sampai Ketua — masing-masing dengan hak akses yang tepat.',
        stat: '6',
        statLabel: 'Role Access',
    },
    {
        icon: Banknote,
        title: 'Tanpa Biaya Setup',
        desc: 'Tidak ada biaya tersembunyi. Harga transparan, batalkan kapan saja tanpa penalty.',
        stat: 'Rp 0',
        statLabel: 'Biaya Setup',
    },
];

const stats = [
    { value: '50+', label: 'Koperasi Aktif' },
    { value: '10K+', label: 'Anggota Terdaftar' },
    { value: '5+', label: 'Tahun Operasi' },
    { value: '99.9%', label: 'Uptime SLA' },
];
</script>

<template>
    <section id="mengapa-kami" class="py-20 lg:py-28 bg-neutral-50 dark:bg-neutral-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Bar -->
            <div :ref="(el: any) => reveal.setItemRef(0, el)" class="bg-primary-600 dark:bg-primary-700 rounded-2xl p-8 mb-16">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                    <div v-for="s in stats" :key="s.label">
                        <p class="text-3xl sm:text-4xl font-bold text-white">{{ s.value }}</p>
                        <p class="text-sm text-primary-100 dark:text-primary-200 mt-1">{{ s.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div :ref="(el: any) => reveal.setItemRef(1, el)" class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    Kenapa 50+ Koperasi Memilih e-Koperasi?
                </h2>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    Bukan cuma soal teknologi — kami memahami bisnis koperasi Indonesia karena kami juga koperasi.
                </p>
            </div>

            <!-- Reasons Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="(reason, i) in reasons"
                    :key="reason.title"
                    :ref="(el: any) => reveal.setItemRef(i + 2, el)"
                    class="group bg-white dark:bg-neutral-800 rounded-2xl p-6 border border-neutral-100 dark:border-neutral-700 hover:border-primary-200 dark:hover:border-primary-500 hover:shadow-xl transition-all duration-300"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="h-12 w-12 rounded-xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center group-hover:bg-primary-600 transition-colors">
                            <component :is="reason.icon" class="h-6 w-6 text-primary-600 dark:text-primary-400 group-hover:text-white transition-colors" />
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ reason.stat }}</p>
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ reason.statLabel }}</p>
                        </div>
                    </div>
                    <h4 class="text-base font-bold text-neutral-900 dark:text-white mb-2">{{ reason.title }}</h4>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">{{ reason.desc }}</p>
                </div>
            </div>

            <!-- Origin Story -->
            <div :ref="(el: any) => reveal.setItemRef(reasons.length + 2, el)" class="mt-16 bg-white dark:bg-neutral-800 rounded-2xl p-8 lg:p-12 border border-neutral-100 dark:border-neutral-700">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400 text-xs font-semibold mb-4">
                            Cerita Kami
                        </span>
                        <h3 class="text-2xl font-bold text-neutral-900 dark:text-white mb-4">
                            Dibuat oleh Koperasi, Untuk Koperasi
                        </h3>
                        <p class="text-neutral-600 dark:text-neutral-300 leading-relaxed mb-4">
                            e-Koperasi lahir dari kebutuhan nyata <strong class="text-neutral-900 dark:text-white">KSU Tabanan Jaya</strong> — koperasi serba usaha yang melayani 3000+ anggota di Bali. Sistem manual Excel dan WhatsApp tidak lagi memadai.
                        </p>
                        <p class="text-neutral-600 dark:text-neutral-300 leading-relaxed mb-6">
                            Setelah terbukti di internal, kami membuka platform ini untuk koperasi lain di Indonesia. Sekarang dipercaya oleh 50+ koperasi di berbagai sektor.
                        </p>
                        <Link
                            href="/about"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition"
                        >
                            Baca Cerita Lengkap
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-neutral-50 dark:bg-neutral-700/50">
                            <Check class="h-5 w-5 text-primary-600 mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Dibangun dari pengalaman nyata</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Fitur kami bukan teori — semuanya lahir dari masalah harian koperasi</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-neutral-50 dark:bg-neutral-700/50">
                            <Check class="h-5 w-5 text-primary-600 mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Tim yang memahami koperasi</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">CEO kami adalah Ketua Koperasi dengan 15+ tahun pengalaman</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-neutral-50 dark:bg-neutral-700/50">
                            <Check class="h-5 w-5 text-primary-600 mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Terus berkembang bersama pengguna</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">Update fitur berdasarkan feedback langsung dari lapangan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
