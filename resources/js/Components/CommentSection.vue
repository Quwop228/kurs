<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    comments: {
        type: Array,
        default: () => [],
    },
    articleSlug: {
        type: String,
        required: true,
    },
});

const page = usePage();
const user = page.props.auth?.user;

const form = useForm({
    content: '',
});

const submitComment = () => {
    form.post(`/articles/${props.articleSlug}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const deleteComment = (commentId) => {
    if (confirm('Удалить комментарий?')) {
        router.delete(`/comments/${commentId}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const canDelete = (comment) => {
    if (!user) return false;
    return user.id === comment.user_id || user.role === 'admin';
};
</script>

<template>
    <div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
            Комментарии ({{ comments.length }})
        </h3>

        <div v-if="user" class="mb-8">
            <form @submit.prevent="submitComment">
                <textarea
                    v-model="form.content"
                    rows="3"
                    placeholder="Написать комментарий..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none transition"
                ></textarea>
                <div class="flex items-center justify-between mt-2">
                    <p v-if="form.errors.content" class="text-sm text-red-500">{{ form.errors.content }}</p>
                    <div class="ml-auto">
                        <button
                            type="submit"
                            :disabled="form.processing || !form.content.trim()"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ form.processing ? 'Отправка...' : 'Отправить' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div v-else class="mb-8 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg text-sm text-gray-500 dark:text-gray-400 text-center">
            <a href="/login" class="text-indigo-600 dark:text-indigo-400 hover:underline">Войдите</a>, чтобы оставить комментарий.
        </div>

        <div class="space-y-4">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg"
            >
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            {{ comment.user?.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ comment.user?.name }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">
                                {{ formatDate(comment.created_at) }}
                            </span>
                        </div>
                    </div>
                    <button
                        v-if="canDelete(comment)"
                        @click="deleteComment(comment.id)"
                        class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors"
                        title="Удалить"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap ml-10">
                    {{ comment.content }}
                </p>
            </div>

            <p v-if="comments.length === 0" class="text-center text-gray-400 dark:text-gray-500 py-6 text-sm">
                Пока нет комментариев. Будьте первым!
            </p>
        </div>
    </div>
</template>
