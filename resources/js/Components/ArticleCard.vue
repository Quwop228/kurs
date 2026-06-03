<script setup>
import { Link } from '@inertiajs/vue3';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
});

const formatDate = (date) => {
    const d = new Date(date);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
};

const averageRating = (ratings) => {
    if (!ratings || ratings.length === 0) return null;
    const sum = ratings.reduce((acc, r) => acc + r.value, 0);
    return (sum / ratings.length).toFixed(1);
};
</script>

<template>
    <article class="group bg-paper-50 dark:bg-ink-900 border-2 border-ink-900 dark:border-ink-200 shadow-hard-sm hover:shadow-hard hover:-translate-y-0.5 hover:-translate-x-0.5 transition-all flex flex-col">
        <div class="flex items-center justify-between px-3 py-1 bg-ink-900 dark:bg-ink-200 text-paper dark:text-ink-900 font-mono text-[11px]">
            <span class="truncate">article.md</span>
            <span class="flex gap-1">
                <span class="w-2 h-2 rounded-full bg-brand-400"></span>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span class="w-2 h-2 rounded-full bg-red-400"></span>
            </span>
        </div>

        <div class="p-4 flex flex-col flex-1">
            <div v-if="article.category" class="flex items-center gap-2 mb-3">
                <CategoryBadge :category="article.category" />
            </div>

            <h3 class="font-serif text-xl font-semibold leading-snug mb-2">
                <Link
                    :href="`/articles/${article.slug}`"
                    class="text-ink-900 dark:text-paper group-hover:text-brand-700 dark:group-hover:text-brand-400 transition-colors line-clamp-2"
                >
                    {{ article.title }}
                </Link>
            </h3>

            <p v-if="article.excerpt" class="text-sm text-ink-700 dark:text-ink-100 mb-3 line-clamp-3 flex-1">
                {{ article.excerpt }}
            </p>
            <div v-else class="flex-1"></div>

            <div v-if="article.tags && article.tags.length > 0" class="flex flex-wrap gap-1 mb-3">
                <TagBadge
                    v-for="tag in article.tags.slice(0, 3)"
                    :key="tag.id"
                    :tag="tag"
                />
                <span v-if="article.tags.length > 3" class="font-mono text-[11px] text-ink-700 dark:text-ink-100 self-center">
                    +{{ article.tags.length - 3 }}
                </span>
            </div>

            <div class="pt-3 border-t border-dashed border-ink-300 dark:border-ink-700 font-mono text-[11px] text-ink-700 dark:text-ink-100 space-y-1">
                <div class="flex items-center justify-between">
                    <span v-if="article.user" class="truncate">
                        <span class="text-brand-700 dark:text-brand-400">author:</span> {{ article.user.name }}
                    </span>
                    <span>
                        <span class="text-brand-700 dark:text-brand-400">date:</span> {{ formatDate(article.created_at) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span>
                        <span class="text-brand-700 dark:text-brand-400">views:</span> {{ article.views_count ?? 0 }}
                    </span>
                    <span v-if="averageRating(article.ratings)">
                        <span class="text-brand-700 dark:text-brand-400">rating:</span> {{ averageRating(article.ratings) }}/5 ★
                    </span>
                    <span v-else class="text-ink-300 dark:text-ink-700">
                        <span>rating:</span> —
                    </span>
                </div>
            </div>
        </div>
    </article>
</template>
