<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    articles: Object,
});
</script>

<template>
    <Head><title>Избранное - Энциклопедия</title></Head>

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="mb-8 pb-4 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-2">
                    &gt; ls /favorites/ --user=me
                </div>
                <h1 class="font-serif text-4xl font-bold text-ink-900 dark:text-paper mb-1">Избранное</h1>
                <p class="font-mono text-sm text-ink-700 dark:text-ink-100">
                    <span class="text-brand-700 dark:text-brand-400">//</span> статьи, отмеченные как важное
                    <span v-if="articles?.total">
                        · <span class="font-bold text-ink-900 dark:text-paper">{{ articles.total }}</span>
                    </span>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ArticleCard
                    v-for="article in articles.data"
                    :key="article.id"
                    :article="article"
                />
            </div>

            <div v-if="articles.data && articles.data.length === 0" class="text-center py-16 font-mono text-sm">
                <div class="text-5xl font-bold text-ink-300 dark:text-ink-700 mb-2 select-none">♡</div>
                <p class="text-ink-700 dark:text-ink-100 mb-6">
                    // у вас пока нет избранных статей
                </p>
                <Link
                    href="/articles"
                    class="inline-flex items-center gap-2 px-5 py-3 font-mono font-bold text-paper bg-brand-700 hover:bg-brand-800 border-2 border-ink-900 dark:border-ink-200 shadow-hard active:shadow-none active:translate-x-1 active:translate-y-1 transition-all"
                >
                    [ к статьям → ]
                </Link>
            </div>

            <Pagination v-if="articles.links" :links="articles.links" />
        </div>
    </AppLayout>
</template>
