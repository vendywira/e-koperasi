<script setup lang="ts">
import { computed } from 'vue';

const stats = computed(() => window.siteConfig?.stats || []);
</script>

<template>
    <section class="py-12 bg-primary-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div v-for="s in stats" :key="s.label">
                    <p class="text-3xl sm:text-4xl font-bold">{{ s.value }}</p>
                    <p class="text-sm text-primary-100 mt-1">{{ s.label }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
