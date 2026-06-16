<script lang="ts">
export default { name: 'CmsGroup' };
</script>

<script setup lang="ts">
import { getCurrentInstance } from 'vue';
import type { FormField } from '../schemas';
import CmsInput from './CmsInput.vue';
import CmsTextarea from './CmsTextarea.vue';
import CmsImagePicker from './CmsImagePicker.vue';
import CmsRepeater from './CmsRepeater.vue';
import CmsLinkEditor from './CmsLinkEditor.vue';
import CmsCTAEditor from './CmsCTAEditor.vue';
import CmsIconSelect from './CmsIconSelect.vue';
import CmsBooleanToggle from './CmsBooleanToggle.vue';

interface Props {
    modelValue: Record<string, any>;
    label: string;
    fields: FormField[];
    helpText?: string;
    collapsible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    helpText: '',
    collapsible: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<string, any>): void;
}>();

// Self-reference for recursive rendering (CmsGroup rendering nested CmsGroup)
const selfComponent = getCurrentInstance()!.type;

function getDefaultForType(field: FormField): any {
    switch (field.type) {
        case 'repeater': return [];
        case 'cta':
        case 'link': return { label: '', href: '' };
        case 'group': return {};
        default: return '';
    }
}

function getComponentType(type: string): any {
    const map: Record<string, any> = {
        text: CmsInput,
        textarea: CmsTextarea,
        image: CmsImagePicker,
        repeater: CmsRepeater,
        link: CmsLinkEditor,
        cta: CmsCTAEditor,
        group: selfComponent,
        'icon-select': CmsIconSelect,
        boolean: CmsBooleanToggle,
    };
    return map[type] || CmsInput;
}

function getFieldValue(field: FormField): any {
    const val = props.modelValue?.[field.key];
    if (val !== undefined && val !== null) return val;
    return getDefaultForType(field);
}

function handleUpdate(key: string, val: any) {
    emit('update:modelValue', {
        ...(props.modelValue || {}),
        [key]: val,
    });
}
</script>

<template>
    <div class="space-y-1.5">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            {{ label }}
        </label>
        <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-xl p-4 space-y-4">
            <component
                v-for="field in fields"
                :key="field.key"
                :is="getComponentType(field.type)"
                :model-value="getFieldValue(field)"
                @update:model-value="(val: any) => handleUpdate(field.key, val)"
                :label="field.label"
                :fields="(field.fields || []) as FormField[]"
                v-bind="field.props || {}"
            />
        </div>
        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
