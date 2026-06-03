<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    tag: Object,
    articles: Object,
});
</script>

<template>
    <Head><title>#{{ tag.name }} - Энциклопедия</title></Head>

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
                <Link href="/" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Главная</Link>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 dark:text-gray-300">#{{ tag.name }}</span>
            </nav>

            <div class="mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-violet-100 dark:bg-violet-900/30 rounded-full mb-4">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <span class="text-lg font-semibold text-violet-700 dark:text-violet-300">{{ tag.name }}</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Статьи с тегом #{{ tag.name }}
                </h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ArticleCard
                    v-for="article in articles.data"
                    :key="article.id"
                    :article="article"
                />
            </div>

            <p v-if="articles.data && articles.data.length === 0" class="text-center text-gray-400 dark:text-gray-500 py-12">
                Статьи с этим тегом не найдены.
            </p>

            <Pagination v-if="articles.links" :links="articles.links" />
        </div>
    </AppLayout>
</template>
