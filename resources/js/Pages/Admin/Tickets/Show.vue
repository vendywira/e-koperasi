<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    ticket: any;
    staffUsers: { id: string; name: string }[];
}>();

const replyMessage = ref('');
const replyAttachments = ref<File[]>([]);
const attachmentErrors = ref<string[]>([]);

const MAX_FILE_SIZE = 10 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

const statusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    acknowledge: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    in_progress: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    solved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    close: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
};

const statusLabels: Record<string, string> = {
    pending: 'Pending', acknowledge: 'Acknowledge', in_progress: 'In Progress', solved: 'Solved', close: 'Closed',
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

function onFileSelect(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    attachmentErrors.value = [];
    Array.from(input.files).forEach((file) => {
        if (file.size > MAX_FILE_SIZE) {
            attachmentErrors.value.push(`"${file.name}" melebihi 10MB.`);
            return;
        }
        if (!ALLOWED_TYPES.includes(file.type) && !file.name.match(/\.(pdf|jpg|jpeg|png|mp4|mov|avi)$/i)) {
            attachmentErrors.value.push(`"${file.name}" format tidak didukung.`);
            return;
        }
        replyAttachments.value.push(file);
    });
    input.value = '';
}

function removeAttachment(index: number) {
    replyAttachments.value.splice(index, 1);
}

function changeStatus(status: string) {
    const form = new FormData();
    form.append('_method', 'PUT');
    form.append('status', status);
    router.post(`/admin/tickets/${props.ticket.id}/status`, form, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['ticket'] }),
    });
}

function assignUser(userId: string) {
    router.put(`/admin/tickets/${props.ticket.id}/assign`, { assigned_to: userId || null }, {
        preserveScroll: true,
    });
}

function submitReply() {
    if (!replyMessage.value.trim() && !replyAttachments.value.length) return;

    const formData = new FormData();
    formData.append('message', replyMessage.value);
    replyAttachments.value.forEach((f) => formData.append('attachments[]', f));

    router.post(`/admin/tickets/${props.ticket.id}/reply`, formData, {
        onSuccess: () => {
            replyMessage.value = '';
            replyAttachments.value = [];
        },
        preserveScroll: true,
    });
}

function getFileUrl(path: string) {
    return '/storage/' + path;
}

function isImage(mime: string) { return mime?.startsWith('image/'); }
function isVideo(mime: string) { return mime?.startsWith('video/'); }
function getFileIcon(name: string) {
    if (name?.endsWith('.pdf')) return '📄';
    if (name?.match(/\.(mp4|mov|avi)$/i)) return '🎬';
    return '📎';
}

const nextStatuses: Record<string, { value: string; label: string }[]> = {
    pending: [{ value: 'acknowledge', label: 'Acknowledge' }],
    acknowledge: [{ value: 'in_progress', label: 'Proses' }, { value: 'pending', label: 'Kembali ke Pending' }],
    in_progress: [{ value: 'solved', label: 'Selesai' }, { value: 'acknowledge', label: 'Kembali ke Acknowledge' }],
    solved: [{ value: 'close', label: 'Tutup Ticket' }, { value: 'in_progress', label: 'Buka Kembali' }],
    close: [],
};

const availableStatuses = nextStatuses[props.ticket.status] || [];
</script>

<template>
    <AdminLayout :title="'Ticket #' + ticket.ticket_number">
        <Head :title="ticket.ticket_number + ' - Admin'" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl">
            <Link href="/admin/tickets" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Ticket
            </Link>

            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                            <span class="font-mono text-xs font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ ticket.ticket_number }}</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold" :class="statusColors[ticket.status] || ''">{{ statusLabels[ticket.status] || ticket.status }}</span>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 capitalize">{{ ticket.priority }} priority</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-neutral-900 dark:text-white">{{ ticket.subject }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Oleh {{ ticket.user?.name }} ({{ ticket.user?.email }}) — {{ formatDate(ticket.created_at) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button v-for="s in availableStatuses" :key="s.value" @click="changeStatus(s.value)"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                            :class="s.value === 'close' ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' :
                                   s.value === 'solved' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                   'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300'">
                            {{ s.label }}
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <label class="text-xs text-neutral-500 dark:text-neutral-400">Ditangani oleh:</label>
                    <select @change="assignUser(($event.target as HTMLSelectElement).value)"
                        class="px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm text-neutral-700 dark:text-neutral-300 outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Pilih Staff —</option>
                        <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id" :selected="ticket.assigned_to?.id === staff.id">
                            {{ staff.name }}
                        </option>
                    </select>
                    <span v-if="ticket.assigned_to" class="text-xs text-neutral-400">({{ ticket.assigned_to?.name }})</span>
                </div>

                <div class="mt-4 p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-wider">Deskripsi</p>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>

                <div v-if="ticket.attachments?.length" class="mt-4 flex flex-wrap gap-2">
                    <a v-for="att in ticket.attachments" :key="att.id"
                        :href="getFileUrl(att.file_path)" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                        {{ isImage(att.mime_type) ? '🖼️' : isVideo(att.mime_type) ? '🎬' : getFileIcon(att.file_name) }} {{ att.file_name }}
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Percakapan ({{ ticket.replies?.length || 0 }})</h3>

                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                            {{ (ticket.user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ ticket.user?.name }}</p>
                            <p class="text-xs text-neutral-400">{{ formatDate(ticket.created_at) }}</p>
                        </div>
                        <span class="ml-auto text-[10px] font-medium text-neutral-400 uppercase bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ ticket.user?.role }}</span>
                    </div>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>

                <div v-for="reply in ticket.replies" :key="reply.id"
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-5"
                    :class="reply.user_id !== ticket.user_id ? 'border-l-4 border-l-emerald-500' : ''">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full"
                            :class="reply.user_id !== ticket.user_id ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400'">
                            {{ (reply.user?.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ reply.user?.name }}</p>
                            <p class="text-xs text-neutral-400">{{ formatDate(reply.created_at) }}</p>
                        </div>
                        <span class="ml-auto text-[10px] font-medium text-neutral-400 uppercase bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ reply.user?.role }}</span>
                    </div>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{{ reply.message }}</p>

                    <div v-if="reply.attachments?.length" class="mt-3 flex flex-wrap gap-2">
                        <a v-for="att in reply.attachments" :key="att.id"
                            :href="getFileUrl(att.file_path)" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                            {{ isImage(att.mime_type) ? '🖼️' : isVideo(att.mime_type) ? '🎬' : getFileIcon(att.file_name) }} {{ att.file_name }}
                        </a>
                    </div>
                </div>
            </div>

            <div v-if="ticket.status !== 'close'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-4">Balas Ticket</h3>

                <form @submit.prevent="submitReply">
                    <textarea v-model="replyMessage" rows="4" placeholder="Tulis balasan Anda..."
                        class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-y"></textarea>

                    <div class="mt-3">
                        <label class="inline-flex items-center gap-2 px-4 py-2 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-500 dark:text-neutral-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                            </svg>
                            Lampirkan File
                            <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                        </label>
                    </div>

                    <ul v-if="replyAttachments.length" class="mt-2 space-y-1">
                        <li v-for="(file, i) in replyAttachments" :key="i" class="flex items-center gap-2 text-sm text-neutral-500">
                            <span>{{ file.name }}</span>
                            <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700 text-xs">hapus</button>
                        </li>
                    </ul>
                    <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="mt-1 text-xs text-red-500">{{ err }}</p>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" :disabled="!replyMessage.trim() && !replyAttachments.length"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                            Kirim Balasan
                        </button>
                    </div>
                </form>
            </div>

            <div v-else class="bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Ticket ini sudah ditutup.</p>
            </div>
        </div>
    </AdminLayout>
</template>
