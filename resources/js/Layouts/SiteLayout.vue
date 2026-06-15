<script setup lang="ts">
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import SeoHead from '@/Components/SeoHead.vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white dark:bg-neutral-950">
        <SeoHead :title="title || 'e-Koperasi - Platform Digital Koperasi Indonesia'" />
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:rounded-md focus:bg-primary-600 focus:text-white focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
            Lewati ke konten utama
        </a>
        <SiteHeader />
        <main id="main-content" class="flex-1" tabindex="-1">
            <slot />
        </main>
        <SiteFooter />
    </div>
</template>
