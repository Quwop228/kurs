<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';
import StarRating from '@/Components/StarRating.vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import CommentSection from '@/Components/CommentSection.vue';
import DailyUpdateBlock from '@/Components/DailyUpdateBlock.vue';
import InteractiveMode from '@/Components/InteractiveMode.vue';

const props = defineProps({
    article: Object,
    isFavorited: {
        type: Boolean,
        default: false,
    },
    interactiveExplanation: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const user = page.props.auth?.user;
const isAdmin = computed(() => user?.role === 'admin');

const interactiveMode = ref(false);

const averageRating = computed(() => {
    if (!props.article.ratings || props.article.ratings.length === 0) return 0;
    const sum = props.article.ratings.reduce((acc, r) => acc + r.value, 0);
    return sum / props.article.ratings.length;
});

const userRating = computed(() => {
    if (!user || !props.article.ratings) return 0;
    const found = props.article.ratings.find(r => r.user_id === user.id);
    return found ? found.value : 0;
});

const latestDailyUpdate = computed(() => {
    if (!props.article.daily_updates || props.article.daily_updates.length === 0) return null;
    return props.article.daily_updates[0];
});

const formatDate = (date) => {
    const d = new Date(date);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

const submitRating = (value) => {
    router.post(`/articles/${props.article.slug}/rating`, { value }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head><title>{{ article.title }} - Энциклопедия</title></Head>

    <AppLayout>
        <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <nav class="font-mono text-xs text-ink-700 dark:text-ink-100 mb-6 flex items-center gap-1 flex-wrap">
                <span class="text-brand-700 dark:text-brand-400">/</span>
                <Link href="/" class="hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-2">home</Link>
                <span class="text-brand-700 dark:text-brand-400">/</span>
                <Link href="/articles" class="hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-2">articles</Link>
                <template v-if="article.category">
                    <span class="text-brand-700 dark:text-brand-400">/</span>
                    <Link :href="`/categories/${article.category.slug}`" class="hover:text-brand-700 dark:hover:text-brand-400 hover:underline underline-offset-2">
                        {{ article.category.slug }}
                    </Link>
                </template>
                <span class="text-brand-700 dark:text-brand-400">/</span>
                <span class="text-ink-900 dark:text-paper truncate">{{ article.slug }}</span>
            </nav>

            <header class="mb-8 pb-6 border-b-2 border-dashed border-ink-300 dark:border-ink-700">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <CategoryBadge v-if="article.category" :category="article.category" />
                    <TagBadge
                        v-for="tag in article.tags"
                        :key="tag.id"
                        :tag="tag"
                    />
                </div>

                <h1 class="font-serif text-4xl sm:text-5xl font-bold text-ink-900 dark:text-paper mb-5 leading-tight">
                    {{ article.title }}
                </h1>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 font-mono text-xs text-ink-700 dark:text-ink-100 mb-5">
                    <span v-if="article.user">
                        <span class="text-brand-700 dark:text-brand-400">author:</span> {{ article.user.name }}
                    </span>
                    <span>
                        <span class="text-brand-700 dark:text-brand-400">published:</span> {{ formatDate(article.created_at) }}
                    </span>
                    <span>
                        <span class="text-brand-700 dark:text-brand-400">views:</span> {{ article.views_count ?? 0 }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <StarRating
                        v-if="user"
                        :model-value="userRating"
                        :average="averageRating"
                        :count="article.ratings?.length ?? 0"
                        @update:model-value="submitRating"
                    />
                    <StarRating
                        v-else
                        :readonly="true"
                        :average="averageRating"
                        :count="article.ratings?.length ?? 0"
                    />
                    <FavoriteButton
                        v-if="user"
                        :article-slug="article.slug"
                        :is-favorited="isFavorited"
                    />

                    <button
                        @click="interactiveMode = !interactiveMode"
                        :class="[
                            'inline-flex items-center gap-2 px-3 py-1.5 font-mono text-sm font-bold border-2 transition-all',
                            interactiveMode
                                ? 'border-ink-900 dark:border-paper bg-brand-700 text-paper shadow-hard-sm'
                                : 'border-ink-900 dark:border-paper bg-paper-50 dark:bg-ink-900 text-ink-900 dark:text-paper shadow-hard-sm hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-hard'
                        ]"
                    >
                        <span>[ai]</span>
                        <span>{{ interactiveMode ? 'выйти из разбора' : 'интерактивный разбор' }}</span>
                    </button>
                </div>
            </header>

            <div v-if="interactiveMode" class="mb-10">
                <InteractiveMode
                    :article-slug="article.slug"
                    :article-id="article.id"
                    :explanation="interactiveExplanation"
                    :is-admin="isAdmin"
                    @close="interactiveMode = false"
                />
            </div>

            <div v-else
                class="prose prose-lg dark:prose-invert max-w-none font-serif prose-headings:font-serif prose-headings:text-ink-900 dark:prose-headings:text-paper prose-a:text-brand-700 dark:prose-a:text-brand-400 prose-a:no-underline hover:prose-a:underline prose-a:decoration-2 prose-a:underline-offset-4 prose-strong:text-ink-900 dark:prose-strong:text-paper prose-code:font-mono prose-code:text-brand-700 dark:prose-code:text-brand-400 prose-blockquote:border-l-brand-700 prose-blockquote:not-italic prose-img:border-2 prose-img:border-ink-900 dark:prose-img:border-ink-200 mb-10"
                v-html="article.content"
            ></div>

            <div class="mb-10">
                <DailyUpdateBlock
                    :daily-update="latestDailyUpdate"
                    :article-slug="article.slug"
                />
            </div>

            <div class="font-mono text-xs text-ink-300 dark:text-ink-700 text-center mb-10 select-none">
                ═══════════════════════════════════════════════
            </div>

            <CommentSection
                :comments="article.comments || []"
                :article-slug="article.slug"
            />
        </article>
    </AppLayout>
</template>
