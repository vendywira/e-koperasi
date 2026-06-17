<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MobileNav from './MobileNav.vue';
import { Menu, X, Sun, Moon } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';
import { useSiteConfig } from '@/composables/useSiteConfig';

const isScrolled = ref(false);
const mobileOpen = ref(false);
const { theme, toggleTheme } = useTheme();
const { nav: navConfig, brand } = useSiteConfig();

const nav = computed(() => navConfig.value.items ?? []);
const brandName = computed(() => brand.value.name ?? 'e-Koperasi');

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
            isScrolled ? 'bg-white/80 dark:bg-neutral-950/80 backdrop-blur-md border-b border-neutral-100 dark:border-neutral-800' : 'bg-transparent',
        ]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <Link href="/" class="flex items-center gap-2">
                <img src="/images/logo-only-gold.png" alt="e-Koperasi" class="h-8 w-8" />
                <span class="font-bold text-lg text-neutral-900 dark:text-white">{{ brandName }}</span>
            </Link>

            <nav class="hidden md:flex items-center gap-8">
                <a
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="text-sm font-medium text-neutral-600 hover:text-primary-600 dark:text-neutral-300 dark:hover:text-primary-400 transition"
                >
                    {{ item.label }}
                </a>
            </nav>

            <div class="hidden md:flex items-center gap-3">
                <button
                    @click="toggleTheme"
                    class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                    aria-pressed="true"
                >
                    <Moon v-if="theme === 'dark'" class="h-4 w-4" />
                    <Sun v-else class="h-4 w-4" />
                </button>
                <Link
                    href="/demo"
                    class="text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-sm"
                >
                    Coba Demo
                </Link>
                <Link
                    href="/client/login"
                    class="text-sm font-medium text-neutral-600 hover:text-primary-600 dark:text-neutral-400 dark:hover:text-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-sm"
                >
                    Login Client
                </Link>
                <Link
                    href="/demo#konsultasi"
                    class="inline-flex items-center gap-1 px-4 py-2 rounded-md bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                    Request Demo
                </Link>
            </div>

            <div class="md:hidden flex items-center gap-1">
                <button
                    @click="toggleTheme"
                    class="p-2 rounded-md text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500"
                    :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                >
                    <Moon v-if="theme === 'dark'" class="h-5 w-5" />
                    <Sun v-else class="h-5 w-5" />
                </button>
                <button
                    class="p-2 rounded-md text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    @click="mobileOpen = !mobileOpen"
                    aria-label="Toggle menu"
                >
                    <X v-if="mobileOpen" class="h-6 w-6" />
                    <Menu v-else class="h-6 w-6" />
                </button>
            </div>
        </div>

        <MobileNav :open="mobileOpen" :items="nav" @close="mobileOpen = false" />
    </header>
</template>
