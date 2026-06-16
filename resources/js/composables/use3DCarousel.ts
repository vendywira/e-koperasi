import { onMounted, onUnmounted, ref, computed } from 'vue';

export type CarouselSlide = {
    /** Path used by the <img> element */
    src: string;
    /** Optional small variant for srcset (defaults to replacing ext with -sm.webp) */
    srcSmall?: string;
    /** Alt text describing the slide */
    alt: string;
};

type CarouselOptions = {
    /** Total number of slides (used to compute the angle per slide). */
    count: number;
    /** Radius of the rotation ring in pixels. Default 380 */
    radius?: number;
    /** Auto-rotate interval in ms. Default 4000 */
    interval?: number;
};

/**
 * Composable that drives a 3D rotating carousel of phone mockups.
 *
 * Returns:
 *   - currentIndex:  Reactive ref of the active slide
 *   - rotationDeg:   Reactive ref of the current Y-axis rotation in degrees
 *   - pause:         Call to pause auto-rotation
 *   - resume:        Call to resume auto-rotation
 *   - goTo(i):       Jump to slide index
 *   - next / prev:   Step forward / backward
 *
 * The component is responsible for rendering the actual phone frames and
 * applying each frame's individual transform based on `rotationDeg` and the
 * slide's index. This composable only owns the state + timing.
 *
 * Respects prefers-reduced-motion (auto-rotation is disabled).
 */
export function use3DCarousel(options: CarouselOptions) {
    const radius = ref(options.radius ?? 380);
    const interval = options.interval ?? 4000;

    const currentIndex = ref(0);
    const isPaused = ref(false);
    let timer: number | null = null;
    let reducedMotion = false;

    const anglePerSlide = computed(() => 360 / options.count);
    // We rotate the whole ring by -(current * angle) so the current slide is at front (0deg)
    const rotationDeg = computed(() => -currentIndex.value * anglePerSlide.value);

    function next() {
        currentIndex.value = (currentIndex.value + 1) % options.count;
    }

    function prev() {
        currentIndex.value = (currentIndex.value - 1 + options.count) % options.count;
    }

    function goTo(i: number) {
        currentIndex.value = ((i % options.count) + options.count) % options.count;
    }

    function start() {
        if (reducedMotion || isPaused.value) return;
        stop();
        timer = window.setInterval(next, interval);
    }

    function stop() {
        if (timer !== null) {
            clearInterval(timer);
            timer = null;
        }
    }

    function pause() {
        isPaused.value = true;
        stop();
    }

    function resume() {
        isPaused.value = false;
        start();
    }

    onMounted(() => {
        reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reducedMotion) start();
    });

    onUnmounted(stop);

    return {
        currentIndex,
        rotationDeg,
        anglePerSlide,
        radius,
        next,
        prev,
        goTo,
        pause,
        resume,
    };
}
