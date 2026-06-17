<script setup lang="ts">
import SiteLayout from '@/Layouts/SiteLayout.vue';

defineProps<{ type: 'privacy' | 'terms' | 'pdp' }>();

const content = {
    privacy: {
        title: 'Kebijakan Privasi',
        sections: [
            { heading: 'Data yang Kami Kumpulkan', body: 'Kami mengumpulkan data yang Anda berikan secara langsung melalui form demo request, termasuk nama, jabatan, nama koperasi, jumlah anggota, dan nomor WhatsApp.' },
            { heading: 'Penggunaan Data', body: 'Data digunakan untuk menghubungi Anda terkait permintaan demo dan informasi produk e-Koperasi.' },
            { heading: 'Penyimpanan Data', body: 'Data disimpan di server yang aman dan tidak akan dibagikan ke pihak ketiga tanpa persetujuan Anda.' },
            { heading: 'Hak Anda', body: 'Anda dapat meminta penghapusan data Anda kapan saja dengan menghubungi halo@e-koperasi.com.' },
        ],
    },
    terms: {
        title: 'Syarat & Ketentuan',
        sections: [
            { heading: 'Penerimaan Syarat', body: 'Dengan menggunakan layanan e-Koperasi, Anda setuju dengan syarat dan ketentuan ini.' },
            { heading: 'Layanan', body: 'e-Koperasi menyediakan platform SaaS untuk manajemen koperasi. Fitur dan harga dapat berubah sewaktu-waktu.' },
            { heading: 'Tanggung Jawab Pengguna', body: 'Pengguna bertanggung jawab atas keakuratan data yang dimasukkan dan menjaga kerahasiaan kredensial.' },
            { heading: 'Pembatalan', body: 'Layanan dapat dibatalkan kapan saja melalui dashboard admin atau menghubungi tim support.' },
        ],
    },
    pdp: {
        title: 'Kepatuhan UU PDP',
        sections: [
            { heading: 'Dasar Hukum', body: 'e-Koperasi berkomitmen penuh terhadap kepatuhan terhadap Undang-Undang No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP).' },
            { heading: 'Prinsip Pemrosesan Data', body: 'Data pribadi diproses secara sah, transparan, dan terbatas pada tujuan yang telah disampaikan kepada pengguna.' },
            { heading: 'Hak Subjek Data', body: 'Setiap pemilik data berhak mengakses, memperbaiki, menghapus, dan menarik persetujuan pemrosesan data kapan saja.' },
            { heading: 'Keamanan Data', body: 'Kami menerapkan enkripsi AES-256, backup harian, dan audit trail untuk melindungi data pribadi sesuai standar UU PDP.' },
        ],
    },
};
</script>

<template>
    <SiteLayout :title="content[type].title">
        <section class="py-20 bg-white dark:bg-neutral-950">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold text-neutral-900 dark:text-white mb-8">{{ content[type].title }}</h1>
                <div class="prose prose-neutral dark:prose-invert max-w-none space-y-6">
                    <div v-for="(section, i) in content[type].sections" :key="i">
                        <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-2">{{ section.heading }}</h2>
                        <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed">{{ section.body }}</p>
                    </div>
                </div>
                <p class="mt-12 text-sm text-neutral-500 dark:text-neutral-400">Terakhir diperbarui: Juni 2026</p>
            </div>
        </section>
    </SiteLayout>
</template>
