<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="`/categories/${category.slug}`"
        class="inline-flex items-center px-2 py-0.5 font-mono text-[11px] font-medium uppercase tracking-wider border border-brand-700 dark:border-brand-400 bg-brand-50 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300 hover:bg-brand-700 hover:text-paper dark:hover:bg-brand-400 dark:hover:text-ink-900 transition-colors"
    >
        {{ category.name }}
    </Link>
</template>
