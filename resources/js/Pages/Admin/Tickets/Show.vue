<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    ticket: any;
    staffUsers: { id: string; name: string }[];
}>();

const page = usePage();
const currentUser = (page.props as any).auth?.user;
const isItOps = currentUser?.role === 'it-ops';
const showAssignee = !isItOps;

const replyMessage = ref('');
const replyAttachments = ref<File[]>([]);
const attachmentErrors = ref<string[]>([]);
const showMobileReply = ref(false);
const mobileReplyMessage = ref('');
const mobileReplyAttachments = ref<File[]>([]);
const mobileAttachmentErrors = ref<string[]>([]);

const MAX_FILE_SIZE = 10 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

const statusConfig: Record<string, { label: string; color: string; bg: string }> = {
    pending: { label: 'Pending', color: 'text-amber-700 dark:text-amber-400', bg: 'bg-amber-100 dark:bg-amber-900/30' },
    acknowledge: { label: 'Acknowledge', color: 'text-blue-700 dark:text-blue-400', bg: 'bg-blue-100 dark:bg-blue-900/30' },
    in_progress: { label: 'In Progress', color: 'text-indigo-700 dark:text-indigo-400', bg: 'bg-indigo-100 dark:bg-indigo-900/30' },
    solved: { label: 'Solved', color: 'text-emerald-700 dark:text-emerald-400', bg: 'bg-emerald-100 dark:bg-emerald-900/30' },
    close: { label: 'Closed', color: 'text-neutral-600 dark:text-neutral-400', bg: 'bg-neutral-100 dark:bg-neutral-800' },
};

const formatDate = (dt: string) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatTime = (dt: string) => {
    if (!dt) return '';
    return new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
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
    router.put(`/admin/tickets/${props.ticket.id}/status`, { status }, {
        preserveScroll: true,
        preserveState: false,
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

function getFileUrl(path: string) { return '/storage/' + path; }
function isImage(mime: string) { return mime?.startsWith('image/'); }
function isVideo(mime: string) { return mime?.startsWith('video/'); }
const fileIcon = (name: string) => {
    if (name?.endsWith('.pdf')) return '📄';
    if (name?.match(/\.(mp4|mov|avi)$/i)) return '🎬';
    return '📎';
};

const nextStatuses: Record<string, { value: string; label: string; variant: string }[]> = {
    pending: [{ value: 'acknowledge', label: 'Acknowledge', variant: 'blue' }],
    acknowledge: [{ value: 'in_progress', label: 'Proses', variant: 'indigo' }, { value: 'pending', label: 'Pending', variant: 'neutral' }],
    in_progress: [{ value: 'solved', label: 'Selesai', variant: 'emerald' }, { value: 'acknowledge', label: 'Acknowledge', variant: 'neutral' }],
    solved: [{ value: 'close', label: 'Tutup', variant: 'red' }, { value: 'in_progress', label: 'Buka', variant: 'neutral' }],
    close: [],
};

const availableStatuses = computed(() => nextStatuses[props.ticket.status] || []);

const variantStyles: Record<string, string> = {
    red: 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400',
    emerald: 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400',
    blue: 'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400',
    indigo: 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400',
    neutral: 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300',
};
</script>

<template>
    <AdminLayout :title="'Ticket #' + ticket.ticket_number">
        <Head :title="ticket.ticket_number + ' - Admin'" />

        <div class="h-screen bg-neutral-50 dark:bg-neutral-950 flex flex-col overflow-hidden">
            <!-- Sticky Top Bar -->
            <div class="shrink-0 bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800">
                <div class="flex items-center gap-2 px-3 sm:px-6 h-12 sm:h-14">
                    <Link href="/admin/tickets" class="shrink-0 p-1.5 -ml-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </Link>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">Ticket Management</p>
                        <h1 class="text-sm font-semibold text-neutral-900 dark:text-white truncate">{{ ticket.subject }}</h1>
                    </div>
                </div>
            </div>

            <!-- Scrollable content area -->
            <div class="flex-1 overflow-y-auto px-3 sm:px-6 lg:px-8 py-4 max-w-5xl mx-auto w-full">
                <div class="space-y-3">
                    <!-- Status Bar + Actions -->
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="font-mono text-[11px] font-semibold text-neutral-400 bg-neutral-200 dark:bg-neutral-800 px-2 py-1 rounded shrink-0">{{ ticket.ticket_number }}</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold shrink-0"
                            :class="[statusConfig[ticket.status]?.bg, statusConfig[ticket.status]?.color]">
                            <span class="w-1.5 h-1.5 rounded-full" :class="{
                                'bg-amber-500': ticket.status === 'pending',
                                'bg-blue-500': ticket.status === 'acknowledge',
                                'bg-indigo-500': ticket.status === 'in_progress',
                                'bg-emerald-500': ticket.status === 'solved',
                                'bg-neutral-500': ticket.status === 'close',
                            }"></span>
                            {{ statusConfig[ticket.status]?.label || ticket.status }}
                        </span>
                        <button v-for="s in availableStatuses" :key="s.value" @click="changeStatus(s.value)"
                            class="shrink-0 px-2.5 py-1 text-[11px] font-semibold rounded-lg transition-colors"
                            :class="variantStyles[s.variant] || variantStyles.neutral">
                            {{ s.label }}
                        </button>
                        <select v-if="showAssignee" @change="assignUser(($event.target as HTMLSelectElement).value)"
                            class="ml-auto px-2 py-1 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-xs text-neutral-700 dark:text-neutral-300 outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">— Assign —</option>
                            <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id" :selected="ticket.assigned_to?.id === staff.id">{{ staff.name }}</option>
                        </select>
                    </div>

                    <!-- Mobile Status Bar -->
                    <div class="sm:hidden flex items-center gap-2">
                        <span class="font-mono text-[11px] font-semibold text-neutral-400 bg-neutral-200 dark:bg-neutral-800 px-2 py-1 rounded shrink-0">{{ ticket.ticket_number }}</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold shrink-0"
                            :class="[statusConfig[ticket.status]?.bg, statusConfig[ticket.status]?.color]">
                            <span class="w-1.5 h-1.5 rounded-full" :class="{
                                'bg-amber-500': ticket.status === 'pending',
                                'bg-blue-500': ticket.status === 'acknowledge',
                                'bg-indigo-500': ticket.status === 'in_progress',
                                'bg-emerald-500': ticket.status === 'solved',
                                'bg-neutral-500': ticket.status === 'close',
                            }"></span>
                            {{ statusConfig[ticket.status]?.label || ticket.status }}
                        </span>
                    </div>

                    <!-- Desktop: meta info (by) -->
                    <div class="hidden sm:flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 flex-wrap">
                        <span>Oleh {{ ticket.user?.name }} ({{ ticket.user?.email }})</span>
                        <span class="text-neutral-300">·</span>
                        <span>{{ formatDate(ticket.created_at) }}</span>
                    </div>

                    <!-- Description -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-neutral-100 dark:border-neutral-800 flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-[11px] font-bold shrink-0">
                                {{ (ticket.user?.name || '?').charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ ticket.user?.name }}</p>
                                <p class="text-[11px] text-neutral-400">{{ formatDate(ticket.created_at) }}</p>
                            </div>
                            <span class="text-[10px] font-medium text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded uppercase shrink-0">{{ ticket.user?.role }}</span>
                        </div>
                        <div class="px-4 py-3">
                            <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap leading-relaxed">{{ ticket.description }}</p>
                        </div>
                        <div v-if="ticket.attachments?.length" class="px-4 pb-3 flex flex-wrap gap-1.5">
                            <a v-for="att in ticket.attachments" :key="att.id" :href="getFileUrl(att.file_path)" target="_blank"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-xs text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors max-w-full">
                                <span class="truncate">{{ fileIcon(att.file_name) }} {{ att.file_name }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- Replies Thread -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 px-1">
                            <h3 class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Percakapan</h3>
                            <span class="text-xs text-neutral-400">({{ ticket.replies?.length || 0 }})</span>
                        </div>

                        <template v-for="reply in ticket.replies" :key="reply.id">
                            <div v-if="reply.user_id !== ticket.user_id" class="flex items-start gap-2 justify-end">
                                <div class="flex-1 max-w-[88%] sm:max-w-[75%]">
                                    <div class="bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-900 rounded-2xl rounded-br-md px-4 py-3">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ reply.user?.name }}</span>
                                            <span class="text-[10px] text-neutral-400">{{ formatTime(reply.created_at) }}</span>
                                        </div>
                                        <p class="text-sm text-neutral-800 dark:text-neutral-200 whitespace-pre-wrap leading-relaxed">{{ reply.message }}</p>
                                    </div>
                                    <p class="text-[10px] text-neutral-400 mt-0.5 text-right mr-1">{{ formatDate(reply.created_at) }}</p>
                                </div>
                                <div class="w-6 h-6 rounded-full bg-emerald-200 dark:bg-emerald-800 flex items-center justify-center text-emerald-700 dark:text-emerald-300 text-[10px] font-bold shrink-0 mt-1">
                                    {{ (reply.user?.name || '?').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div v-else class="flex items-start gap-2">
                                <div class="w-6 h-6 rounded-full bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center text-neutral-600 dark:text-neutral-400 text-[10px] font-bold shrink-0 mt-1">
                                    {{ (reply.user?.name || '?').charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 max-w-[88%] sm:max-w-[75%]">
                                    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl rounded-tl-md px-4 py-3">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">{{ reply.user?.name }}</span>
                                            <span class="text-[10px] text-neutral-400">{{ formatTime(reply.created_at) }}</span>
                                        </div>
                                        <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap leading-relaxed">{{ reply.message }}</p>
                                    </div>
                                    <p class="text-[10px] text-neutral-400 mt-0.5 ml-1">{{ formatDate(reply.created_at) }}</p>
                                </div>
                            </div>
                        </template>

                        <div v-if="!ticket.replies?.length" class="text-center py-8">
                            <p class="text-sm text-neutral-400 dark:text-neutral-500">Belum ada balasan.</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Desktop Reply Form -->
            <div class="hidden sm:block shrink-0 px-6 pb-4 max-w-5xl mx-auto w-full">
                <div v-if="ticket.status !== 'close'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-neutral-100 dark:border-neutral-800">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Balas Ticket</h3>
                    </div>
                    <form @submit.prevent="submitReply" class="p-4 space-y-3">
                        <textarea v-model="replyMessage" rows="3" placeholder="Tulis balasan Anda..."
                            class="w-full px-3.5 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none"></textarea>
                        <div class="flex items-center gap-2">
                            <label class="inline-flex items-center gap-1.5 px-3 py-2 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-xs text-neutral-500 dark:text-neutral-400">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                                </svg>
                                Lampirkan
                                <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                            </label>
                            <span class="text-[10px] text-neutral-400">PDF, JPG, MP4 (max 10MB)</span>
                        </div>
                        <ul v-if="replyAttachments.length" class="space-y-1">
                            <li v-for="(file, i) in replyAttachments" :key="i" class="flex items-center gap-2 text-xs text-neutral-500 bg-neutral-50 dark:bg-neutral-800/50 px-3 py-1.5 rounded-lg">
                                <span class="flex-1 truncate">{{ file.name }}</span>
                                <span class="text-neutral-400">({{ (file.size / 1024 / 1024).toFixed(1) }} MB)</span>
                                <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700 font-medium">✕</button>
                            </li>
                        </ul>
                        <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="text-xs text-red-500">{{ err }}</p>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" :disabled="!replyMessage.trim() && !replyAttachments.length"
                                class="flex-1 sm:flex-none px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>
                <div v-else class="bg-neutral-100 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 text-center">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">✅ Ticket ini sudah ditutup.</p>
                </div>
            </div>

            <!-- Mobile Bottom Navigation Bar -->
            <div v-if="ticket.status !== 'close'" class="sm:hidden shrink-0 bg-white dark:bg-neutral-900 border-t border-neutral-200 dark:border-neutral-800 px-3 py-2">
                <div class="flex items-center gap-2">
                    <select
                        @change="changeStatus(($event.target as HTMLSelectElement).value)"
                        class="flex-1 py-2.5 px-3 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 appearance-none dropdown-chevron pr-7">
                        <option value="" disabled selected>{{ statusConfig[ticket.status]?.label || ticket.status }}</option>
                        <option v-for="s in availableStatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                    <select v-if="showAssignee"
                        @change="assignUser(($event.target as HTMLSelectElement).value)"
                        class="flex-1 py-2.5 px-3 text-xs font-semibold rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 appearance-none dropdown-chevron pr-7">
                        <option value="">— Assign —</option>
                        <option v-for="staff in staffUsers" :key="staff.id" :value="staff.id" :selected="ticket.assigned_to?.id === staff.id">{{ staff.name }}</option>
                    </select>
                    <button @click="showMobileReply = true"
                        class="shrink-0 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                        Balas
                    </button>
                </div>
            </div>

            <!-- Mobile Reply Sheet -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="translate-y-full opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-full opacity-0"
            >
                <div v-if="showMobileReply" class="sm:hidden fixed inset-0 z-50 flex flex-col">
                    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="showMobileReply = false"></div>
                    <div class="relative mt-auto bg-white dark:bg-neutral-900 rounded-t-2xl shadow-xl max-h-[70vh] flex flex-col">
                        <div class="shrink-0 flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-800">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Balas Ticket</h3>
                            <button @click="showMobileReply = false" class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="overflow-y-auto flex-1 p-4 space-y-3">
                            <textarea v-model="replyMessage" rows="4" placeholder="Tulis balasan Anda..."
                                class="w-full px-3.5 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none"></textarea>
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 px-3 py-2 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-xs text-neutral-500 dark:text-neutral-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                                    </svg>
                                    Lampirkan
                                    <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                                </label>
                                <span class="text-[10px] text-neutral-400">PDF, JPG, MP4 (max 10MB)</span>
                            </div>
                            <ul v-if="replyAttachments.length" class="space-y-1">
                                <li v-for="(file, i) in replyAttachments" :key="i" class="flex items-center gap-2 text-xs text-neutral-500 bg-neutral-50 dark:bg-neutral-800/50 px-3 py-1.5 rounded-lg">
                                    <span class="flex-1 truncate">{{ file.name }}</span>
                                    <span class="text-neutral-400">({{ (file.size / 1024 / 1024).toFixed(1) }} MB)</span>
                                    <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700 font-medium">✕</button>
                                </li>
                            </ul>
                            <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="text-xs text-red-500">{{ err }}</p>
                        </div>
                        <div class="shrink-0 px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
                            <button @click="submitReply"
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors inline-flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                                Kirim Balasan
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Mobile closed state -->
            <div v-if="ticket.status === 'close'" class="sm:hidden shrink-0 bg-neutral-100 dark:bg-neutral-800/50 border-t border-neutral-200 dark:border-neutral-800 py-3 text-center">
                <p class="text-xs text-neutral-500 dark:text-neutral-400">✅ Ticket ini sudah ditutup.</p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.dropdown-chevron {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
}
</style>
