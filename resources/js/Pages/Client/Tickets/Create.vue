<script setup lang="ts">
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    subject: '',
    description: '',
    priority: 'medium',
});

const attachments = ref<File[]>([]);
const attachmentErrors = ref<string[]>([]);

const MAX_FILE_SIZE = 10 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];

function onFileSelect(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;

    attachmentErrors.value = [];
    const valid: File[] = [];

    Array.from(input.files).forEach((file) => {
        if (file.size > MAX_FILE_SIZE) {
            attachmentErrors.value.push(`"${file.name}" melebihi 10MB.`);
            return;
        }
        if (!ALLOWED_TYPES.includes(file.type) && !file.name.match(/\.(pdf|jpg|jpeg|png|mp4|mov|avi)$/i)) {
            attachmentErrors.value.push(`"${file.name}" format tidak didukung.`);
            return;
        }
        valid.push(file);
    });

    attachments.value = [...attachments.value, ...valid];
    input.value = '';
}

function removeAttachment(index: number) {
    attachments.value.splice(index, 1);
}

function submit() {
    const formData = new FormData();
    formData.append('subject', form.subject);
    formData.append('description', form.description);
    formData.append('priority', form.priority);
    attachments.value.forEach((f) => formData.append('attachments[]', f));

    router.post('/tickets', formData, {
        onSuccess: () => {
            form.reset();
            attachments.value = [];
        },
    });
}
</script>

<template>
    <ClientLayout title="Buat Ticket Baru">
        <Head title="Buat Ticket Baru - e-Koperasi" />

        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-3xl">
            <div>
                <Link href="/tickets" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar Ticket
                </Link>
                <h2 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Buat Ticket Baru</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Laporkan masalah atau ajukan bantuan teknis.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Subject <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.subject"
                        type="text"
                        placeholder="Contoh: Tidak bisa login ke dashboard"
                        class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition-all"
                        :class="form.errors.subject ? 'border-red-400 focus:ring-2 focus:ring-red-500' : 'border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-emerald-500'"
                    />
                    <p v-if="form.errors.subject" class="mt-1 text-xs text-red-500">{{ form.errors.subject }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Prioritas <span class="text-red-500">*</span></label>
                    <select
                        v-model="form.priority"
                        class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
                    >
                        <option value="low">Rendah</option>
                        <option value="medium">Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                    <p v-if="form.errors.priority" class="mt-1 text-xs text-red-500">{{ form.errors.priority }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="form.description"
                        rows="6"
                        placeholder="Jelaskan masalah yang Anda alami secara detail..."
                        class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition-all resize-y"
                        :class="form.errors.description ? 'border-red-400 focus:ring-2 focus:ring-red-500' : 'border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-emerald-500'"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Lampiran (opsional)</label>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 px-4 py-2.5 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm text-neutral-500 dark:text-neutral-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25m-13.5-9l3-3m0 0l3 3m-3-3v12" />
                            </svg>
                            Pilih File (max 10MB)
                            <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.avi" @change="onFileSelect" class="hidden" />
                        </label>
                        <span class="text-xs text-neutral-400">PDF, JPG/PNG, MP4/MOV/AVI</span>
                    </div>

                    <ul v-if="attachments.length" class="mt-3 space-y-1.5">
                        <li v-for="(file, i) in attachments" :key="i" class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                            </svg>
                            <span class="flex-1 truncate">{{ file.name }}</span>
                            <span class="text-xs text-neutral-400">({{ (file.size / 1024 / 1024).toFixed(1) }} MB)</span>
                            <button type="button" @click="removeAttachment(i)" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    </ul>

                    <p v-for="(err, i) in attachmentErrors" :key="'err-' + i" class="mt-1 text-xs text-red-500">{{ err }}</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Mengirim...' : 'Kirim Ticket' }}
                    </button>
                    <Link href="/tickets" class="px-4 py-2.5 text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors">Batal</Link>
                </div>
            </form>
        </div>
    </ClientLayout>
</template>
