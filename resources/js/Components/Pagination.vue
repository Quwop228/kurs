<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-center gap-1 mt-8">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                    'px-3 py-2 text-sm rounded-lg transition-colors',
                    link.active
                        ? 'bg-indigo-600 text-white font-medium'
                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                ]"
                preserve-scroll
            />
            <span
                v-else
                v-html="link.label"
                class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600"
            />
        </template>
    </nav>
</template>
