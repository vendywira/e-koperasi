<script setup lang="ts">
import { ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

const faqs = [
    {
        q: 'Berapa lama proses setup?',
        a: 'Biasanya 3-7 hari kerja, tergantung kompleksitas data koperasi Anda. Tim kami akan membantu migrasi data dari sistem lama.',
    },
    {
        q: 'Apakah ada training?',
        a: 'Ya, kami menyediakan training online gratis untuk semua paket, plus on-site training untuk paket Enterprise.',
    },
    {
        q: 'Bisa custom fitur sesuai koperasi kami?',
        a: 'Untuk paket Bisnis dan Enterprise, kami menerima permintaan custom dengan biaya tambahan yang wajar.',
    },
    {
        q: 'Bagaimana dengan keamanan data?',
        a: 'Data dienkripsi at-rest dan in-transit. Backup otomatis harian. Compliance dengan UU PDP Indonesia.',
    },
    {
        q: 'Apakah bisa coba gratis?',
        a: 'Ya, Anda bisa eksplorasi sandbox demo kami dengan 6 akun berbeda role — tanpa perlu daftar.',
    },
    {
        q: 'Apakah support WhatsApp?',
        a: 'Paket Bisnis dan Enterprise include support WhatsApp. Paket Starter via email.',
    },
];

const open = ref<number | null>(0);

function toggle(i: number) {
    open.value = open.value === i ? null : i;
}
</script>

<template>
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900">
                    Pertanyaan Umum
                </h2>
            </div>

            <div class="mt-12 space-y-3">
                <div
                    v-for="(faq, i) in faqs"
                    :key="i"
                    class="border border-neutral-100 rounded-lg overflow-hidden"
                >
                    <button
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-neutral-50 transition"
                        @click="toggle(i)"
                    >
                        <span class="font-semibold text-neutral-900">{{ faq.q }}</span>
                        <ChevronDown
                            :class="['h-5 w-5 text-neutral-400 transition-transform', open === i && 'rotate-180']"
                        />
                    </button>
                    <div
                        v-if="open === i"
                        class="px-6 pb-4 text-sm text-neutral-600 leading-relaxed"
                    >
                        {{ faq.a }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
