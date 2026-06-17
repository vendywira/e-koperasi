<script setup lang="ts">
import {Link, usePage, router} from '@inertiajs/vue3';
import {computed, ref, onMounted, onUnmounted} from 'vue';
import {index as cmsIndex} from '@/routes/admin/cms';
import {useTheme} from '@/composables/useTheme';

defineProps<{
    title?: string;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const {theme, toggleTheme} = useTheme();
const sidebarOpen = ref(false);

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebar() {
    sidebarOpen.value = false;
}

function logout() {
    router.post('/logout', {}, {
        onFinish: () => window.location.href = '/login',
    });
}

// Close sidebar on escape key
function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && sidebarOpen.value) {
        closeSidebar();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="min-h-screen flex bg-neutral-50 dark:bg-neutral-950 cms-page">
        <!-- Mobile overlay backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="sidebarOpen"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex flex-col lg:static lg:z-auto lg:translate-x-0"
            >
                <div
                    class="px-6 py-5 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-2.5 group" @click="closeSidebar">
                        <div
                            class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-sm group-hover:bg-primary-700 transition-colors">
                            eK
                        </div>
                        <div>
                            <span class="font-bold text-sm text-neutral-900 dark:text-white">e-Koperasi</span>
                            <span
                                class="block text-[10px] text-neutral-400 dark:text-neutral-500 -mt-0.5">CMS Admin</span>
                        </div>
                    </Link>
                    <!-- Close button on mobile -->
                    <button
                        @click="closeSidebar"
                        class="lg:hidden p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1">
                    <p v-if="user?.role === 'admin'"
                       class="px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                        Dashboard
                    </p>
                    <Link
                        v-if="user?.role === 'admin'"
                        :href="'/admin'"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url === '/admin'
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                        @click="closeSidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        Overview
                    </Link>

                    <p class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                        Konten
                    </p>
                    <Link
                        :href="cmsIndex().url"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url === '/admin/cms'
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                        @click="closeSidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        Semua Section
                    </Link>

                    <Link
                        href="/admin/media"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url === '/admin/media'
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                        @click="closeSidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                        </svg>
                        Media Library
                    </Link>

                    <p v-if="user?.role === 'admin'"
                       class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                        Client
                    </p>
                    <Link
                        v-if="user?.role === 'admin'"
                        href="/admin/clients"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url.startsWith('/admin/clients')
                            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                        @click="closeSidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                        Client Management
                    </Link>

                    <Link
                        v-if="user?.role === 'admin'"
                        href="/admin/users"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url.startsWith('/admin/users')
                            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                        @click="closeSidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        User Management
                    </Link>

                    <p class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                        Lainnya
                    </p>
                    <a
                        href="/"
                        target="_blank"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-colors"
                        @click="closeSidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                        Lihat Website
                    </a>
                </nav>

                <!-- Theme Toggle -->
                <div class="px-3 py-3 border-t border-neutral-200 dark:border-neutral-800">
                    <button
                        @click="toggleTheme"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
                        :class="theme === 'dark'
                            ? 'text-neutral-400 hover:text-white hover:bg-neutral-800'
                            : 'text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100'"
                    >
                        <svg v-if="theme === 'dark'"
                             class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform duration-300"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                        </svg>
                        <svg v-else
                             class="w-5 h-5 text-indigo-500 group-hover:-rotate-12 transition-transform duration-300"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                        </svg>
                        <span class="flex-1 text-left">{{ theme === 'dark' ? 'Mode Terang' : 'Mode Gelap' }}</span>
                        <kbd
                            class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-mono rounded border opacity-60 group-hover:opacity-100 transition-opacity"
                            :class="theme === 'dark'
                                ? 'bg-neutral-800 text-neutral-400 border-neutral-700'
                                : 'bg-neutral-100 text-neutral-500 border-neutral-200'">
                            <span>⌘</span>⇧D
                        </kbd>
                    </button>
                </div>

                <!-- User -->
                <div class="px-3 py-3 border-t border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <div
                            class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 text-sm font-semibold">
                            {{ user?.name?.charAt(0) || 'A' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                {{ user?.name || 'Admin' }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{
                                    user?.email || ''
                                }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </Transition>

        <!-- Desktop sidebar (always visible, hidden on mobile) -->
        <aside
            class="hidden lg:flex lg:flex-col w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex-shrink-0">
            <div class="px-6 py-5 border-b border-neutral-200 dark:border-neutral-800">
                <Link href="/" class="flex items-center gap-2.5 group">
                    <div
                        class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-sm group-hover:bg-primary-700 transition-colors">
                        eK
                    </div>
                    <div>
                        <span class="font-bold text-sm text-neutral-900 dark:text-white">e-Koperasi</span>
                        <span class="block text-[10px] text-neutral-400 dark:text-neutral-500 -mt-0.5">CMS Admin</span>
                    </div>
                </Link>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1">
                <p v-if="user?.role === 'admin'"
                   class="px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    Dashboard
                </p>
                <Link
                    v-if="user?.role === 'admin'"
                    :href="'/admin'"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    :class="$page.url === '/admin'
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                    @click="closeSidebar"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                    Overview
                </Link>

                <p v-if="user?.role === 'admin'"
                   class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    Client
                </p>
                <Link
                    v-if="user?.role === 'admin'"
                    href="/admin/clients"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    :class="$page.url.startsWith('/admin/clients')
                            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                    @click="closeSidebar"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                    Client Management
                </Link>
                <Link
                    v-if="user?.role === 'admin'"
                    href="/admin/users"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    :class="$page.url.startsWith('/admin/users')
                            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    User Management
                </Link>
                <p class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    Konten
                </p>
                <Link
                    :href="cmsIndex().url"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    :class="$page.url === '/admin/cms'
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                    Semua Section
                </Link>

                <Link
                    href="/admin/media"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    :class="$page.url === '/admin/media'
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                    </svg>
                    Media Library
                </Link>

                <p class="px-3 py-1 mt-4 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    Lainnya
                </p>
                <a
                    href="/"
                    target="_blank"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                    Lihat Website
                </a>
            </nav>

            <!-- Theme Toggle -->
            <div class="px-3 py-3 border-t border-neutral-200 dark:border-neutral-800">
                <button
                    @click="toggleTheme"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
                    :class="theme === 'dark'
                        ? 'text-neutral-400 hover:text-white hover:bg-neutral-800'
                        : 'text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100'"
                >
                    <svg v-if="theme === 'dark'"
                         class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform duration-300"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                    <svg v-else class="w-5 h-5 text-indigo-500 group-hover:-rotate-12 transition-transform duration-300"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                    </svg>
                    <span class="flex-1 text-left">{{ theme === 'dark' ? 'Mode Terang' : 'Mode Gelap' }}</span>
                    <kbd
                        class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-mono rounded border opacity-60 group-hover:opacity-100 transition-opacity"
                        :class="theme === 'dark'
                            ? 'bg-neutral-800 text-neutral-400 border-neutral-700'
                            : 'bg-neutral-100 text-neutral-500 border-neutral-200'">
                        <span>⌘</span>⇧D
                    </kbd>
                </button>
            </div>

            <!-- User -->
            <div class="px-3 py-3 border-t border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div
                        class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 text-sm font-semibold">
                        {{ user?.name?.charAt(0) || 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                            {{ user?.name || 'Admin' }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ user?.email || '' }}</p>
                    </div>
                    <button
                        @click="logout"
                        class="p-1.5 rounded-lg text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        title="Logout"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile header with hamburger and title -->
            <header
                class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30">
                <!-- Mobile top bar -->
                <div class="flex items-center gap-3 px-4 py-3 lg:hidden">
                    <button
                        @click="toggleSidebar"
                        class="p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>
                    <h1 class="text-base font-bold text-neutral-900 dark:text-white truncate">{{
                            title || 'CMS Editor'
                        }}</h1>
                </div>
                <!-- Desktop header -->
                <div class="hidden lg:block px-6 py-4">
                    <h1 class="text-lg font-bold text-neutral-900 dark:text-white">{{ title || 'CMS Editor' }}</h1>
                </div>
            </header>
            <main class="flex-1 overflow-auto">
                <slot/>
            </main>
        </div>
    </div>
</template>
