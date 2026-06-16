<script lang="ts">
export default { name: 'CmsRepeater' };
</script>

<script setup lang="ts">
import { getCurrentInstance } from 'vue';
import type { FormField } from '../schemas';
import CmsInput from './CmsInput.vue';
import CmsTextarea from './CmsTextarea.vue';
import CmsImagePicker from './CmsImagePicker.vue';
import CmsGroup from './CmsGroup.vue';
import CmsLinkEditor from './CmsLinkEditor.vue';
import CmsCTAEditor from './CmsCTAEditor.vue';
import CmsIconSelect from './CmsIconSelect.vue';
import CmsBooleanToggle from './CmsBooleanToggle.vue';

// Self-reference for recursive rendering (CmsRepeater nested inside CmsRepeater)
const selfComponent = getCurrentInstance()!.type;

const props = withDefaults(defineProps<{
    modelValue: any[];
    label: string;
    fields: FormField[];
    helpText?: string;
    addLabel?: string;
    emptyLabel?: string;
    titleKey?: string;
}>(), {
    helpText: '',
    addLabel: 'Tambah Item',
    emptyLabel: 'Belum ada item',
    titleKey: 'title',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: any[]): void;
}>();

function getComponentType(type: string): any {
    const map: Record<string, any> = {
        text: CmsInput,
        textarea: CmsTextarea,
        image: CmsImagePicker,
        repeater: selfComponent,
        link: CmsLinkEditor,
        cta: CmsCTAEditor,
        group: CmsGroup,
        'icon-select': CmsIconSelect,
        boolean: CmsBooleanToggle,
    };
    return map[type] || CmsInput;
}

function addItem() {
    const newItem: Record<string, any> = {};
    for (const field of props.fields) {
        if (field.type === 'repeater') {
            newItem[field.key] = [];
        } else if (field.type === 'cta' || field.type === 'link') {
            newItem[field.key] = { label: '', href: '' };
        } else {
            newItem[field.key] = '';
        }
    }
    emit('update:modelValue', [...props.modelValue, newItem]);
}

function removeItem(index: number) {
    const updated = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', updated);
}

function moveItem(from: number, direction: -1 | 1) {
    const to = from + direction;
    if (to < 0 || to >= props.modelValue.length) return;
    const updated = [...props.modelValue];
    const temp = updated[from];
    updated[from] = updated[to];
    updated[to] = temp;
    emit('update:modelValue', updated);
}

function updateItem(index: number, key: string, value: any) {
    const updated = [...props.modelValue];
    const currentItem = { ...updated[index] };
    currentItem[key] = value;
    updated[index] = currentItem;
    emit('update:modelValue', updated);
}

function updateStringItem(index: number, value: string) {
    const updated = [...props.modelValue];
    updated[index] = value;
    emit('update:modelValue', updated);
}

function getItemTitle(item: any, index: number): string {
    if (!item || typeof item === 'string') {
        const val = item || '';
        return val.length > 40 ? val.substring(0, 40) + '...' : val || `Item #${index + 1}`;
    }
    if (props.titleKey && item[props.titleKey]) return item[props.titleKey];
    if (item.name) return item.name;
    if (item.label) return item.label;
    if (item.q) return item.q;
    return `Item #${index + 1}`;
}

function getFieldValue(item: any, key: string): any {
    if (!item || typeof item === 'string') return '';
    return item[key] !== undefined ? item[key] : '';
}

function isStringItem(item: any): boolean {
    return typeof item === 'string';
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                {{ label }}
            </label>
            <button
                type="button"
                @click="addItem"
                class="px-3 py-1.5 text-xs font-semibold text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-all flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ addLabel }}
            </button>
        </div>

        <div v-if="modelValue.length === 0" class="text-center py-8 text-sm text-neutral-400 dark:text-neutral-500 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-700">
            {{ emptyLabel }}
        </div>

        <div class="space-y-3">
            <div
                v-for="(item, index) in modelValue"
                :key="index"
                class="group relative bg-white dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden hover:border-neutral-300 dark:hover:border-neutral-600 transition-all"
            >
                <!-- Item Header -->
                <div class="flex items-center justify-between px-4 py-3 bg-neutral-50 dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-xs font-mono text-neutral-400 dark:text-neutral-500 bg-neutral-200 dark:bg-neutral-700 px-1.5 py-0.5 rounded flex-shrink-0">
                            #{{ index + 1 }}
                        </span>
                        <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200 truncate">
                            {{ getItemTitle(item, index) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                            v-if="index > 0"
                            type="button"
                            @click="moveItem(index, -1)"
                            class="p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors"
                            title="Naik"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                        <button
                            v-if="index < modelValue.length - 1"
                            type="button"
                            @click="moveItem(index, 1)"
                            class="p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors"
                            title="Turun"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="removeItem(index)"
                            class="p-1.5 text-neutral-400 hover:text-red-500 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="Hapus"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Item Body -->
                <div class="p-4">
                    <!-- For string items (simple arrays like benefits) -->
                    <div v-if="isStringItem(item)" class="space-y-2">
                        <input
                            :value="item"
                            @input="updateStringItem(index, ($event.target as HTMLInputElement).value)"
                            type="text"
                            placeholder="..."
                            class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 transition-all"
                        />
                    </div>

                    <!-- For object items -->
                    <div v-else class="space-y-4">
                        <component
                            v-for="field in fields"
                            :key="field.key"
                            :is="getComponentType(field.type)"
                            :model-value="getFieldValue(item, field.key)"
                            @update:model-value="(val: any) => updateItem(index, field.key, val)"
                            :label="field.label"
                            :fields="(field.fields || []) as FormField[]"
                            v-bind="field.props || {}"
                        />
                    </div>
                </div>
            </div>
        </div>

        <p v-if="helpText" class="text-xs text-neutral-500 dark:text-neutral-400">{{ helpText }}</p>
    </div>
</template>
