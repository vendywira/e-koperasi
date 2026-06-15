<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { index as mediaIndex, store as mediaStore, destroy as mediaDestroy } from '@/routes/admin/cms/media';

interface MediaFile {
    name: string;
    path: string;
    url: string;
    size: number;
    modified: number;
}

const emit = defineEmits<{
    (e: 'select', url: string): void;
}>();

const props = defineProps<{
    directory?: string;
    selectMode?: boolean;
}>();

const files = ref<MediaFile[]>([]);
const loading = ref(false);
const uploading = ref(false);
const dragover = ref(false);
const searchQuery = ref('');
const fileInput = ref<HTMLInputElement | null>(null);
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);

const directory = computed(() => props.directory || 'cms');

const filteredFiles = computed(() => {
    if (!searchQuery.value) return files.value;
    const q = searchQuery.value.toLowerCase();
    return files.value.filter((f) => f.name.toLowerCase().includes(q));
});

function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.value = { message, type };
    setTimeout(() => (toast.value = null), 3000);
}

async function loadFiles() {
    loading.value = true;
    try {
        const res = await fetch(mediaIndex({ query: { directory: directory.value } }).url);
        const data = await res.json();
        files.value = data.files || [];
    } catch {
        showToast('Gagal memuat file', 'error');
    } finally {
        loading.value = false;
    }
}

async function uploadFiles(fileList: FileList) {
    uploading.value = true;
    let successCount = 0;

    for (const file of Array.from(fileList)) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('directory', directory.value);

        try {
            const res = await fetch(mediaStore().url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '',
                },
                body: formData,
            });

            if (res.ok) {
                successCount++;
            }
        } catch {
            // continue with next file
        }
    }

    uploading.value = false;
    if (successCount > 0) {
        showToast(`${successCount} file berhasil di-upload`);
        await loadFiles();
    } else {
        showToast('Upload gagal', 'error');
    }
}

function handleDrop(e: DragEvent) {
    dragover.value = false;
    if (e.dataTransfer?.files.length) {
        uploadFiles(e.dataTransfer.files);
    }
}

function handleFileInput(e: Event) {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        uploadFiles(input.files);
        input.value = '';
    }
}

async function deleteFile(file: MediaFile) {
    if (!confirm(`Hapus file "${file.name}"?`)) return;

    try {
        const res = await fetch(mediaDestroy().url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '',
            },
            body: JSON.stringify({ path: file.path }),
        });

        if (res.ok) {
            showToast(`File "${file.name}" dihapus`);
            files.value = files.value.filter((f) => f.path !== file.path);
        }
    } catch {
        showToast('Gagal menghapus file', 'error');
    }
}

function selectFile(file: MediaFile) {
    emit('select', file.url);
}

function formatSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function isImage(filename: string): boolean {
    return /\.(jpg|jpeg|png|gif|webp|svg|ico)$/i.test(filename);
}

onMounted(loadFiles);
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Media Library</h3>
            <button
                @click="fileInput?.click()"
                :disabled="uploading"
                class="px-3 py-1.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors disabled:opacity-50"
            >
                {{ uploading ? 'Upload...' : 'Upload File' }}
            </button>
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*,.pdf,.svg"
                class="hidden"
                @change="handleFileInput"
            />
        </div>

        <!-- Drop Zone -->
        <div
            @dragover.prevent="dragover = true"
            @dragleave="dragover = false"
            @drop.prevent="handleDrop"
            :class="[
                'border-2 border-dashed rounded-xl p-8 text-center mb-4 transition-colors',
                dragover
                    ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20'
                    : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600',
            ]"
        >
            <div v-if="uploading" class="text-sm text-neutral-500 dark:text-neutral-400">
                <div class="w-8 h-8 border-2 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto mb-2" />
                Mengupload file...
            </div>
            <div v-else class="text-sm text-neutral-400 dark:text-neutral-500">
                <svg class="w-8 h-8 mx-auto mb-2 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <p class="font-medium text-neutral-600 dark:text-neutral-300">Drag & drop file ke sini</p>
                <p class="text-xs mt-1">atau klik tombol Upload File</p>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-3">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari file..."
                class="w-full px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400"
            />
        </div>

        <!-- File List -->
        <div class="flex-1 overflow-y-auto space-y-2">
            <div v-if="loading" class="text-center py-8 text-sm text-neutral-400">Memuat file...</div>
            <div v-else-if="filteredFiles.length === 0" class="text-center py-8 text-sm text-neutral-400">
                Belum ada file
            </div>
            <div
                v-for="file in filteredFiles"
                :key="file.path"
                class="flex items-center gap-3 p-3 bg-neutral-50 dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-600 transition-colors group"
            >
                <!-- Preview -->
                <div class="w-10 h-10 rounded-lg bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                    <img
                        v-if="isImage(file.name)"
                        :src="file.url"
                        :alt="file.name"
                        class="w-full h-full object-cover"
                    />
                    <svg v-else class="w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ file.name }}</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatSize(file.size) }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        v-if="selectMode"
                        @click="selectFile(file)"
                        class="px-2 py-1 text-[10px] font-semibold text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 rounded hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors"
                    >
                        Pilih
                    </button>
                    <button
                        @click="deleteFile(file)"
                        class="p-1 text-neutral-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                        title="Hapus"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                    <button
                        @click="navigator.clipboard.writeText(file.url); showToast('URL disalin')"
                        class="p-1 text-neutral-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors"
                        title="Copy URL"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <Teleport to="body">
            <Transition
                enter-active-class="transform transition duration-300 ease-out"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transform transition duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-2 opacity-0"
            >
                <div
                    v-if="toast"
                    class="fixed bottom-6 right-6 z-[100] px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
                    :class="toast.type === 'success' ? 'bg-primary-600 text-white' : 'bg-red-600 text-white'"
                >
                    {{ toast.message }}
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
