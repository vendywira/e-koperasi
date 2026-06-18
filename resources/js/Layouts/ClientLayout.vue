<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useTheme } from '@/composables/useTheme';
import NotificationBell from '@/Components/NotificationBell.vue';

defineProps<{
    title?: string;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const subscription = computed(() => (page.props as any).auth?.user?.subscription);
const { theme, toggleTheme } = useTheme();
const sidebarOpen = ref(false);

function logout() {
    router.post('/logout', {}, {
        onFinish: () => window.location.href = '/login',
    });
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') sidebarOpen.value = false;
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

const navItems = [
    { key: 'dashboard', label: 'Beranda', href: '/client/dashboard', icon: 'home' },
    { key: 'subscription', label: 'Langganan', href: '/client/subscription', icon: 'clipboard' },
    { key: 'payments', label: 'Pembayaran', href: '/client/payments', icon: 'credit-card' },
    { key: 'tickets', label: 'Ticket', href: '/tickets', icon: 'ticket' },
];

const isActive = (href: string) => page.url === href || page.url.startsWith(href + '/');
</script>

<template>
    <div class="min-h-screen flex bg-neutral-50 dark:bg-neutral-950">
        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />
        </Transition>

        <!-- Mobile Sidebar -->
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
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex flex-col lg:hidden"
            >
                <SidebarContent :user :subscription :navItems :isActive :theme :toggleTheme :logout :closeSidebar="() => sidebarOpen = false" />
            </aside>
        </Transition>

        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-64 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex-shrink-0">
            <SidebarContent :user :subscription :navItems :isActive :theme :toggleTheme :logout :closeSidebar="() => {}" />
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile header -->
            <header class="bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 sticky top-0 z-30 lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <h1 class="text-base font-bold text-neutral-900 dark:text-white truncate">{{ title || 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <button
                            @click="logout"
                            class="p-1.5 rounded-lg text-neutral-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Logout"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>
            <!-- Desktop header -->
            <header class="hidden lg:block bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold text-neutral-900 dark:text-white">{{ title || 'Dashboard' }}</h1>
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <span class="text-xs text-neutral-400 dark:text-neutral-500">{{ user?.email }}</span>
                        <button
                            @click="logout"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-neutral-500 dark:text-neutral-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Logout
                        </button>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, h } from 'vue';
import { Link } from '@inertiajs/vue3';

const SidebarContent = defineComponent({
    props: ['user', 'subscription', 'navItems', 'isActive', 'theme', 'toggleTheme', 'logout', 'closeSidebar'],
    setup(props) {
        const icons: Record<string, string> = {
            home: 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            clipboard: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'credit-card': 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
            ticket: 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155',
        };

        return () => h('div', { class: 'flex flex-col h-full' }, [
            // Logo
            h('div', { class: 'px-6 py-5 border-b border-neutral-200 dark:border-neutral-800' }, [
                h('div', { class: 'flex items-center justify-between' }, [
                    h(Link, { href: '/client/dashboard', class: 'flex items-center gap-2.5 group', onClick: props.closeSidebar }, () => [
                        h('div', { class: 'w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-sm' }, 'eK'),
                        h('div', {}, [
                            h('span', { class: 'font-bold text-sm text-neutral-900 dark:text-white' }, 'e-Koperasi'),
                            h('span', { class: 'block text-[10px] text-neutral-400 dark:text-neutral-500 -mt-0.5' }, 'Client Portal'),
                        ]),
                    ]),
                    // Close button on mobile
                    h('button', {
                        class: 'lg:hidden p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors',
                        onClick: props.closeSidebar,
                    }, () => h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' }, [
                        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M6 18L18 6M6 6l12 12' }),
                    ])),
                ]),
            ]),

            // Nav items
            h('nav', { class: 'flex-1 px-3 py-4 space-y-1' }, [
                h('p', { class: 'px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500' }, 'Menu'),
                ...props.navItems.map((item: any) =>
                    h(Link, {
                        href: item.href,
                        class: 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ' +
                            (props.isActive(item.href)
                                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                                : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'),
                        onClick: props.closeSidebar,
                    }, () => [
                        h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: icons[item.icon] }),
                        ]),
                        item.label,
                    ])
                ),
            ]),

            // Subscription status mini
            h('div', { class: 'px-3 py-3 border-t border-neutral-200 dark:border-neutral-800' }, [
                h('div', { class: 'px-3 py-2 rounded-lg bg-neutral-50 dark:bg-neutral-800/50' }, [
                    h('p', { class: 'text-[10px] font-medium uppercase tracking-wider text-neutral-400 dark:text-neutral-500' }, 'Status Langganan'),
                    h('div', { class: 'flex items-center gap-2 mt-1' }, [
                        h('span', {
                            class: 'w-2 h-2 rounded-full ' +
                                (props.subscription?.is_active ? 'bg-emerald-500' : 'bg-red-500'),
                        }),
                        h('span', { class: 'text-sm font-medium text-neutral-900 dark:text-white' },
                            props.subscription?.plan
                                ? (props.subscription.plan.charAt(0).toUpperCase() + props.subscription.plan.slice(1)) + ' · ' + (props.subscription.is_active ? 'Aktif' : 'Tidak Aktif')
                                : 'Belum ada'
                        ),
                    ]),
                ]),
            ]),

            // Theme + Logout
            h('div', { class: 'px-3 py-3 border-t border-neutral-200 dark:border-neutral-800 space-y-1' }, [
                h('button', {
                    onClick: props.toggleTheme,
                    class: 'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white',
                }, () => [
                    props.theme === 'dark'
                        ? h('svg', { class: 'w-5 h-5 text-amber-400', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z' }),
                        ])
                        : h('svg', { class: 'w-5 h-5 text-indigo-500', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z' }),
                        ]),
                    h('span', { class: 'flex-1 text-left' }, props.theme === 'dark' ? 'Mode Terang' : 'Mode Gelap'),
                ]),
                h('button', {
                    onClick: props.logout,
                    class: 'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors',
                }, () => [
                    h('svg', { class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '1.5' }, [
                        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' }),
                    ]),
                    'Logout',
                ]),
            ]),
        ]);
    },
});
</script>
