<script setup lang="ts">
import { Quote } from 'lucide-vue-next';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { useSiteConfig } from '@/composables/useSiteConfig';

const reveal = useScrollReveal({ staggerMs: 100 });

const { testimonials: testimonialsConfig } = useSiteConfig();

type Testimonial = { quote: string; name: string; role: string };

const props = defineProps<{ items?: Testimonial[] }>();

const testimonials: Testimonial[] =
    (props.items && props.items.length > 0)
        ? props.items
        : (testimonialsConfig.value?.items ?? testimonialsConfig.value) ?? [
            {
                quote: 'Sebelum pakai e-Koperasi, kami tutup buku butuh 2 jam. Sekarang 15 menit sudah selesai. Rekor NPL kami turun 40%.',
                name: 'I Wayan Sudirta',
                role: 'Ketua KSU Tabanan Jaya',
            },
            {
                quote: 'Route optimization-nya sangat membantu tim penagih kami. Sekarang mereka bisa visit 30% lebih banyak nasabah per hari.',
                name: 'Ni Putu Sari',
                role: 'Koordinator KSU Mitra Mandiri',
            },
        ];
</script>

<template>
    <section id="testimoni" class="py-20 lg:py-28 bg-neutral-50 dark:bg-neutral-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div :ref="(el: any) => reveal.setItemRef(0, el)" class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    Apa Kata Mereka
                </h2>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">
                <div
                    v-for="(t, i) in testimonials"
                    :key="t.name"
                    :ref="(el: any) => reveal.setItemRef(i + 1, el)"
                    class="relative bg-white dark:bg-neutral-800 rounded-xl p-8 pl-12 border border-neutral-100 dark:border-neutral-700"
                >
                    <Quote
                        class="h-7 w-7 text-primary-300 dark:text-primary-500/50 absolute top-7 left-4"
                        aria-hidden="true"
                    />
                    <p class="text-lg text-neutral-700 dark:text-neutral-200 leading-relaxed italic">
                        {{ t.quote }}
                    </p>
                    <div class="mt-6">
                        <p class="font-semibold text-neutral-900 dark:text-white">{{ t.name }}</p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ t.role }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
