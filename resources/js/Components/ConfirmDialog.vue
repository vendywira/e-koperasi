<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'primary';
    loading?: boolean;
}>();

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') emit('cancel');
}

watch(() => props.show, (v) => {
    if (v) window.addEventListener('keydown', onKeydown);
    else window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" @click.self="emit('cancel')">
                <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="scale-95 opacity-0" leave-active-class="transition duration-150 ease-in" leave-to-class="scale-95 opacity-0">
                    <div v-if="show" class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-xl max-w-sm w-full p-6 space-y-4">
                        <!-- Icon -->
                        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto"
                            :class="variant === 'danger' ? 'bg-red-100 dark:bg-red-900/20' : 'bg-primary-100 dark:bg-primary-900/20'">
                            <svg v-if="variant === 'danger'" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                            <svg v-else class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5m-2.25-2.25v4.5m0 0l3 3m-3-3l-3 3m3-3V3.375"/>
                            </svg>
                        </div>

                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ title || 'Konfirmasi' }}</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ message }}</p>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button @click="emit('cancel')" :disabled="loading"
                                class="flex-1 px-4 py-2.5 border-2 border-neutral-200 dark:border-neutral-700 rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition disabled:opacity-50 cursor-pointer min-h-[44px]">
                                {{ cancelText || 'Batal' }}
                            </button>
                            <button @click="emit('confirm')" :disabled="loading"
                                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium text-white transition disabled:opacity-50 cursor-pointer min-h-[44px]"
                                :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'">
                                <span v-if="loading" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-1.5 align-middle"></span>
                                {{ loading ? 'Memproses...' : (confirmText || 'Konfirmasi') }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>