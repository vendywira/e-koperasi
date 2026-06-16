<script setup lang="ts">
import { ref, computed } from 'vue';

interface IconItem {
    value: string;
    label: string;
    emoji: string;
}

interface IconCategory {
    id: string;
    label: string;
    icons: IconItem[];
}

const CATEGORIES: { id: string; label: string }[] = [
    { id: 'all', label: 'Semua' },
    { id: 'finance', label: 'Keuangan' },
    { id: 'documents', label: 'Dokumen' },
    { id: 'communication', label: 'Komunikasi' },
    { id: 'people', label: 'Pengguna' },
    { id: 'actions', label: 'Aksi' },
    { id: 'navigation', label: 'Navigasi' },
    { id: 'time', label: 'Waktu' },
    { id: 'security', label: 'Keamanan' },
    { id: 'media', label: 'Media' },
    { id: 'awards', label: 'Penghargaan' },
    { id: 'location', label: 'Lokasi' },
    { id: 'tech', label: 'Teknologi' },
    { id: 'misc', label: 'Lainnya' },
];

const ICON_GROUPS: IconCategory[] = [
    {
        id: 'finance',
        label: 'Keuangan',
        icons: [
            { value: 'shield', label: 'Shield', emoji: '🛡️' },
            { value: 'zap', label: 'Zap', emoji: '⚡' },
            { value: 'sparkles', label: 'Sparkles', emoji: '✨' },
            { value: 'gift', label: 'Gift', emoji: '🎁' },
            { value: 'wallet', label: 'Wallet', emoji: '👛' },
            { value: 'piggy-bank', label: 'Piggy Bank', emoji: '🐷' },
            { value: 'badge-dollar-sign', label: 'Dollar', emoji: '💵' },
            { value: 'money-bag', label: 'Money', emoji: '💰' },
            { value: 'credit-card', label: 'Card', emoji: '💳' },
            { value: 'bank', label: 'Bank', emoji: '🏦' },
            { value: 'receipt', label: 'Receipt', emoji: '🧾' },
            { value: 'handshake', label: 'Handshake', emoji: '🤝' },
            { value: 'percentage', label: 'Percent', emoji: '💹' },
            { value: 'gem', label: 'Gem', emoji: '💎' },
            { value: 'scale', label: 'Scale', emoji: '⚖️' },
        ],
    },
    {
        id: 'documents',
        label: 'Dokumen',
        icons: [
            { value: 'briefcase', label: 'Briefcase', emoji: '💼' },
            { value: 'building', label: 'Building', emoji: '🏢' },
            { value: 'file-text', label: 'File', emoji: '📄' },
            { value: 'document', label: 'Document', emoji: '📃' },
            { value: 'clipboard-list', label: 'Clipboard', emoji: '📋' },
            { value: 'folder', label: 'Folder', emoji: '📁' },
            { value: 'folder-open', label: 'Folder Open', emoji: '📂' },
            { value: 'bookmark', label: 'Bookmark', emoji: '🔖' },
            { value: 'pushpin', label: 'Pin', emoji: '📌' },
            { value: 'memo', label: 'Memo', emoji: '📝' },
            { value: 'newspaper', label: 'News', emoji: '📰' },
            { value: 'bar-chart', label: 'Chart', emoji: '📊' },
            { value: 'trending-up', label: 'Growth', emoji: '📈' },
            { value: 'trending-down', label: 'Trending', emoji: '📉' },
        ],
    },
    {
        id: 'communication',
        label: 'Komunikasi',
        icons: [
            { value: 'mail', label: 'Mail', emoji: '✉️' },
            { value: 'phone', label: 'Phone', emoji: '📞' },
            { value: 'smartphone', label: 'Smartphone', emoji: '📱' },
            { value: 'message', label: 'Message', emoji: '💬' },
            { value: 'chat', label: 'Chat', emoji: '💭' },
            { value: 'notification', label: 'Notification', emoji: '🔔' },
            { value: 'send', label: 'Send', emoji: '📤' },
            { value: 'inbox', label: 'Inbox', emoji: '📥' },
        ],
    },
    {
        id: 'people',
        label: 'Pengguna',
        icons: [
            { value: 'users', label: 'Users', emoji: '👥' },
            { value: 'person', label: 'Person', emoji: '👤' },
            { value: 'crown', label: 'Crown', emoji: '👑' },
            { value: 'support', label: 'Support', emoji: '🧑‍💻' },
        ],
    },
    {
        id: 'actions',
        label: 'Aksi',
        icons: [
            { value: 'search', label: 'Search', emoji: '🔍' },
            { value: 'edit', label: 'Edit', emoji: '✏️' },
            { value: 'trash', label: 'Delete', emoji: '🗑️' },
            { value: 'plus', label: 'Add', emoji: '➕' },
            { value: 'check-circle', label: 'Done', emoji: '✅' },
            { value: 'close', label: 'Close', emoji: '❌' },
            { value: 'upload', label: 'Upload', emoji: '⬆️' },
            { value: 'download', label: 'Download', emoji: '⬇️' },
            { value: 'refresh', label: 'Refresh', emoji: '🔄' },
            { value: 'save', label: 'Save', emoji: '💾' },
        ],
    },
    {
        id: 'navigation',
        label: 'Navigasi',
        icons: [
            { value: 'home', label: 'Home', emoji: '🏠' },
            { value: 'link', label: 'Link', emoji: '🔗' },
            { value: 'arrow-right', label: 'Arrow Right', emoji: '➡️' },
            { value: 'arrow-left', label: 'Arrow Left', emoji: '⬅️' },
            { value: 'arrow-up', label: 'Arrow Up', emoji: '⬆️' },
            { value: 'arrow-down', label: 'Arrow Down', emoji: '⬇️' },
        ],
    },
    {
        id: 'time',
        label: 'Waktu',
        icons: [
            { value: 'clock', label: 'Clock', emoji: '⏰' },
            { value: 'calendar-days', label: 'Calendar', emoji: '📅' },
            { value: 'calendar-clock', label: 'Calendar Clock', emoji: '📆' },
            { value: 'hourglass', label: 'Hourglass', emoji: '⌛' },
            { value: 'timer', label: 'Timer', emoji: '⏱️' },
            { value: 'watch', label: 'Watch', emoji: '⌚' },
        ],
    },
    {
        id: 'security',
        label: 'Keamanan',
        icons: [
            { value: 'lock', label: 'Lock', emoji: '🔒' },
            { value: 'unlock', label: 'Unlock', emoji: '🔓' },
            { value: 'key', label: 'Key', emoji: '🔑' },
            { value: 'verified', label: 'Verified', emoji: '✔️' },
        ],
    },
    {
        id: 'media',
        label: 'Media',
        icons: [
            { value: 'camera', label: 'Camera', emoji: '📷' },
            { value: 'image', label: 'Image', emoji: '🖼️' },
            { value: 'video', label: 'Video', emoji: '🎬' },
            { value: 'play', label: 'Play', emoji: '▶️' },
            { value: 'headphone', label: 'Headset', emoji: '🎧' },
        ],
    },
    {
        id: 'awards',
        label: 'Penghargaan',
        icons: [
            { value: 'star', label: 'Star', emoji: '⭐' },
            { value: 'heart', label: 'Heart', emoji: '❤️' },
            { value: 'award', label: 'Award', emoji: '🏆' },
            { value: 'medal', label: 'Medal', emoji: '🥇' },
            { value: 'fire', label: 'Fire', emoji: '🔥' },
            { value: 'rocket', label: 'Rocket', emoji: '🚀' },
            { value: 'target', label: 'Target', emoji: '🎯' },
            { value: 'flag', label: 'Flag', emoji: '🚩' },
        ],
    },
    {
        id: 'location',
        label: 'Lokasi',
        icons: [
            { value: 'map-pin', label: 'Map Pin', emoji: '📍' },
            { value: 'route', label: 'Route', emoji: '🗺️' },
            { value: 'globe', label: 'Globe', emoji: '🌐' },
        ],
    },
    {
        id: 'tech',
        label: 'Teknologi',
        icons: [
            { value: 'database', label: 'Database', emoji: '🗄️' },
            { value: 'cloud', label: 'Cloud', emoji: '☁️' },
            { value: 'server', label: 'Server', emoji: '🖥️' },
            { value: 'code', label: 'Code', emoji: '💻' },
            { value: 'cog', label: 'Settings', emoji: '⚙️' },
        ],
    },
    {
        id: 'misc',
        label: 'Lainnya',
        icons: [
            { value: 'lightbulb', label: 'Idea', emoji: '💡' },
            { value: 'cart', label: 'Cart', emoji: '🛒' },
            { value: 'truck', label: 'Truck', emoji: '🚚' },
            { value: 'airplane', label: 'Airplane', emoji: '✈️' },
            { value: 'ship', label: 'Ship', emoji: '🚢' },
            { value: 'umbrella', label: 'Umbrella', emoji: '☂️' },
        ],
    },
];

const ICONS = ICON_GROUPS.flatMap(g => g.icons);

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
const selectedCategory = ref('all');

const filteredIcons = computed(() => {
    let result = ICONS;

    // Filter by category when not searching
    if (selectedCategory.value !== 'all' && !searchQuery.value) {
        const group = ICON_GROUPS.find(g => g.id === selectedCategory.value);
        if (group) result = group.icons;
    }

    // Filter by search (searches across all categories)
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = ICONS.filter(i => i.label.toLowerCase().includes(q) || i.value.toLowerCase().includes(q));
    }

    return result;
});

const selectedIcon = computed(() => ICONS.find(i => i.value === props.modelValue));

function selectCategory(id: string) {
    selectedCategory.value = id;
    searchQuery.value = ''; // Reset search when switching category
}
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
                    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[80vh] flex flex-col border border-neutral-200 dark:border-neutral-800">
                        <!-- Header -->
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

                        <!-- Category Tabs -->
                        <div class="px-3 pt-3 overflow-x-auto scrollbar-thin">
                            <div class="flex items-center gap-1.5 pb-2 min-w-max">
                                <button
                                    v-for="cat in CATEGORIES"
                                    :key="cat.id"
                                    type="button"
                                    @click="selectCategory(cat.id)"
                                    class="whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-full transition-all shrink-0"
                                    :class="selectedCategory === cat.id
                                        ? 'bg-primary-600 text-white shadow-sm'
                                        : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700'"
                                >
                                    {{ cat.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="px-3 pb-3">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari icon..."
                                    class="w-full pl-9 pr-3 py-2 text-sm bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none text-neutral-900 dark:text-white placeholder-neutral-400"
                                />
                            </div>
                        </div>

                        <!-- Icon Grid -->
                        <div class="flex-1 overflow-y-auto px-3 pb-3">
                            <!-- Active category label -->
                            <div v-if="!searchQuery && selectedCategory !== 'all'" class="flex items-center gap-1.5 mb-2 text-xs text-neutral-400 dark:text-neutral-500">
                                <span>{{ filteredIcons.length }} icon</span>
                                <span class="w-1 h-1 rounded-full bg-neutral-300 dark:bg-neutral-600"></span>
                                <span>{{ CATEGORIES.find(c => c.id === selectedCategory)?.label }}</span>
                            </div>

                            <div class="grid grid-cols-5 gap-2">
                                <button
                                    v-for="icon in filteredIcons"
                                    :key="icon.value"
                                    type="button"
                                    @click="emit('update:modelValue', icon.value); showDropdown = false"
                                    class="flex flex-col items-center gap-1 p-2.5 rounded-lg transition-all"
                                    :class="modelValue === icon.value
                                        ? 'bg-primary-100 dark:bg-primary-900/30 ring-2 ring-primary-500'
                                        : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                                >
                                    <span class="text-xl">{{ icon.emoji }}</span>
                                    <span class="text-[9px] text-neutral-500 dark:text-neutral-400 text-center leading-tight truncate w-full">{{ icon.label }}</span>
                                </button>
                            </div>
                            <p v-if="filteredIcons.length === 0" class="text-center py-8 text-sm text-neutral-400">
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
