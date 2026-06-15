<script setup lang="ts">
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import SeoHead from '@/Components/SeoHead.vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white">
        <SeoHead :title="title || 'e-Koperasi - Platform Digital Koperasi Indonesia'" />
        <SiteHeader />
        <main class="flex-1">
            <slot />
        </main>
        <SiteFooter />
    </div>
</template>
