<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: boolean;
    label: string;
    helpText?: string;
    trueLabel?: string;
    falseLabel?: string;
}>(), {
    helpText: '',
    trueLabel: 'Ya',
    falseLabel: 'Tidak',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();
</script>

<template>
    <div class="space-y-1.5">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ label }}
        </label>
        <div class="flex items-center gap-3">
            <button
                type="button"
                role="switch"
                :aria-checked="modelValue"
                @click="emit('update:modelValue', !modelValue)"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                :class="modelValue
                    ? 'bg-primary-600 dark:bg-primary-500'
                    : 'bg-neutral-300 dark:bg-neutral-700'"
            >
                <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200"
                    :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
                />
            </button>
            <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">
                {{ modelValue ? trueLabel : falseLabel }}
            </span>
        </div>
        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
