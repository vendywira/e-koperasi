<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
    description?: string;
    url?: string;
    image?: string;
}>();
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta name="description" :content="description || 'e-Koperasi - Platform digital koperasi Indonesia. Kelola pinjaman, simpanan, kasbon, gaji, dan operasional koperasi dalam satu platform.'" />
        <meta name="keywords" content="koperasi digital, software koperasi, aplikasi koperasi, manajemen koperasi, e-koperasi" />
        <meta name="author" content="e-Koperasi" />
        <meta name="robots" content="index, follow" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description || 'Platform digital koperasi Indonesia.'" />
        <meta property="og:image" :content="image || '/images/og-image.png'" />
        <meta property="og:url" :content="url || 'https://e-koperasi.com'" />
        <meta property="og:site_name" content="e-Koperasi" />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title" />
        <meta name="twitter:description" :content="description || 'Platform digital koperasi Indonesia.'" />
        <meta name="twitter:image" :content="image || '/images/og-image.png'" />

        <link rel="canonical" :href="url || 'https://e-koperasi.com'" />
    </Head>
</template>
