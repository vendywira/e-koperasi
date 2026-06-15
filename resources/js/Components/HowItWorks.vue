<script setup lang="ts">
const steps = [
    {
        num: '1',
        title: 'Daftar',
        desc: 'Hubungi tim kami atau coba sandbox demo langsung. Tidak perlu kartu kredit.',
    },
    {
        num: '2',
        title: 'Setup Data',
        desc: 'Tim kami membantu migrasi data anggota, pinjaman, dan saldo dari sistem lama Anda.',
    },
    {
        num: '3',
        title: 'Go Live',
        desc: 'Pelatihan tim, monitoring 1 minggu, dan dukungan penuh selama transisi.',
    },
];
</script>

<template>
    <section class="py-20 lg:py-28 bg-primary-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900">
                    Mulai dalam 3 Langkah
                </h2>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <div
                    v-for="(step, idx) in steps"
                    :key="step.num"
                    class="relative"
                >
                    <div class="bg-white rounded-xl p-8 border border-neutral-100 h-full">
                        <div class="h-12 w-12 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-xl mb-4">
                            {{ step.num }}
                        </div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2">
                            {{ step.title }}
                        </h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            {{ step.desc }}
                        </p>
                    </div>
                    <div
                        v-if="idx < steps.length - 1"
                        class="hidden md:block absolute top-1/2 -right-4 transform -translate-y-1/2 text-primary-300 text-2xl"
                    >
                        →
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
