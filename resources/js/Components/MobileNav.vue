<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    open: boolean;
    items: { label: string; href: string }[];
}>();

defineEmits<{ close: [] }>();
</script>

<template>
    <Transition name="nav-slide" appear>
        <div
            v-if="open"
            class="md:hidden border-t border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-900"
        >
            <div class="px-4 pb-4 space-y-2">
                <a
                    v-for="item in items"
                    :key="item.href"
                    :href="item.href"
                    class="block px-3 py-3 -mx-3 -my-1 rounded-md text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer"
                    @click="$emit('close')"
                >
                    {{ item.label }}
                </a>
                <Link
                    href="/login"
                    class="block px-3 py-3 -mx-3 -my-1 rounded-md text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer"
                    @click="$emit('close')"
                >
                    Login
                </Link>
                <Link
                    href="/demo"
                    class="block w-full text-center px-4 py-3 rounded-md bg-primary-600 text-white text-sm font-semibold cursor-pointer"
                    @click="$emit('close')"
                >
                    Coba Demo
                </Link>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.nav-slide-enter-active,
.nav-slide-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.nav-slide-enter-from,
.nav-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
