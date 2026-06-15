<script setup lang="ts">
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Heart, MapPin, Calendar, ArrowRight, Users, Target, Eye } from 'lucide-vue-next';
import { useSiteConfig } from '@/composables/useSiteConfig';
import { computed } from 'vue';

const { about: aboutConfig } = useSiteConfig();
const about = computed<any>(() => aboutConfig.value.company ?? {});
</script>

<template>
    <SiteLayout :title="`${about.short_name} — Tentang ${about.name}`">
        <!-- Hero -->
        <section class="bg-gradient-to-b from-primary-50 to-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-neutral-900">
                    {{ about.hero_title || 'Cerita di Balik e-Koperasi' }}
                </h1>
                <p class="mt-6 text-lg text-neutral-600">
                    {{ about.hero_subtitle || 'Dibangun dari kebutuhan nyata koperasi, untuk koperasi Indonesia.' }}
                </p>
            </div>
        </section>

        <!-- Story -->
        <section id="cerita" class="py-16 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-lg text-neutral-700 leading-relaxed">{{ about.story }}</p>
            </div>
        </section>

        <!-- Mission / Vision -->
        <section class="py-16 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl p-8 border border-neutral-100">
                    <div class="h-12 w-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
                        <Target class="h-6 w-6 text-primary-600" />
                    </div>
                    <h2 class="text-2xl font-bold text-neutral-900 mb-3">Misi</h2>
                    <p class="text-neutral-700 leading-relaxed">{{ about.mission }}</p>
                </div>
                <div class="bg-white rounded-xl p-8 border border-neutral-100">
                    <div class="h-12 w-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
                        <Eye class="h-6 w-6 text-primary-600" />
                    </div>
                    <h2 class="text-2xl font-bold text-neutral-900 mb-3">Visi</h2>
                    <p class="text-neutral-700 leading-relaxed">{{ about.vision }}</p>
                </div>
            </div>
        </section>

        <!-- Values -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-neutral-900 text-center mb-12">Nilai Kami</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="v in about.values"
                        :key="v.title"
                        class="bg-white rounded-xl p-6 border border-neutral-100"
                    >
                        <Heart class="h-8 w-8 text-primary-600 mb-3" />
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">{{ v.title }}</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">{{ v.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team -->
        <section class="py-16 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-neutral-900 text-center mb-12">Tim</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                    <div
                        v-for="member in about.team"
                        :key="member.name"
                        class="bg-white rounded-xl p-6 border border-neutral-100 flex items-start gap-4"
                    >
                        <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xl flex-shrink-0">
                            {{ member.name.split(' ').map(n => n[0]).join('').slice(0, 2) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-neutral-900">{{ member.name }}</h3>
                            <p class="text-sm text-primary-600 font-medium mb-2">{{ member.role }}</p>
                            <p class="text-sm text-neutral-600">{{ member.bio }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Meta -->
        <section class="py-12 bg-white border-t border-neutral-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-neutral-600">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-primary-600" />
                        Didirikan {{ about.founded }}
                    </div>
                    <div class="flex items-center gap-2">
                        <MapPin class="h-4 w-4 text-primary-600" />
                        {{ about.origin }}
                    </div>
                    <div class="flex items-center gap-2">
                        <Users class="h-4 w-4 text-primary-600" />
                        {{ about.legal_name }}
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 bg-primary-600 text-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold">Siap Bekerja Sama?</h2>
                <p class="mt-4 text-primary-100">Konsultasi gratis untuk koperasi Anda.</p>
                <Link
                    href="/demo#konsultasi"
                    class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-md bg-white text-primary-700 font-semibold hover:bg-primary-50 transition"
                >
                    Hubungi Kami
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
        </section>
    </SiteLayout>
</template>
