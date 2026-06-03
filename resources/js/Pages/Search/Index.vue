<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    articles: {
        type: [Object, Array],
        default: () => ({ data: [] }),
    },
    query: {
        type: String,
        default: '',
    },
});

const searchQuery = ref(props.query);
const searchInput = ref(null);

let searchTimeout = null;

const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/search', searchQuery.value.trim() ? { q: searchQuery.value.trim() } : {}, {
            preserveState: true,
            replace: true,
        });
    }, 500);
};

const submit = () => {
    clearTimeout(searchTimeout);
    router.get('/search', searchQuery.value.trim() ? { q: searchQuery.value.trim() } : {}, {
        preserveState: true,
        replace: true,
    });
};

onMounted(() => {
    searchInput.value?.focus();
});

const hasResults = () => {
    if (Array.isArray(props.articles)) return props.articles.length > 0;
    return props.articles?.data?.length > 0;
};

const articleData = () => {
    if (Array.isArray(props.articles)) return props.articles;
    return props.articles?.data || [];
};

const resultCount = () => {
    if (Array.isArray(props.articles)) return props.articles.length;
    return props.articles?.total ?? props.articles?.data?.length ?? 0;
};
</script>

<template>
    <Head><title>Поиск{{ query ? `: ${query}` : '' }} - Энциклопедия</title></Head>

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="max-w-2xl mx-auto mb-10">
                <div class="mb-6 pb-4 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                    <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-2">
                        &gt; grep -r --query
                    </div>
                    <h1 class="font-serif text-4xl font-bold text-ink-900 dark:text-paper">Поиск</h1>
                </div>

                <form @submit.prevent="submit">
                    <label class="block font-mono text-xs text-ink-700 dark:text-ink-100 mb-2">
                        &gt; search --query=
                    </label>
                    <div class="flex">
                        <input
                            ref="searchInput"
                            v-model="searchQuery"
                            @input="doSearch"
                            type="text"
                            placeholder="введите запрос..."
                            class="flex-1 px-4 py-3 font-mono text-base border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 text-ink-900 dark:text-paper placeholder-ink-200 dark:placeholder-ink-700 focus:ring-0 focus:border-brand-700 dark:focus:border-brand-400 shadow-hard focus:shadow-hard-brand transition-shadow"
                        />
                        <button type="submit" class="px-5 py-3 font-mono font-bold text-paper bg-brand-700 hover:bg-brand-800 border-2 border-l-0 border-ink-900 dark:border-ink-200 shadow-hard active:shadow-none active:translate-x-1 active:translate-y-1 transition-all">
                            enter →
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="query">
                <div class="mb-6 font-mono text-sm text-ink-700 dark:text-ink-100">
                    <span class="text-brand-700 dark:text-brand-400">//</span>
                    найдено <span class="font-bold text-ink-900 dark:text-paper">{{ resultCount() }}</span>
                    по запросу «<span class="font-bold text-ink-900 dark:text-paper">{{ query }}</span>»
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ArticleCard
                        v-for="article in articleData()"
                        :key="article.id"
                        :article="article"
                    />
                </div>

                <div v-if="!hasResults()" class="text-center py-16 font-mono text-sm">
                    <div class="text-5xl font-bold text-ink-300 dark:text-ink-700 mb-2">∅</div>
                    <p class="text-ink-700 dark:text-ink-100">
                        // ничего не найдено. попробуйте изменить запрос
                    </p>
                </div>

                <Pagination v-if="articles.links" :links="articles.links" />
            </div>

            <div v-else class="text-center py-16 font-mono text-sm text-ink-700 dark:text-ink-100">
                <div class="text-5xl text-ink-300 dark:text-ink-700 mb-2 select-none">&gt;_</div>
                <p>// введите запрос для поиска статей</p>
            </div>
        </div>
    </AppLayout>
</template>
