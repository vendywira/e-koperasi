<script setup lang="ts">
import { ref, computed } from 'vue';
import MediaUploader from '@/Components/MediaUploader.vue';

const props = withDefaults(defineProps<{
    modelValue: string;
    label: string;
    helpText?: string;
    accept?: string;
    mediaType?: 'image' | 'video' | 'all';
}>(), {
    helpText: '',
    accept: 'image/*,.pdf,.svg',
    mediaType: 'image',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const showPicker = ref(false);

const isVideoUrl = computed(() => {
    if (!props.modelValue) return false;
    return /\.(mp4|webm|ogg|mov|avi|wmv)(\?.*)?$/i.test(props.modelValue);
});

const pickerTitle = computed(() => {
    if (props.mediaType === 'video') return 'Pilih Video';
    if (props.mediaType === 'all') return 'Pilih Media';
    return 'Pilih Gambar';
});

const placeholderText = computed(() => {
    if (props.mediaType === 'video') return 'URL video...';
    if (props.mediaType === 'all') return 'URL media...';
    return 'URL gambar...';
});
</script>

<template>
    <div class="space-y-1.5">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ label }}
        </label>

        <div class="flex items-start gap-3">
            <!-- Preview -->
            <div
                class="w-20 h-20 rounded-lg border border-neutral-300 dark:border-neutral-700 overflow-hidden flex-shrink-0 bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center"
            >
                <!-- Image preview -->
                <img
                    v-if="modelValue && !isVideoUrl"
                    :src="modelValue"
                    alt="Preview"
                    class="w-full h-full object-cover"
                    @error="($event.target as HTMLImageElement).style.display = 'none'"
                />
                <!-- Video preview -->
                <div v-else-if="modelValue && isVideoUrl" class="relative w-full h-full flex items-center justify-center bg-neutral-900">
                    <video
                        :src="modelValue"
                        class="w-full h-full object-cover"
                        preload="metadata"
                        muted
                    />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-black/50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Empty state -->
                <svg v-else class="w-8 h-8 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                </svg>
            </div>

            <div class="flex-1 space-y-2">
                <div class="flex gap-2">
                    <input
                        :value="modelValue"
                        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                        type="text"
                        :placeholder="placeholderText"
                        class="flex-1 px-3.5 py-2 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 transition-all"
                    />
                    <button
                        type="button"
                        @click="showPicker = !showPicker"
                        class="px-3 py-2 text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors flex-shrink-0"
                    >
                        Pilih
                    </button>
                    <button
                        v-if="modelValue"
                        type="button"
                        @click="emit('update:modelValue', '')"
                        class="px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors flex-shrink-0"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Media Picker Popover -->
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
                    v-if="showPicker"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                    @click.self="showPicker = false"
                >
                    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[85vh] flex flex-col border border-neutral-200 dark:border-neutral-800">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-white">{{ pickerTitle }}</h3>
                            <button
                                @click="showPicker = false"
                                class="p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4">
                            <MediaUploader
                                directory="cms"
                                :select-mode="true"
                                :accept="accept"
                                @select="(url: string) => { emit('update:modelValue', url); showPicker = false; }"
                            />
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
