<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { index as mediaIndex, store as mediaStore, rename as mediaRename, destroy as mediaDestroy } from '@/routes/admin/cms/media';
import { csrfToken } from '@/lib/csrf';

interface MediaFile {
    name: string;
    path: string;
    url: string;
    webp_url?: string | null;
    webp_url_small?: string | null;
    size: number;
    modified: number;
    type?: string;
}

const emit = defineEmits<{
    (e: 'select', url: string): void;
}>();

const props = withDefaults(defineProps<{
    directory?: string;
    selectMode?: boolean;
    viewMode?: 'grid' | 'list';
    accept?: string;
}>(), {
    directory: 'cms',
    selectMode: false,
    viewMode: 'list',
    accept: 'image/*,video/*,.pdf,.svg',
});

const page = usePage();
const userRole = computed(() => (page.props as any).auth?.user?.role ?? 'editor');
const isAdmin = computed(() => userRole.value === 'admin');

const files = ref<MediaFile[]>([]);
const loading = ref(false);
const uploading = ref(false);
const dragover = ref(false);
const searchQuery = ref('');
const fileInput = ref<HTMLInputElement | null>(null);
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
const filterType = ref<'all' | 'image' | 'video' | 'document'>('all');

// Rename state
const renamingFile = ref<MediaFile | null>(null);
const renameValue = ref('');
const renameInputRef = ref<HTMLInputElement | null>(null);
const showAdvancedFilters = ref(false);

// Date range filters
const dateFrom = ref('');
const dateTo = ref('');

// Size range filters
const sizeMin = ref('');
const sizeMax = ref('');

// Sort
const sortBy = ref<'name' | 'date' | 'size'>('date');
const sortOrder = ref<'asc' | 'desc'>('desc');

// Confirm delete state
const confirmDeleteFile = ref<MediaFile | null>(null);

// Preview modal state
const previewFile = ref<MediaFile | null>(null);
const showPreview = ref(false);

const previewIndex = computed(() => {
    if (!previewFile.value) return -1;
    return filteredFiles.value.findIndex((f) => f.path === previewFile.value!.path);
});

const hasPrev = computed(() => previewIndex.value > 0);
const hasNext = computed(() => previewIndex.value < filteredFiles.value.length - 1);

const directory = computed(() => props.directory || 'cms');

const SIZE_PRESETS = [
    { label: '< 100 KB', min: 0, max: 102400 },
    { label: '100 KB – 1 MB', min: 102400, max: 1048576 },
    { label: '1 MB – 10 MB', min: 1048576, max: 10485760 },
    { label: '> 10 MB', min: 10485760, max: Infinity },
] as const;

const activePreset = ref<string>('');

const today = computed(() => {
    const d = new Date();
    return d.toISOString().split('T')[0];
});

function applySizePreset(label: string) {
    if (activePreset.value === label) {
        activePreset.value = '';
        sizeMin.value = '';
        sizeMax.value = '';
        return;
    }
    const preset = SIZE_PRESETS.find((p) => p.label === label);
    if (preset) {
        activePreset.value = label;
        sizeMin.value = preset.min === 0 ? '' : String(preset.min);
        sizeMax.value = preset.max === Infinity ? '' : String(preset.max);
    }
}

const filteredFiles = computed(() => {
    let result = files.value;

    // Filter by search query
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter((f) => f.name.toLowerCase().includes(q));
    }

    // Filter by type
    if (filterType.value !== 'all') {
        result = result.filter((f) => f.type === filterType.value);
    }

    // Filter by date range
    if (dateFrom.value) {
        const from = new Date(dateFrom.value).getTime();
        result = result.filter((f) => f.modified >= from);
    }
    if (dateTo.value) {
        // Include the entire 'to' day
        const to = new Date(dateTo.value + 'T23:59:59').getTime();
        result = result.filter((f) => f.modified <= to);
    }

    // Filter by size range
    if (sizeMin.value) {
        const min = parseInt(sizeMin.value, 10);
        if (!isNaN(min)) {
            result = result.filter((f) => f.size >= min);
        }
    }
    if (sizeMax.value) {
        const max = parseInt(sizeMax.value, 10);
        if (!isNaN(max)) {
            result = result.filter((f) => f.size <= max);
        }
    }

    // Sort
    result.sort((a, b) => {
        let cmp = 0;
        if (sortBy.value === 'name') {
            cmp = a.name.localeCompare(b.name);
        } else if (sortBy.value === 'date') {
            cmp = a.modified - b.modified;
        } else if (sortBy.value === 'size') {
            cmp = a.size - b.size;
        }
        return sortOrder.value === 'desc' ? -cmp : cmp;
    });

    return result;
});

const imageFiles = computed(() => files.value.filter((f) => f.type === 'image'));
const videoFiles = computed(() => files.value.filter((f) => f.type === 'video'));
const documentFiles = computed(() => files.value.filter((f) => f.type === 'document' || f.type === 'other'));

const hasActiveFilters = computed(() => {
    return (
        searchQuery.value !== '' ||
        filterType.value !== 'all' ||
        dateFrom.value !== '' ||
        dateTo.value !== '' ||
        sizeMin.value !== '' ||
        sizeMax.value !== '' ||
        sortBy.value !== 'date' ||
        sortOrder.value !== 'desc'
    );
});

function resetFilters() {
    searchQuery.value = '';
    filterType.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    sizeMin.value = '';
    sizeMax.value = '';
    sortBy.value = 'date';
    sortOrder.value = 'desc';
    activePreset.value = '';
    showAdvancedFilters.value = false;
}

function formatDate(timestamp: number): string {
    const d = new Date(timestamp);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) return 'Hari ini';
    if (days === 1) return 'Kemarin';
    if (days < 7) return `${days} hari lalu`;

    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: d.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
    });
}

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
                    'X-CSRF-TOKEN': csrfToken(),
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

function requestDelete(file: MediaFile) {
    confirmDeleteFile.value = file;
}

function cancelDelete() {
    confirmDeleteFile.value = null;
}

async function confirmDelete() {
    const file = confirmDeleteFile.value;
    if (!file) return;

    confirmDeleteFile.value = null;

    try {
        const res = await fetch(mediaDestroy().url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ path: file.path }),
        });

        if (res.ok) {
            showToast(`File "${file.name}" dihapus`);
            files.value = files.value.filter((f) => f.path !== file.path);
            // Close preview if the deleted file was being previewed
            if (previewFile.value?.path === file.path) {
                closePreview();
            }
        } else {
            const err = await res.json().catch(() => ({}));
            showToast(err.error || 'Gagal menghapus file', 'error');
        }
    } catch {
        showToast('Gagal menghapus file', 'error');
    }
}

async function copyUrl(url: string) {
    try {
        await navigator.clipboard.writeText(url);
        showToast('URL disalin ke clipboard');
    } catch {
        // Fallback for older browsers or non-HTTPS
        const textarea = document.createElement('textarea');
        textarea.value = url;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showToast('URL disalin ke clipboard');
        } catch {
            showToast('Gagal menyalin URL. Salin manual: ' + url, 'error');
        }
        document.body.removeChild(textarea);
    }
}

function selectFile(file: MediaFile) {
    emit('select', file.webp_url ?? file.url);
}

function startRename(file: MediaFile) {
    renamingFile.value = file;
    renameValue.value = file.name;
    // Focus input on next tick after DOM update
    setTimeout(() => renameInputRef.value?.focus(), 50);
}

function cancelRename() {
    renamingFile.value = null;
    renameValue.value = '';
}

async function confirmRename() {
    if (!renamingFile.value || !renameValue.value.trim()) return;

    const newName = renameValue.value.trim();
    const oldName = renamingFile.value.name;

    if (newName === oldName) {
        cancelRename();
        return;
    }        try {
            const res = await fetch(mediaRename().url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    path: renamingFile.value.path,
                    name: newName,
                }),
            });

        if (res.ok) {
            const data = await res.json();
            // Update the file in the list
            const idx = files.value.findIndex((f) => f.path === renamingFile.value!.path);
            if (idx !== -1 && data.file) {
                files.value[idx] = data.file;
            }
            showToast(`File "${oldName}" → "${data.file?.name || newName}"`);
            cancelRename();
        } else {
            const err = await res.json();
            showToast(err.error || 'Gagal rename file', 'error');
        }
    } catch {
        showToast('Gagal rename file', 'error');
    }
}

function openPreview(file: MediaFile) {
    previewFile.value = file;
    showPreview.value = true;
}

function closePreview() {
    showPreview.value = false;
    previewFile.value = null;
}

function goToPrev() {
    const idx = previewIndex.value;
    if (idx > 0) {
        previewFile.value = filteredFiles.value[idx - 1];
    }
}

function goToNext() {
    const idx = previewIndex.value;
    if (idx < filteredFiles.value.length - 1) {
        previewFile.value = filteredFiles.value[idx + 1];
    }
}

function handlePreviewKeydown(e: KeyboardEvent) {
    // Don't close preview if delete dialog is open
    if (confirmDeleteFile.value) return;

    if (e.key === 'Escape') {
        closePreview();
    } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        goToPrev();
    } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        goToNext();
    }
}

function handleRenameKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        confirmRename();
    } else if (e.key === 'Escape') {
        cancelRename();
    }
}

function formatSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function isImage(filename: string): boolean {
    return /\.(jpg|jpeg|png|gif|webp|svg|ico)$/i.test(filename);
}

function isVideo(filename: string): boolean {
    return /\.(mp4|webm|ogg|mov|avi|wmv)$/i.test(filename);
}

function getFileIcon(filename: string): string {
    if (isImage(filename)) return '🖼️';
    if (isVideo(filename)) return '🎬';
    if (/\.pdf$/i.test(filename)) return '📄';
    return '📁';
}

onMounted(loadFiles);
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <!-- Type Filter -->
                <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 rounded-lg p-0.5">
                    <button
                        @click="filterType = 'all'"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                        :class="filterType === 'all'
                            ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                            : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                    >
                        Semua ({{ files.length }})
                    </button>
                    <button
                        @click="filterType = 'image'"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                        :class="filterType === 'image'
                            ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                            : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                    >
                        🖼️ Gambar ({{ imageFiles.length }})
                    </button>
                    <button
                        @click="filterType = 'video'"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                        :class="filterType === 'video'
                            ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                            : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                    >
                        🎬 Video ({{ videoFiles.length }})
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Editor badge -->
                <span v-if="!isAdmin" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-medium rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    Hanya lihat
                </span>
                <template v-if="isAdmin">
                    <button
                        @click="fileInput?.click()"
                        :disabled="uploading"
                        class="px-3 py-1.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ uploading ? 'Upload...' : 'Upload File' }}
                    </button>
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        :accept="accept"
                        class="hidden"
                        @change="handleFileInput"
                    />
                </template>
            </div>
        </div>

        <!-- Drop Zone (admin only) -->
        <div v-if="isAdmin"
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
                <p>Mengupload file...</p>
                <p class="text-xs mt-1 text-neutral-400">Jangan tutup halaman ini</p>
            </div>
            <div v-else class="text-sm text-neutral-400 dark:text-neutral-500">
                <svg class="w-10 h-10 mx-auto mb-2 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <p class="font-medium text-neutral-600 dark:text-neutral-300">Drag & drop file ke sini</p>
                <p class="text-xs mt-1">atau klik tombol Upload File — gambar & video didukung</p>
            </div>
        </div>

        <!-- Read-only notice (editor) -->
        <div v-else class="mb-4 px-4 py-3 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 flex items-center gap-2.5">
            <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Anda dalam mode <strong class="text-neutral-700 dark:text-neutral-300">read-only</strong>. Hanya admin yang dapat mengupload atau menghapus file.
            </p>
        </div>

        <!-- Search + Filter Toggle -->
        <div class="flex items-center gap-2 mb-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari file..."
                    class="w-full pl-9 pr-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400 transition-all"
                />
            </div>
            <button
                @click="showAdvancedFilters = !showAdvancedFilters"
                class="px-3 py-2 text-xs font-medium rounded-lg transition-colors flex items-center gap-1.5"
                :class="showAdvancedFilters || hasActiveFilters
                    ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
                    : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-neutral-700'"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                Filter
            </button>
            <button
                v-if="hasActiveFilters"
                @click="resetFilters"
                class="px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
            >
                Reset
            </button>
        </div>

        <!-- Advanced Filters -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
        >
            <div v-if="showAdvancedFilters" class="mb-4 overflow-hidden">
                <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 space-y-4">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                            Tanggal Upload
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="dateFrom"
                                type="date"
                                :max="dateTo || today"
                                class="flex-1 px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white"
                            />
                            <span class="text-xs text-neutral-400">—</span>
                            <input
                                v-model="dateTo"
                                type="date"
                                :min="dateFrom"
                                :max="today"
                                class="flex-1 px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Size Range -->
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                            Ukuran File
                        </label>
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            <button
                                v-for="preset in SIZE_PRESETS"
                                :key="preset.label"
                                @click="applySizePreset(preset.label)"
                                class="px-2.5 py-1 text-[10px] font-medium rounded-lg transition-colors"
                                :class="activePreset === preset.label
                                    ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
                                    : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-600'
                                "
                            >
                                {{ preset.label }}
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input
                                    v-model="sizeMin"
                                    type="number"
                                    min="0"
                                    placeholder="Min (bytes)"
                                    class="w-full px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400"
                                />
                            </div>
                            <span class="text-xs text-neutral-400">—</span>
                            <div class="relative flex-1">
                                <input
                                    v-model="sizeMax"
                                    type="number"
                                    min="0"
                                    placeholder="Max (bytes)"
                                    class="w-full px-3 py-1.5 text-xs bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                            Urutkan
                        </label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 rounded-lg p-0.5">
                                <button
                                    @click="sortBy = 'date'"
                                    class="px-2.5 py-1.5 rounded-md text-[10px] font-medium transition-colors"
                                    :class="sortBy === 'date'
                                        ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                                        : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                                >
                                    Tanggal
                                </button>
                                <button
                                    @click="sortBy = 'size'"
                                    class="px-2.5 py-1.5 rounded-md text-[10px] font-medium transition-colors"
                                    :class="sortBy === 'size'
                                        ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                                        : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                                >
                                    Ukuran
                                </button>
                                <button
                                    @click="sortBy = 'name'"
                                    class="px-2.5 py-1.5 rounded-md text-[10px] font-medium transition-colors"
                                    :class="sortBy === 'name'
                                        ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                                        : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                                >
                                    Nama
                                </button>
                            </div>
                            <button
                                @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                                class="p-1.5 rounded-lg transition-colors"
                                :class="sortOrder === 'asc'
                                    ? 'text-primary-600 bg-primary-100 dark:bg-primary-900/30'
                                    : 'text-neutral-500 bg-neutral-100 dark:bg-neutral-800 hover:text-neutral-700 dark:hover:text-neutral-300'
                                "
                                :title="sortOrder === 'asc' ? 'Naik' : 'Turun'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path v-if="sortOrder === 'asc'" stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21L21 17.25" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- File List -->
        <div class="flex-1 overflow-y-auto">
            <!-- Loading -->
            <div v-if="loading" class="text-center py-12 text-sm text-neutral-400">
                <div class="w-8 h-8 border-2 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto mb-3" />
                Memuat file...
            </div>

            <!-- Empty -->
            <div v-else-if="filteredFiles.length === 0" class="text-center py-12 text-sm text-neutral-400">
                <span class="text-3xl block mb-3">📂</span>
                <p class="font-medium text-neutral-500 dark:text-neutral-300">Belum ada file</p>
                <p class="text-xs mt-1">Drag & drop file atau klik Upload untuk mulai</p>
            </div>

            <!-- Grid View -->
            <div v-else-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                <div
                    v-for="file in filteredFiles"
                    :key="file.path"
                    class="group relative bg-neutral-50 dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition-all overflow-hidden"
                >
                    <!-- Thumbnail -->
                    <div
                        class="aspect-square bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center overflow-hidden cursor-pointer"
                        @click.stop="openPreview(file)"
                    >
                        <img
                            v-if="isImage(file.name)"
                            :src="file.webp_url_small ?? file.webp_url ?? file.url"
                            :alt="file.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div v-else-if="isVideo(file.name)" class="relative w-full h-full flex items-center justify-center bg-neutral-800">
                            <video
                                :src="file.url"
                                class="w-full h-full object-cover"
                                preload="metadata"
                                muted
                                @mouseenter="($event.target as HTMLVideoElement).play()"
                                @mouseleave="($event.target as HTMLVideoElement).pause(); ($event.target as HTMLVideoElement).currentTime = 0"
                                loop
                            />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-black/50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <span v-else class="text-3xl">{{ getFileIcon(file.name) }}</span>
                    </div>

                    <!-- File info overlay -->
                    <div class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                        <button
                            v-if="selectMode"
                            @click.stop="selectFile(file)"
                            class="p-1.5 bg-white dark:bg-neutral-800 rounded-lg shadow-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors"
                            title="Pilih"
                        >
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </button>
                        <button
                            @click.stop="copyUrl(file.url)"
                            class="p-1.5 bg-white dark:bg-neutral-800 rounded-lg shadow-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors"
                            title="Copy URL"
                        >
                            <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                        </button>
                        <button
                            v-if="isAdmin && renamingFile?.path !== file.path"
                            @click.stop="startRename(file)"
                            class="p-1.5 bg-white dark:bg-neutral-800 rounded-lg shadow-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors"
                            title="Rename"
                        >
                            <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>
                        <button
                            v-if="isAdmin"
                            @click.stop="requestDelete(file)"
                            class="p-1.5 bg-white dark:bg-neutral-800 rounded-lg shadow-sm hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Hapus"
                        >
                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>

                    <!-- Type badge -->
                    <div class="absolute bottom-1.5 left-1.5">
                        <span class="px-1.5 py-0.5 text-[9px] font-medium rounded bg-black/40 text-white backdrop-blur-sm"
                            :class="file.type === 'video' ? 'bg-purple-600/70' : file.type === 'image' ? 'bg-blue-600/70' : 'bg-neutral-600/70'"
                        >
                            {{ file.type === 'video' ? '🎬' : file.type === 'image' ? '🖼️' : '📄' }}
                            {{ file.type }}
                        </span>
                    </div>

                    <!-- Name -->
                    <div class="px-2.5 py-2">
                        <p class="text-[10px] font-medium text-neutral-700 dark:text-neutral-300 truncate">{{ file.name }}</p>
                        <p class="text-[9px] text-neutral-400">{{ formatSize(file.size) }}</p>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="space-y-1.5">
                <div
                    v-for="file in filteredFiles"
                    :key="file.path"
                    class="flex items-center gap-3 p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors group"
                >
                    <!-- Preview -->
                    <div
                        class="w-11 h-11 rounded-lg bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center overflow-hidden flex-shrink-0 cursor-pointer"
                        @click="openPreview(file)"
                    >
                        <img
                            v-if="isImage(file.name)"
                            :src="file.webp_url_small ?? file.webp_url ?? file.url"
                            :alt="file.name"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                        <div v-else-if="isVideo(file.name)" class="relative w-full h-full flex items-center justify-center">
                            <video :src="file.url" class="w-full h-full object-cover" preload="metadata" muted />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                        </div>
                        <span v-else class="text-lg">{{ getFileIcon(file.name) }}</span>
                    </div>

                    <!-- Type pill -->
                    <div class="flex-shrink-0">
                        <span class="inline-block px-1.5 py-0.5 text-[9px] font-medium rounded"
                            :class="file.type === 'video' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : file.type === 'image' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400'"
                        >
                            {{ file.type || 'unknown' }}
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <!-- Inline rename input -->
                        <div v-if="renamingFile?.path === file.path" class="flex items-center gap-1.5">
                            <input
                                ref="renameInputRef"
                                v-model="renameValue"
                                type="text"
                                class="flex-1 px-2 py-1 text-xs bg-white dark:bg-neutral-800 border border-primary-400 dark:border-primary-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white"
                                @keydown="handleRenameKeydown"
                            />
                            <button
                                @click="confirmRename"
                                class="p-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                                title="Simpan"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </button>
                            <button
                                @click="cancelRename"
                                class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                                title="Batal"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <!-- Normal display -->
                        <template v-else>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ file.name }}</p>
                            <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                                <span>{{ formatSize(file.size) }}</span>
                                <span class="text-neutral-300 dark:text-neutral-600">·</span>
                                <span>{{ formatDate(file.modified) }}</span>
                            </div>
                        </template>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                        <button
                            v-if="selectMode"
                            @click="selectFile(file)"
                            class="px-2.5 py-1 text-[10px] font-semibold text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors"
                        >
                            Pilih
                        </button>
                        <button
                            @click="copyUrl(file.url)"
                            class="p-1.5 text-neutral-400 hover:text-primary-500 dark:hover:text-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                            title="Copy URL"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                        </button>
                        <button
                            v-if="isAdmin && renamingFile?.path !== file.path"
                            @click="startRename(file)"
                            class="p-1.5 text-neutral-400 hover:text-primary-500 dark:hover:text-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                            title="Rename"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>
                        <button
                            v-if="isAdmin"
                            @click="requestDelete(file)"
                            class="p-1.5 text-neutral-400 hover:text-red-500 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Hapus"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
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

        <!-- Preview Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showPreview && previewFile"
                    class="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 backdrop-blur-sm"
                    @click.self="closePreview"
                    @keydown.window="handlePreviewKeydown"
                >
                    <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] flex flex-col overflow-hidden border border-neutral-200 dark:border-neutral-800">
                        <!-- Preview Header -->
                        <div class="flex items-center justify-between px-5 py-3 border-b border-neutral-200 dark:border-neutral-800">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-lg flex-shrink-0">{{ isImage(previewFile.name) ? '🖼️' : isVideo(previewFile.name) ? '🎬' : '📄' }}</span>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ previewFile.name }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-0.5 text-[10px] font-medium rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400">
                                    {{ previewFile.type || 'unknown' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Position counter -->
                                <span class="text-xs text-neutral-500 dark:text-neutral-400 tabular-nums">
                                    {{ previewIndex + 1 }} / {{ filteredFiles.length }}
                                </span>
                                <button
                                    @click="closePreview"
                                    class="p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors flex-shrink-0"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Preview Body -->
                        <div class="flex-1 overflow-y-auto bg-neutral-950 flex items-center justify-center min-h-[300px] relative">
                            <!-- Prev Button (floating left) -->
                            <button
                                v-if="hasPrev"
                                @click="goToPrev"
                                class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/90 dark:bg-neutral-800/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-neutral-700 dark:text-neutral-200 hover:bg-white dark:hover:bg-neutral-700 hover:scale-105 transition-all"
                                title="Sebelumnya (←)"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Next Button (floating right) -->
                            <button
                                v-if="hasNext"
                                @click="goToNext"
                                class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/90 dark:bg-neutral-800/90 backdrop-blur-sm shadow-lg flex items-center justify-center text-neutral-700 dark:text-neutral-200 hover:bg-white dark:hover:bg-neutral-700 hover:scale-105 transition-all"
                                title="Selanjutnya (→)"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>

                            <!-- Image Preview -->
                            <img
                                v-if="isImage(previewFile.name)"
                                :src="previewFile.webp_url ?? previewFile.url"
                                :alt="previewFile.name"
                                :key="previewFile.path"
                                class="max-w-full max-h-[65vh] object-contain"
                            />
                            <!-- Video Preview -->
                            <div v-else-if="isVideo(previewFile.name)" class="w-full max-w-3xl mx-auto p-4">
                                <video
                                    :src="previewFile.url"
                                    class="w-full rounded-lg"
                                    controls
                                    autoplay
                                    :key="previewFile.path"
                                />
                            </div>
                            <!-- Document/Other Preview -->
                            <div v-else class="text-center p-12">
                                <span class="text-5xl block mb-4">{{ getFileIcon(previewFile.name) }}</span>
                                <p class="text-sm text-neutral-400">Pratinjau tidak tersedia untuk jenis file ini</p>
                            </div>
                        </div>

                        <!-- Preview Footer -->
                        <div class="px-5 py-3 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                            <div class="flex items-center gap-4 text-xs text-neutral-500 dark:text-neutral-400">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    {{ formatSize(previewFile.size) }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    {{ formatDate(previewFile.modified) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="copyUrl(previewFile.url)"
                                    class="px-3 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                    Salin URL
                                </button>
                                <button
                                    v-if="selectMode"
                                    @click="selectFile(previewFile!); closePreview()"
                                    class="px-4 py-1.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    Pilih File Ini
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Confirm Delete Dialog -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="confirmDeleteFile"
                    class="fixed inset-0 z-[95] flex items-center justify-center bg-black/40 backdrop-blur-sm"
                    @click.self="cancelDelete"
                    @keydown.window.escape="cancelDelete"
                >
                    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-neutral-200 dark:border-neutral-800">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Hapus File</h3>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                                        Tindakan ini tidak bisa dibatalkan.
                                    </p>
                                </div>
                            </div>
                            <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg px-3 py-2 mb-4 border border-neutral-200 dark:border-neutral-700">
                                <p class="text-xs font-medium text-neutral-700 dark:text-neutral-300 truncate">{{ confirmDeleteFile.name }}</p>
                                <p class="text-[10px] text-neutral-400 mt-0.5">{{ formatSize(confirmDeleteFile.size) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="cancelDelete"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="confirmDelete"
                                    class="flex-1 px-3 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
