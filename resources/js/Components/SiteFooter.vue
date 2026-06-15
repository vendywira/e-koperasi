<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const sections = [
    {
        title: 'Produk',
        links: [
            { label: 'Fitur', href: '/#fitur' },
            { label: 'Harga', href: '/#harga' },
            { label: 'Coba Demo', href: '/demo' },
            { label: 'Roadmap', href: '/about#roadmap' },
        ],
    },
    {
        title: 'Perusahaan',
        links: [
            { label: 'Tentang Kami', href: '/about' },
            { label: 'Cerita e-Koperasi', href: '/about#cerita' },
            { label: 'Kontak', href: 'mailto:halo@e-koperasi.com' },
        ],
    },
    {
        title: 'Legal',
        links: [
            { label: 'Kebijakan Privasi', href: '/privacy' },
            { label: 'Syarat & Ketentuan', href: '/terms' },
        ],
    },
];

const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-primary-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2 md:col-span-1">
                    <Link href="/" class="flex items-center gap-2">
                        <img src="/images/logo-white.svg" alt="e-Koperasi" class="h-8 w-8" />
                        <span class="font-bold text-lg">e-Koperasi</span>
                    </Link>
                    <p class="mt-3 text-sm text-primary-100">
                        Platform digital untuk koperasi Indonesia.
                    </p>
                </div>

                <div v-for="section in sections" :key="section.title">
                    <h3 class="font-semibold text-sm mb-3">{{ section.title }}</h3>
                    <ul class="space-y-2">
                        <li v-for="link in section.links" :key="link.href">
                            <a
                                :href="link.href"
                                class="text-sm text-primary-100 hover:text-white transition"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-primary-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-primary-100">
                    &copy; {{ year }} e-Koperasi. Dibuat di Tabanan, Bali.
                </p>
                <p class="text-sm text-primary-100">
                    🇮🇩 Untuk Koperasi Indonesia
                </p>
            </div>
        </div>
    </footer>
</template>
