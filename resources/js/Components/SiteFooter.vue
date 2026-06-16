<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useSiteConfig } from '@/composables/useSiteConfig';

const { brand, footer } = useSiteConfig();

const brandName = computed(() => brand.value.name ?? 'e-Koperasi');
const tagline = computed(() => brand.value.tagline ?? 'Platform digital untuk koperasi Indonesia.');
const sections = computed(() => footer.value?.columns ?? []);
const copyright = computed(() => footer.value?.copyright ?? `&copy; ${new Date().getFullYear()} e-Koperasi. Dibuat di Tabanan, Bali.`);
const taglineSecondary = computed(() => footer.value?.tagline ?? 'Untuk Koperasi Indonesia');
const footerDescription = computed(() => footer.value?.description ?? '');
const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-primary-900 dark:bg-primary-950 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2 md:col-span-1">
                    <Link href="/" class="flex items-center gap-2">
                        <img src="/images/logo-only-white.png" :alt="brandName" class="h-8 w-8" />
                        <span class="font-bold text-lg">{{ brandName }}</span>
                    </Link>
                    <p class="mt-3 text-sm text-primary-100">
                        {{ footerDescription || tagline }}
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

            <div class="mt-12 pt-6 border-t border-primary-700 dark:border-primary-800 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-primary-100 dark:text-primary-200" v-html="copyright" />
                <p class="text-sm text-primary-100 dark:text-primary-200">
                    {{ taglineSecondary }}
                </p>
            </div>
        </div>
    </footer>
</template>

