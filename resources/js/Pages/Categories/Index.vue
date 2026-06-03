<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head><title>Категории - Энциклопедия</title></Head>

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="mb-8 pb-4 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-2">
                    &gt; ls /categories/
                </div>
                <h1 class="font-serif text-4xl font-bold text-ink-900 dark:text-paper mb-1">Категории</h1>
                <p class="font-mono text-sm text-ink-700 dark:text-ink-100">
                    <span class="text-brand-700 dark:text-brand-400">//</span> выберите интересующую вас область знаний · {{ categories.length }} разделов
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <Link
                    v-for="(category, idx) in categories"
                    :key="category.id"
                    :href="`/categories/${category.slug}`"
                    class="group flex flex-col bg-paper-50 dark:bg-ink-900 border-2 border-ink-900 dark:border-ink-200 shadow-hard-sm hover:shadow-hard-brand hover:-translate-y-0.5 hover:-translate-x-0.5 transition-all"
                >
                    <div class="flex items-center justify-between px-3 py-1 bg-ink-900 dark:bg-ink-200 text-paper dark:text-ink-900 font-mono text-[11px]">
                        <span>cat_{{ String(idx + 1).padStart(2, '0') }}.md</span>
                        <span>{{ category.articles_count ?? 0 }} {{ (category.articles_count ?? 0) === 1 ? 'статья' : 'статей' }}</span>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="font-mono text-[11px] text-brand-700 dark:text-brand-400 mb-1">
                            // /{{ category.slug }}
                        </div>
                        <h3 class="font-serif text-xl font-semibold text-ink-900 dark:text-paper group-hover:text-brand-700 dark:group-hover:text-brand-400 transition-colors mb-2">
                            {{ category.name }}
                        </h3>
                        <p v-if="category.description" class="text-sm text-ink-700 dark:text-ink-100 line-clamp-3 mb-3 flex-1">
                            {{ category.description }}
                        </p>
                        <div v-else class="flex-1"></div>

                        <div class="pt-2 border-t border-dashed border-ink-300 dark:border-ink-700 font-mono text-[11px] text-ink-700 dark:text-ink-100 flex items-center justify-between">
                            <span>
                                <span class="text-brand-700 dark:text-brand-400">articles:</span> {{ category.articles_count ?? 0 }}
                            </span>
                            <span class="text-brand-700 dark:text-brand-400 group-hover:underline underline-offset-2">
                                [open →]
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="categories.length === 0" class="text-center font-mono text-sm text-ink-700 dark:text-ink-100 py-16">
                <div class="text-4xl font-bold text-ink-300 dark:text-ink-700 mb-2">∅</div>
                // категории пока не добавлены
            </div>
        </div>
    </AppLayout>
</template>
