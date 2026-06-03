<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    articles: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU');
};

const deleteArticle = (slug) => {
    if (confirm('Вы уверены, что хотите удалить эту статью?')) {
        router.delete(`/admin/articles/${slug}`);
    }
};
</script>

<template>
    <Head><title>Управление статьями - Энциклопедия</title></Head>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Управление статьями
                </h2>
                <Link
                    href="/admin/articles/create"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Создать статью
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Название
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Категория
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Автор
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Статус
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Просмотры
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Дата
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="article in articles.data"
                                    :key="article.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <Link
                                            :href="`/articles/${article.slug}`"
                                            class="text-gray-900 dark:text-gray-100 font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                        >
                                            {{ article.title }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ article.category?.name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ article.user?.name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs font-medium rounded-full',
                                                article.is_published
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                            ]"
                                        >
                                            {{ article.is_published ? 'Опубликована' : 'Черновик' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ article.views_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ formatDate(article.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="`/admin/articles/${article.slug}/edit`"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium transition-colors"
                                            >
                                                Редактировать
                                            </Link>
                                            <button
                                                @click="deleteArticle(article.slug)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium transition-colors"
                                            >
                                                Удалить
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="articles.data && articles.data.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500">
                        Статьи пока не созданы.
                    </div>
                </div>

                <Pagination v-if="articles.links" :links="articles.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
