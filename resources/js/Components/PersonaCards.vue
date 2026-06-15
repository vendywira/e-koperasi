<script setup lang="ts">
import { User, Wallet, Briefcase, ShieldCheck, Check } from 'lucide-vue-next';

const personas = [
    {
        icon: User,
        role: 'Penagih (PDL)',
        title: 'Untuk Penagih',
        points: [
            'Kelola pinjaman anggota',
            'Catat titipan harian di lapangan',
            'Route otomatis ke peminjam terdekat',
            'Lihat history & status pembayaran',
        ],
    },
    {
        icon: Wallet,
        role: 'Cashier',
        title: 'Untuk Kasir',
        points: [
            'Transaksi tunai harian',
            'BON, suntik dana, titip gaji',
            'Rekonsiliasi saldo real-time',
            'Tutup kas harian otomatis',
        ],
    },
    {
        icon: Briefcase,
        role: 'Pimpinan',
        title: 'Untuk Pimpinan',
        points: [
            'Dashboard KPI real-time',
            'Laporan keuangan & NPL',
            'Approval transaksi bertingkat',
            'Monitoring multi-unit',
        ],
    },
    {
        icon: ShieldCheck,
        role: 'Admin (Ketua)',
        title: 'Untuk Admin',
        points: [
            'Laporan keuangan real-time',
            'Budgeting per unit',
            'Kelola multi-resort & multi-unit',
            'Atur rule engine & approval',
        ],
    },
];
</script>

<template>
    <section id="untuk-siapa" class="py-20 lg:py-28 bg-neutral-50 dark:bg-neutral-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    Untuk Siapa e-Koperasi?
                </h2>
                <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-300">
                    Dirancang untuk seluruh hierarki koperasi — dari penagih di lapangan sampai ketua.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="p in personas"
                    :key="p.role"
                    class="bg-white dark:bg-neutral-800 rounded-xl p-6 border border-neutral-100 dark:border-neutral-700 hover:border-primary-200 dark:hover:border-primary-500 hover:shadow-md transition"
                >
                    <div class="h-12 w-12 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center mb-4">
                        <component :is="p.icon" class="h-6 w-6 text-primary-600" />
                    </div>
                    <p class="text-xs font-semibold text-primary-600 uppercase tracking-wider mb-1">
                        {{ p.role }}
                    </p>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">
                        {{ p.title }}
                    </h3>
                    <ul class="space-y-2">
                        <li
                            v-for="point in p.points"
                            :key="point"
                            class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-300"
                        >
                            <Check class="h-4 w-4 text-primary-600 mt-0.5 flex-shrink-0" />
                            {{ point }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
