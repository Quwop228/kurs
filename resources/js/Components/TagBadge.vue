<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    tag: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="`/tags/${tag.slug}`"
        class="inline-flex items-center px-1.5 py-0.5 font-mono text-[11px] text-ink-700 dark:text-ink-100 border border-dashed border-ink-300 dark:border-ink-700 hover:border-brand-700 hover:text-brand-700 dark:hover:border-brand-400 dark:hover:text-brand-400 transition-colors"
    >
        #{{ tag.name }}
    </Link>
</template>
