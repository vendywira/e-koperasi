<script setup lang="ts">
import { computed } from 'vue';
import type { CarouselSlide } from '@/composables/use3DCarousel';
import { use3DCarousel } from '@/composables/use3DCarousel';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        slides: CarouselSlide[];
        /** Ring radius in px. */
        radius?: number;
        /** Auto-rotate interval in ms. */
        interval?: number;
    }>(),
    { radius: 360, interval: 5000 }
);

const { currentIndex, rotationDeg, anglePerSlide, radius, next, prev, goTo, pause, resume } =
    use3DCarousel({
        count: props.slides.length,
        radius: props.radius,
        interval: props.interval,
    });

// Whole ring rotates; Z-translated so the ring center is at the camera's focus.
const ringTransform = computed(
    () => `translateZ(-${radius.value}px) rotateY(${rotationDeg.value}deg)`
);

// Each phone sits on the ring at its angular slot.
// The closer a phone is to the front (current), the larger and brighter it appears.
function slideTransform(index: number) {
    const slotAngle = index * anglePerSlide.value;
    // Normalized distance from "front" position: 0 = front, 1 = side, 2 = back
    const offsetSlots = Math.abs(index - currentIndex.value);
    const wrapped = Math.min(offsetSlots, props.slides.length - offsetSlots);
    const dist = wrapped / (props.slides.length / 2); // 0..1
    const scale = 1 - dist * 0.18;                     // 1.0 → 0.82
    const opacity = 1 - dist * 0.55;                  // 1.0 → 0.45
    return `rotateY(${slotAngle}deg) translateZ(${radius.value}px) scale(${scale})`;
}

function slideOpacity(index: number) {
    const offsetSlots = Math.abs(index - currentIndex.value);
    const wrapped = Math.min(offsetSlots, props.slides.length - offsetSlots);
    const dist = wrapped / (props.slides.length / 2);
    return 1 - dist * 0.55;
}

const isFront = (index: number) => index === currentIndex.value;
</script>

<template>
    <div class="relative">
        <!-- Scene with perspective -->
        <div
            class="relative mx-auto"
            :style="{
                width: '260px',
                height: '600px',
                perspective: '1400px',
                perspectiveOrigin: '50% 40%',
            }"
            @mouseenter="pause()"
            @mouseleave="resume()"
        >
            <!-- Ambient floor reflection (static, behind everything) -->
            <div
                class="absolute left-1/2 -translate-x-1/2 pointer-events-none"
                style="bottom: -60px; width: 220px; height: 120px;
                       background: radial-gradient(ellipse at center,
                           rgba(16, 185, 129, 0.25) 0%,
                           rgba(16, 185, 129, 0.10) 35%,
                           transparent 70%);
                       filter: blur(8px);"
            />

            <!-- Rotating ring of phones -->
            <div
                class="w-full h-full relative"
                :style="{
                    transformStyle: 'preserve-3d',
                    transition: 'transform 1s cubic-bezier(0.22, 1, 0.36, 1)',
                    transform: ringTransform,
                }"
            >
                <div
                    v-for="(slide, index) in slides"
                    :key="index"
                    class="absolute inset-0"
                    :style="{
                        transform: slideTransform(index),
                        opacity: slideOpacity(index),
                        transition: 'transform 1s cubic-bezier(0.22, 1, 0.36, 1), opacity 1s ease',
                    }"
                >
                    <!-- Reflection underneath (mirrored) -->
                    <div
                        class="absolute left-0 right-0 pointer-events-none"
                        style="top: 100%; height: 100%;
                               transform: scaleY(-1);
                               mask-image: linear-gradient(to bottom, rgba(0,0,0,0.45), transparent 70%);
                               -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,0.45), transparent 70%);"
                    >
                        <div class="w-full h-full rounded-[2.5rem] p-[10px]">
                            <div class="w-full h-full rounded-[2rem] overflow-hidden">
                                <img
                                    :src="slide.srcSmall ?? slide.src"
                                    :alt="''"
                                    aria-hidden="true"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Phone body -->
                    <div
                        class="relative w-full h-full rounded-[2.5rem] p-[10px] shadow-2xl border-2 transition-colors duration-500"
                        :class="isFront(index)
                            ? 'bg-neutral-900 border-primary-400/60 ring-2 ring-primary-500/40'
                            : 'bg-neutral-900 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700'"
                    >
                        <!-- Glossy highlight on top of phone frame -->
                        <div
                            class="absolute inset-0 rounded-[2.5rem] pointer-events-none"
                            style="background: linear-gradient(135deg,
                                rgba(255,255,255,0.10) 0%,
                                rgba(255,255,255,0.02) 30%,
                                transparent 50%,
                                rgba(255,255,255,0.03) 80%,
                                rgba(255,255,255,0.08) 100%);"
                        />

                        <!-- Active glow -->
                        <div
                            v-if="isFront(index)"
                            class="absolute -inset-1 rounded-[2.6rem] pointer-events-none"
                            style="background: radial-gradient(ellipse at center,
                                rgba(16, 185, 129, 0.35) 0%,
                                transparent 70%);
                                filter: blur(12px); z-index: -1;"
                        />

                        <!-- Screen -->
                        <div class="relative w-full h-full rounded-[2rem] overflow-hidden bg-white">
                            <picture>
                                <source
                                    :srcset="slide.srcSmall ? `${slide.srcSmall} 640w, ${slide.src} 1280w` : slide.src"
                                    type="image/webp"
                                />
                                <img
                                    :src="slide.src"
                                    :alt="slide.alt"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                            </picture>

                            <!-- Subtle screen glare -->
                            <div
                                class="absolute inset-0 pointer-events-none"
                                style="background: linear-gradient(135deg,
                                    rgba(255,255,255,0.18) 0%,
                                    transparent 35%,
                                    transparent 65%,
                                    rgba(255,255,255,0.08) 100%);"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation arrows -->
            <button
                @click="prev()"
                class="absolute top-1/2 -left-4 sm:-left-12 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-white/95 dark:bg-neutral-800/95 border border-neutral-200 dark:border-neutral-600 shadow-xl hover:scale-110 hover:shadow-2xl transition-all duration-200 text-neutral-700 dark:text-neutral-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 backdrop-blur-sm"
                aria-label="Previous slide"
            >
                <ChevronLeft class="w-5 h-5" />
            </button>
            <button
                @click="next()"
                class="absolute top-1/2 -right-4 sm:-right-12 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-white/95 dark:bg-neutral-800/95 border border-neutral-200 dark:border-neutral-600 shadow-xl hover:scale-110 hover:shadow-2xl transition-all duration-200 text-neutral-700 dark:text-neutral-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 backdrop-blur-sm"
                aria-label="Next slide"
            >
                <ChevronRight class="w-5 h-5" />
            </button>
        </div>

        <!-- Dot indicators -->
        <div class="flex justify-center gap-2 mt-10">
            <button
                v-for="(_, i) in slides"
                :key="i"
                @click="goTo(i)"
                class="h-2 rounded-full transition-all duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-400"
                :class="i === currentIndex
                    ? 'w-8 bg-gradient-to-r from-primary-500 to-primary-600 shadow-md shadow-primary-500/50'
                    : 'w-2 bg-neutral-300 dark:bg-neutral-600 hover:bg-neutral-400'"
                :aria-label="'Go to slide ' + (i + 1)"
                :aria-current="i === currentIndex ? 'true' : undefined"
            />
        </div>
    </div>
</template>
