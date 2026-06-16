import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useSiteConfig() {
    const page = usePage();
    const config = computed(() => (page.props as any).siteConfig ?? {});

    return {
        config,
        brand:        computed(() => config.value.brand ?? {}),
        nav:          computed(() => config.value.nav ?? {}),
        footer:       computed(() => config.value.footer ?? {}),
        hero:         computed(() => config.value.hero ?? {}),
        trustBar:     computed(() => config.value.trustBar ?? {}),
        stats:        computed(() => config.value.stats ?? []),
        personas:     computed(() => config.value.personas ?? []),
        products:     computed(() => config.value.products ?? {}),
        features:     computed(() => config.value.features ?? []),
        howItWorks:   computed(() => config.value.howItWorks ?? {}),
        pricing:      computed(() => config.value.pricing ?? {}),
        testimonials: computed(() => config.value.testimonials ?? {}),
        faqs:         computed(() => config.value.faqs ?? {}),
        cta:          computed(() => config.value.cta ?? {}),
        demo:         computed(() => config.value.demo ?? {}),
        contact:      computed(() => config.value.contact ?? {}),
        legal:        computed(() => config.value.legal ?? {}),
        app_features: computed(() => config.value.app_features ?? {}),
        about:        computed(() => config.value.about ?? {}),
        seo:          computed(() => config.value.seo ?? {}),
    };
}
