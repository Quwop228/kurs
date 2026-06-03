<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <Head><title>Личный кабинет - Энциклопедия</title></Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Личный кабинет
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-2xl font-bold text-white">
                            {{ user?.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                Добро пожаловать, {{ user?.name }}!
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    :class="[
                                        'px-2 py-0.5 text-xs font-medium rounded-full',
                                        user?.role === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
                                        user?.role === 'editor' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                    ]"
                                >
                                    {{ user?.role }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ user?.email }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        href="/favorites"
                        class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Избранное</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Ваши сохраненные статьи</p>
                        </div>
                    </Link>

                    <Link
                        href="/articles"
                        class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Все статьи</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Каталог энциклопедии</p>
                        </div>
                    </Link>

                    <Link
                        v-if="user?.role === 'admin' || user?.role === 'editor'"
                        href="/admin/articles"
                        class="flex items-center gap-4 p-5 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Админ-панель</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Управление контентом</p>
                        </div>
                    </Link>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Настройки профиля</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Обновите ваше имя, email или пароль</p>
                        </div>
                        <Link
                            href="/profile"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Перейти к профилю
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
