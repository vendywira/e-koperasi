<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/MediaUploader.vue';
import CmsFormBuilder from '@/Components/CmsEditor/CmsFormBuilder.vue';
import CmsPreview from '@/Components/CmsEditor/CmsPreview.vue';
import { sectionSchemaMap } from '@/Components/CmsEditor/schemas';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { update as cmsUpdate, seed as cmsSeed } from '@/routes/admin/cms';
import { useTheme } from '@/composables/useTheme';

const props = defineProps<{
    sections: Record<string, any>;
    configDefaults: Record<string, any>;
}>();

const activeSection = ref<string | null>(Object.keys(props.sections)[0] || 'brand');
const editData = ref<Record<string, any>>({});
const saving = ref(false);
const seedConfirm = ref(false);
const searchQuery = ref('');
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
const showMedia = ref(false);
const showPreview = ref(false);
const jsonMode = ref(false);
const jsonEditData = ref('');

// Mobile drawer/overlay state
const showSectionDrawer = ref(false);
const showMenuDropdown = ref(false);

const sectionLabels: Record<string, string> = {
    brand: 'Brand & Identitas', nav: 'Navigasi', footer: 'Footer',
    hero: 'Hero Section', trust_bar: 'Trust Bar', stats: 'Statistik',
    personas: 'Persona Cards', products: 'Produk', features: 'Fitur',
    app_features: 'Fitur Aplikasi', how_it_works: 'Cara Kerja',
    pricing: 'Harga', testimonials: 'Testimoni',
    faqs: 'FAQ', cta: 'Call to Action', demo: 'Demo Page',
    contact: 'Kontak', about: 'Tentang Kami', legal: 'Legal', seo: 'SEO',
};

const sectionIcons: Record<string, string> = {
    brand: '🏷️', nav: '🧭', footer: '📋', hero: '🎯', trust_bar: '🤝',
    stats: '📊', personas: '👤', products: '📦', features: '⚡',
    how_it_works: '🔧', pricing: '💰', testimonials: '💬', faqs: '❓',
    cta: '📣', demo: '🎮', contact: '📞', about: '🏢', legal: '⚖️', seo: '🔍',
};

const allSections = computed(() => Object.keys(props.sections).sort());

const filteredSections = computed(() => {
    if (!searchQuery.value) return allSections.value;
    const q = searchQuery.value.toLowerCase();
    return allSections.value.filter(
        (key) => key.toLowerCase().includes(q) || (sectionLabels[key] || '').toLowerCase().includes(q)
    );
});

const currentSchema = computed(() => {
    if (!activeSection.value) return undefined;
    return sectionSchemaMap[activeSection.value];
});

const hasChanges = computed(() => {
    if (!activeSection.value) return false;
    const original = JSON.stringify(props.sections[activeSection.value], null, 2);
    if (jsonMode.value) return jsonEditData.value !== original;
    return JSON.stringify(editData.value) !== original;
});

function selectSection(key: string) {
    activeSection.value = key;
    loadSectionData(key);
    showSectionDrawer.value = false; // Close drawer on mobile after selection
}

const ARRAY_SECTIONS = new Set(['stats', 'features', 'personas']);

function loadSectionData(key: string) {
    const raw = props.sections[key];
    if (Array.isArray(raw)) {
        editData.value = { items: JSON.parse(JSON.stringify(raw)) };
    } else {
        editData.value = raw ? JSON.parse(JSON.stringify(raw)) : {};
    }
    jsonEditData.value = JSON.stringify(raw ?? {}, null, 2);
    jsonMode.value = false;
}

if (activeSection.value) {
    loadSectionData(activeSection.value);
}

function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.value = { message, type };
    setTimeout(() => (toast.value = null), 3000);
}

function saveSection() {
    if (!activeSection.value) return;
    let valueToSave: any;

    if (jsonMode.value) {
        try { valueToSave = JSON.parse(jsonEditData.value); }
        catch { showToast('JSON tidak valid. Periksa syntax Anda.', 'error'); return; }
    } else {
        valueToSave = editData.value;
        if (activeSection.value && ARRAY_SECTIONS.has(activeSection.value) && 'items' in editData.value) {
            valueToSave = editData.value.items;
        }
    }

    saving.value = true;
    router.put(
        cmsUpdate(activeSection.value).url,
        { value: valueToSave },
        {
            preserveScroll: true, preserveState: true,
            onFinish: () => { saving.value = false; },
            onSuccess: () => {
                showToast(`Section "${sectionLabels[activeSection.value] || activeSection.value}" berhasil disimpan.`);
                router.reload({ only: ['sections'] });
            },
            onError: () => { showToast('Gagal menyimpan. Coba lagi.', 'error'); },
        }
    );
}

function resetSection() {
    if (!activeSection.value) return;
    const defaultVal = props.configDefaults[activeSection.value];
    if (!defaultVal) { showToast('Tidak ada default config untuk section ini.', 'error'); return; }
    const cloned = JSON.parse(JSON.stringify(defaultVal));
    editData.value = cloned;
    jsonEditData.value = JSON.stringify(cloned, null, 2);
    showToast('Dikembalikan ke default config. Klik Simpan untuk menerapkan.');
}

function seedFromConfig() {
    saving.value = true;
    seedConfirm.value = false;
    router.post(
        cmsSeed().url, {},
        {
            preserveScroll: true, preserveState: true,
            onFinish: () => { saving.value = false; },
            onSuccess: () => { showToast('Semua section berhasil di-seed dari config default.'); router.reload({ only: ['sections'] }); },
            onError: () => { showToast('Gagal seed data.', 'error'); },
        }
    );
}

function formatJson() {
    try { const parsed = JSON.parse(jsonEditData.value); jsonEditData.value = JSON.stringify(parsed, null, 2); showToast('JSON berhasil di-format.'); }
    catch { showToast('JSON tidak valid.', 'error'); }
}

const { toggleTheme } = useTheme();

function handleKeydown(e: KeyboardEvent) {
    if ((e.metaKey || e.ctrlKey) && e.key === 's') { e.preventDefault(); saveSection(); }
    if ((e.metaKey || e.ctrlKey) && e.shiftKey && e.key === 'D') { e.preventDefault(); toggleTheme(); }
    if (e.key === 'Escape') { showMenuDropdown.value = false; }
}

onMounted(() => { window.addEventListener('keydown', handleKeydown); });
onUnmounted(() => { window.removeEventListener('keydown', handleKeydown); });

function handleMediaSelect(url: string) {
    const textarea = document.querySelector('textarea') as HTMLTextAreaElement;
    if (textarea && jsonMode.value) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        jsonEditData.value = jsonEditData.value.substring(0, start) + '"' + url + '"' + jsonEditData.value.substring(end);
        showMedia.value = false;
        showToast('URL gambar disisipkan');
    } else { showMedia.value = false; }
}

function toggleJsonMode() {
    jsonMode.value = !jsonMode.value;
    if (jsonMode.value) {
        const raw = editData.value;
        if (activeSection.value && ARRAY_SECTIONS.has(activeSection.value) && raw?.items) {
            jsonEditData.value = JSON.stringify(raw.items, null, 2);
        } else {
            jsonEditData.value = JSON.stringify(raw, null, 2);
        }
    }
}
</script>

<template>
    <AdminLayout title="CMS Editor">
        <div class="flex gap-6 h-full">
            <!-- ===== DESKTOP: Section List Sidebar ===== -->
            <div class="hidden lg:flex w-72 flex-shrink-0 bg-white dark:bg-neutral-900/95 rounded-xl border border-neutral-200 dark:border-neutral-800 flex-col overflow-hidden shadow-sm dark:shadow-neutral-950/50">
                <div class="p-4 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Sections</h2>
                        <button @click="seedConfirm = true" class="text-xs text-neutral-500 hover:text-primary-600 dark:text-neutral-400 dark:hover:text-primary-400 transition-colors font-medium">Seed All</button>
                    </div>
                    <input v-model="searchQuery" type="text" placeholder="Cari section..." class="w-full px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 transition-all" />
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
                    <button v-for="key in filteredSections" :key="key" @click="selectSection(key)"
                        class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 flex items-center gap-2.5 group"
                        :class="activeSection === key ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 shadow-sm ring-1 ring-primary-200 dark:ring-primary-800/50' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'">
                        <span class="text-base flex-shrink-0">{{ sectionIcons[key] || '📄' }}</span>
                        <span class="truncate">{{ sectionLabels[key] || key }}</span>
                        <span v-if="currentSchema" class="ml-auto text-[9px] font-mono px-1.5 py-0.5 rounded flex-shrink-0"
                            :class="activeSection === key ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-300' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500 dark:text-neutral-400 opacity-0 group-hover:opacity-100 transition-opacity'">{{ sectionSchemaMap[key] ? 'form' : 'json' }}</span>
                    </button>
                </div>
                <div class="p-3 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/50">
                    <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                        <span>{{ filteredSections.length }} section</span>
                        <span class="font-mono">{{ activeSection }}</span>
                    </div>
                </div>
            </div>

            <!-- ===== MAIN EDITOR AREA ===== -->
            <div class="flex-1 flex flex-col min-w-0 bg-white dark:bg-neutral-900/95 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden shadow-sm dark:shadow-neutral-950/50">
                <!-- Editor Header -->
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-white dark:bg-neutral-900/95 gap-2 flex-shrink-0">
                    <!-- Left: section icon + name + section drawer trigger (mobile) -->
                    <div class="flex items-center gap-2 min-w-0">
                        <!-- Mobile: section drawer trigger -->
                        <button @click="showSectionDrawer = true" class="lg:hidden flex items-center gap-1 px-2 py-1.5 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors flex-shrink-0 text-xs font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            Sections
                        </button>
                        <span class="text-xl flex-shrink-0 hidden sm:inline-block">{{ sectionIcons[activeSection || ''] || '📄' }}</span>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-white truncate flex items-center gap-2">
                                <span class="sm:hidden">{{ sectionIcons[activeSection || ''] || '📄' }}</span>
                                {{ sectionLabels[activeSection || ''] || activeSection }}
                                <span v-if="hasChanges" class="ml-1 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full flex-shrink-0 animate-pulse">Unsaved</span>
                            </h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono truncate">Key: {{ activeSection }}</p>
                                <span v-if="currentSchema" class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 flex-shrink-0">Form Editor</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: toolbar buttons -->
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                        <!-- JSON Mode Toggle (always visible) -->
                        <button @click="toggleJsonMode"
                            class="px-2 sm:px-3 py-1.5 text-[11px] sm:text-xs font-medium rounded-lg transition-all flex items-center gap-1 sm:gap-1.5 whitespace-nowrap"
                            :class="jsonMode ? 'text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 ring-1 ring-primary-300 dark:ring-primary-700' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700'">
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            <span class="hidden sm:inline">{{ jsonMode ? 'Form Mode' : 'JSON Mode' }}</span>
                            <span class="sm:hidden">{{ jsonMode ? 'Form' : 'JSON' }}</span>
                        </button>

                        <!-- Desktop: visible buttons -->
                        <div class="hidden sm:flex items-center gap-1.5">
                            <!-- Format (JSON only) -->
                            <button v-if="jsonMode" @click="formatJson" class="px-3 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">Format</button>
                            <!-- Reset -->
                            <button @click="resetSection" class="px-3 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Reset</button>
                        </div>

                        <!-- Mobile: More dropdown -->
                        <div class="relative sm:hidden">
                            <button @click="showMenuDropdown = !showMenuDropdown" class="p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                            <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                                <div v-if="showMenuDropdown" class="absolute right-0 top-full mt-1 w-44 bg-white dark:bg-neutral-900 rounded-xl shadow-xl dark:shadow-neutral-950/80 border border-neutral-200 dark:border-neutral-800 py-1 z-50">
                                    <button @click="showPreview = !showPreview; showMenuDropdown = false" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors text-left">
                                        <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ showPreview ? 'Tutup Preview' : 'Buka Preview' }}
                                    </button>
                                    <button @click="showMedia = !showMedia; showMenuDropdown = false" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors text-left">
                                        <span class="text-base">📷</span>
                                        {{ showMedia ? 'Tutup Media' : 'Buka Media' }}
                                    </button>
                                    <hr class="my-1 border-neutral-200 dark:border-neutral-700" />
                                    <button @click="resetSection(); showMenuDropdown = false" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-left">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg>
                                        Reset ke Default
                                    </button>
                                    <button v-if="jsonMode" @click="formatJson(); showMenuDropdown = false" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors text-left">
                                        <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" /></svg>
                                        Format JSON
                                    </button>
                                </div>
                            </Transition>
                        </div>

                        <!-- Preview Toggle (desktop) -->
                        <button @click="showPreview = !showPreview"
                            class="hidden sm:flex px-3 py-1.5 text-xs font-medium rounded-lg transition-all items-center gap-1.5"
                            :class="showPreview ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 ring-1 ring-emerald-300 dark:ring-emerald-700' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700'">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Preview
                        </button>

                        <!-- Media (desktop) -->
                        <button @click="showMedia = !showMedia" class="hidden sm:flex px-3 py-1.5 text-xs font-medium rounded-lg transition-colors items-center gap-1"
                            :class="showMedia ? 'text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700'">
                            <span class="text-sm">📷</span> Media
                        </button>

                        <!-- Save -->
                        <button @click="saveSection" :disabled="saving || !hasChanges"
                            class="px-3 sm:px-4 py-1.5 text-xs font-semibold text-white rounded-lg transition-all duration-150 flex items-center gap-1.5"
                            :class="saving || !hasChanges ? 'bg-neutral-300 dark:bg-neutral-700 cursor-not-allowed' : 'bg-primary-600 hover:bg-primary-700 shadow-sm hover:shadow-md active:scale-[0.98]'">
                            <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                            {{ saving ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>

                <!-- Content area -->
                <div class="flex-1 flex overflow-hidden">
                    <!-- Editor Body -->
                    <div class="flex-1 overflow-y-auto">
                        <div v-if="!jsonMode && currentSchema" class="p-4 sm:p-6">
                            <CmsFormBuilder :section="activeSection || ''" :schema="currentSchema" :model-value="editData" @update:model-value="editData = $event" />
                        </div>

                        <div v-else-if="!jsonMode && !currentSchema" class="p-4 sm:p-6 flex flex-col items-center justify-center h-full text-center">
                            <div class="max-w-sm">
                                <span class="text-4xl block mb-4">{{ sectionIcons[activeSection || ''] || '📄' }}</span>
                                <h3 class="text-base font-semibold text-neutral-900 dark:text-white mb-2">{{ sectionLabels[activeSection || ''] || activeSection }}</h3>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">Section ini belum memiliki form editor visual. Gunakan JSON Mode untuk mengedit, atau tambahkan form schema.</p>
                                <button @click="toggleJsonMode" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">Buka JSON Mode</button>
                            </div>
                        </div>

                        <div v-if="jsonMode" class="p-4 sm:p-6 h-full flex flex-col">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded">JSON</span>
                                    <span class="text-xs text-neutral-400 dark:text-neutral-500">Edit langsung format JSON</span>
                                </div>
                                <span class="text-xs text-neutral-400 dark:text-neutral-500 font-mono px-2 py-1 rounded">{{ activeSection }}.json</span>
                            </div>
                            <textarea v-model="jsonEditData" @keydown="handleKeydown" spellcheck="false"
                                class="flex-1 w-full min-h-[300px] sm:min-h-[400px] p-3 sm:p-4 text-sm font-mono leading-relaxed bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none resize-none text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-600 transition-all"
                                placeholder="Edit JSON..."></textarea>
                        </div>
                    </div>

                    <!-- ===== Preview Panel (desktop) ===== -->
                    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="w-0 opacity-0" enter-to-class="w-80 opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="w-80 opacity-100" leave-to-class="w-0 opacity-0">
                        <div v-if="showPreview" class="hidden lg:flex w-80 flex-shrink-0 border-l border-neutral-200 dark:border-neutral-800 overflow-hidden flex-col bg-neutral-50 dark:bg-neutral-900/50">
                            <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-white dark:bg-neutral-900/95">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Live Preview</h3>
                                </div>
                                <span class="flex items-center gap-1.5 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Live</span>
                            </div>
                            <div class="flex-1 overflow-y-auto p-4">
                                <CmsPreview :section="activeSection || ''" :data="editData" />
                                <div class="mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                        <p class="text-[10px] text-amber-700 dark:text-amber-300 leading-relaxed">Preview bersifat real-time. Perubahan akan terlihat langsung di sini sebelum disimpan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>

                    <!-- ===== Media Panel (desktop) ===== -->
                    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="w-0 opacity-0" enter-to-class="w-80 opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="w-80 opacity-100" leave-to-class="w-0 opacity-0">
                        <div v-if="showMedia" class="hidden lg:block w-80 flex-shrink-0 border-l border-neutral-200 dark:border-neutral-800 p-4 overflow-y-auto bg-neutral-50 dark:bg-neutral-900/50">
                            <MediaUploader directory="cms" :select-mode="true" @select="handleMediaSelect" />
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- ===== MOBILE: Section Drawer ===== -->
        <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showSectionDrawer" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm lg:hidden" @click="showSectionDrawer = false" />
        </Transition>
        <Transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="-translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition-transform duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="-translate-x-full">
            <div v-if="showSectionDrawer" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-neutral-900 border-r border-neutral-200 dark:border-neutral-800 flex flex-col shadow-2xl dark:shadow-neutral-950/80 lg:hidden">
                <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Sections</h2>
                    <button @click="showSectionDrawer = false" class="p-1.5 rounded-lg text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ filteredSections.length }} section</span>
                        <button @click="seedConfirm = true" class="text-xs text-neutral-500 hover:text-primary-600 dark:text-neutral-400 dark:hover:text-primary-400 transition-colors font-medium">Seed All</button>
                    </div>
                    <input v-model="searchQuery" type="text" placeholder="Cari section..." class="w-full px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 transition-all" />
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
                    <button v-for="key in filteredSections" :key="key" @click="selectSection(key)"
                        class="w-full text-left px-3 py-3 rounded-lg text-sm font-medium transition-all flex items-center gap-2.5"
                        :class="activeSection === key ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 shadow-sm ring-1 ring-primary-200 dark:ring-primary-800/50' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800'">
                        <span class="text-lg flex-shrink-0">{{ sectionIcons[key] || '📄' }}</span>
                        <span class="truncate">{{ sectionLabels[key] || key }}</span>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ===== MOBILE: Preview Overlay ===== -->
        <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showPreview" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="showPreview = false" />
        </Transition>
        <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-full opacity-0">
            <div v-if="showPreview" class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] bg-white dark:bg-neutral-900 rounded-t-2xl shadow-2xl dark:shadow-neutral-950/80 border-t border-neutral-200 dark:border-neutral-800 flex flex-col lg:hidden">
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-white dark:bg-neutral-900/95 rounded-t-2xl flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Live Preview</h3>
                        <span class="flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Live</span>
                    </div>
                    <button @click="showPreview = false" class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <CmsPreview :section="activeSection || ''" :data="editData" />
                </div>
            </div>
        </Transition>

        <!-- ===== MOBILE: Media Overlay ===== -->
        <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showMedia" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="showMedia = false" />
        </Transition>
        <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-full opacity-0">
            <div v-if="showMedia" class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] bg-white dark:bg-neutral-900 rounded-t-2xl shadow-2xl dark:shadow-neutral-950/80 border-t border-neutral-200 dark:border-neutral-800 flex flex-col lg:hidden">
                <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-white dark:bg-neutral-900/95 rounded-t-2xl flex-shrink-0">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">📷 Media Library</h3>
                    <button @click="showMedia = false" class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <MediaUploader directory="cms" :select-mode="true" @select="handleMediaSelect" />
                </div>
            </div>
        </Transition>

        <!-- ===== Seed Confirm Modal ===== -->
        <Teleport to="body">
            <div v-if="seedConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="seedConfirm = false">
                <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl dark:shadow-neutral-950/80 p-6 max-w-md w-full mx-auto border border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Seed Semua Section?</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">Ini akan menimpa semua data CMS dengan nilai dari <code class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 rounded text-xs font-mono">config/site.php</code>. Perubahan yang sudah disimpan di database akan hilang.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="seedConfirm = false" class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 rounded-lg transition-colors">Batal</button>
                        <button @click="seedFromConfig" :disabled="saving" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm disabled:opacity-50">{{ saving ? 'Processing...' : 'Ya, Seed Semua' }}</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ===== Toast ===== -->
        <Teleport to="body">
            <Transition enter-active-class="transform transition duration-300 ease-out" enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transform transition duration-200 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0">
                <div v-if="toast" class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 max-w-[90vw]"
                    :class="toast.type === 'success' ? 'bg-primary-600 text-white' : 'bg-red-600 text-white'">
                    <svg v-if="toast.type === 'success'" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-else class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <span class="truncate">{{ toast.message }}</span>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
