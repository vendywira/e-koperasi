<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { useSiteConfig } from '@/composables/useSiteConfig';
import { useScrollReveal } from '@/composables/useScrollReveal';

const reveal = useScrollReveal({ staggerMs: 0, threshold: 0.2 });

const { cta } = useSiteConfig();

const section = computed(() => cta.value ?? {});
const heading = computed(() => section.value.heading ?? 'Bawa Koperasi Anda ke Era Digital');
const subtext = computed(() => section.value.subtext ?? 'Eksplorasi sandbox demo gratis. Tidak perlu kartu kredit. 6 akun role siap pakai.');
const primary = computed(() => section.value.primary ?? { label: 'Coba Demo Sekarang', href: '/demo' });
const secondary = computed(() => section.value.secondary ?? { label: 'Request Demo 1-on-1', href: '/demo#konsultasi' });
</script>

<template>
    <section class="py-20 lg:py-28 bg-gradient-to-br from-primary-600 dark:from-primary-800 to-primary-900 dark:to-primary-950 text-white">
        <div :ref="(el: any) => reveal.setItemRef(0, el)" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold">
                {{ heading }}
            </h2>
            <p class="mt-4 text-lg text-primary-100 dark:text-primary-100">
                {{ subtext }}
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <Link
                    :href="primary.href"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-white text-primary-700 font-semibold hover:bg-primary-50 transition"
                >
                    {{ primary.label }}
                    <ArrowRight class="h-4 w-4" />
                </Link>
                <Link
                    :href="secondary.href"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-primary-700 text-white font-semibold border border-primary-500 hover:bg-primary-800 transition"
                >
                    {{ secondary.label }}
                </Link>
            </div>
        </div>
    </section>
</template>

