<script setup lang="ts">
import { computed } from 'vue';
import type { FormField, FieldType, SectionSchema } from './schemas';
import CmsInput from './fields/CmsInput.vue';
import CmsTextarea from './fields/CmsTextarea.vue';
import CmsImagePicker from './fields/CmsImagePicker.vue';
import CmsRepeater from './fields/CmsRepeater.vue';
import CmsGroup from './fields/CmsGroup.vue';
import CmsLinkEditor from './fields/CmsLinkEditor.vue';
import CmsCTAEditor from './fields/CmsCTAEditor.vue';
import CmsIconSelect from './fields/CmsIconSelect.vue';
import CmsBooleanToggle from './fields/CmsBooleanToggle.vue';

const props = defineProps<{
    section: string;
    schema: SectionSchema;
    modelValue: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<string, any>): void;
}>();

const data = computed({
    get: () => props.modelValue || {},
    set: (val: Record<string, any>) => emit('update:modelValue', val),
});

function getDefaultValue(type: FieldType): any {
    switch (type) {
        case 'repeater': return [];
        case 'group': return {};
        case 'cta':
        case 'link': return { label: '', href: '' };
        case 'boolean': return false;
        default: return '';
    }
}

function getFieldValue(field: FormField): any {
    const val = data.value[field.key];
    if (val !== undefined && val !== null) return val;
    return getDefaultValue(field.type);
}

function updateField(key: string, val: any) {
    data.value = { ...data.value, [key]: val };
}

</script>

<template>
    <div class="space-y-6">
        <!-- Section Description -->
        <div v-if="schema.description" class="text-sm text-neutral-500 dark:text-neutral-400 bg-neutral-50 dark:bg-neutral-800/50 rounded-xl px-4 py-3 border border-neutral-200 dark:border-neutral-700">
            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 mt-0.5 text-neutral-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.25c3.225 0 5.854-2.1 5.854-4.625S15.225 2 12 2 6.146 4.1 6.146 6.625 8.775 11.25 12 11.25zm0 0c-2.025 0-3.66-1.35-3.66-3 0-1.65 1.635-3 3.66-3s3.66 1.35 3.66 3c0 1.65-1.635 3-3.66 3zm0 0c1.594 0 3.536.464 4.695 1.125C19.154 14.438 20 16.2 20 18.5v2.25M4 20.75v-2.25c0-2.3.846-4.062 2.305-5.375C7.464 12.464 9.406 12 11 12" />
                </svg>
                <span>{{ schema.description }}</span>
            </div>
        </div>

        <!-- Form Fields -->
        <div class="space-y-5">
            <template v-for="field in schema.fields" :key="field.key">
                <!-- Text Input -->
                <CmsInput
                    v-if="field.type === 'text'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: string) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- Textarea -->
                <CmsTextarea
                    v-else-if="field.type === 'textarea'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: string) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- Image Picker -->
                <CmsImagePicker
                    v-else-if="field.type === 'image'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: string) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- Video -->
                <CmsImagePicker
                    v-else-if="field.type === 'video'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: string) => updateField(field.key, val)"
                    :label="field.label"
                    media-type="video"
                    accept="video/*"
                    v-bind="field.props || {}"
                />

                <!-- Icon Select -->
                <CmsIconSelect
                    v-else-if="field.type === 'icon-select'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: string) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- Link Editor -->
                <CmsLinkEditor
                    v-else-if="field.type === 'link'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: { label: string; href: string }) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- Boolean Toggle -->
                <CmsBooleanToggle
                    v-else-if="field.type === 'boolean'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: boolean) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- CTA Editor -->
                <CmsCTAEditor
                    v-else-if="field.type === 'cta'"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: { label: string; href: string }) => updateField(field.key, val)"
                    :label="field.label"
                    v-bind="field.props || {}"
                />

                <!-- Group -->
                <CmsGroup
                    v-else-if="field.type === 'group' && field.fields"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: Record<string, any>) => updateField(field.key, val)"
                    :label="field.label"
                    :fields="field.fields"
                    v-bind="field.props || {}"
                />

                <!-- Repeater -->
                <CmsRepeater
                    v-else-if="field.type === 'repeater' && field.fields"
                    :model-value="getFieldValue(field)"
                    @update:model-value="(val: Record<string, any>[]) => updateField(field.key, val)"
                    :label="field.label"
                    :fields="field.fields"
                    v-bind="field.props || {}"
                />
            </template>
        </div>

        <!-- Empty state for sections without schema -->
        <div v-if="schema.fields.length === 0" class="text-center py-12 text-neutral-400 dark:text-neutral-500 bg-neutral-50 dark:bg-neutral-800/50 rounded-xl border border-dashed border-neutral-300 dark:border-neutral-700">
            <svg class="w-12 h-12 mx-auto mb-3 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.42 5.42m0 0l-2.83-2.83m2.83 2.83l5.41-5.41m8.41-8.41l2.83 2.83-5.42 5.42m0 0l-2.83-2.83m2.83 2.83l5.42-5.42" />
            </svg>
            <p class="text-sm font-medium">Belum ada form editor untuk section ini</p>
            <p class="text-xs mt-1">Kamu masih bisa mengedit JSON secara manual menggunakan tombol "JSON Mode"</p>
        </div>
    </div>
</template>
