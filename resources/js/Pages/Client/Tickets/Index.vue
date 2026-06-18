<script setup lang="ts">
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    tickets: any;
}>();

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    acknowledge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    solved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    close: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending',
    acknowledge: 'Acknowledge',
    in_progress: 'In Progress',
    solved: 'Solved',
    close: 'Closed',
};

const priorityColors: Record<string, string> = {
    low: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    medium: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    high: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <ClientLayout title="Ticket Saya">
        <Head title="Ticket Saya - e-Koperasi" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ticket Saya</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Kelola permintaan bantuan dan laporan masalah Anda.</p>
                </div>
                <Link href="/tickets/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Ticket Baru
                </Link>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                            <tr>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Ticket</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Subject</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Status</th>
                                <th class="text-center px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Prioritas</th>
                                <th class="text-left px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Tanggal</th>
                                <th class="text-right px-5 py-3 font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-colors">
                                <td class="px-5 py-3 font-mono text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ ticket.ticket_number }}</td>
                                <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white max-w-xs truncate">{{ ticket.subject }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="statusColors[ticket.status] || ''">
                                        {{ statusLabels[ticket.status] || ticket.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="priorityColors[ticket.priority] || ''">
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-neutral-500 dark:text-neutral-400 text-xs">{{ formatDate(ticket.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <Link :href="'/tickets/' + ticket.id"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium text-xs">
                                        Detail
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!tickets.data?.length">
                                <td colspan="6" class="px-5 py-12 text-center text-neutral-400 dark:text-neutral-500">
                                    <p class="text-lg mb-2">Belum ada ticket</p>
                                    <p class="text-sm">Buat ticket baru untuk melaporkan masalah atau mengajukan bantuan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="tickets.links?.length > 3" class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex justify-between items-center text-sm">
                    <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="text-emerald-600 dark:text-emerald-400 hover:underline">← Sebelumnya</Link>
                    <span v-else class="text-neutral-400">← Sebelumnya</span>
                    <span class="text-neutral-500 dark:text-neutral-400">Halaman {{ tickets.current_page }} dari {{ tickets.last_page }}</span>
                    <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="text-emerald-600 dark:text-emerald-400 hover:underline">Selanjutnya →</Link>
                    <span v-else class="text-neutral-400">Selanjutnya →</span>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
