<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const tiers = computed(() => window.siteConfig?.pricing || []);
</script>

<template>
    <section id="harga" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900">
                    Harga Sederhana & Transparan
                </h2>
                <p class="mt-4 text-lg text-neutral-600">
                    Bayar bulanan. Tanpa biaya setup tersembunyi. Batalkan kapan saja.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div
                    v-for="tier in tiers"
                    :key="tier.name"
                    :class="[
                        'rounded-xl p-8 border-2',
                        tier.highlight
                            ? 'border-primary-600 bg-primary-50 shadow-lg scale-105'
                            : 'border-neutral-100 bg-white',
                    ]"
                >
                    <div v-if="tier.highlight" class="inline-block px-3 py-1 rounded-full bg-primary-600 text-white text-xs font-semibold mb-3">
                        PALING POPULER
                    </div>
                    <h3 class="text-xl font-bold text-neutral-900">{{ tier.name }}</h3>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-4xl font-bold text-neutral-900">{{ tier.price }}</span>
                        <span class="text-sm text-neutral-500">/ {{ tier.period }}</span>
                    </div>
                    <ul class="mt-6 space-y-3">
                        <li
                            v-for="feature in tier.features"
                            :key="feature"
                            class="flex items-start gap-2 text-sm text-neutral-700"
                        >
                            <Check class="h-4 w-4 text-primary-600 mt-0.5 flex-shrink-0" />
                            {{ feature }}
                        </li>
                    </ul>
                    <Link
                        href="/demo#konsultasi"
                        :class="[
                            'mt-8 block w-full text-center px-4 py-3 rounded-md font-semibold transition',
                            tier.highlight
                                ? 'bg-primary-600 text-white hover:bg-primary-700'
                                : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200',
                        ]"
                    >
                        Pilih {{ tier.name }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
