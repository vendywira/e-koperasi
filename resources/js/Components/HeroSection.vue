<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, PlayCircle, Sparkles } from 'lucide-vue-next';
</script>

<template>
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20 lg:pt-20 lg:pb-32">
            <div class="text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-semibold">
                    <Sparkles class="h-3.5 w-3.5" />
                    Dibuat untuk Koperasi Indonesia
                </span>

                <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-neutral-900 leading-tight">
                    Kelola Koperasi dalam
                    <span class="text-primary-600">Satu Genggaman</span>
                </h1>

                <p class="mt-6 text-lg sm:text-xl text-neutral-600 leading-relaxed">
                    Tunai, kasbon, titipan, pinjaman, gaji, dan tracking lapangan — semua digital, real-time, dengan dukungan AI.
                    <br class="hidden sm:block" />
                    Multi-resort, multi-unit, multi-role. Dari penagih di lapangan sampai ketua.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        href="/demo#konsultasi"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-primary-600 text-white font-semibold hover:bg-primary-700 transition shadow-sm"
                    >
                        Request Demo
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <Link
                        href="/#fitur"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-white text-neutral-700 font-semibold border border-neutral-200 hover:border-neutral-300 transition"
                    >
                        <PlayCircle class="h-4 w-4" />
                        Lihat Fitur
                    </Link>
                </div>

                <p class="mt-6 text-sm text-neutral-500">
                    Dipercaya 50+ koperasi di Bali · Tanpa kartu kredit · Eksplorasi sandbox gratis
                </p>
            </div>

            <div class="mt-12 lg:mt-16 relative">
                <div class="aspect-video bg-neutral-100 rounded-xl border border-neutral-200 shadow-2xl overflow-hidden flex items-center justify-center">
                    <img
                        src="/images/hero-mockup-placeholder.svg"
                        alt="e-Koperasi Dashboard"
                        class="w-full h-full object-cover"
                        loading="eager"
                    />
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 bg-white rounded-lg shadow-lg px-4 py-2 text-xs font-medium text-neutral-600 border border-neutral-100">
                    📱 Tersedia di iOS & Android
                </div>
            </div>
        </div>
    </section>
</template>
