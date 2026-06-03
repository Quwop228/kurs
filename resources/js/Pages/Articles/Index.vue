<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    articles: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const categories = page.props.categories || [];

const selectedCategory = ref(props.filters?.category || '');
const selectedSort = ref(props.filters?.sort || 'latest');

const applyFilters = () => {
    const params = {};
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (selectedSort.value && selectedSort.value !== 'latest') params.sort = selectedSort.value;

    router.get('/articles', params, {
        preserveState: true,
        replace: true,
    });
};

watch([selectedCategory, selectedSort], () => {
    applyFilters();
});
</script>

<template>
    <Head><title>Статьи - Энциклопедия</title></Head>

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="mb-8 pb-4 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-2">
                    &gt; ls /articles/ --sort={{ selectedSort }}<span v-if="selectedCategory"> --category={{ selectedCategory }}</span>
                </div>
                <h1 class="font-serif text-4xl font-bold text-ink-900 dark:text-paper mb-1">Статьи</h1>
                <p class="font-mono text-sm text-ink-700 dark:text-ink-100">
                    <span class="text-brand-700 dark:text-brand-400">//</span> исследуйте нашу коллекцию знаний · {{ articles.total ?? articles.data?.length ?? 0 }} записей
                </p>
            </div>

            <div class="mb-8 bg-paper-50 dark:bg-ink-900 border-2 border-ink-900 dark:border-ink-200 shadow-hard-sm">
                <div class="px-4 py-1.5 bg-ink-900 dark:bg-ink-200 text-paper dark:text-ink-900 font-mono text-[11px] border-b-2 border-ink-900 dark:border-ink-200">
                    filters.conf
                </div>
                <div class="flex flex-wrap gap-4 p-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block font-mono text-[11px] uppercase tracking-wider text-ink-700 dark:text-ink-100 mb-1.5">
                            &gt; category=
                        </label>
                        <select
                            v-model="selectedCategory"
                            class="w-full font-mono border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 text-ink-900 dark:text-paper text-sm py-2 px-3 focus:ring-0 focus:border-brand-700 dark:focus:border-brand-400 transition"
                        >
                            <option value="">* (все категории)</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.slug"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block font-mono text-[11px] uppercase tracking-wider text-ink-700 dark:text-ink-100 mb-1.5">
                            &gt; sort=
                        </label>
                        <select
                            v-model="selectedSort"
                            class="w-full font-mono border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 text-ink-900 dark:text-paper text-sm py-2 px-3 focus:ring-0 focus:border-brand-700 dark:focus:border-brand-400 transition"
                        >
                            <option value="latest">latest (новые)</option>
                            <option value="popular">popular (популярные)</option>
                            <option value="rated">rated (лучшие)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ArticleCard
                    v-for="article in articles.data"
                    :key="article.id"
                    :article="article"
                />
            </div>

            <div v-if="articles.data && articles.data.length === 0" class="text-center font-mono text-sm text-ink-700 dark:text-ink-100 py-16">
                <div class="text-4xl font-bold text-ink-300 dark:text-ink-700 mb-2">404</div>
                // статьи не найдены
            </div>

            <Pagination v-if="articles.links" :links="articles.links" />
        </div>
    </AppLayout>
</template>
