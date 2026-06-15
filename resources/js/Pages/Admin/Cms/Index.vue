<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/MediaUploader.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { update as cmsUpdate, seed as cmsSeed } from '@/routes/admin/cms';

const props = defineProps<{
    sections: Record<string, any>;
    configDefaults: Record<string, any>;
}>();

const activeSection = ref<string | null>(Object.keys(props.sections)[0] || 'brand');
const editData = ref<string>('');
const saving = ref(false);
const seedConfirm = ref(false);
const searchQuery = ref('');
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
const showMedia = ref(false);

const sectionLabels: Record<string, string> = {
    brand: 'Brand & Identitas',
    nav: 'Navigasi',
    footer: 'Footer',
    hero: 'Hero Section',
    trust_bar: 'Trust Bar',
    stats: 'Statistik',
    personas: 'Persona Cards',
    products: 'Produk',
    features: 'Fitur',
    how_it_works: 'Cara Kerja',
    pricing: 'Harga',
    testimonials: 'Testimoni',
    faqs: 'FAQ',
    cta: 'Call to Action',
    demo: 'Demo Page',
    contact: 'Kontak',
    about: 'Tentang Kami',
    legal: 'Legal',
    seo: 'SEO',
};

const sectionIcons: Record<string, string> = {
    brand: '🏷️',
    nav: '🧭',
    footer: '📋',
    hero: '🎯',
    trust_bar: '🤝',
    stats: '📊',
    personas: '👤',
    products: '📦',
    features: '⚡',
    how_it_works: '🔧',
    pricing: '💰',
    testimonials: '💬',
    faqs: '❓',
    cta: '📣',
    demo: '🎮',
    contact: '📞',
    about: '🏢',
    legal: '⚖️',
    seo: '🔍',
};

const allSections = computed(() => {
    return Object.keys(props.sections).sort();
});

const filteredSections = computed(() => {
    if (!searchQuery.value) return allSections.value;
    const q = searchQuery.value.toLowerCase();
    return allSections.value.filter(
        (key) =>
            key.toLowerCase().includes(q) ||
            (sectionLabels[key] || '').toLowerCase().includes(q)
    );
});

const hasChanges = computed(() => {
    if (!activeSection.value) return false;
    const original = JSON.stringify(props.sections[activeSection.value], null, 2);
    return editData.value !== original;
});

function selectSection(key: string) {
    activeSection.value = key;
    editData.value = JSON.stringify(props.sections[key], null, 2);
}

// Initialize first section
if (activeSection.value) {
    editData.value = JSON.stringify(props.sections[activeSection.value], null, 2);
}

function showToast(message: string, type: 'success' | 'error' = 'success') {
    toast.value = { message, type };
    setTimeout(() => (toast.value = null), 3000);
}

function saveSection() {
    if (!activeSection.value) return;

    let parsed: any;
    try {
        parsed = JSON.parse(editData.value);
    } catch (e) {
        showToast('JSON tidak valid. Periksa syntax Anda.', 'error');
        return;
    }

    saving.value = true;

    router.put(
        cmsUpdate(activeSection.value).url,
        { value: parsed },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
            onSuccess: () => {
                showToast(`Section "${sectionLabels[activeSection.value] || activeSection.value}" berhasil disimpan.`);
            },
            onError: () => {
                showToast('Gagal menyimpan. Coba lagi.', 'error');
            },
        }
    );
}

function resetSection() {
    if (!activeSection.value) return;

    const defaultVal = props.configDefaults[activeSection.value];
    if (!defaultVal) {
        showToast('Tidak ada default config untuk section ini.', 'error');
        return;
    }

    editData.value = JSON.stringify(defaultVal, null, 2);
    showToast('Dikembalikan ke default config. Klik Simpan untuk menerapkan.');
}

function seedFromConfig() {
    saving.value = true;
    seedConfirm.value = false;

    router.post(
        cmsSeed().url,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
            onSuccess: () => {
                showToast('Semua section berhasil di-seed dari config default.');
                // Refresh data
                router.reload({ only: ['sections'] });
            },
            onError: () => {
                showToast('Gagal seed data.', 'error');
            },
        }
    );
}

function formatJson() {
    try {
        const parsed = JSON.parse(editData.value);
        editData.value = JSON.stringify(parsed, null, 2);
        showToast('JSON berhasil di-format.');
    } catch {
        showToast('JSON tidak valid.', 'error');
    }
}

function handleKeydown(e: KeyboardEvent) {
    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
        e.preventDefault();
        saveSection();
    }
}

function handleMediaSelect(url: string) {
    // Insert URL at cursor position in textarea
    const textarea = document.querySelector('textarea') as HTMLTextAreaElement;
    if (textarea) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = editData.value;
        editData.value = value.substring(0, start) + '"' + url + '"' + value.substring(end);
        showMedia.value = false;
        showToast('URL gambar disisipkan');
    } else {
        showMedia.value = false;
    }
}
</script>

<template>
    <AdminLayout title="CMS Editor">
        <div class="flex gap-6 h-[calc(100vh-120px)]">
            <!-- Section List -->
            <div class="w-72 flex-shrink-0 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 flex flex-col overflow-hidden">
                <div class="p-4 border-b border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Sections</h2>
                        <button
                            @click="seedConfirm = true"
                            class="text-xs text-neutral-500 hover:text-primary-600 dark:text-neutral-400 dark:hover:text-primary-400 transition-colors"
                        >
                            Seed All
                        </button>
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari section..."
                        class="w-full px-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500"
                    />
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
                    <button
                        v-for="key in filteredSections"
                        :key="key"
                        @click="selectSection(key)"
                        class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 flex items-center gap-2.5"
                        :class="
                            activeSection === key
                                ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 shadow-sm'
                                : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white'
                        "
                    >
                        <span class="text-base">{{ sectionIcons[key] || '📄' }}</span>
                        <span class="truncate">{{ sectionLabels[key] || key }}</span>
                    </button>
                </div>
            </div>

            <!-- Editor -->
            <div class="flex-1 flex flex-col min-w-0 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
                <!-- Editor Header -->
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">{{ sectionIcons[activeSection || ''] || '📄' }}</span>
                        <div>
                            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">
                                {{ sectionLabels[activeSection || ''] || activeSection }}
                            </h2>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Key: {{ activeSection }}</p>
                        </div>
                        <span
                            v-if="hasChanges"
                            class="ml-2 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full"
                        >
                            Unsaved
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="showMedia = !showMedia"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                            :class="showMedia ? 'text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700'"
                        >
                            📷 Media
                        </button>
                        <button
                            @click="formatJson"
                            class="px-3 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors"
                        >
                            Format
                        </button>
                        <button
                            @click="resetSection"
                            class="px-3 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        >
                            Reset
                        </button>
                        <button
                            @click="saveSection"
                            :disabled="saving || !hasChanges"
                            class="px-4 py-1.5 text-xs font-semibold text-white rounded-lg transition-all duration-150"
                            :class="
                                saving || !hasChanges
                                    ? 'bg-neutral-300 dark:bg-neutral-700 cursor-not-allowed'
                                    : 'bg-primary-600 hover:bg-primary-700 shadow-sm hover:shadow-md active:scale-[0.98]'
                            "
                        >
                            {{ saving ? 'Menyimpan...' : 'Simpan (⌘S)' }}
                        </button>
                    </div>
                </div>

                <!-- Content area -->
                <div class="flex-1 flex overflow-hidden">
                    <!-- JSON Editor -->
                    <div class="flex-1 p-6 overflow-auto">
                        <textarea
                            v-model="editData"
                            @keydown="handleKeydown"
                            spellcheck="false"
                            class="w-full h-full min-h-[500px] p-4 text-sm font-mono leading-relaxed bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none resize-none text-neutral-900 dark:text-neutral-100"
                            :placeholder="`Edit JSON for ${sectionLabels[activeSection || ''] || activeSection}...`"
                        />
                    </div>

                    <!-- Media Panel -->
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="w-0 opacity-0"
                        enter-to-class="w-80 opacity-100"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="w-80 opacity-100"
                        leave-to-class="w-0 opacity-0"
                    >
                        <div v-if="showMedia" class="w-80 flex-shrink-0 border-l border-neutral-200 dark:border-neutral-800 p-4 overflow-y-auto">
                            <MediaUploader
                                directory="cms"
                                :select-mode="true"
                                @select="handleMediaSelect"
                            />
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- Seed Confirm Modal -->
        <Teleport to="body">
            <div
                v-if="seedConfirm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="seedConfirm = false"
            >
                <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl p-6 max-w-md w-full mx-4 border border-neutral-200 dark:border-neutral-800">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Seed Semua Section?</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">
                        Ini akan menimpa semua data CMS dengan nilai dari <code class="px-1 py-0.5 bg-neutral-100 dark:bg-neutral-800 rounded text-xs">config/site.php</code>. Perubahan yang sudah disimpan di database akan hilang.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button
                            @click="seedConfirm = false"
                            class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            @click="seedFromConfig"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm"
                        >
                            Ya, Seed Semua
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Toast -->
        <Teleport to="body">
            <Transition
                enter-active-class="transform transition duration-300 ease-out"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transform transition duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-2 opacity-0"
            >
                <div
                    v-if="toast"
                    class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
                    :class="
                        toast.type === 'success'
                            ? 'bg-primary-600 text-white'
                            : 'bg-red-600 text-white'
                    "
                >
                    {{ toast.message }}
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
