import { onMounted, onUnmounted, ref, type Ref } from 'vue';

/**
 * Composable that triggers a CSS class on an element when it scrolls into view.
 *
 * Usage:
 *   const { containerRef, itemRefs } = useScrollReveal();
 *   <div ref="containerRef">
 *       <div v-for="(item, i) in items" :key="i" :ref="el => itemRefs[i] = el">
 *
 * The revealed class is applied after IntersectionObserver fires.
 * Supports staggered delays via `staggerMs` (default 80 ms between items).
 */
export function useScrollReveal(options?: {
    staggerMs?: number;
    threshold?: number;
    rootMargin?: string;
}) {
    const staggerMs = options?.staggerMs ?? 80;
    const threshold = options?.threshold ?? 0.15;
    const rootMargin = options?.rootMargin ?? '0px 0px -60px 0px';

    const containerRef = ref<HTMLElement | null>(null) as Ref<HTMLElement | null>;
    const itemRefs: Record<number, HTMLElement | null> = {};

    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const el = entry.target as HTMLElement;
                        const delay = parseInt(el.dataset.revealDelay ?? '0', 10);
                        setTimeout(() => {
                            el.classList.add('scroll-revealed');
                        }, delay);
                        observer?.unobserve(el);
                    }
                });
            },
            { threshold, rootMargin },
        );

        // Observe items already registered before onMounted (template refs)
        Object.values(itemRefs).forEach((el) => {
            if (el && !el.classList.contains('scroll-revealed')) observer!.observe(el);
        });
    });

    onUnmounted(() => {
        observer?.disconnect();
        observer = null;
    });

    /** Call from template :ref binding to register an element at a given index */
    function setItemRef(index: number, el: any) {
        if (el) {
            itemRefs[index] = el as HTMLElement;
            // Apply stagger delay as data attribute
            el.dataset.revealDelay = String(index * staggerMs);
            // Start hidden
            el.classList.add('scroll-reveal-hidden');
            // Observe immediately if observer is ready
            if (observer) observer.observe(el);
        }
    }

    return { containerRef, itemRefs, setItemRef };
}
