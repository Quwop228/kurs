<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const editingId = ref(null);
const editForm = useForm({
    name: '',
    description: '',
});

const createForm = useForm({
    name: '',
    description: '',
});

const startEdit = (category) => {
    editingId.value = category.id;
    editForm.name = category.name;
    editForm.description = category.description || '';
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const submitCreate = () => {
    createForm.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const submitEdit = (id) => {
    editForm.put(`/admin/categories/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

const deleteCategory = (id) => {
    if (confirm('Удалить категорию? Все связанные статьи потеряют привязку.')) {
        router.delete(`/admin/categories/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head><title>Управление категориями - Энциклопедия</title></Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Управление категориями
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Добавить категорию</h3>
                    <form @submit.prevent="submitCreate" class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input
                                v-model="createForm.name"
                                type="text"
                                placeholder="Название"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                required
                            />
                            <InputError class="mt-1" :message="createForm.errors.name" />
                        </div>
                        <div class="flex-1">
                            <input
                                v-model="createForm.description"
                                type="text"
                                placeholder="Описание (необязательно)"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            />
                            <InputError class="mt-1" :message="createForm.errors.description" />
                        </div>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Описание</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Статей</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <template v-if="editingId === category.id">
                                    <td class="px-6 py-3">
                                        <input
                                            v-model="editForm.name"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        />
                                    </td>
                                    <td class="px-6 py-3">
                                        <input
                                            v-model="editForm.description"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        />
                                    </td>
                                    <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                        {{ category.articles_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="submitEdit(category.id)" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 text-sm font-medium transition-colors">
                                                Сохранить
                                            </button>
                                            <button @click="cancelEdit" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm transition-colors">
                                                Отмена
                                            </button>
                                        </div>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                        {{ category.name }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ category.description || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ category.articles_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="startEdit(category)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium transition-colors">
                                                Редактировать
                                            </button>
                                            <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium transition-colors">
                                                Удалить
                                            </button>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="categories.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500">
                        Категории пока не созданы.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
