<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
</script>

<template>
    <section class="py-20 lg:py-28 bg-gradient-to-br from-primary-600 to-primary-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold">
                Bawa Koperasi Anda ke Era Digital
            </h2>
            <p class="mt-4 text-lg text-primary-100">
                Eksplorasi sandbox demo gratis. Tidak perlu kartu kredit. 6 akun role siap pakai.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <Link
                    href="/demo"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-white text-primary-700 font-semibold hover:bg-primary-50 transition"
                >
                    Coba Demo Sekarang
                    <ArrowRight class="h-4 w-4" />
                </Link>
                <Link
                    href="/demo#konsultasi"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md bg-primary-700 text-white font-semibold border border-primary-500 hover:bg-primary-800 transition"
                >
                    Request Demo 1-on-1
                </Link>
            </div>
        </div>
    </section>
</template>
