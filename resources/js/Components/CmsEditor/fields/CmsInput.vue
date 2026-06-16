<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: string | number;
    label: string;
    placeholder?: string;
    helpText?: string;
    type?: 'text' | 'number' | 'url' | 'email';
}>(), {
    placeholder: '',
    helpText: '',
    type: 'text',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="space-y-1.5">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ label }}
        </label>
        <input
            :value="modelValue"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            :type="type"
            :placeholder="placeholder"
            class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 transition-all"
        />
        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
