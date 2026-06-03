<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    users: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU');
};

const updateRole = (userId, role) => {
    router.patch(`/admin/users/${userId}/role`, { role }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head><title>Управление пользователями - Энциклопедия</title></Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Управление пользователями
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Имя</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Роль</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Дата регистрации</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ user.name?.charAt(0)?.toUpperCase() }}
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ user.email }}
                                </td>
                                <td class="px-6 py-4">
                                    <select
                                        :value="user.role"
                                        @change="updateRole(user.id, $event.target.value)"
                                        :class="[
                                            'rounded-md border text-sm py-1 px-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition',
                                            user.role === 'admin'
                                                ? 'border-red-300 bg-red-50 text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-400'
                                                : user.role === 'editor'
                                                    ? 'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                                    : 'border-gray-300 bg-gray-50 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'
                                        ]"
                                    >
                                        <option value="reader">reader</option>
                                        <option value="editor">editor</option>
                                        <option value="admin">admin</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ formatDate(user.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="users.data && users.data.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500">
                        Пользователи не найдены.
                    </div>
                </div>

                <Pagination v-if="users.links" :links="users.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
