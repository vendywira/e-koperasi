<script setup lang="ts">
import { Quote } from 'lucide-vue-next';

const testimonials = [
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
    <section class="py-20 lg:py-28 bg-neutral-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900">
                    Apa Kata Mereka
                </h2>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">
                <div
                    v-for="t in testimonials"
                    :key="t.name"
                    class="bg-white rounded-xl p-8 border border-neutral-100 relative"
                >
                    <Quote class="h-8 w-8 text-primary-200 absolute top-6 right-6" />
                    <p class="text-lg text-neutral-700 leading-relaxed italic">
                        "{{ t.quote }}"
                    </p>
                    <div class="mt-6">
                        <p class="font-semibold text-neutral-900">{{ t.name }}</p>
                        <p class="text-sm text-neutral-500">{{ t.role }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
