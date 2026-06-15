<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    open: boolean;
    items: { label: string; href: string }[];
}>();

defineEmits<{ close: [] }>();
</script>

<template>
    <div
        v-if="open"
        class="md:hidden border-t border-neutral-100 bg-white"
    >
        <div class="px-4 py-4 space-y-2">
            <a
                v-for="item in items"
                :key="item.href"
                :href="item.href"
                class="block px-3 py-2 rounded-md text-sm font-medium text-neutral-700 hover:bg-neutral-50"
                @click="$emit('close')"
            >
                {{ item.label }}
            </a>
            <Link
                href="/demo"
                class="block w-full text-center mt-2 px-4 py-2 rounded-md bg-primary-600 text-white text-sm font-semibold"
                @click="$emit('close')"
            >
                Request Demo
            </Link>
        </div>
    </div>
</template>
