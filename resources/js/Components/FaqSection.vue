<script setup lang="ts">
import { ref, computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { useSiteConfig } from '@/composables/useSiteConfig';
import { useScrollReveal } from '@/composables/useScrollReveal';

const reveal = useScrollReveal({ staggerMs: 60 });

type Faq = { q: string; a: string };

const props = defineProps<{ items?: Faq[] }>();
const { faqs: faqConfig } = useSiteConfig();

const faqs = computed<Faq[]>(() => {
    if (props.items && props.items.length > 0) return props.items;
    return (faqConfig.value?.items ?? []) as Faq[];
});
const heading = computed(() => faqConfig.value?.heading ?? 'Pertanyaan Umum');

const open = ref<number | null>(0);

function toggle(i: number) {
    open.value = open.value === i ? null : i;
}
</script>

<template>
    <section id="faq" class="py-20 lg:py-28 bg-white dark:bg-neutral-950">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div :ref="(el: any) => reveal.setItemRef(0, el)" class="text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white">
                    {{ heading }}
                </h2>
            </div>

            <div class="mt-12 space-y-3">
                <div
                    v-for="(faq, i) in faqs"
                    :key="i"
                    :ref="(el: any) => reveal.setItemRef(i + 1, el)"
                    class="border border-neutral-100 dark:border-neutral-700 rounded-lg overflow-hidden"
                >
                    <button
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                        :aria-expanded="open === i"
                        :aria-controls="`faq-panel-${i}`"
                        @click="toggle(i)"
                    >
                        <span class="font-semibold text-neutral-900 dark:text-white">{{ faq.q }}</span>
                        <ChevronDown
                            :class="['h-5 w-5 text-neutral-400 dark:text-neutral-500 transition-transform', open === i && 'rotate-180']"
                        />
                    </button>
                    <div
                        v-if="open === i"
                        :id="`faq-panel-${i}`"
                        class="px-6 pb-4 text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed"
                    >
                        {{ faq.a }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

