<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    section: string;
    data: Record<string, any>;
}>();

// ====== PREVIEW RENDERERS ======

// Brand Preview
function renderBrand(d: any) {
    return {
        name: d?.name || 'Nama Brand',
        tagline: d?.tagline || 'Tagline perusahaan',
        description: d?.description || '',
        logo: d?.logo || '',
        favicon: d?.favicon || '',
    };
}

// Nav Preview
function renderNav(d: any) {
    return {
        items: Array.isArray(d?.items) ? d.items : [],
    };
}

// Footer Preview
function renderFooter(d: any) {
    return {
        description: d?.description || '',
        columns: Array.isArray(d?.columns) ? d.columns : [],
        copyright: d?.copyright || '',
    };
}

// Hero Preview
function renderHero(d: any) {
    return {
        badge: d?.badge || '',
        headline: d?.headline || '',
        subheadline: d?.subheadline || '',
        primary_cta: d?.primary_cta || { label: '', href: '#' },
        secondary_cta: d?.secondary_cta || { label: '', href: '#' },
        floating_badge: d?.floating_badge || '',
        carousel_slides: Array.isArray(d?.carousel_slides) ? d.carousel_slides : [],
    };
}

// Stats Preview
function renderStats(d: any): any[] {
    if (Array.isArray(d?.items)) return d.items;
    if (Array.isArray(d)) return d;
    return [];
}

// Features Preview
function renderFeatures(d: any): any[] {
    if (Array.isArray(d?.items)) return d.items;
    if (Array.isArray(d)) return d;
    return [];
}

// Personas Preview
function renderPersonas(d: any): any[] {
    if (Array.isArray(d?.items)) return d.items;
    if (Array.isArray(d)) return d;
    return [];
}

// Products Preview
function renderProducts(d: any) {
    return {
        highlights: Array.isArray(d?.highlights) ? d.highlights : [],
        saving: Array.isArray(d?.saving) ? d.saving : [],
        loan: Array.isArray(d?.loan) ? d.loan : [],
    };
}

// How It Works Preview
function renderHowItWorks(d: any) {
    return {
        title: d?.title || '',
        subtitle: d?.subtitle || '',
        steps: Array.isArray(d?.steps) ? d.steps : [],
    };
}

// Pricing Preview
function renderPricing(d: any) {
    return {
        title: d?.title || '',
        subtitle: d?.subtitle || '',
        tiers: d?.tiers || {},
        footer_note: d?.footer_note || '',
    };
}

// Testimonials Preview
function renderTestimonials(d: any) {
    return {
        title: d?.title || '',
        subtitle: d?.subtitle || '',
        items: Array.isArray(d?.items) ? d.items : [],
    };
}

// FAQs Preview
function renderFaqs(d: any) {
    return {
        title: d?.title || '',
        items: Array.isArray(d?.items) ? d.items : [],
    };
}

// CTA Preview
function renderCta(d: any) {
    return {
        title: d?.title || '',
        subtitle: d?.subtitle || '',
        primary: d?.primary || { label: '', href: '#' },
        secondary: d?.secondary || { label: '', href: '#' },
    };
}

// Demo Preview
function renderDemo(d: any) {
    return {
        badge: d?.badge || '',
        title: d?.title || '',
        subtitle: d?.subtitle || '',
        reset_notice: d?.reset_notice || '',
        accounts_intro: d?.accounts_intro || '',
        roles_intro: d?.roles_intro || {},
    };
}

// Contact Preview
function renderContact(d: any) {
    return {
        email: d?.email || '',
        whatsapp: d?.whatsapp || '',
        address: d?.address || '',
    };
}

// About Preview
function renderAbout(d: any) {
    const company = d?.company || {};
    return {
        name: company?.name || '',
        legal_name: company?.legal_name || '',
        mission: company?.mission || '',
        vision: company?.vision || '',
        values: Array.isArray(company?.values) ? company.values : [],
        team: Array.isArray(company?.team) ? company.team : [],
    };
}

// SEO Preview
function renderSeo(d: any) {
    const og = d?.og || {};
    return {
        default_title: d?.default_title || '',
        description: d?.description || '',
        keywords: d?.keywords || '',
        canonical_url: d?.canonical_url || '',
        og_title: og?.title || '',
        og_description: og?.description || '',
        og_image: og?.image || '',
    };
}

// Legal Preview
function renderLegal(d: any) {
    const privacy = d?.privacy || {};
    const terms = d?.terms || {};
    return {
        last_updated: d?.last_updated || '',
        company_name: d?.company_name || '',
        privacy_email: d?.privacy_email || '',
        privacy_headline: privacy?.headline || '',
        privacy_intro: privacy?.intro || '',
        terms_headline: terms?.headline || '',
        terms_intro: terms?.intro || '',
    };
}

// Trust Bar Preview
function renderTrustBar(d: any) {
    return {
        title: d?.title || '',
        partners: Array.isArray(d?.partners) ? d.partners : [],
    };
}

const previewData = computed(() => {
    switch (props.section) {
        case 'brand': return { type: 'brand', data: renderBrand(props.data) };
        case 'nav': return { type: 'nav', data: renderNav(props.data) };
        case 'footer': return { type: 'footer', data: renderFooter(props.data) };
        case 'hero': return { type: 'hero', data: renderHero(props.data) };
        case 'trust_bar': return { type: 'trust_bar', data: renderTrustBar(props.data) };
        case 'stats': return { type: 'stats', data: renderStats(props.data) };
        case 'features': return { type: 'features', data: renderFeatures(props.data) };
        case 'personas': return { type: 'personas', data: renderPersonas(props.data) };
        case 'products': return { type: 'products', data: renderProducts(props.data) };
        case 'how_it_works': return { type: 'how_it_works', data: renderHowItWorks(props.data) };
        case 'pricing': return { type: 'pricing', data: renderPricing(props.data) };
        case 'testimonials': return { type: 'testimonials', data: renderTestimonials(props.data) };
        case 'faqs': return { type: 'faqs', data: renderFaqs(props.data) };
        case 'cta': return { type: 'cta', data: renderCta(props.data) };
        case 'demo': return { type: 'demo', data: renderDemo(props.data) };
        case 'contact': return { type: 'contact', data: renderContact(props.data) };
        case 'about': return { type: 'about', data: renderAbout(props.data) };
        case 'seo': return { type: 'seo', data: renderSeo(props.data) };
        case 'legal': return { type: 'legal', data: renderLegal(props.data) };
        default: return { type: 'empty', data: {} };
    }
});

// Icon helper map for icon-select values to emoji previews
const iconEmoji: Record<string, string> = {
    // Finance
    shield: '🛡️', zap: '⚡', sparkles: '✨', gift: '🎁',
    wallet: '👛', 'piggy-bank': '🐷', 'badge-dollar-sign': '💵',
    'money-bag': '💰', 'credit-card': '💳', bank: '🏦',
    receipt: '🧾', handshake: '🤝', percentage: '💹', gem: '💎',
    scale: '⚖️',
    // Business & Documents
    briefcase: '💼', building: '🏢', 'file-text': '📄',
    document: '📃', 'clipboard-list': '📋', folder: '📁',
    'folder-open': '📂', bookmark: '🔖', pushpin: '📌',
    memo: '📝', newspaper: '📰', 'bar-chart': '📊',
    'trending-up': '📈', 'trending-down': '📉',
    // Communication
    mail: '✉️', phone: '📞', smartphone: '📱',
    message: '💬', chat: '💭', notification: '🔔',
    send: '📤', inbox: '📥',
    // People
    users: '👥', person: '👤', crown: '👑', support: '🧑‍💻',
    // Actions
    search: '🔍', edit: '✏️', trash: '🗑️', plus: '➕',
    'check-circle': '✅', close: '❌', upload: '⬆️',
    download: '⬇️', refresh: '🔄', save: '💾',
    // Navigation
    home: '🏠', link: '🔗', 'arrow-right': '➡️', 'arrow-left': '⬅️',
    'arrow-up': '⬆️', 'arrow-down': '⬇️',
    // Time
    clock: '⏰', 'calendar-days': '📅', 'calendar-clock': '📆',
    hourglass: '⌛', timer: '⏱️', watch: '⌚',
    // Security
    lock: '🔒', unlock: '🔓', key: '🔑', verified: '✔️',
    // Media
    camera: '📷', image: '🖼️', video: '🎬',
    play: '▶️', headphone: '🎧',
    // Stars & Awards
    star: '⭐', heart: '❤️', award: '🏆', medal: '🥇',
    fire: '🔥', rocket: '🚀', target: '🎯', flag: '🚩',
    // Location
    'map-pin': '📍', route: '🗺️', globe: '🌐',
    // Technology
    database: '🗄️', cloud: '☁️', server: '🖥️',
    code: '💻', cog: '⚙️',
    // Misc
    lightbulb: '💡', cart: '🛒', truck: '🚚',
    airplane: '✈️', ship: '🚢', umbrella: '☂️',
};

function getIconEmoji(icon: string): string {
    return iconEmoji[icon] || '📌';
}
</script>

<template>
    <div class="space-y-3">
        <!-- Hero Section Preview -->
        <div v-if="previewData.type === 'hero'" class="space-y-3">
            <div class="bg-gradient-to-br from-primary-50 to-white dark:from-primary-900/20 dark:to-neutral-900 rounded-xl p-5 border border-neutral-200 dark:border-neutral-700">
                <span v-if="previewData.data.badge" class="inline-block px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 text-[9px] font-semibold mb-2">
                    ✨ {{ previewData.data.badge }}
                </span>
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white leading-tight">{{ previewData.data.headline || '(headline)' }}</h2>
                <p v-if="previewData.data.subheadline" class="mt-2 text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed line-clamp-3">{{ previewData.data.subheadline }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span v-if="previewData.data.primary_cta?.label" class="px-3 py-1 text-xs font-semibold text-white bg-primary-600 rounded-md">{{ previewData.data.primary_cta.label }}</span>
                    <span v-if="previewData.data.secondary_cta?.label" class="px-3 py-1 text-xs font-semibold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-md">{{ previewData.data.secondary_cta.label }}</span>
                </div>
                <p v-if="previewData.data.floating_badge" class="mt-2 text-[10px] text-neutral-500 flex items-center gap-1">
                    <svg class="w-3 h-3 text-primary-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ previewData.data.floating_badge }}
                </p>
                <!-- Carousel preview -->
                <div v-if="previewData.data.carousel_slides.length" class="mt-3 flex gap-2 overflow-x-auto pb-1">
                    <div v-for="(slide, idx) in previewData.data.carousel_slides" :key="idx" class="flex-shrink-0 w-24 h-16 rounded-lg overflow-hidden border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                        <img
                            v-if="slide.src"
                            :src="slide.src"
                            :alt="slide.alt || 'Slide ' + (idx + 1)"
                            class="w-full h-full object-cover"
                            @error="($event.target as HTMLImageElement).style.display = 'none'"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-[18px] text-neutral-300">📱</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Brand Preview -->
        <div v-if="previewData.type === 'brand'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-5 border border-neutral-200 dark:border-neutral-700 text-center">
                <div v-if="previewData.data.logo" class="mb-3 flex justify-center">
                    <img :src="previewData.data.logo" alt="Logo" class="h-10 object-contain" @error="($event.target as HTMLImageElement).style.display='none'" />
                </div>
                <div v-else class="w-14 h-14 rounded-xl bg-primary-600 flex items-center justify-center text-white font-bold text-xl mx-auto mb-3">
                    {{ (previewData.data.name || 'N').charAt(0).toUpperCase() }}
                </div>
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">{{ previewData.data.name }}</h3>
                <p class="text-xs text-primary-600 dark:text-primary-400 font-medium mt-0.5">{{ previewData.data.tagline }}</p>
                <p v-if="previewData.data.description" class="mt-2 text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">{{ previewData.data.description }}</p>
                <p v-if="previewData.data.favicon" class="mt-2 text-[9px] text-neutral-400">Favicon: {{ previewData.data.favicon }}</p>
            </div>
        </div>

        <!-- Nav Preview -->
        <div v-if="previewData.type === 'nav'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-100 dark:border-neutral-700">
                    <span class="text-sm font-bold text-neutral-900 dark:text-white">Logo</span>
                    <div class="flex items-center gap-3">
                        <template v-for="item in previewData.data.items" :key="item.label + item.href">
                            <span class="text-[10px] text-neutral-600 dark:text-neutral-400 font-medium">{{ item.label }}</span>
                        </template>
                        <span class="px-2 py-0.5 text-[9px] font-semibold text-white bg-primary-600 rounded">Demo</span>
                    </div>
                </div>
                <div class="px-4 py-2 bg-neutral-50 dark:bg-neutral-800/50 flex flex-wrap gap-2">
                    <template v-for="item in previewData.data.items" :key="item.label + item.href">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[9px] bg-neutral-100 dark:bg-neutral-700 rounded text-neutral-600 dark:text-neutral-300">
                            🔗 {{ item.label }}
                        </span>
                    </template>
                    <span v-if="previewData.data.items.length === 0" class="text-[10px] text-neutral-400 italic">Belum ada menu navigasi</span>
                </div>
            </div>
        </div>

        <!-- Footer Preview -->
        <div v-if="previewData.type === 'footer'" class="space-y-3">
            <div class="bg-primary-900 dark:bg-primary-950 text-white rounded-xl p-5 border border-primary-800">
                <div class="flex items-center gap-2 mb-3">
                    <img src="/images/logo-only-white.png" alt="e-Koperasi" class="h-11 w-11" />
                    <span class="text-sm font-bold">{{ previewData.data.name || 'Brand' }}</span>
                </div>
                <p v-if="previewData.data.description" class="text-[10px] text-primary-200 leading-relaxed mb-3">{{ previewData.data.description }}</p>
                <div v-if="previewData.data.columns.length" class="grid grid-cols-2 gap-3 mb-3">
                    <div v-for="col in previewData.data.columns" :key="col.title" class="space-y-1">
                        <p class="text-[10px] font-semibold text-white">{{ col.title }}</p>
                        <div v-for="link in col.links || []" :key="link.label" class="text-[9px] text-primary-200">{{ link.label }}</div>
                    </div>
                </div>
                <p v-if="previewData.data.copyright" class="text-[9px] text-primary-300 pt-2 border-t border-primary-700">{{ previewData.data.copyright }}</p>
            </div>
        </div>

        <!-- Stats Preview -->
        <div v-if="previewData.type === 'stats'" class="space-y-3">
            <div class="bg-primary-600 dark:bg-primary-700 text-white rounded-xl p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="(stat, i) in previewData.data.slice(0, 4)" :key="i" class="text-center">
                        <p class="text-lg font-bold">{{ stat.value }}</p>
                        <p class="text-[9px] text-primary-100">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Preview -->
        <div v-if="previewData.type === 'features'" class="space-y-3">
            <div class="grid grid-cols-1 gap-2">
                <div v-for="(f, i) in previewData.data.slice(0, 4)" :key="i"
                    class="flex items-start gap-2.5 bg-white dark:bg-neutral-800 rounded-lg p-3 border border-neutral-200 dark:border-neutral-700"
                >
                    <span class="text-lg flex-shrink-0">{{ getIconEmoji(f.icon) }}</span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-neutral-900 dark:text-white">{{ f.title }}</p>
                        <p v-if="f.description" class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ f.description }}</p>
                    </div>
                </div>
                <p v-if="previewData.data.length === 0" class="text-xs text-neutral-400 text-center py-4">Belum ada fitur</p>
                <p v-if="previewData.data.length > 4" class="text-[10px] text-neutral-400 text-center">+{{ previewData.data.length - 4 }} fitur lainnya</p>
            </div>
        </div>

        <!-- Personas Preview -->
        <div v-if="previewData.type === 'personas'" class="space-y-3">
            <div class="grid grid-cols-1 gap-2">
                <div v-for="(p, i) in previewData.data.slice(0, 3)" :key="i"
                    class="bg-white dark:bg-neutral-800 rounded-lg p-3 border border-neutral-200 dark:border-neutral-700"
                >
                    <span class="text-lg block mb-1">{{ getIconEmoji(p.icon) }}</span>
                    <p class="text-xs font-semibold text-neutral-900 dark:text-white">{{ p.title }}</p>
                    <p v-if="p.description" class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">{{ p.description }}</p>
                </div>
            </div>
        </div>

        <!-- Products Preview -->
        <div v-if="previewData.type === 'products'" class="space-y-3">
            <!-- Highlights -->
            <div v-if="previewData.data.highlights.length" class="grid grid-cols-2 gap-2">
                <div v-for="h in previewData.data.highlights.slice(0, 4)" :key="h.label"
                    class="bg-white dark:bg-neutral-800 rounded-lg p-2.5 border border-neutral-200 dark:border-neutral-700 text-center"
                >
                    <span class="text-lg block">{{ getIconEmoji(h.icon) }}</span>
                    <p class="text-[10px] font-bold text-neutral-900 dark:text-white">{{ h.value }}</p>
                    <p class="text-[8px] text-neutral-500">{{ h.label }}</p>
                </div>
            </div>
            <!-- Saving Products -->
            <div v-if="previewData.data.saving.length" class="space-y-2">
                <p class="text-[10px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Tabungan</p>
                <div v-for="prod in previewData.data.saving.slice(0, 2)" :key="prod.name"
                    class="bg-white dark:bg-neutral-800 rounded-lg p-3 border border-neutral-200 dark:border-neutral-700"
                >
                    <div class="flex items-start gap-2">
                        <span class="text-lg flex-shrink-0">{{ getIconEmoji(prod.icon) }}</span>
                        <div>
                            <p class="text-xs font-semibold text-neutral-900 dark:text-white">{{ prod.name }}</p>
                            <p class="text-[9px] text-neutral-500 dark:text-neutral-400">{{ prod.tagline }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Loan Products -->
            <div v-if="previewData.data.loan.length" class="space-y-2">
                <p class="text-[10px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Pinjaman</p>
                <div v-for="loan in previewData.data.loan.slice(0, 2)" :key="loan.name"
                    class="bg-white dark:bg-neutral-800 rounded-lg p-3 border border-neutral-200 dark:border-neutral-700"
                >
                    <div class="flex items-start justify-between mb-1">
                        <p class="text-xs font-semibold text-neutral-900 dark:text-white">{{ loan.name }}</p>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 font-medium">{{ loan.badge }}</span>
                    </div>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 line-clamp-2">{{ loan.description }}</p>
                </div>
            </div>
        </div>

        <!-- How It Works Preview -->
        <div v-if="previewData.type === 'how_it_works'" class="space-y-3">
            <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-5 border border-primary-100 dark:border-primary-800">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white mb-1">{{ previewData.data.title || 'Cara Kerja' }}</h3>
                <p v-if="previewData.data.subtitle" class="text-[10px] text-neutral-500 dark:text-neutral-400 mb-3">{{ previewData.data.subtitle }}</p>
                <div class="space-y-2">
                    <div v-for="(step, i) in previewData.data.steps.slice(0, 3)" :key="i"
                        class="flex items-start gap-2.5 bg-white dark:bg-neutral-800 rounded-lg p-2.5"
                    >
                        <div class="w-6 h-6 rounded-full bg-primary-600 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0">{{ step.step || i + 1 }}</div>
                        <div>
                            <p class="text-[11px] font-semibold text-neutral-900 dark:text-white">{{ step.title }}</p>
                            <p v-if="step.description" class="text-[9px] text-neutral-500 dark:text-neutral-400">{{ step.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Preview -->
        <div v-if="previewData.type === 'pricing'" class="space-y-3">
            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">{{ previewData.data.title || 'Harga' }}</h3>
            <p v-if="previewData.data.subtitle" class="text-[10px] text-neutral-500 dark:text-neutral-400 -mt-2">{{ previewData.data.subtitle }}</p>
            <div class="grid grid-cols-1 gap-2">
                <div v-for="(tier, key) in previewData.data.tiers" :key="key"
                    class="bg-white dark:bg-neutral-800 rounded-lg p-3 border"
                    :class="tier.highlight ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-500' : 'border-neutral-200 dark:border-neutral-700'"
                >
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs font-bold text-neutral-900 dark:text-white">{{ tier.name }}</p>
                        <span class="text-sm font-bold text-primary-600">{{ tier.price }}</span>
                    </div>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400">{{ tier.tagline }}</p>
                    <div class="mt-2 space-y-0.5">
                        <div v-for="f in (tier.features || []).slice(0, 3)" :key="f" class="flex items-center gap-1 text-[9px] text-neutral-600 dark:text-neutral-300">
                            <svg class="w-2.5 h-2.5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ f }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Preview -->
        <div v-if="previewData.type === 'testimonials'" class="space-y-3">
            <div class="space-y-2">
                <div v-for="(t, i) in previewData.data.items.slice(0, 2)" :key="i"
                    class="bg-white dark:bg-neutral-800 rounded-lg p-3 border border-neutral-200 dark:border-neutral-700"
                >
                    <p class="text-[10px] text-neutral-600 dark:text-neutral-300 italic leading-relaxed line-clamp-3">"{{ t.quote }}"</p>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-[9px] font-bold text-primary-700 dark:text-primary-400 flex-shrink-0">
                            {{ (t.name || '?').charAt(0) }}
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-neutral-900 dark:text-white">{{ t.name }}</p>
                            <p v-if="t.role" class="text-[8px] text-neutral-500">{{ t.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQs Preview -->
        <div v-if="previewData.type === 'faqs'" class="space-y-3">
            <div class="space-y-1.5">
                <div v-for="(faq, i) in previewData.data.items.slice(0, 3)" :key="i"
                    class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden"
                >
                    <div class="px-3 py-2 bg-neutral-50 dark:bg-neutral-800/50">
                        <p class="text-[10px] font-medium text-neutral-900 dark:text-white">❓ {{ faq.q }}</p>
                    </div>
                    <p class="px-3 py-2 text-[9px] text-neutral-500 dark:text-neutral-400 leading-relaxed">{{ faq.a }}</p>
                </div>
            </div>
        </div>

        <!-- CTA Preview -->
        <div v-if="previewData.type === 'cta'" class="space-y-3">
            <div class="bg-gradient-to-br from-primary-600 to-primary-800 text-white rounded-xl p-5 text-center">
                <h3 class="text-sm font-bold leading-tight">{{ previewData.data.title || 'Call to Action' }}</h3>
                <p v-if="previewData.data.subtitle" class="mt-1 text-[10px] text-primary-100">{{ previewData.data.subtitle }}</p>
                <div class="mt-3 flex flex-wrap gap-2 justify-center">
                    <span v-if="previewData.data.primary?.label" class="px-3 py-1 text-[10px] font-semibold text-primary-700 bg-white rounded-md">{{ previewData.data.primary.label }}</span>
                    <span v-if="previewData.data.secondary?.label" class="px-3 py-1 text-[10px] font-semibold text-white bg-primary-700 border border-primary-500 rounded-md">{{ previewData.data.secondary.label }}</span>
                </div>
            </div>
        </div>

        <!-- Contact Preview -->
        <div v-if="previewData.type === 'contact'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm">📧</span>
                    <span class="text-xs text-neutral-700 dark:text-neutral-200">{{ previewData.data.email || '(email)' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm">💬</span>
                    <span class="text-xs text-neutral-700 dark:text-neutral-200">{{ previewData.data.whatsapp || '(whatsapp)' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm">📍</span>
                    <span class="text-xs text-neutral-700 dark:text-neutral-200">{{ previewData.data.address || '(alamat)' }}</span>
                </div>
            </div>
        </div>

        <!-- About Preview -->
        <div v-if="previewData.type === 'about'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700 space-y-3">
                <div class="text-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center text-white font-bold text-sm mx-auto mb-2">{{ (previewData.data.name || 'eK').slice(0, 2) }}</div>
                    <p class="text-sm font-bold text-neutral-900 dark:text-white">{{ previewData.data.name || 'Nama Perusahaan' }}</p>
                    <p class="text-[9px] text-neutral-500">{{ previewData.data.legal_name }}</p>
                </div>
                <div v-if="previewData.data.mission" class="pt-2 border-t border-neutral-100 dark:border-neutral-700">
                    <p class="text-[9px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Misi</p>
                    <p class="text-[10px] text-neutral-700 dark:text-neutral-200 mt-0.5">{{ previewData.data.mission }}</p>
                </div>
                <div v-if="previewData.data.team.length" class="pt-2 border-t border-neutral-100 dark:border-neutral-700">
                    <p class="text-[9px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Tim</p>
                    <div v-for="member in previewData.data.team.slice(0, 2)" :key="member.name" class="flex items-center gap-2 py-1">
                        <div class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-[7px] font-bold text-primary-700 dark:text-primary-400">{{ (member.name || '?').charAt(0) }}</div>
                        <div>
                            <p class="text-[10px] font-medium text-neutral-900 dark:text-white">{{ member.name }}</p>
                            <p class="text-[8px] text-neutral-500">{{ member.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demo Preview -->
        <div v-if="previewData.type === 'demo'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700">
                <span class="inline-block px-2 py-0.5 rounded bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 text-[9px] font-semibold mb-2">{{ previewData.data.badge || 'Demo' }}</span>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">{{ previewData.data.title || 'Halaman Demo' }}</h3>
                <p v-if="previewData.data.subtitle" class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1">{{ previewData.data.subtitle }}</p>
                <div v-if="Object.keys(previewData.data.roles_intro).length" class="mt-3 flex flex-wrap gap-1.5">
                    <span v-for="(roleLabel, roleKey) in previewData.data.roles_intro" :key="roleKey" class="px-2 py-0.5 text-[8px] rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
                        {{ roleLabel }}
                    </span>
                </div>
            </div>
        </div>

        <!-- SEO Preview -->
        <div v-if="previewData.type === 'seo'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700 space-y-2">
                <!-- Google Snippet -->
                <div class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-3 border border-neutral-200 dark:border-neutral-700">
                    <p class="text-sm font-bold text-blue-700 dark:text-blue-400 leading-tight">{{ previewData.data.default_title || '(title)' }}</p>
                    <p class="text-xs text-green-700 dark:text-green-400 mt-0.5">{{ previewData.data.canonical_url || 'https://e-koperasi.com/' }}</p>
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 mt-0.5 line-clamp-2">{{ previewData.data.description || '(description)' }}</p>
                </div>
                <p class="text-[9px] text-neutral-400">Keywords: {{ previewData.data.keywords || '(keywords)' }}</p>

                <!-- Social Media Preview -->
                <div v-if="previewData.data.og_title || previewData.data.og_image" class="rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div v-if="previewData.data.og_image" class="h-16 bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                        <img :src="previewData.data.og_image" alt="OG" class="w-full h-full object-cover" @error="($event.target as HTMLImageElement).style.display='none'" />
                    </div>
                    <div class="p-2 bg-white dark:bg-neutral-800">
                        <p class="text-[10px] font-semibold text-neutral-900 dark:text-white leading-tight line-clamp-1">{{ previewData.data.og_title || '(og title)' }}</p>
                        <p v-if="previewData.data.og_description" class="text-[8px] text-neutral-500 dark:text-neutral-400 line-clamp-1">{{ previewData.data.og_description }}</p>
                        <p class="text-[8px] text-neutral-400 mt-0.5">{{ previewData.data.canonical_url || 'e-koperasi.com' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Preview -->
        <div v-if="previewData.type === 'legal'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700 space-y-2">
                <div class="text-center">
                    <span class="text-2xl block mb-2">⚖️</span>
                    <p class="text-xs font-medium text-neutral-900 dark:text-white">Legal Pages</p>
                    <p v-if="previewData.data.last_updated" class="text-[10px] text-neutral-500 dark:text-neutral-400">Terakhir diperbarui: {{ previewData.data.last_updated }}</p>
                </div>
                <div v-if="previewData.data.company_name" class="pt-2 border-t border-neutral-100 dark:border-neutral-700">
                    <p class="text-[9px] text-neutral-500 dark:text-neutral-400">Perusahaan: <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ previewData.data.company_name }}</span></p>
                </div>
                <div v-if="previewData.data.privacy_headline || previewData.data.terms_headline" class="flex gap-2 pt-1">
                    <span v-if="previewData.data.privacy_headline" class="px-2 py-0.5 text-[8px] rounded bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400">{{ previewData.data.privacy_headline }}</span>
                    <span v-if="previewData.data.terms_headline" class="px-2 py-0.5 text-[8px] rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">{{ previewData.data.terms_headline }}</span>
                </div>
            </div>
        </div>

        <!-- Trust Bar Preview -->
        <div v-if="previewData.type === 'trust_bar'" class="space-y-3">
            <div class="bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-200 dark:border-neutral-700 text-center">
                <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">{{ previewData.data.title || '(trust bar title)' }}</p>
                <div v-if="previewData.data.partners.length" class="mt-3 flex flex-wrap justify-center gap-3">
                    <div v-for="p in previewData.data.partners.slice(0, 5)" :key="p.name" class="flex flex-col items-center gap-1">
                        <div v-if="p.src" class="w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center overflow-hidden p-1">
                            <img :src="p.src" :alt="p.name" class="max-w-full max-h-full object-contain" @error="($event.target as HTMLImageElement).style.display='none'" />
                        </div>
                        <span v-else class="text-[8px] font-bold text-neutral-400">{{ (p.name || 'Logo').slice(0, 8) }}</span>
                    </div>
                </div>
                <div v-else class="mt-3 flex flex-wrap justify-center gap-4 opacity-50">
                    <span class="text-[10px] font-bold text-neutral-400">LOGO</span>
                    <span class="text-[10px] font-bold text-neutral-400">LOGO</span>
                    <span class="text-[10px] font-bold text-neutral-400">LOGO</span>
                </div>
            </div>
        </div>

        <!-- Empty Preview -->
        <div v-if="previewData.type === 'empty'" class="text-center py-8 text-neutral-400 dark:text-neutral-500">
            <span class="text-2xl block mb-2">👁️</span>
            <p class="text-xs">Preview tidak tersedia</p>
        </div>
    </div>
</template>
