<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';

defineProps({
    latestArticles: {
        type: Array,
        default: () => [],
    },
    popularArticles: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const heroSearch = ref('');

let searchTimeout = null;
const doHeroSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (heroSearch.value.trim()) {
            router.get('/search', { q: heroSearch.value.trim() });
        }
    }, 500);
};

const submitHeroSearch = () => {
    if (heroSearch.value.trim()) {
        router.get('/search', { q: heroSearch.value.trim() });
    }
};
</script>

<template>
    <Head><title>Главная - Энциклопедия</title></Head>

    <AppLayout>
        <section class="relative border-b-2 border-ink-900 dark:border-brand-800 bg-paper dark:bg-ink-950 overflow-hidden">
            <div class="absolute inset-0 opacity-[0.08] dark:opacity-[0.12]" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 40px 40px;"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
                <div class="max-w-3xl">
                    <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-4">
                        &gt; cat /index.md
                    </div>

                    <h1 class="font-serif text-5xl sm:text-6xl lg:text-7xl font-bold text-ink-900 dark:text-paper mb-4 tracking-tight leading-none">
                        Энциклопедия<span class="text-brand-700 dark:text-brand-400">.</span>
                    </h1>

                    <p class="font-mono text-sm sm:text-base text-ink-700 dark:text-ink-100 mb-2">
                        <span class="text-brand-700 dark:text-brand-400">//</span> база знаний с ежедневными AI-обновлениями
                    </p>
                    <p class="font-mono text-sm sm:text-base text-ink-700 dark:text-ink-100 mb-8">
                        <span class="text-brand-700 dark:text-brand-400">//</span> интерактивные разборы, импорт из wikipedia, rss
                    </p>

                    <form @submit.prevent="submitHeroSearch" class="max-w-xl">
                        <label class="block font-mono text-xs text-ink-700 dark:text-ink-100 mb-2">
                            &gt; search --query=
                        </label>
                        <div class="flex">
                            <input
                                v-model="heroSearch"
                                @input="doHeroSearch"
                                type="text"
                                placeholder="что вы хотите узнать?"
                                class="flex-1 px-4 py-3 font-mono text-base border-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-800 text-ink-900 dark:text-paper placeholder-ink-200 dark:placeholder-ink-700 focus:ring-0 focus:border-brand-700 dark:focus:border-brand-400 shadow-hard focus:shadow-hard-brand transition-shadow"
                            />
                            <button type="submit" class="px-5 py-3 font-mono font-bold text-paper bg-brand-700 hover:bg-brand-800 border-2 border-l-0 border-ink-900 dark:border-ink-200 shadow-hard active:shadow-none active:translate-x-1 active:translate-y-1 transition-all">
                                enter →
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 font-mono text-xs text-ink-700 dark:text-ink-100">
                        <span><span class="text-brand-700 dark:text-brand-400">[*]</span> {{ latestArticles.length + popularArticles.length }}+ статей</span>
                        <span><span class="text-brand-700 dark:text-brand-400">[*]</span> {{ categories.length }} категорий</span>
                        <span><span class="text-brand-700 dark:text-brand-400">[*]</span> ai-powered</span>
                        <span><span class="text-brand-700 dark:text-brand-400">[*]</span> rss-feed</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="flex items-end justify-between mb-8 pb-4 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                <div>
                    <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-1">// section_01</div>
                    <h2 class="font-serif text-3xl font-bold text-ink-900 dark:text-paper">
                        Последние статьи
                    </h2>
                </div>
                <Link href="/articles" class="font-mono text-sm text-brand-700 dark:text-brand-400 hover:underline underline-offset-4 decoration-2">
                    [все статьи →]
                </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <ArticleCard
                    v-for="article in latestArticles"
                    :key="article.id"
                    :article="article"
                />
            </div>
            <p v-if="latestArticles.length === 0" class="text-center font-mono text-sm text-ink-300 dark:text-ink-700 py-8">
                // no articles yet
            </p>
        </section>

        <section class="bg-ink-900 dark:bg-ink-900/70 text-paper border-y-2 border-ink-900 dark:border-brand-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="flex items-end justify-between mb-8 pb-4 border-b-2 border-dashed border-brand-800 dark:border-ink-700">
                    <div>
                        <div class="font-mono text-xs text-brand-400 mb-1">// section_02 | sort=popular</div>
                        <h2 class="font-serif text-3xl font-bold text-paper">
                            Популярные статьи
                        </h2>
                    </div>
                    <Link href="/articles?sort=popular" class="font-mono text-sm text-brand-400 hover:underline underline-offset-4 decoration-2">
                        [смотреть все →]
                    </Link>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ArticleCard
                        v-for="article in popularArticles"
                        :key="article.id"
                        :article="article"
                    />
                </div>
                <p v-if="popularArticles.length === 0" class="text-center font-mono text-sm text-ink-200 py-8">
                    // no popular articles yet
                </p>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="flex items-end justify-between mb-8 pb-4 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                <div>
                    <div class="font-mono text-xs text-brand-700 dark:text-brand-400 mb-1">// section_03 | index</div>
                    <h2 class="font-serif text-3xl font-bold text-ink-900 dark:text-paper">
                        Категории
                    </h2>
                </div>
                <Link href="/categories" class="font-mono text-sm text-brand-700 dark:text-brand-400 hover:underline underline-offset-4 decoration-2">
                    [все категории →]
                </Link>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <Link
                    v-for="(category, idx) in categories"
                    :key="category.id"
                    :href="`/categories/${category.slug}`"
                    class="group flex flex-col p-4 bg-paper-50 dark:bg-ink-900 border-2 border-ink-900 dark:border-ink-200 shadow-hard-sm hover:shadow-hard-brand hover:-translate-y-0.5 hover:-translate-x-0.5 transition-all"
                >
                    <div class="flex items-center justify-between mb-2 font-mono text-[11px]">
                        <span class="text-brand-700 dark:text-brand-400">#{{ String(idx + 1).padStart(2, '0') }}</span>
                        <span class="text-ink-700 dark:text-ink-100">{{ category.articles_count ?? 0 }} {{ (category.articles_count ?? 0) === 1 ? 'статья' : 'статей' }}</span>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink-900 dark:text-paper group-hover:text-brand-700 dark:group-hover:text-brand-400 transition-colors mb-1">
                        {{ category.name }}
                    </h3>
                    <p v-if="category.description" class="text-sm text-ink-700 dark:text-ink-100 line-clamp-2">
                        {{ category.description }}
                    </p>
                </Link>
            </div>
        </section>
    </AppLayout>
</template>
