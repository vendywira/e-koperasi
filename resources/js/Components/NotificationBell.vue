<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useNotifications, type Notification } from '@/Composables/useNotifications';

const {
    unreadCount,
    notifications,
    showDropdown,
    loading,
    error,
    markAsRead,
    markAllAsRead,
    toggleDropdown,
    closeDropdown,
    fetchNotifications,
} = useNotifications();

const page = usePage();
const user = (page.props as any).auth?.user;
const isStaff = user?.role === 'admin' || user?.role === 'it-ops';
const allNotificationsLink = isStaff ? '/admin/notifications' : '/tickets';

function handleClickOutside(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (!target.closest('[data-notification-bell]')) {
        closeDropdown();
    }
}

function handleNotificationClick(notif: Notification) {
    closeDropdown();
    if (!notif.is_read) {
        notif.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        // keepalive: true agar request tetap dikirim walaupun navigasi
        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
        fetch(`/api/notifications/${notif.id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '{}',
            keepalive: true,
        }).then(() => {
            // Sync previousUnreadCount di composable via polling berikutnya
        });
    }
    if (notif.link) {
        router.visit(notif.link);
    }
}

function getIcon(type: string): string {
    switch (type) {
        case 'ticket':
        case 'ticket_reply':
            return 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155';
        case 'demo':
            return 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155';
        case 'subscription':
            return 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605';
        case 'payment':
            return 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z';
        default:
            return 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0';
    }
}

function getIconColor(type: string): string {
    switch (type) {
        case 'ticket':
        case 'ticket_reply':
            return 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30';
        case 'demo':
            return 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30';
        case 'subscription':
            return 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30';
        case 'payment':
            return 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30';
        default:
            return 'text-neutral-600 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800';
    }
}

function timeAgo(dateStr: string): string {
    const now = new Date();
    const date = new Date(dateStr);
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'baru saja';
    if (diffMins < 60) return `${diffMins} menit yang lalu`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} jam yang lalu`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays} hari yang lalu`;
    return date.toLocaleDateString('id-ID');
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div data-notification-bell class="relative">
        <!-- Bell Button -->
        <button
            @click.stop="toggleDropdown"
            class="relative p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
            aria-label="Notifikasi"
        >
            <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-2"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-xl overflow-hidden z-50"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-white">Notifikasi</h4>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-[10px] font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                    >
                        Tandai telah dibaca
                    </button>
                </div>

                <!-- Loading state -->
                <div v-if="loading && notifications.length === 0" class="px-4 py-6 text-center">
                    <div class="flex justify-center gap-1">
                        <span class="w-2 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                        <span class="w-2 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                        <span class="w-2 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                    </div>
                </div>

                <!-- Error state -->
                <div v-else-if="error && notifications.length === 0" class="px-4 py-6 text-center">
                    <p class="text-xs text-red-500 dark:text-red-400">{{ error }}</p>
                    <button
                        @click="fetchNotifications"
                        class="mt-2 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        Coba lagi
                    </button>
                </div>

                <!-- Empty state -->
                <div v-else-if="notifications.length === 0 && !loading" class="px-4 py-8 text-center">
                    <svg class="w-8 h-8 mx-auto text-neutral-300 dark:text-neutral-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="text-xs text-neutral-400 dark:text-neutral-500">Belum ada notifikasi</p>
                </div>

                <!-- Notification list -->
                <div v-else class="max-h-80 overflow-y-auto divide-y divide-neutral-100 dark:divide-neutral-800">
                    <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        @click="handleNotificationClick(notif)"
                        class="px-4 py-3 cursor-pointer transition-colors"
                        :class="!notif.is_read
                            ? 'bg-blue-50 dark:bg-blue-900/10 hover:bg-blue-100 dark:hover:bg-blue-900/20'
                            : 'hover:bg-neutral-50 dark:hover:bg-neutral-800'"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                :class="getIconColor(notif.type)"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(notif.type)" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-neutral-900 dark:text-white">{{ notif.title }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ notif.body }}</p>
                                <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-1">{{ timeAgo(notif.created_at) }}</p>
                            </div>
                            <div v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 border-t border-neutral-100 dark:border-neutral-800 text-center">
                    <a
                        :href="allNotificationsLink"
                        class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                    >
                        Lihat semua notifikasi →
                    </a>
                </div>
            </div>
        </Transition>
    </div>
</template>
