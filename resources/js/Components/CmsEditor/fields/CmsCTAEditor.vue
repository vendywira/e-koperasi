<script setup lang="ts">
defineProps<{
    modelValue: { label: string; href: string };
    label: string;
    labelPlaceholder?: string;
    hrefPlaceholder?: string;
    helpText?: string;
    variant?: 'primary' | 'secondary';
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: { label: string; href: string }): void;
}>();
</script>

<template>
    <div class="space-y-1.5">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ label }}
        </label>
        <div
            class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-lg border"
            :class="variant === 'secondary'
                ? 'bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-neutral-700'
                : 'bg-primary-50/50 dark:bg-primary-900/10 border-primary-200 dark:border-primary-800/50'"
        >
            <div class="space-y-1">
                <span class="text-[10px] font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Label</span>
                <input
                    :value="modelValue?.label || ''"
                    @input="emit('update:modelValue', { ...modelValue, label: ($event.target as HTMLInputElement).value })"
                    type="text"
                    :placeholder="labelPlaceholder || 'Label tombol'"
                    class="w-full px-3 py-2 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 transition-all"
                />
            </div>
            <div class="space-y-1">
                <span class="text-[10px] font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">URL</span>
                <input
                    :value="modelValue?.href || ''"
                    @input="emit('update:modelValue', { ...modelValue, href: ($event.target as HTMLInputElement).value })"
                    type="url"
                    :placeholder="hrefPlaceholder || '/path'"
                    class="w-full px-3 py-2 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 transition-all"
                />
            </div>
        </div>
        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
