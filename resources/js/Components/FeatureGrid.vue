<script setup lang="ts">
import {
    Banknote,
    CreditCard,
    PiggyBank,
    TrendingUp,
    Cog,
    FileText,
    BarChart3,
    Building2,
    Camera,
} from 'lucide-vue-next';

const features = [
    {
        icon: Banknote,
        title: 'Manajemen Tunai Harian',
        desc: 'Catat kas masuk/keluar, rekonsiliasi real-time, laporan harian otomatis.',
    },
    {
        icon: CreditCard,
        title: 'Manajemen Kasbon (BON)',
        desc: 'Ajukan, approval, tracking saldo BON, potong otomatis dari gaji.',
    },
    {
        icon: PiggyBank,
        title: 'Titipan & Simpanan',
        desc: 'Simpanan pokok, wajib, sukarela. Titipan penagih. Approval flow.',
    },
    {
        icon: TrendingUp,
        title: 'Kontrol Pinjaman + AI',
        desc: 'Pinjaman gadai/agunan (foto+video), AI risk scoring, GPS tracking, route optimization.',
    },
    {
        icon: Cog,
        title: 'Rule Engine Dinamis',
        desc: 'Atur logika bisnis tanpa coding. Insentif, pinjaman, approval bertingkat.',
    },
    {
        icon: FileText,
        title: 'Slip Gaji Digital',
        desc: 'Cetak PDF, tunjangan & insentif otomatis, integrasi simpanan & kasbon.',
    },
    {
        icon: BarChart3,
        title: 'Real-time Dashboard',
        desc: 'Performance, saldo lapangan, NPL, target harian, nasabah aktif — semua real-time.',
    },
    {
        icon: Building2,
        title: 'Multi-Resort & Multi-Unit',
        desc: '6 role bertingkat, budgeting per unit, laporan lintas resort.',
    },
    {
        icon: Camera,
        title: 'Absensi Foto + Kalender',
        desc: 'Upload foto absen anggota di meja kerja. Set hari libur untuk loan schedule.',
    },
];
</script>

<template>
    <section id="fitur" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900">
                    9 Fitur Unggulan
                </h2>
                <p class="mt-4 text-lg text-neutral-600">
                    Platform lengkap untuk digitalisasi operasional koperasi. Setiap fitur dirancang berdasarkan kebutuhan nyata koperasi Indonesia.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="(f, idx) in features"
                    :key="f.title"
                    class="group bg-white rounded-xl p-6 border border-neutral-100 hover:border-primary-200 hover:shadow-lg transition"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="h-12 w-12 rounded-lg bg-primary-100 flex items-center justify-center group-hover:bg-primary-600 transition">
                            <component :is="f.icon" class="h-6 w-6 text-primary-600 group-hover:text-white transition" />
                        </div>
                        <span class="text-xs font-mono text-neutral-300">
                            {{ String(idx + 1).padStart(2, '0') }}
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 mb-2">
                        {{ f.title }}
                    </h3>
                    <p class="text-sm text-neutral-600 leading-relaxed">
                        {{ f.desc }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
