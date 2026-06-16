<script setup lang="ts">
import { ref, computed } from 'vue';

const ICONS = [
    { value: 'shield', label: 'Shield', emoji: '🛡️' },
    { value: 'zap', label: 'Zap', emoji: '⚡' },
    { value: 'clock', label: 'Clock', emoji: '⏰' },
    { value: 'sparkles', label: 'Sparkles', emoji: '✨' },
    { value: 'gift', label: 'Gift', emoji: '🎁' },
    { value: 'wallet', label: 'Wallet', emoji: '👛' },
    { value: 'piggy-bank', label: 'Piggy Bank', emoji: '🐷' },
    { value: 'calendar-days', label: 'Calendar', emoji: '📅' },
    { value: 'calendar-clock', label: 'Calendar Clock', emoji: '📆' },
    { value: 'trending-down', label: 'Trending', emoji: '📉' },
    { value: 'trending-up', label: 'Growth', emoji: '📈' },
    { value: 'briefcase', label: 'Briefcase', emoji: '💼' },
    { value: 'users', label: 'Users', emoji: '👥' },
    { value: 'smartphone', label: 'Smartphone', emoji: '📱' },
    { value: 'cog', label: 'Settings', emoji: '⚙️' },
    { value: 'file-text', label: 'File', emoji: '📄' },
    { value: 'bar-chart', label: 'Chart', emoji: '📊' },
    { value: 'building', label: 'Building', emoji: '🏢' },
    { value: 'camera', label: 'Camera', emoji: '📷' },
    { value: 'map-pin', label: 'Map Pin', emoji: '📍' },
    { value: 'route', label: 'Route', emoji: '🗺️' },
    { value: 'badge-dollar-sign', label: 'Dollar', emoji: '💵' },
];

const props = withDefaults(defineProps<{
    modelValue: string;
    label: string;
    helpText?: string;
}>(), {
    helpText: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const searchQuery = ref('');
const showDropdown = ref(false);

const filteredIcons = computed(() => {
    if (!searchQuery.value) return ICONS;
    const q = searchQuery.value.toLowerCase();
    return ICONS.filter(i => i.label.toLowerCase().includes(q) || i.value.toLowerCase().includes(q));
});

const selectedIcon = computed(() => ICONS.find(i => i.value === props.modelValue));
</script>

<template>
    <div class="space-y-1.5 relative">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ label }}
        </label>

        <!-- Selected / Trigger -->
        <button
            type="button"
            @click="showDropdown = !showDropdown"
            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:border-neutral-400 dark:hover:border-neutral-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 transition-all text-left"
        >
            <span v-if="selectedIcon" class="text-lg">{{ selectedIcon.emoji }}</span>
            <svg v-else class="w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            <span :class="selectedIcon ? 'text-neutral-900 dark:text-neutral-100' : 'text-neutral-400 dark:text-neutral-500'">
                {{ selectedIcon ? selectedIcon.label : 'Pilih icon...' }}
            </span>
            <svg class="w-4 h-4 ml-auto text-neutral-400" :class="showDropdown ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
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
                    v-if="showDropdown"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                    @click.self="showDropdown = false"
                >
                    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl w-full max-w-md mx-4 max-h-[70vh] flex flex-col border border-neutral-200 dark:border-neutral-800">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-white">Pilih Icon</h3>
                            <button
                                @click="showDropdown = false"
                                class="p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="p-3 border-b border-neutral-200 dark:border-neutral-800">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari icon..."
                                class="w-full px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400"
                            />
                        </div>
                        <div class="flex-1 overflow-y-auto p-3">
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="icon in filteredIcons"
                                    :key="icon.value"
                                    type="button"
                                    @click="emit('update:modelValue', icon.value); showDropdown = false"
                                    class="flex flex-col items-center gap-1 p-3 rounded-lg transition-all"
                                    :class="modelValue === icon.value
                                        ? 'bg-primary-100 dark:bg-primary-900/30 ring-2 ring-primary-500'
                                        : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                                >
                                    <span class="text-2xl">{{ icon.emoji }}</span>
                                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400 text-center leading-tight">{{ icon.label }}</span>
                                </button>
                            </div>
                            <p v-if="filteredIcons.length === 0" class="text-center py-6 text-sm text-neutral-400">
                                Tidak ada icon yang cocok
                            </p>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
