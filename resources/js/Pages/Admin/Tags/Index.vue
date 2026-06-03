<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    tags: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
});

const submitCreate = () => {
    form.post('/admin/tags', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const deleteTag = (id) => {
    if (confirm('Удалить тег?')) {
        router.delete(`/admin/tags/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head><title>Управление тегами - Энциклопедия</title></Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Управление тегами
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Добавить тег</h3>
                    <form @submit.prevent="submitCreate" class="flex gap-3">
                        <div class="flex-1">
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Название тега"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                required
                            />
                            <InputError class="mt-1" :message="form.errors.name" />
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 transition-colors whitespace-nowrap"
                        >
                            Добавить
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Название</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Slug</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Статей</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="tag in tags" :key="tag.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ tag.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                    {{ tag.slug }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ tag.articles_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="deleteTag(tag.id)"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium transition-colors"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="tags.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500">
                        Теги пока не созданы.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
