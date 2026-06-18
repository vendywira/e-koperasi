<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

interface Notification {
    id: string;
    user_id: string;
    type: 'ticket' | 'ticket_reply' | 'demo' | 'subscription' | 'payment';
    title: string;
    body: string;
    link: string | null;
    reference_id: string | null;
    reference_type: string | null;
    is_read: boolean;
    created_at: string;
    updated_at: string;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    notifications: PaginatedData<Notification>;
    unreadCount: number;
}>();

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

const typeLabels: Record<string, string> = {
    ticket: 'Tiket',
    ticket_reply: 'Balasan Tiket',
    demo: 'Demo Request',
    subscription: 'Langganan',
    payment: 'Pembayaran',
};
</script>

<template>
    <AdminLayout title="Notifikasi">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Semua Notifikasi</h2>
                    <p v-if="unreadCount > 0" class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ unreadCount }} belum dibaca
                    </p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="notifications.data.length === 0" class="text-center py-16">
                <svg class="w-12 h-12 mx-auto text-neutral-300 dark:text-neutral-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Belum ada notifikasi</p>
            </div>

            <!-- Notification list -->
            <div v-else class="space-y-1">
                <div
                    v-for="notif in notifications.data"
                    :key="notif.id"
                    class="block"
                >
                    <Link
                        :href="notif.link || '#'"
                        class="flex items-start gap-4 px-4 py-3.5 rounded-xl transition-colors"
                        :class="!notif.is_read
                            ? 'bg-blue-50 dark:bg-blue-900/10 hover:bg-blue-100 dark:hover:bg-blue-900/20'
                            : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                    >
                        <div
                            class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                            :class="getIconColor(notif.type)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="getIcon(notif.type)" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                                    {{ notif.title }}
                                </p>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400">
                                    {{ typeLabels[notif.type] || notif.type }}
                                </span>
                            </div>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ notif.body }}</p>
                            <p class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-1">
                                {{ timeAgo(notif.created_at) }}
                            </p>
                        </div>
                        <div v-if="!notif.is_read" class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0 mt-2"></div>
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="flex items-center justify-center gap-2 mt-6">
                <Link
                    v-if="notifications.current_page > 1"
                    :href="`/admin/notifications?page=${notifications.current_page - 1}`"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800"
                >
                    Sebelumnya
                </Link>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    Halaman {{ notifications.current_page }} dari {{ notifications.last_page }}
                </span>
                <Link
                    v-if="notifications.current_page < notifications.last_page"
                    :href="`/admin/notifications?page=${notifications.current_page + 1}`"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800"
                >
                    Selanjutnya
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
