<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    category: Object,
    articles: Object,
});
</script>

<template>
    <Head><title>{{ category.name }} - Энциклопедия</title></Head>

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
                <Link href="/" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Главная</Link>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <Link href="/categories" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Категории</Link>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 dark:text-gray-300">{{ category.name }}</span>
            </nav>

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ category.name }}</h1>
                <p v-if="category.description" class="text-gray-500 dark:text-gray-400">{{ category.description }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ArticleCard
                    v-for="article in articles.data"
                    :key="article.id"
                    :article="article"
                />
            </div>

            <p v-if="articles.data && articles.data.length === 0" class="text-center text-gray-400 dark:text-gray-500 py-12">
                В этой категории пока нет статей.
            </p>

            <Pagination v-if="articles.links" :links="articles.links" />
        </div>
    </AppLayout>
</template>
