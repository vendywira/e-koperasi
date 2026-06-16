<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/MediaUploader.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const viewMode = ref<'grid' | 'list'>('grid');
const showUploader = ref(false);
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);

function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.value = { message, type };
    setTimeout(() => (toast.value = null), 3000);
}

function handleMediaSelect(url: string) {
    navigator.clipboard.writeText(url);
    showToast('URL media disalin ke clipboard');
}
</script>

<template>
    <AdminLayout title="Media Management">
        <div class="p-4 sm:p-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Media Library</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                        Kelola semua file media — upload, pilih, dan hapus gambar & video
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- View Toggle -->
                    <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 rounded-lg p-0.5">
                        <button
                            @click="viewMode = 'list'"
                            class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                            :class="viewMode === 'list'
                                ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                                : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </button>
                        <button
                            @click="viewMode = 'grid'"
                            class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                            :class="viewMode === 'grid'
                                ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                                : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300'"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Media Uploader in full-page mode -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm">
                <div class="p-4 sm:p-6">
                    <MediaUploader
                        :view-mode="viewMode"
                        @select="handleMediaSelect"
                    />
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
    </AdminLayout>
</template>
