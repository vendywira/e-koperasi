<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MobileNav from './MobileNav.vue';
import { Menu, X } from 'lucide-vue-next';

const isScrolled = ref(false);
const mobileOpen = ref(false);

const nav = [
    { label: 'Fitur', href: '/#fitur' },
    { label: 'Untuk Siapa', href: '/#untuk-siapa' },
    { label: 'Harga', href: '/#harga' },
    { label: 'Tentang', href: '/about' },
];

function onScroll() {
    isScrolled.value = window.scrollY > 10;
}

onMounted(() => window.addEventListener('scroll', onScroll));
onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <header
        :class="[
            'sticky top-0 z-50 transition-all duration-200',
            isScrolled ? 'bg-white/80 backdrop-blur-md border-b border-neutral-100' : 'bg-transparent',
        ]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <Link href="/" class="flex items-center gap-2">
                <img src="/images/logo.svg" alt="e-Koperasi" class="h-8 w-8" />
                <span class="font-bold text-lg text-neutral-900">e-Koperasi</span>
            </Link>

            <nav class="hidden md:flex items-center gap-8">
                <a
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="text-sm font-medium text-neutral-600 hover:text-primary-600 transition"
                >
                    {{ item.label }}
                </a>
            </nav>

            <div class="hidden md:flex items-center gap-3">
                <Link
                    href="/demo"
                    class="text-sm font-medium text-neutral-600 hover:text-neutral-900"
                >
                    Coba Demo
                </Link>
                <Link
                    href="/demo#konsultasi"
                    class="inline-flex items-center gap-1 px-4 py-2 rounded-md bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition"
                >
                    Request Demo
                </Link>
            </div>

            <button
                class="md:hidden p-2"
                @click="mobileOpen = !mobileOpen"
                aria-label="Toggle menu"
            >
                <X v-if="mobileOpen" class="h-6 w-6" />
                <Menu v-else class="h-6 w-6" />
            </button>
        </div>

        <MobileNav :open="mobileOpen" :items="nav" @close="mobileOpen = false" />
    </header>
</template>
