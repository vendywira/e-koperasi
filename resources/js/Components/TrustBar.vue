<script setup lang="ts">
const partners = [
    { name: 'KSU Tabanan Jaya', src: '/images/partners/ksu-tabanan-jaya.svg' },
    { name: 'KSU Mitra Mandiri', src: '/images/partners/ksu-mitra-mandiri.svg' },
    { name: 'KSU Bali Sejahtera', src: '/images/partners/ksu-bali-sejahtera.svg' },
    { name: 'KSU Empu Sari', src: '/images/partners/ksu-empu-sari.svg' },
    { name: 'KSU Karya Bersama', src: '/images/partners/ksu-karya-bersama.svg' },
];
</script>

<template>
    <section class="py-12 border-y border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-6">
                Dipercaya oleh koperasi di seluruh Bali
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6 items-center">
                <img
                    v-for="p in partners"
                    :key="p.name"
                    :src="p.src"
                    :alt="p.name"
                    class="h-8 w-auto mx-auto opacity-60 hover:opacity-100 transition grayscale hover:grayscale-0"
                    loading="lazy"
                />
            </div>
        </div>
    </section>
</template>
