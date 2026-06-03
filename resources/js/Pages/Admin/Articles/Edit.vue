<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TipTapEditor from '@/Components/TipTapEditor.vue';

const props = defineProps({
    article: Object,
    categories: {
        type: Array,
        default: () => [],
    },
    tags: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: props.article.title || '',
    category_id: props.article.category_id || '',
    tags: props.article.tags?.map(t => t.id) || [],
    excerpt: props.article.excerpt || '',
    content: props.article.content || '',
    is_published: props.article.is_published ?? false,
});

const submit = () => {
    form.put(`/admin/articles/${props.article.slug}`);
};

const wikiUrl = ref('');
const wikiLoading = ref(false);
const wikiError = ref('');
const wikiStatus = ref('');

const importFromWikipedia = async () => {
    if (!wikiUrl.value) return;
    wikiLoading.value = true;
    wikiError.value = '';
    wikiStatus.value = 'Загрузка статьи из Википедии...';

    try {
        const isRu = wikiUrl.value.includes('ru.wikipedia.org');
        if (!isRu) {
            wikiStatus.value = 'Загрузка и перевод через AI... Это может занять время.';
        }

        const response = await axios.post('/admin/wikipedia-import', { url: wikiUrl.value });
        const data = response.data;

        form.title = data.title;
        form.content = data.content;
        form.excerpt = data.excerpt;

        wikiStatus.value = data.translated
            ? `Импортировано и переведено с ${data.source_lang}.wikipedia.org`
            : 'Импортировано из Википедии';
    } catch (e) {
        wikiError.value = e.response?.data?.error || 'Ошибка при импорте. Проверьте ссылку.';
        wikiStatus.value = '';
    } finally {
        wikiLoading.value = false;
    }
};

const toggleTag = (tagId) => {
    const index = form.tags.indexOf(tagId);
    if (index > -1) {
        form.tags.splice(index, 1);
    } else {
        form.tags.push(tagId);
    }
};
</script>

<template>
    <Head><title>Редактировать: {{ article.title }} - Энциклопедия</title></Head>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link href="/admin/articles" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Редактировать статью
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.09 13.119c-.936 1.932-2.217 4.548-2.853 5.728-.616 1.074-1.127.931-1.532.029-1.406-3.321-4.293-9.144-5.651-12.409-.251-.601-.441-.987-.619-1.139-.181-.15-.554-.24-1.122-.271C.103 5.033 0 4.982 0 4.898v-.455l.052-.045c.924-.005 5.401 0 5.401 0l.051.045v.434c0 .119-.075.176-.225.176l-.564.031c-.485.029-.727.164-.727.407 0 .09.053.235.157.437l3.992 8.841.075.007 2.476-5.32-.359-.733c-.578-1.181-1.056-2.109-1.229-2.398-.236-.395-.47-.577-.934-.625l-.161-.013c-.151 0-.225-.059-.225-.176v-.434l.049-.045h4.179l.051.045v.434c0 .119-.075.176-.225.176-.553.024-.834.09-.834.271 0 .098.064.268.188.51l1.658 3.453h.06l1.665-3.407c.098-.202.147-.37.147-.502 0-.209-.289-.289-.867-.313-.15 0-.225-.059-.225-.176v-.434l.048-.045c.924-.005 3.823 0 3.823 0l.051.045v.434c0 .119-.075.176-.225.176-.698.063-1.088.27-1.455.937l-2.344 4.559h-.045l.601 1.227 2.963 6.239c.34.678.705.737 1.09.151.667-1.078 2.016-3.975 2.907-5.88l1.607-3.47c.188-.397.283-.678.283-.844 0-.194-.338-.282-1.008-.313-.151 0-.226-.059-.226-.176v-.434l.05-.045h3.982l.051.045v.434c0 .119-.075.176-.225.176-.609.039-1.065.245-1.371.842-.9 1.767-4.088 8.543-5.604 11.579-.545 1.074-1.042.957-1.423.088-.508-1.157-2.379-4.864-2.926-6.069z"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Заменить содержимое из Википедии</h3>
                    </div>
                    <div class="flex gap-2">
                        <input
                            v-model="wikiUrl"
                            type="url"
                            placeholder="https://en.wikipedia.org/wiki/Solar_System"
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :disabled="wikiLoading"
                        />
                        <button
                            type="button"
                            @click="importFromWikipedia"
                            :disabled="wikiLoading || !wikiUrl"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 dark:bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-800 dark:hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg v-if="wikiLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ wikiLoading ? 'Загрузка...' : 'Импортировать' }}
                        </button>
                    </div>
                    <p v-if="wikiStatus" class="mt-2 text-sm text-green-600 dark:text-green-400">{{ wikiStatus }}</p>
                    <p v-if="wikiError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ wikiError }}</p>
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                        Поддерживается любой язык. Статьи не на русском будут переведены через AI.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="title" value="Название" />
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div>
                            <InputLabel for="category_id" value="Категория" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="" disabled>Выберите категорию</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div>
                            <InputLabel value="Теги" />
                            <div class="mt-2 flex flex-wrap gap-2">
                                <label
                                    v-for="tag in tags"
                                    :key="tag.id"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium cursor-pointer transition-all border',
                                        form.tags.includes(tag.id)
                                            ? 'border-indigo-300 bg-indigo-50 text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'
                                            : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-600'
                                    ]"
                                >
                                    <input
                                        type="checkbox"
                                        :value="tag.id"
                                        :checked="form.tags.includes(tag.id)"
                                        @change="toggleTag(tag.id)"
                                        class="hidden"
                                    />
                                    <svg v-if="form.tags.includes(tag.id)" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ tag.name }}
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.tags" />
                        </div>

                        <div>
                            <InputLabel for="excerpt" value="Краткое описание" />
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.excerpt" />
                        </div>

                        <div>
                            <InputLabel value="Содержание" />
                            <div class="mt-1">
                                <TipTapEditor v-model="form.content" placeholder="Напишите содержание статьи..." />
                            </div>
                            <InputError class="mt-2" :message="form.errors.content" />
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.is_published"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Опубликовать</span>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link href="/admin/articles" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                                Отмена
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
