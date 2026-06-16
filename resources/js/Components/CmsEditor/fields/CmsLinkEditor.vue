<script setup lang="ts">
defineProps<{
    modelValue: { label: string; href: string };
    label: string;
    labelPlaceholder?: string;
    hrefPlaceholder?: string;
    helpText?: string;
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
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <input
                :value="modelValue?.label || ''"
                @input="emit('update:modelValue', { ...modelValue, label: ($event.target as HTMLInputElement).value })"
                type="text"
                :placeholder="labelPlaceholder || 'Label tombol'"
                class="px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 transition-all"
            />
            <input
                :value="modelValue?.href || ''"
                @input="emit('update:modelValue', { ...modelValue, href: ($event.target as HTMLInputElement).value })"
                type="url"
                :placeholder="hrefPlaceholder || 'URL (contoh: /path)'"
                class="px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 transition-all"
            />
        </div>
        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
