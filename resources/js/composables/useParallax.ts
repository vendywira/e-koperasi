import { onMounted, onUnmounted, ref, type Ref } from 'vue';

/**
 * Composable that applies a subtle parallax translateY effect to an element
 * as it scrolls through the viewport.
 *
 * Usage:
 *   const { elementRef } = useParallax({ speed: 0.15 });
 *   <div ref="elementRef">...
 *
 * The element moves slightly slower than the scroll, creating depth.
 * Uses requestAnimationFrame for smooth, jank-free animation.
 * Respects prefers-reduced-motion.
 */
export function useParallax(options?: {
    /** Parallax intensity (0 = none, 0.3 = noticeable). Default 0.12 */
    speed?: number;
    /** Max px the element can shift. Default 40 */
    maxOffset?: number;
}) {
    const speed = options?.speed ?? 0.12;
    const maxOffset = options?.maxOffset ?? 40;

    const elementRef = ref<HTMLElement | null>(null) as Ref<HTMLElement | null>;
    let ticking = false;
    let reducedMotion = false;

    function onScroll() {
        if (reducedMotion || !elementRef.value) return;
        if (!ticking) {
            requestAnimationFrame(() => {
                applyParallax();
                ticking = false;
            });
            ticking = true;
        }
    }

    function applyParallax() {
        const el = elementRef.value;
        if (!el) return;

        const rect = el.getBoundingClientRect();
        const viewH = window.innerHeight;
        // 0 when element is at bottom of viewport, 1 when at top
        const progress = 1 - (rect.top + rect.height) / (viewH + rect.height);
        // Clamp progress to [0, 1]
        const clamped = Math.max(0, Math.min(1, progress));
        // Center around 0.5 so element shifts up on enter, down on exit
        const offset = (clamped - 0.5) * 2 * maxOffset * speed;

        el.style.transform = `translateY(${offset.toFixed(2)}px)`;
    }

    onMounted(() => {
        reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reducedMotion) {
            window.addEventListener('scroll', onScroll, { passive: true });
            // Initial position
            applyParallax();
        }
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', onScroll);
    });

    return { elementRef };
}
